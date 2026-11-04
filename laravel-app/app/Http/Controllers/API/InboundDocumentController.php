<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InboundDocument;
use App\Models\InboundDocumentAssignment;
use App\Models\InboundDocumentMovement;
use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\InboundSenderOrganization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\TelegramService;
use App\Services\StorageService;

class InboundDocumentController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Helper: Category to DG Prefix mapping
     */
    protected function getCategoryPrefix(string $category): string
    {
        return match ($category) {
            'COMPANY' => 'AA',               // ក្រុមហ៊ុន AA001/26
            'MEF' => 'E',                   // ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ E001/26
            'FSA_REGULATOR' => 'NF',         // អ.ស.ហ. និងនិយ័តករ NF001/26
            'DEPT_GENERAL_AFFAIRS' => 'A',   // នាយកដ្ឋានកិច្ចការទូទៅ A001/26
            'DEPT_REGISTRATION' => 'R',      // នាយកដ្ឋានចុះបញ្ជី R001/26
            'DEPT_RESEARCH' => 'T',          // នាយកដ្ឋានស្រាវជ្រាវ T001/26
            'DEPT_LEGAL' => 'L',             // នាយកដ្ឋានគតិយុត្ត L001/26
            'PROJECT_ACSEP' => 'AS',         // គម្រោង ACSEP AS001/26
            default => 'O',                  // ផ្សេងៗ
        };
    }

    /**
     * 1. គណនាលេខចូលបន្ទាប់ដោយស្វ័យប្រវត្តិ (Generate Next Inbound Number)
     */
    public function generateNextNumber(Request $request)
    {
        $type = $request->input('type', 'general'); // 'general' ឬ 'dg'
        $yy = date('y'); // ឧ. '26'

        if ($type === 'general') {
            // ទម្រង់អ្នកទទួលឯកសារ: 001/26, 002/26, ...
            $maxSeq = InboundDocument::where('general_inbound_year', $yy)->max('general_inbound_seq') ?? 0;
            $nextSeq = (int) $maxSeq + 1;
            $formatted = sprintf('%03d/%s', $nextSeq, $yy);

            return response()->json([
                'type' => 'general',
                'year' => $yy,
                'next_seq' => $nextSeq,
                'formatted_number' => $formatted,
            ]);
        }

        // ទម្រង់ជំនួយការអគ្គនាយក: AA001/26, E001/26, NF001/26, A001/26...
        $category = $request->input('category', 'COMPANY');
        $prefix = $this->getCategoryPrefix($category);

        $maxSeq = InboundDocument::where('dg_inbound_prefix', $prefix)
            ->where('dg_inbound_year', $yy)
            ->max('dg_inbound_seq') ?? 0;
        $nextSeq = (int) $maxSeq + 1;
        $formatted = sprintf('%s%03d/%s', $prefix, $nextSeq, $yy);

        return response()->json([
            'type' => 'dg',
            'category' => $category,
            'prefix' => $prefix,
            'year' => $yy,
            'next_seq' => $nextSeq,
            'formatted_number' => $formatted,
        ]);
    }

    /**
     * ជម្រើសសម្រាប់ Dispatch (នាយកដ្ឋាន ការិយាល័យ មន្ត្រី និងក្រុមការងារ)
     */
    public function recipientsOptions(Request $request)
    {
        $departments = \App\Models\Department::orderBy('id')->get(['id', 'name_kh', 'name_en']);
        $offices = \App\Models\Office::orderBy('id')->get(['id', 'department_id', 'name_kh', 'name_en']);
        $users = User::whereNotIn('users.status', ['DISABLED', 'SUSPENDED'])
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->with(['position:id,title_kh,title_en,level', 'department:id,name_kh', 'office:id,name_kh'])
            ->orderByRaw('COALESCE(positions.level, 999) ASC')
            ->orderBy('users.name_kh', 'ASC')
            ->select('users.id', 'users.name', 'users.name_kh', 'users.name_en', 'users.department_id', 'users.office_id', 'users.position_id', 'users.email', 'users.profile_image')
            ->get();

        $workingGroups = \App\Models\WorkingGroup::where('status', 'ACTIVE')
            ->with([
                'members' => function ($mq) {
                    $mq->where('status', 'ACTIVE')
                       ->orderBy('order_index', 'asc')
                       ->with('user:id,name,name_kh,email,profile_image,position_id');
                },
                'members.user.position:id,title_kh,level',
            ])
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $viewer = $request->user();
        $myConfig = $viewer ? \App\Models\DocumentWorkflowConfig::where('user_id', $viewer->id)->first() : null;

        return response()->json([
            'departments' => $departments,
            'offices' => $offices,
            'users' => $users,
            'working_groups' => $workingGroups,
            'my_workflow_config' => $myConfig,
        ]);
    }

    /**
     * 2. បញ្ជីឯកសារចូល (Index with Filters & Scopes)
     */
    public function index(Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isReceptionist = $isAdmin || $viewer->hasPermission('inbound-documents-receptionist');
        $isAssistant = $isAdmin || $viewer->hasPermission('inbound-documents-assistant');
        $isDg = $isAdmin || ($viewer->position && (int) $viewer->position->level === 1);

        $scope = $request->input('scope', 'all');

        // Security check for restricted desk scopes
        if ($scope === 'reception' && !$isReceptionist) {
            $scope = 'my_todo';
        }
        if ($scope === 'assistant_inbox' && !$isAssistant) {
            $scope = 'my_todo';
        }
        if ($scope === 'dg_inbox' && !$isDg) {
            $scope = 'my_todo';
        }

        $query = InboundDocument::with([
            'registeredByUser:id,name,name_kh,email,profile_image',
            'assistant:id,name,name_kh,email,profile_image',
            'targetDepartment:id,name_kh',
            'targetOffice:id,name_kh',
            'targetUser:id,name,name_kh,email,profile_image',
            'acknowledgedByUser:id,name,name_kh,profile_image',
            'latestResponse.currentApprover:id,name,name_kh,profile_image',
            'latestResponse.draftedByUser:id,name,name_kh,profile_image',
            'latestResponse.approvedByUser:id,name,name_kh,profile_image',
            'assignments:id,inbound_document_id,target_type,department_id,office_id,user_id,working_group_id,status,instruction,assigned_by',
            'assignments.department:id,name_kh',
            'assignments.office:id,name_kh',
            'assignments.user:id,name,name_kh,profile_image',
            'assignments.workingGroup:id,name_kh,name_en,code',
            'assignments.workingGroup.members:id,working_group_id,user_id,role,status',
            'assignments.workingGroup.members.user:id,name,name_kh,profile_image',
        ]);

        // 3-Status Unified Tabs (Doing, In Progress, Complete)
        $tab = strtolower($request->input('tab', ''));
        if ($tab === 'complete' || $tab === 'completed') {
            $query->where('status', 'COMPLETED');
        } elseif ($tab === 'in_progress') {
            $query->where('status', '!=', 'COMPLETED')
                ->where(function ($q) use ($viewer, $isDg, $isAssistant) {
                    if ($isDg) {
                        $q->whereNotNull('dispatched_at');
                    } elseif ($isAssistant) {
                        $q->where(fn($sub) => $sub->whereNotNull('assistant_reviewed_at')->orWhereNotNull('dispatched_at'));
                    } else {
                        $q->where('registered_by', $viewer->id)
                          ->orWhereHas('assignments', fn($aq) => $aq->where('assigned_by', $viewer->id)->whereIn('status', ['PENDING', 'FORWARDED']));
                    }
                });
        } elseif ($tab === 'doing') {
            $query->where('status', '!=', 'COMPLETED')
                ->where(function ($q) use ($viewer, $isDg, $isAssistant) {
                    if ($isDg) {
                        $q->whereNull('dispatched_at');
                    } elseif ($isAssistant) {
                        $q->whereNull('dispatched_at')->whereNull('assistant_reviewed_at');
                    } else {
                        $q->where('target_user_id', $viewer->id)
                          ->orWhereHas('assignments', function ($aq) use ($viewer) {
                              $aq->where(function ($sub) use ($viewer) {
                                  $sub->where('user_id', $viewer->id);
                                  if ($viewer->office_id) $sub->orWhere('office_id', $viewer->office_id);
                                  if ($viewer->department_id) $sub->orWhere('department_id', $viewer->department_id);
                              })->where('status', 'PENDING')
                                ->where('assigned_by', '!=', $viewer->id);
                          });
                    }
                });
        } elseif ($scope === 'pending') {
            $this->applyPendingScope($query, $viewer);
        } elseif ($scope === 'doing') {
            $this->applyDoingScope($query, $viewer);
        } elseif ($scope === 'completed' || $scope === 'complete') {
            $query->where('status', 'COMPLETED');
        } elseif ($scope === 'reception') {
            $query->where('status', 'RECEPTION_DRAFT');
        } elseif ($scope === 'assistant_inbox') {
            $query->whereIn('status', ['SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG', 'DG_ANNOTATED']);
        } elseif ($scope === 'dg_inbox') {
            $query->where('status', 'SUBMITTED_TO_DG');
        } elseif ($scope === 'my_todo' || $scope === 'assigned_to_me') {
            $this->scopeMyTodo($query, $viewer);
        } elseif ($scope === 'my_done') {
            $this->scopeMyDone($query, $viewer);
        } elseif ($scope === 'my_unit') {
            $this->scopeMyUnit($query, $viewer);
        } elseif ($scope === 'response_workflow') {
            $query->where(function ($q) use ($viewer) {
                $q->where('status', 'IN_RESPONSE_PROGRESS')
                    ->orWhereHas('responses', function ($rq) use ($viewer) {
                        $rq->where('current_approver_id', $viewer->id)
                           ->orWhere('drafted_by', $viewer->id);
                    });
            });
        }

        // CRITICAL: Always enforce accessible boundary for non-privileged users across all queries
        $this->scopeAccessibleByViewer($query, $viewer);

        // Filtering by status
        if ($request->filled('status')) {
            $statusVal = strtolower($request->input('status'));
            if ($statusVal === 'pending') {
                $this->applyPendingScope($query, $viewer);
            } elseif ($statusVal === 'doing') {
                $this->applyDoingScope($query, $viewer);
            } elseif ($statusVal === 'complete' || $statusVal === 'completed') {
                $query->where('status', 'COMPLETED');
            } elseif ($statusVal === 'dispatched_all') {
                $query->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS']);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        // Filtering by category
        if ($request->filled('dg_inbound_category')) {
            $query->where('dg_inbound_category', $request->input('dg_inbound_category'));
        }

        // Filtering by urgency
        if ($request->filled('urgency')) {
            $query->where('urgency', $request->input('urgency'));
        }

        // Filtering by confidentiality
        if ($request->filled('confidentiality')) {
            $query->where('confidentiality', $request->input('confidentiality'));
        }

        // Filtering by date range
        if ($request->filled('from_date')) {
            $query->whereDate('received_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('received_date', '<=', $request->input('to_date'));
        }

        // Filtering by deadline
        if ($request->input('deadline_filter') === 'overdue') {
            $query->whereNotNull('deadline')
                ->whereDate('deadline', '<', Carbon::today())
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED']);
        } elseif ($request->input('deadline_filter') === 'due_soon') {
            $query->whereNotNull('deadline')
                ->whereDate('deadline', '>=', Carbon::today())
                ->whereDate('deadline', '<=', Carbon::today()->addDays(3))
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED']);
        }

        // Search text
        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('general_inbound_number', 'like', "%{$s}%")
                    ->orWhere('dg_inbound_number', 'like', "%{$s}%")
                    ->orWhere('external_reference_number', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('sender_organization', 'like', "%{$s}%")
                    ->orWhere('deliverer_name', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $documents = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($documents);
    }

    /**
     * 3. ស្ថិតិ Card Counters (Stats)
     */
    public function stats(Request $request)
    {
        $viewer = $request->user();
        if (!$viewer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        // Base accessible query for viewer - strictly enforces DocumentWorkflowConfig and permission boundaries!
        $baseAccessibleQuery = $this->scopeAccessibleByViewer(InboundDocument::query(), $viewer);

        $total = (clone $baseAccessibleQuery)->count();

        $receptionPending = ($isAdmin || $isReceptionist)
            ? (clone $baseAccessibleQuery)->where('status', 'RECEPTION_DRAFT')->count()
            : 0;

        $assistantPending = ($isAdmin || $isAssistant)
            ? (clone $baseAccessibleQuery)->whereIn('status', ['SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG', 'DG_ANNOTATED'])->count()
            : 0;

        $dgPending = ($isAdmin || $isDg)
            ? (clone $baseAccessibleQuery)->where('status', 'SUBMITTED_TO_DG')->count()
            : 0;

        $dispatched = (clone $baseAccessibleQuery)->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS'])->count();

        $inResponse = (clone $baseAccessibleQuery)->where('status', 'IN_RESPONSE_PROGRESS')->count();

        $completed = (clone $baseAccessibleQuery)->where('status', 'COMPLETED')->count();

        // 3-State Unified Counters (Pending, Doing, Complete)
        $pendingCount = $this->applyPendingScope(clone $baseAccessibleQuery, $viewer)->count();
        $doingCount = $this->applyDoingScope(clone $baseAccessibleQuery, $viewer)->count();

        $myTodo = $this->scopeMyTodo(clone $baseAccessibleQuery, $viewer)->count();
        $myDone = $this->scopeMyDone(clone $baseAccessibleQuery, $viewer)->count();
        $myUnit = $this->scopeMyUnit(clone $baseAccessibleQuery, $viewer)->count();

        $today = Carbon::today();
        $overdueCount = (clone $baseAccessibleQuery)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', $today)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->count();

        $dueSoonCount = (clone $baseAccessibleQuery)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', $today)
            ->whereDate('deadline', '<=', $today->copy()->addDays(3))
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->count();

        $tabDoingCount = (clone $baseAccessibleQuery)
            ->where('status', '!=', 'COMPLETED')
            ->where(function ($q) use ($viewer, $isDg, $isAssistant) {
                if ($isDg) {
                    $q->whereNull('dispatched_at');
                } elseif ($isAssistant) {
                    $q->whereNull('dispatched_at')->whereNull('assistant_reviewed_at');
                } else {
                    $q->where('target_user_id', $viewer->id)
                      ->orWhereHas('assignments', function ($aq) use ($viewer) {
                          $aq->where(function ($sub) use ($viewer) {
                              $sub->where('user_id', $viewer->id);
                              if ($viewer->office_id) $sub->orWhere('office_id', $viewer->office_id);
                              if ($viewer->department_id) $sub->orWhere('department_id', $viewer->department_id);
                          })->where('status', 'PENDING')
                            ->where('assigned_by', '!=', $viewer->id);
                      });
                }
            })->count();

        $tabInProgressCount = (clone $baseAccessibleQuery)
            ->where('status', '!=', 'COMPLETED')
            ->where(function ($q) use ($viewer, $isDg, $isAssistant) {
                if ($isDg) {
                    $q->whereNotNull('dispatched_at');
                } elseif ($isAssistant) {
                    $q->where(fn($sub) => $sub->whereNotNull('assistant_reviewed_at')->orWhereNotNull('dispatched_at'));
                } else {
                    $q->where('registered_by', $viewer->id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('assigned_by', $viewer->id)->whereIn('status', ['PENDING', 'FORWARDED']));
                }
            })->count();

        $tabCompleteCount = (clone $baseAccessibleQuery)->where('status', 'COMPLETED')->count();

        return response()->json([
            'total' => $total,
            'tab_doing' => $tabDoingCount,
            'tab_in_progress' => $tabInProgressCount,
            'tab_complete' => $tabCompleteCount,
            'pending' => $pendingCount,
            'doing' => $doingCount,
            'completed' => $completed,
            'reception_pending' => $receptionPending,
            'assistant_pending' => $assistantPending,
            'dg_pending' => $dgPending,
            'dispatched' => $dispatched,
            'in_response' => $inResponse,
            'assigned_to_me' => $myTodo,
            'my_todo' => $myTodo,
            'my_done' => $myDone,
            'my_unit' => $myUnit,
            'overdue' => $overdueCount,
            'due_soon' => $dueSoonCount,
        ]);
    }

    /**
     * Scope Pending តាមតួនាទីអ្នកប្រើប្រាស់ (ករណីចូលមកដំបូង ឬមិនទាន់ចាត់ចែង)
     */
    protected function applyPendingScope($query, $viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        $query->whereNotIn('status', ['COMPLETED', 'CANCELLED']);

        if ($isAdmin) {
            $query->whereIn('status', ['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG']);
        } elseif ($isDg) {
            $query->where('status', 'SUBMITTED_TO_DG');
        } elseif ($isAssistant) {
            $query->where(function ($q) {
                $q->whereIn('status', ['SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG'])
                  ->whereNull('assistant_reviewed_at')
                  ->whereNull('dispatched_at');
            });
        } elseif ($isReceptionist && !$isDg && $viewerPosLevel > 5) {
            $query->where('status', 'RECEPTION_DRAFT');
        } else {
            // សម្រាប់ប្រធាននាយកដ្ឋាន, ប្រធានការិយាល័យ និងមន្ត្រី (ឯកសារចូលដំបូង មិនទាន់ចាត់ចែង)
            $query->whereIn('status', ['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG']);
        }
        return $query;
    }

    /**
     * Scope Doing តាមតួនាទីអ្នកប្រើប្រាស់ (ករណីបានចាត់ចែងបន្ត ឬកំពុងដំណើរការ)
     */
    protected function applyDoingScope($query, $viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        $query->whereNotIn('status', ['COMPLETED', 'CANCELLED']);

        if ($isAdmin) {
            $query->where(function ($q) {
                $q->whereIn('status', ['DG_ANNOTATED', 'DISPATCHED', 'IN_RESPONSE_PROGRESS'])
                  ->orWhereNotNull('dispatched_at');
            });
        } elseif ($isDg) {
            $query->where(function ($q) {
                $q->whereNotNull('dispatched_at')
                  ->orWhereIn('status', ['DG_ANNOTATED', 'DISPATCHED', 'IN_RESPONSE_PROGRESS']);
            });
        } elseif ($isAssistant) {
            $query->where(function ($q) {
                $q->whereNotNull('assistant_reviewed_at')
                  ->orWhereNotNull('dispatched_at')
                  ->orWhereIn('status', ['DG_ANNOTATED', 'DISPATCHED', 'IN_RESPONSE_PROGRESS']);
            });
        } elseif ($isReceptionist && !$isDg && $viewerPosLevel > 5) {
            $query->where('status', '!=', 'RECEPTION_DRAFT');
        } else {
            // សម្រាប់ប្រធាននាយកដ្ឋាន, ប្រធានការិយាល័យ និងមន្ត្រី (ឯកសារបានចាត់ចែងចេញមក ឬកំពុងដំណើរការឆ្លើយតប)
            $query->where(function ($q) {
                $q->whereIn('status', ['DG_ANNOTATED', 'DISPATCHED', 'IN_RESPONSE_PROGRESS'])
                  ->orWhereNotNull('dispatched_at');
            });
        }
        return $query;
    }

    /**
     * Scope: ឯកសារដែលអាចមើលឃើញដោយអ្នកប្រើប្រាស់ (Accessible by Viewer)
     * Regular users can ONLY see documents they have received or are involved in.
     * Controlled by DocumentWorkflowConfig (View Type Group) if configured.
     */
    protected function scopeAccessibleByViewer($query, $viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');

        // លើកលែងតែអគ្គនាយកតែមួយគត់ (DG - Level 1) និង Admin/ជំនួយការ ដែលអាចមើលឃើញគ្រប់ឯកសារទាំងអស់ដោយស្វ័យប្រវត្តិ
        if ($isDg || $isAdmin || $isAssistant) {
            return $query;
        }

        // Check explicit DocumentWorkflowConfig (View Type Group)
        $wfConfig = \App\Models\DocumentWorkflowConfig::where('user_id', $viewer->id)->first();

        if ($wfConfig) {
            // 1. ALL: Full view across all documents in system
            if ($wfConfig->view_type === 'ALL' || $isAdmin) {
                return $query;
            }

            // 2. SELF_ONLY: Only documents assigned, drafted, registered, or acknowledged by the user
            if ($wfConfig->view_type === 'SELF_ONLY') {
                return $query->where(function ($q) use ($viewer) {
                    $q->where('target_user_id', $viewer->id)
                      ->orWhere('registered_by', $viewer->id)
                      ->orWhere('acknowledged_by', $viewer->id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $viewer->id))
                      ->orWhereHas('responses', fn($rq) => $rq->where('drafted_by', $viewer->id)->orWhere('current_approver_id', $viewer->id))
                      ->orWhereHas('movements', fn($mq) => $mq->where('user_id', $viewer->id));
                });
            }

            // 3. DEPARTMENT: Can view all documents within their department
            if ($wfConfig->view_type === 'DEPARTMENT') {
                $deptIds = !empty($wfConfig->view_dept_ids) ? $wfConfig->view_dept_ids : array_filter([$viewer->department_id]);
                return $query->where(function ($q) use ($viewer, $deptIds) {
                    $q->where('target_user_id', $viewer->id)
                      ->orWhere('registered_by', $viewer->id)
                      ->orWhere('acknowledged_by', $viewer->id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $viewer->id))
                      ->orWhereHas('responses', fn($rq) => $rq->where('drafted_by', $viewer->id)->orWhere('current_approver_id', $viewer->id))
                      ->orWhereHas('movements', fn($mq) => $mq->where('user_id', $viewer->id));

                    if (!empty($deptIds)) {
                        $q->orWhereIn('target_department_id', $deptIds)
                          ->orWhereHas('assignments', fn($aq) => $aq->whereIn('department_id', $deptIds))
                          ->orWhereHas('targetOffice', fn($oq) => $oq->whereIn('department_id', $deptIds))
                          ->orWhereHas('assignments.office', fn($oq) => $oq->whereIn('department_id', $deptIds))
                          ->orWhereHas('targetUser', fn($uq) => $uq->whereIn('department_id', $deptIds))
                          ->orWhereHas('assignments.user', fn($uq) => $uq->whereIn('department_id', $deptIds))
                          ->orWhereHas('responses', fn($rq) => $rq->whereIn('department_id', $deptIds));
                    }
                });
            }

            // 4. OFFICE: Can view all documents within their office
            if ($wfConfig->view_type === 'OFFICE') {
                $officeIds = !empty($wfConfig->view_office_ids) ? $wfConfig->view_office_ids : array_filter([$viewer->office_id]);
                return $query->where(function ($q) use ($viewer, $officeIds) {
                    $q->where('target_user_id', $viewer->id)
                      ->orWhere('registered_by', $viewer->id)
                      ->orWhere('acknowledged_by', $viewer->id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $viewer->id))
                      ->orWhereHas('responses', fn($rq) => $rq->where('drafted_by', $viewer->id)->orWhere('current_approver_id', $viewer->id))
                      ->orWhereHas('movements', fn($mq) => $mq->where('user_id', $viewer->id));

                    if (!empty($officeIds)) {
                        $q->orWhereIn('target_office_id', $officeIds)
                          ->orWhereHas('assignments', fn($aq) => $aq->whereIn('office_id', $officeIds))
                          ->orWhereHas('targetUser', fn($uq) => $uq->whereIn('office_id', $officeIds))
                          ->orWhereHas('assignments.user', fn($uq) => $uq->whereIn('office_id', $officeIds))
                          ->orWhereHas('responses', fn($rq) => $rq->whereIn('office_id', $officeIds));
                    }
                });
            }

            // 5. SUBORDINATES: Can view own documents + subordinates in their unit
            if ($wfConfig->view_type === 'SUBORDINATES') {
                return $query->where(function ($q) use ($viewer, $viewerPosLevel) {
                    $q->where('target_user_id', $viewer->id)
                      ->orWhere('registered_by', $viewer->id)
                      ->orWhere('acknowledged_by', $viewer->id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $viewer->id))
                      ->orWhereHas('responses', fn($rq) => $rq->where('drafted_by', $viewer->id)->orWhere('current_approver_id', $viewer->id))
                      ->orWhereHas('movements', fn($mq) => $mq->where('user_id', $viewer->id))
                      ->orWhereHas('targetUser', function ($uq) use ($viewer, $viewerPosLevel) {
                          $uq->where(function ($sq) use ($viewer) {
                              if ($viewer->office_id) $sq->where('office_id', $viewer->office_id);
                              elseif ($viewer->department_id) $sq->where('department_id', $viewer->department_id);
                          })->whereHas('position', fn($pq) => $pq->where('level', '>', $viewerPosLevel));
                      })
                      ->orWhereHas('assignments.user', function ($uq) use ($viewer, $viewerPosLevel) {
                          $uq->where(function ($sq) use ($viewer) {
                              if ($viewer->office_id) $sq->where('office_id', $viewer->office_id);
                              elseif ($viewer->department_id) $sq->where('department_id', $viewer->department_id);
                          })->whereHas('position', fn($pq) => $pq->where('level', '>', $viewerPosLevel));
                      });
                });
            }

            // 6. CUSTOM: Can view specific departments, offices, users, or working groups selected by admin
            if ($wfConfig->view_type === 'CUSTOM') {
                $customDeptIds = $wfConfig->view_dept_ids ?: [];
                $customOfficeIds = $wfConfig->view_office_ids ?: [];
                $customUserIds = $wfConfig->view_user_ids ?: [];
                $customWgIds = $wfConfig->view_working_group_ids ?: [];

                return $query->where(function ($q) use ($viewer, $customDeptIds, $customOfficeIds, $customUserIds, $customWgIds) {
                    $q->where('target_user_id', $viewer->id)
                      ->orWhere('registered_by', $viewer->id)
                      ->orWhere('acknowledged_by', $viewer->id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $viewer->id))
                      ->orWhereHas('responses', fn($rq) => $rq->where('drafted_by', $viewer->id)->orWhere('current_approver_id', $viewer->id))
                      ->orWhereHas('movements', fn($mq) => $mq->where('user_id', $viewer->id));

                    if (!empty($customDeptIds)) {
                        $q->orWhereIn('target_department_id', $customDeptIds)
                          ->orWhereHas('assignments', fn($aq) => $aq->whereIn('department_id', $customDeptIds));
                    }
                    if (!empty($customOfficeIds)) {
                        $q->orWhereIn('target_office_id', $customOfficeIds)
                          ->orWhereHas('assignments', fn($aq) => $aq->whereIn('office_id', $customOfficeIds));
                    }
                    if (!empty($customUserIds)) {
                        $q->orWhereIn('target_user_id', $customUserIds)
                          ->orWhereHas('assignments', fn($aq) => $aq->whereIn('user_id', $customUserIds));
                    }
                    if (!empty($customWgIds)) {
                        $q->orWhereHas('assignments', fn($aq) => $aq->whereIn('working_group_id', $customWgIds));
                    }
                });
            }
        }

        // លើកលែងតែ Admin, អគ្គនាយក (DG - Level 1) និង ជំនួយការអគ្គនាយក
        if ($isAdmin || $isDg || $isAssistant) {
            return $query;
        }

        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');
        $isDeptDirector = ($viewerPosLevel <= 5) && !empty($viewer->department_id); // Dept Director, Acting, Deputy Director (levels 3, 4, 5)
        $isOfficeLeadership = ($viewerPosLevel <= 8) && !empty($viewer->office_id); // Office Chief / Acting / Deputy Chief (levels 6, 7, 8)

        // Working groups where viewer is an active member
        $userWgIds = \App\Models\WorkingGroupMember::where('user_id', $viewer->id)
            ->where('status', 'ACTIVE')
            ->pluck('working_group_id')
            ->toArray();

        return $query->where(function ($q) use ($viewer, $isReceptionist, $isDeptDirector, $isOfficeLeadership, $userWgIds) {
            // 1. Receptionist can see documents they registered or reception drafts
            if ($isReceptionist) {
                $q->orWhere('registered_by', $viewer->id)
                  ->orWhere('status', 'RECEPTION_DRAFT');
            }

            // 2. Documents specifically assigned / forwarded to this user
            $q->orWhere('target_user_id', $viewer->id)
              ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $viewer->id));

            // 3. User was the one who registered it
            $q->orWhere('registered_by', $viewer->id);

            // 4. User acknowledged it
            $q->orWhere('acknowledged_by', $viewer->id);

            // 5. User is involved in response workflow (drafted, current reviewer, or in approval history)
            $q->orWhereHas('responses', function ($rq) use ($viewer) {
                $rq->where('drafted_by', $viewer->id)
                   ->orWhere('current_approver_id', $viewer->id)
                   ->orWhereHas('approvals', function ($aq) use ($viewer) {
                       $aq->where('user_id', $viewer->id);
                   });
            });

            // 6. User is in movement tracking history
            $q->orWhereHas('movements', function ($mq) use ($viewer) {
                $mq->where('user_id', $viewer->id);
            });

            // 7. Assigned to a Working Group where user is an active member
            if (!empty($userWgIds)) {
                $q->orWhereHas('assignments', function ($aq) use ($userWgIds) {
                    $aq->whereIn('working_group_id', $userWgIds);
                });
            }

            // 8. Department Leadership (Level 3-5): Can see all documents received by their department AND documents of subordinate offices & officers
            if ($isDeptDirector) {
                $q->orWhere(function ($dq) use ($viewer) {
                    $dq->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'SUBMITTED_TO_DG', 'COMPLETED'])
                       ->where(function ($subDq) use ($viewer) {
                           // Directly to department
                           $subDq->where('target_department_id', $viewer->department_id)
                                 ->orWhereHas('assignments', fn($aq) => $aq->where('department_id', $viewer->department_id))
                           // Or to subordinate offices in this department
                                 ->orWhereHas('targetOffice', fn($oq) => $oq->where('department_id', $viewer->department_id))
                                 ->orWhereHas('assignments.office', fn($oq) => $oq->where('department_id', $viewer->department_id))
                           // Or to subordinate officers in this department
                                 ->orWhereHas('targetUser', fn($uq) => $uq->where('department_id', $viewer->department_id))
                                 ->orWhereHas('assignments.user', fn($uq) => $uq->where('department_id', $viewer->department_id))
                           // Or responses drafted by officers in this department
                                 ->orWhereHas('responses', fn($rq) => $rq->where('department_id', $viewer->department_id));
                       });
                });
            }

            // 9. Office Leadership (Level 6-8): Can see all documents received by their office AND documents of subordinate officers
            if ($isOfficeLeadership) {
                $q->orWhere(function ($oq) use ($viewer) {
                    $oq->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'COMPLETED'])
                       ->where(function ($subOq) use ($viewer) {
                           // Directly to office
                           $subOq->where('target_office_id', $viewer->office_id)
                                 ->orWhereHas('assignments', fn($aq) => $aq->where('office_id', $viewer->office_id))
                           // Or to subordinate officers in this office
                                 ->orWhereHas('targetUser', fn($uq) => $uq->where('office_id', $viewer->office_id))
                                 ->orWhereHas('assignments.user', fn($uq) => $uq->where('office_id', $viewer->office_id))
                           // Or responses drafted by officers in this office
                                 ->orWhereHas('responses', fn($rq) => $rq->where('office_id', $viewer->office_id));
                       });
                });
            }
        });
    }

    /**
     * Check if a specific document is accessible by the viewer
     */
    protected function canUserAccessDocument($doc, $viewer): bool
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isDg = ($viewer->position && (int) $viewer->position->level === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');

        // លើកលែងតែអគ្គនាយកតែមួយគត់ (DG - Level 1) និង Admin/ជំនួយការ
        if ($isAdmin || $isDg || $isAssistant) {
            return true;
        }

        $query = InboundDocument::where('id', $doc->id);
        $this->scopeAccessibleByViewer($query, $viewer);
        return $query->exists();
    }

    /**
     * Scope: ឯកសារត្រូវធ្វើ (To-Do)
     */
    protected function scopeMyTodo($query, $viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isDg = ($viewerPosLevel === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        return $query->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->where(function ($q) use ($viewer, $isAdmin, $isDg, $isAssistant, $isReceptionist, $viewerPosLevel) {
                // 1. Reception Desk (for Receptionist or Admin)
                if ($isAdmin || $isReceptionist) {
                    $q->orWhere('status', 'RECEPTION_DRAFT');
                }

                // 2. Assistant Desk (for Assistant or Admin)
                if ($isAdmin || $isAssistant) {
                    $q->orWhereIn('status', ['SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED']);
                }

                // 3. DG Desk (for DG or Admin)
                if ($isAdmin || $isDg) {
                    $q->orWhere('status', 'SUBMITTED_TO_DG');
                }

                // 4. Assigned specifically to this officer
                $q->orWhere('target_user_id', $viewer->id);
                $q->orWhereHas('assignments', function ($aq) use ($viewer) {
                    $aq->where('user_id', $viewer->id)
                       ->whereNotIn('status', ['COMPLETED']);
                });

                // 5. Assigned to this office (not yet delegated to a specific officer)
                // Only for Office Chief / Acting / Deputy Chief (level <= 8) or Admin
                $isOfficeChief = ($viewerPosLevel <= 8);
                if ($viewer->office_id && ($isOfficeChief || $isAdmin)) {
                    $q->orWhere(function ($oq) use ($viewer) {
                        $oq->where('target_office_id', $viewer->office_id)
                           ->whereNull('target_user_id');
                    });
                    $q->orWhereHas('assignments', function ($aq) use ($viewer) {
                        $aq->where('office_id', $viewer->office_id)
                           ->whereNull('user_id')
                           ->whereNotIn('status', ['COMPLETED']);
                    });
                }

                // 6. Assigned to this department (not yet delegated to an office or officer)
                // Only for Department Leadership (level <= 5) or Admin
                $isDeptDirector = ($viewerPosLevel <= 5);
                if ($viewer->department_id && ($isDeptDirector || $isAdmin)) {
                    $q->orWhere(function ($dq) use ($viewer) {
                        $dq->where('target_department_id', $viewer->department_id)
                           ->whereNull('target_office_id')
                           ->whereNull('target_user_id');
                    });
                    $q->orWhereHas('assignments', function ($aq) use ($viewer) {
                        $aq->where('department_id', $viewer->department_id)
                           ->whereNull('user_id')
                           ->whereNotIn('status', ['COMPLETED', 'RECALLED']);
                    });
                }

                // 6b. Assigned to a working group where user is an active member
                $userWgIds = \App\Models\WorkingGroupMember::where('user_id', $viewer->id)
                    ->where('status', 'ACTIVE')
                    ->pluck('working_group_id')
                    ->toArray();
                if (!empty($userWgIds)) {
                    $q->orWhereHas('assignments', function ($aq) use ($userWgIds) {
                        $aq->whereIn('working_group_id', $userWgIds)
                           ->whereNotIn('status', ['COMPLETED']);
                    });
                }

                // 7. Active response review or return
                $q->orWhereHas('responses', function ($rq) use ($viewer) {
                    $rq->where(function ($subRq) use ($viewer) {
                        $subRq->where('current_approver_id', $viewer->id)
                              ->where('status', 'UNDER_REVIEW');
                    })->orWhere(function ($subRq) use ($viewer) {
                        $subRq->where('drafted_by', $viewer->id)
                              ->where('status', 'RETURNED');
                    });
                });
            });
    }

    /**
     * Scope: ឯកសារបានធ្វើរួច (Done)
     */
    protected function scopeMyDone($query, $viewer)
    {
        return $query->where(function ($q) use ($viewer) {
            // Acknowledged by viewer
            $q->where('acknowledged_by', $viewer->id)
                // Or viewer was the assigned user of a completed document
                ->orWhere(function ($q2) use ($viewer) {
                    $q2->where('status', 'COMPLETED')
                       ->where('target_user_id', $viewer->id);
                })
                // Or viewer drafted a response
                ->orWhereHas('responses', function ($rq) use ($viewer) {
                    $rq->where('drafted_by', $viewer->id);
                })
                // Or viewer was an approver who approved/forwarded the response
                ->orWhereHas('responses.approvals', function ($aq) use ($viewer) {
                    $aq->where('user_id', $viewer->id);
                })
                // Or viewer moved/forwarded/annotated/dispatched it
                ->orWhereHas('movements', function ($mq) use ($viewer) {
                    $mq->where('user_id', $viewer->id)
                       ->whereIn('action', ['FORWARDED', 'DISPATCHED', 'DG_ANNOTATED', 'ACKNOWLEDGED']);
                });
        });
    }

    /**
     * Scope: អង្គភាពរបស់ខ្ញុំ (My Unit)
     */
    protected function scopeMyUnit($query, $viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isLeadership = ($viewerPosLevel <= 2);

        // DG, Deputy DG, or Admin sees all organizational documents
        if ($isAdmin || $isLeadership) {
            return $query;
        }

        $isDeptDirector = ($viewerPosLevel <= 4) && !empty($viewer->department_id);
        $isOfficeLeadership = ($viewerPosLevel <= 8) && !empty($viewer->office_id);

        return $query->where(function ($q) use ($viewer, $isDeptDirector, $isOfficeLeadership) {
            // Only dispatched/in progress/completed documents
            $q->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'COMPLETED']);

            if ($isDeptDirector) {
                $q->where(function ($dq) use ($viewer) {
                    $dq->where('target_department_id', $viewer->department_id)
                       ->orWhereHas('assignments', fn($aq) => $aq->where('department_id', $viewer->department_id))
                       ->orWhereHas('targetOffice', fn($oq) => $oq->where('department_id', $viewer->department_id))
                       ->orWhereHas('assignments.office', fn($oq) => $oq->where('department_id', $viewer->department_id))
                       ->orWhereHas('targetUser', fn($uq) => $uq->where('department_id', $viewer->department_id))
                       ->orWhereHas('assignments.user', fn($uq) => $uq->where('department_id', $viewer->department_id))
                       ->orWhereHas('responses', fn($rq) => $rq->where('department_id', $viewer->department_id));
                });
            } elseif ($isOfficeLeadership) {
                $q->where(function ($oq) use ($viewer) {
                    $oq->where('target_office_id', $viewer->office_id)
                       ->orWhereHas('assignments', fn($aq) => $aq->where('office_id', $viewer->office_id))
                       ->orWhereHas('targetUser', fn($uq) => $uq->where('office_id', $viewer->office_id))
                       ->orWhereHas('assignments.user', fn($uq) => $uq->where('office_id', $viewer->office_id))
                       ->orWhereHas('responses', fn($rq) => $rq->where('office_id', $viewer->office_id));
                });
            } else {
                // Regular officer: only documents assigned directly to them or their working group
                $userWgIds = \App\Models\WorkingGroupMember::where('user_id', $viewer->id)
                    ->where('status', 'ACTIVE')
                    ->pluck('working_group_id')
                    ->toArray();

                $q->where(function ($oq) use ($viewer, $userWgIds) {
                    $oq->where('target_user_id', $viewer->id)
                       ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $viewer->id));

                    if (!empty($userWgIds)) {
                        $oq->orWhereHas('assignments', fn($aq) => $aq->whereIn('working_group_id', $userWgIds));
                    }
                });
            }
        });
    }

    /**
     * 4. បង្ហាញព័ត៌មានលម្អិតឯកសារ (Show Single Inbound Document)
     */
    public function show($id, Request $request)
    {
        $viewer = $request->user();
        $query = InboundDocument::with([
            'registeredByUser:id,name,name_kh,email',
            'assistant:id,name,name_kh,email',
            'assistantReviewer:id,name,name_kh',
            'recalledByUser:id,name,name_kh',
            'outboundDocuments',
            'dispatchedByUser:id,name,name_kh',
            'targetDepartment:id,name_kh',
            'targetOffice:id,name_kh',
            'targetUser:id,name,name_kh,email,profile_image',
            'acknowledgedByUser:id,name,name_kh,profile_image',
            'assignments.department:id,name_kh,name_en',
            'assignments.office:id,name_kh,name_en',
            'assignments.user:id,name,name_kh,email,position_id,profile_image',
            'assignments.user.position:id,title_kh',
            'assignments.workingGroup:id,name_kh,name_en,code',
            'assignments.workingGroup.members:id,working_group_id,user_id,role,status',
            'assignments.workingGroup.members.user:id,name,name_kh,profile_image',
            'assignments.assignedByUser:id,name,name_kh,profile_image',
            'assignments.childAssignments.user:id,name,name_kh',
            'assignments.childAssignments.office:id,name_kh',
            'assignments.childAssignments.department:id,name_kh',
            'movements.user:id,name,name_kh,position_id',
            'movements.user.position:id,title_kh',
            'responses.draftedByUser:id,name,name_kh,position_id',
            'responses.draftedByUser.position:id,title_kh',
            'responses.currentApprover:id,name,name_kh,position_id',
            'responses.currentApprover.position:id,title_kh',
            'responses.approvedByUser:id,name,name_kh,position_id',
            'responses.approvedByUser.position:id,title_kh',
            'responses.approvals.user:id,name,name_kh,position_id',
            'responses.approvals.user.position:id,title_kh',
            'responses.approvals.forwardedTo:id,name,name_kh,position_id',
            'responses.approvals.forwardedTo.position:id,title_kh',
        ]);

        $this->scopeAccessibleByViewer($query, $viewer);

        $document = $query->find($id);
        if (!$document) {
            return response()->json(['message' => 'ឯកសារនេះមិនមាន ឬលោកអ្នកមិនមានសិទ្ធិចូលមើលឡើយ!'], 404);
        }

        return response()->json($document);
    }

    /**
     * 5. ចុះបញ្ជីឯកសារចូលថ្មីដោយអ្នកទទួល (Store by Receptionist)
     */
    public function store(Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-receptionist')) {
            return response()->json(['message' => 'មានតែអ្នកទទួលឯកសារទេទើបមានសិទ្ធិចុះបញ្ជីឯកសារចូលថ្មី!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'general_inbound_number' => 'nullable|string|max:100',
            'received_date' => 'required|date',
            'received_time' => 'required',
            'deliverer_name' => 'required|string|max:255',
            'deliverer_phone' => 'nullable|string|max:50',
            'sender_organization' => 'required|string|max:255',
            'title' => 'required|string',
            'document_type' => 'required|string',
            'urgency' => 'required|string',
            'confidentiality' => 'required|string',
            'external_reference_number' => 'nullable|string|max:100',
            'external_document_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'receptionist_notes' => 'nullable|string',
            'original_file' => 'nullable|file|max:25600', // 25MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $sendImmediately = filter_var($request->input('send_immediately', true), FILTER_VALIDATE_BOOLEAN);

        $doc = DB::transaction(function () use ($request, $viewer, $sendImmediately) {
            $yy = date('y');
            $maxSeq = InboundDocument::where('general_inbound_year', $yy)->max('general_inbound_seq') ?? 0;
            $nextSeq = (int) $maxSeq + 1;
            $generalNumber = sprintf('%03d/%s', $nextSeq, $yy);

            $customGeneralNumber = trim($request->input('general_inbound_number') ?? '');
            if (!empty($customGeneralNumber)) {
                $generalNumber = $customGeneralNumber;
                if (preg_match('/^(\d+)\/(\d+)$/', $generalNumber, $m)) {
                    $nextSeq = (int) $m[1];
                    $yy = $m[2];
                }
            }

            $filePath = null;
            $fileName = null;
            if ($request->hasFile('original_file')) {
                $file = $request->file('original_file');
                $fileName = $file->getClientOriginalName();
                $filePath = StorageService::store($file, 'inbound_documents/originals');
            }

            $assistantUser = User::where(function ($q) {
                $q->whereHas('position', fn($pq) => $pq->where('title_kh', 'like', '%ជំនួយការ%')->orWhere('title_en', 'like', '%assistant%'))
                  ->orWhere('permissions', 'like', '%"inbound-documents-assistant"%');
            })->where('status', 'ACTIVE')->first();

            $doc = InboundDocument::create([
                'general_inbound_number' => $generalNumber,
                'general_inbound_seq' => $nextSeq,
                'general_inbound_year' => $yy,
                'received_date' => $request->input('received_date'),
                'received_time' => $request->input('received_time'),
                'deliverer_name' => $request->input('deliverer_name'),
                'deliverer_phone' => $request->input('deliverer_phone'),
                'sender_organization' => $request->input('sender_organization'),
                'external_reference_number' => $request->input('external_reference_number'),
                'external_document_date' => $request->input('external_document_date'),
                'deadline' => $request->input('deadline'),
                'title' => $request->input('title'),
                'document_type' => $request->input('document_type', 'LETTER'),
                'urgency' => $request->input('urgency', 'NORMAL'),
                'confidentiality' => $request->input('confidentiality', 'NORMAL'),
                'receptionist_notes' => $request->input('receptionist_notes'),
                'original_file_path' => $filePath,
                'original_file_name' => $fileName,
                'registered_by' => $viewer->id,
                'dg_assistant_id' => $assistantUser ? $assistantUser->id : null,
                'status' => 'SUBMITTED_TO_DG',
            ]);

            // Movement log: Registered and forwarded simultaneously to DG and Assistant
            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'REGISTERED',
                'from_status' => null,
                'to_status' => 'SUBMITTED_TO_DG',
                'comment' => "បានបញ្ជូនឯកសារលេខ៖ {$generalNumber} ជូនឯកឧត្តមអគ្គនាយក និងជំនួយការរួចរាល់",
            ]);

            return $doc;
        });

        // Telegram Notification for new inbound document
        if ($sendImmediately) {
            $this->telegram->notifyNewInbound($doc);
        }

        return response()->json([
            'message' => 'បានចុះបញ្ជីឯកសារចូលដោយជោគជ័យ!',
            'document' => $doc->load('registeredByUser'),
        ], 201);
    }

    /**
     * 6. បញ្ជូនឯកសារពីអ្នកទទួលទៅកាន់ជំនួយការអគ្គនាយក (Send to DG Assistant)
     */
    public function sendToAssistant($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);

        if ($doc->status !== 'RECEPTION_DRAFT') {
            return response()->json(['message' => 'ឯកសារនេះត្រូវបានបញ្ជូនរួចហើយ!'], 400);
        }

        $doc->update(['status' => 'SUBMITTED_TO_DG']);

        InboundDocumentMovement::create([
            'inbound_document_id' => $doc->id,
            'user_id' => $viewer->id,
            'action' => 'FORWARDED_TO_DG_AND_ASSISTANT',
            'from_status' => 'RECEPTION_DRAFT',
            'to_status' => 'SUBMITTED_TO_DG',
            'comment' => $request->input('comment', 'បានដាក់ជូនឯកឧត្តមអគ្គនាយក'),
        ]);

        // Telegram Notification
        $this->telegram->notifyNewInbound($doc);

        return response()->json([
            'message' => 'បានដាក់ជូនឯកឧត្តមអគ្គនាយករួចរាល់!',
            'document' => $doc,
        ]);
    }

    /**
     * 7. ជំនួយការទទួល និងចុះលេខចូលជំនួយការអគ្គនាយក រួចដាក់ជូនអគ្គនាយក (Assistant Receive & Submit to DG)
     */
    public function assistantReceiveAndSubmitToDg($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-assistant')) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិជាជំនួយការអគ្គនាយកឡើយ!'], 403);
        }

        $doc = InboundDocument::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'dg_inbound_category' => 'required|string',
            'dg_received_date' => 'required|date',
            'dg_assistant_notes' => 'nullable|string',
            'custom_dg_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $doc = DB::transaction(function () use ($request, $viewer, $doc) {
            $category = $request->input('dg_inbound_category');
            $prefix = $this->getCategoryPrefix($category);
            $yy = date('y');

            $customNumber = trim((string) $request->input('custom_dg_number', ''));
            if (!empty($customNumber)) {
                $dgNumber = $customNumber;
                $nextSeq = null;
            } else {
                $maxSeq = InboundDocument::where('dg_inbound_prefix', $prefix)
                    ->where('dg_inbound_year', $yy)
                    ->max('dg_inbound_seq') ?? 0;
                $nextSeq = (int) $maxSeq + 1;
                $dgNumber = sprintf('%s%03d/%s', $prefix, $nextSeq, $yy);
            }

            $doc->update([
                'dg_inbound_category' => $category,
                'dg_inbound_prefix' => $prefix,
                'dg_inbound_seq' => $nextSeq,
                'dg_inbound_year' => $yy,
                'dg_inbound_number' => $dgNumber,
                'dg_received_date' => $request->input('dg_received_date'),
                'dg_assistant_id' => $viewer->id,
                'dg_assistant_notes' => $request->input('dg_assistant_notes'),
                'status' => 'SUBMITTED_TO_DG',
            ]);

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'ASSISTANT_RECEIVED',
                'from_status' => 'SUBMITTED_TO_ASSISTANT',
                'to_status' => 'SUBMITTED_TO_DG',
                'comment' => "ជំនួយការបានចុះលេខចូល៖ {$dgNumber} និងដាក់ជូនឯកឧត្តមអគ្គនាយកពិនិត្យ",
                'meta' => [
                    'category' => $category,
                    'dg_number' => $dgNumber,
                ],
            ]);

            return $doc->fresh(['registeredByUser', 'assistant']);
        });

        // Telegram Notification for DG
        $this->telegram->notifySubmittedToDg($doc);

        return response()->json([
            'message' => "បានចុះលេខចូល {$doc->dg_inbound_number} និងដាក់ជូនឯកឧត្តមអគ្គនាយករួចរាល់!",
            'document' => $doc,
        ]);
    }

    /**
     * 7B. ជំនួយការអគ្គនាយកចុច «បានពិនិត្យរួចរាល់» (Mark as Reviewed)
     */
    public function markAssistantReviewed($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isAssistant = $isAdmin || $viewer->hasPermission('inbound-documents-assistant');
        if (!$isAssistant) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិជាជំនួយការអគ្គនាយកឡើយ!'], 403);
        }

        $doc = InboundDocument::findOrFail($id);
        if ($doc->dispatched_at) {
            return response()->json(['message' => 'ឯកសារនេះត្រូវបានឯកឧត្តមអគ្គនាយកចំណាត់ការរួចហើយ!'], 400);
        }

        $doc->update([
            'assistant_reviewed_at' => Carbon::now(),
            'assistant_reviewed_by' => $viewer->id,
            'dg_assistant_notes' => $request->input('notes', $doc->dg_assistant_notes),
        ]);

        InboundDocumentMovement::create([
            'inbound_document_id' => $doc->id,
            'user_id' => $viewer->id,
            'action' => 'ASSISTANT_REVIEWED',
            'from_status' => $doc->status,
            'to_status' => $doc->status,
            'comment' => 'ជំនួយការបានពិនិត្យ',
        ]);

        return response()->json([
            'message' => 'បានកត់ត្រាការពិនិត្យរួចរាល់ដោយជោគជ័យ',
            'document' => $doc->fresh(['assistantReviewer', 'registeredByUser']),
        ]);
    }

    /**
     * 7C. ទាញយកការចាត់ចែងមកវិញ (Recall Inbound Document Assignment)
     */
    public function recall($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::with('assignments')->findOrFail($id);
        $isDg = ($viewer->position && (int)$viewer->position->level === 1) || strtoupper($viewer->level ?? '') === 'ADMIN';

        if ($isDg && $doc->dispatched_by === $viewer->id) {
            // DG recall
            $pendingAssignments = InboundDocumentAssignment::where('inbound_document_id', $doc->id)
                ->where('assigned_by', $viewer->id)
                ->where('status', 'PENDING')
                ->get();

            if ($pendingAssignments->isEmpty()) {
                return response()->json(['message' => 'មិនអាចទាញយកមកវិញបានឡើយ ព្រោះអ្នកទទួលប្រហែលជាបានចាត់ការបន្តរួចហើយ!'], 400);
            }

            foreach ($pendingAssignments as $pa) {
                $pa->update(['status' => 'RECALLED']);
            }

            $doc->update([
                'dispatched_at' => null,
                'dispatched_by' => null,
                'recalled_at' => Carbon::now(),
                'recalled_by' => $viewer->id,
            ]);

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'RECALLED',
                'comment' => 'ឯកឧត្តមអគ្គនាយកបានទាញយកឯកសារមកវិញ (Recall)',
            ]);

            return response()->json([
                'message' => 'បានទាញយកឯកសារមកវិញជោគជ័យ',
                'document' => $doc->fresh(),
            ]);
        }

        // Subordinate leader/officer recall
        $myAssignment = InboundDocumentAssignment::where('inbound_document_id', $doc->id)
            ->where('assigned_by', $viewer->id)
            ->where('status', 'PENDING')
            ->first();

        if (!$myAssignment) {
            return response()->json(['message' => 'មិនអាចទាញយកមកវិញបានឡើយ ព្រោះអ្នកទទួលប្រហែលជាបានចាត់ការបន្តរួចហើយ ឬលោកអ្នកមិនមែនជាអ្នកចាត់ចែង!'], 400);
        }

        $myAssignment->update(['status' => 'RECALLED']);

        InboundDocumentMovement::create([
            'inbound_document_id' => $doc->id,
            'user_id' => $viewer->id,
            'action' => 'RECALLED',
            'comment' => 'បានទាញយកការចាត់ចែងមកវិញ (Recall)',
        ]);

        return response()->json([
            'message' => 'បានទាញយកការចាត់ចែងមកវិញជោគជ័យ',
            'document' => $doc->fresh(),
        ]);
    }

    /**
     * 8. ឯកឧត្តមអគ្គនាយកធ្វើចំណារ (DG Annotation)
     * គាំទ្រការជ្រើសរើសគោលដៅចាត់ចែងភ្លាមៗ (មន្ត្រី, នាយកដ្ឋាន, ការិយាល័យ, ក្រុមការងារ) ឬរក្សាទុកចំណាររង់ចាំជំនួយការចែកចាយ
     */
    public function dgAnnotate($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isDg = ($viewer->position && (int) $viewer->position->level === 1);

        if (!$isAdmin && !$isDg) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិកត់ត្រាចំណាររបស់អគ្គនាយកឡើយ!'], 403);
        }

        $doc = InboundDocument::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'dg_annotation' => 'required|string',
            'is_response_required' => 'required|boolean',
            'deadline' => 'nullable|date',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'office_ids' => 'nullable|array',
            'office_ids.*' => 'exists:offices,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'working_group_ids' => 'nullable|array',
            'working_group_ids.*' => 'exists:working_groups,id',
            'dispatch_notes' => 'nullable|string',
            'annotated_file' => 'nullable|file|max:25600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $deptIds = $request->input('department_ids', []);
        $officeIds = $request->input('office_ids', []);
        $userIds = $request->input('user_ids', []);
        $wgIds = $request->input('working_group_ids', []);
        $hasTargets = !empty($deptIds) || !empty($officeIds) || !empty($userIds) || !empty($wgIds);

        $doc = DB::transaction(function () use ($request, $viewer, $doc, $deptIds, $officeIds, $userIds, $wgIds, $hasTargets) {
            $annotatedPath = $doc->annotated_file_path;
            $annotatedName = $doc->annotated_file_name;

            if ($request->hasFile('annotated_file')) {
                if ($annotatedPath && StorageService::exists($annotatedPath)) {
                    StorageService::delete($annotatedPath);
                }
                $file = $request->file('annotated_file');
                $annotatedName = $file->getClientOriginalName();
                $annotatedPath = StorageService::store($file, 'inbound_documents/annotated');
            }

            $isResponseRequired = (bool) $request->input('is_response_required');
            $newStatus = $hasTargets
                ? ($isResponseRequired ? 'IN_RESPONSE_PROGRESS' : 'DISPATCHED')
                : 'DG_ANNOTATED';

            $updateData = [
                'dg_annotation' => $request->input('dg_annotation'),
                'is_response_required' => $isResponseRequired,
                'dg_annotated_at' => Carbon::now(),
                'status' => $newStatus,
                'annotated_file_path' => $annotatedPath,
                'annotated_file_name' => $annotatedName,
            ];

            if ($request->filled('deadline')) {
                $updateData['deadline'] = $request->input('deadline');
            }

            $dispatchNotes = $request->input('dispatch_notes') ?: $request->input('dg_annotation');
            $assignedLabels = [];

            if ($hasTargets) {
                // Clear previous top-level assignments
                InboundDocumentAssignment::where('inbound_document_id', $doc->id)->whereNull('parent_assignment_id')->delete();

                // 1. Department assignments
                foreach ($deptIds as $deptId) {
                    InboundDocumentAssignment::create([
                        'inbound_document_id' => $doc->id,
                        'assigned_by' => $viewer->id,
                        'target_type' => 'DEPARTMENT',
                        'department_id' => $deptId,
                        'instruction' => $dispatchNotes,
                        'status' => 'PENDING',
                    ]);
                    $dept = \App\Models\Department::find($deptId);
                    if ($dept) $assignedLabels[] = "នាយកដ្ឋាន៖ {$dept->name_kh}";
                }

                // 2. Office assignments
                foreach ($officeIds as $officeId) {
                    $off = \App\Models\Office::find($officeId);
                    InboundDocumentAssignment::create([
                        'inbound_document_id' => $doc->id,
                        'assigned_by' => $viewer->id,
                        'target_type' => 'OFFICE',
                        'department_id' => $off?->department_id,
                        'office_id' => $officeId,
                        'instruction' => $dispatchNotes,
                        'status' => 'PENDING',
                    ]);
                    if ($off) $assignedLabels[] = "ការិយាល័យ៖ {$off->name_kh}";
                }

                // 3. User assignments
                foreach ($userIds as $userId) {
                    $usr = User::find($userId);
                    InboundDocumentAssignment::create([
                        'inbound_document_id' => $doc->id,
                        'assigned_by' => $viewer->id,
                        'target_type' => 'USER',
                        'department_id' => $usr?->department_id,
                        'office_id' => $usr?->office_id,
                        'user_id' => $userId,
                        'instruction' => $dispatchNotes,
                        'status' => 'PENDING',
                    ]);
                    if ($usr) $assignedLabels[] = "មន្ត្រី៖ " . ($usr->name_kh ?? $usr->name);
                }

                // 4. Working Group assignments
                foreach ($wgIds as $wgId) {
                    $wg = \App\Models\WorkingGroup::find($wgId);
                    InboundDocumentAssignment::create([
                        'inbound_document_id' => $doc->id,
                        'assigned_by' => $viewer->id,
                        'target_type' => 'WORKING_GROUP',
                        'working_group_id' => $wgId,
                        'instruction' => $dispatchNotes,
                        'status' => 'PENDING',
                    ]);
                    if ($wg) $assignedLabels[] = "ក្រុមការងារ៖ {$wg->name_kh}";
                }

                // Primary targets for backward compatibility
                $primaryTargetType = 'MULTIPLE';
                $primaryDeptId = !empty($deptIds) ? $deptIds[0] : null;
                $primaryOfficeId = !empty($officeIds) ? $officeIds[0] : null;
                $primaryUserId = !empty($userIds) ? $userIds[0] : null;

                if (count($deptIds) === 1 && empty($officeIds) && empty($userIds) && empty($wgIds)) {
                    $primaryTargetType = 'DEPARTMENT';
                } elseif (count($officeIds) === 1 && empty($deptIds) && empty($userIds) && empty($wgIds)) {
                    $primaryTargetType = 'OFFICE';
                    if (!$primaryDeptId && $primaryOfficeId) {
                        $off = \App\Models\Office::find($primaryOfficeId);
                        if ($off) $primaryDeptId = $off->department_id;
                    }
                } elseif (count($userIds) === 1 && empty($deptIds) && empty($officeIds) && empty($wgIds)) {
                    $primaryTargetType = 'OFFICER';
                    if ($primaryUserId) {
                        $usr = User::find($primaryUserId);
                        if ($usr) {
                            $primaryDeptId = $usr->department_id;
                            $primaryOfficeId = $usr->office_id;
                        }
                    }
                } elseif (count($wgIds) === 1 && empty($deptIds) && empty($officeIds) && empty($userIds)) {
                    $primaryTargetType = 'WORKING_GROUP';
                }

                $updateData['target_type'] = $primaryTargetType;
                $updateData['target_department_id'] = $primaryDeptId;
                $updateData['target_office_id'] = $primaryOfficeId;
                $updateData['target_user_id'] = $primaryUserId;
                $updateData['dispatch_notes'] = $dispatchNotes;
                $updateData['dispatched_at'] = Carbon::now();
                $updateData['dispatched_by'] = $viewer->id;
            }

            $doc->update($updateData);

            if ($hasTargets) {
                $targetSummary = !empty($assignedLabels) ? implode(', ', $assignedLabels) : 'ភាគីពាក់ព័ន្ធ';
                InboundDocumentMovement::create([
                    'inbound_document_id' => $doc->id,
                    'user_id' => $viewer->id,
                    'action' => 'DISPATCHED',
                    'from_status' => 'SUBMITTED_TO_DG',
                    'to_status' => $newStatus,
                    'comment' => "ឯកឧត្តមអគ្គនាយកបានធ្វើចំណារ និងចាត់ចែងចែកចាយភ្លាមៗទៅកាន់៖ {$targetSummary}",
                    'meta' => [
                        'annotation' => $request->input('dg_annotation'),
                        'is_response_required' => $doc->is_response_required,
                        'targets' => $assignedLabels,
                        'department_ids' => $deptIds,
                        'office_ids' => $officeIds,
                        'user_ids' => $userIds,
                        'working_group_ids' => $wgIds,
                    ],
                ]);
            } else {
                InboundDocumentMovement::create([
                    'inbound_document_id' => $doc->id,
                    'user_id' => $viewer->id,
                    'action' => 'DG_ANNOTATED',
                    'from_status' => 'SUBMITTED_TO_DG',
                    'to_status' => 'DG_ANNOTATED',
                    'comment' => 'ឯកឧត្តមអគ្គនាយកបានធ្វើចំណារលើឯកសារ' . ($doc->is_response_required ? ' (តម្រូវឱ្យឆ្លើយតប)' : ' (សម្រាប់ជ្រាប)'),
                    'meta' => [
                        'annotation' => $request->input('dg_annotation'),
                        'is_response_required' => $doc->is_response_required,
                    ],
                ]);
            }

            return $doc->fresh([
                'targetDepartment',
                'targetOffice',
                'targetUser',
                'dispatchedByUser',
                'assignments.department',
                'assignments.office',
                'assignments.user',
                'assignments.workingGroup',
            ]);
        });

        if ($hasTargets) {
            $this->telegram->notifyDispatched($doc);
        }

        return response()->json([
            'message' => $hasTargets
                ? 'បានកត់ត្រាចំណារ និងចាត់ចែងចែកចាយទៅកាន់ភាគីពាក់ព័ន្ធរួចរាល់!'
                : 'បានកត់ត្រាចំណារឯកឧត្តមអគ្គនាយករួចរាល់!',
            'document' => $doc,
        ]);
    }

    /**
     * 9. ជំនួយការ Scan ឯកសារមានចំណារ និងចែកចាយទៅភាគីពាក់ព័ន្ធ (Assistant Scan & Multi-target Dispatch)
     * គាំទ្រការចាត់ចែងទៅមនុស្សច្រើននាក់ ការិយាល័យច្រើន និងនាយកដ្ឋានច្រើនក្នុងពេលតែមួយ
     */
    public function assistantDispatch($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isLeadership = ($viewer->position && (int) $viewer->position->level <= 2);
        if (!$isAdmin && !$isLeadership && !$viewer->hasPermission('inbound-documents-assistant')) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់ចែងចែកចាយឯកសារនេះឡើយ!'], 403);
        }

        $doc = InboundDocument::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'target_type' => 'nullable|in:DEPARTMENT,OFFICE,OFFICER,WORKING_GROUP,MULTIPLE',
            'target_department_id' => 'nullable|exists:departments,id',
            'target_office_id' => 'nullable|exists:offices,id',
            'target_user_id' => 'nullable|exists:users,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'office_ids' => 'nullable|array',
            'office_ids.*' => 'exists:offices,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'working_group_ids' => 'nullable|array',
            'working_group_ids.*' => 'exists:working_groups,id',
            'dispatch_notes' => 'nullable|string',
            'annotated_file' => 'nullable|file|max:25600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $doc = DB::transaction(function () use ($request, $viewer, $doc) {
            $annotatedPath = $doc->annotated_file_path;
            $annotatedName = $doc->annotated_file_name;

            if ($request->hasFile('annotated_file')) {
                if ($annotatedPath && StorageService::exists($annotatedPath)) {
                    StorageService::delete($annotatedPath);
                }
                $file = $request->file('annotated_file');
                $annotatedName = $file->getClientOriginalName();
                $annotatedPath = StorageService::store($file, 'inbound_documents/annotated');
            }

            // If response is required: IN_RESPONSE_PROGRESS; else: DISPATCHED
            $newStatus = $doc->is_response_required ? 'IN_RESPONSE_PROGRESS' : 'DISPATCHED';

            $deptIds = $request->input('department_ids', []);
            $officeIds = $request->input('office_ids', []);
            $userIds = $request->input('user_ids', []);
            $wgIds = $request->input('working_group_ids', []);

            // If legacy single selection was passed, merge it into lists
            if (empty($deptIds) && $request->filled('target_department_id')) {
                $deptIds[] = (int) $request->input('target_department_id');
            }
            if (empty($officeIds) && $request->filled('target_office_id')) {
                $officeIds[] = (int) $request->input('target_office_id');
            }
            if (empty($userIds) && $request->filled('target_user_id')) {
                $userIds[] = (int) $request->input('target_user_id');
            }

            $dispatchNotes = $request->input('dispatch_notes');

            // Clear previous top-level assignments for fresh dispatch
            InboundDocumentAssignment::where('inbound_document_id', $doc->id)->whereNull('parent_assignment_id')->delete();

            $assignedLabels = [];

            // 1. Department assignments
            foreach ($deptIds as $deptId) {
                InboundDocumentAssignment::create([
                    'inbound_document_id' => $doc->id,
                    'assigned_by' => $viewer->id,
                    'target_type' => 'DEPARTMENT',
                    'department_id' => $deptId,
                    'instruction' => $dispatchNotes,
                    'status' => 'PENDING',
                ]);
                $dept = \App\Models\Department::find($deptId);
                if ($dept) $assignedLabels[] = "នាយកដ្ឋាន៖ {$dept->name_kh}";
            }

            // 2. Office assignments
            foreach ($officeIds as $officeId) {
                $off = \App\Models\Office::find($officeId);
                InboundDocumentAssignment::create([
                    'inbound_document_id' => $doc->id,
                    'assigned_by' => $viewer->id,
                    'target_type' => 'OFFICE',
                    'department_id' => $off?->department_id,
                    'office_id' => $officeId,
                    'instruction' => $dispatchNotes,
                    'status' => 'PENDING',
                ]);
                if ($off) $assignedLabels[] = "ការិយាល័យ៖ {$off->name_kh}";
            }

            // 3. User assignments
            foreach ($userIds as $userId) {
                $usr = User::find($userId);
                InboundDocumentAssignment::create([
                    'inbound_document_id' => $doc->id,
                    'assigned_by' => $viewer->id,
                    'target_type' => 'USER',
                    'department_id' => $usr?->department_id,
                    'office_id' => $usr?->office_id,
                    'user_id' => $userId,
                    'instruction' => $dispatchNotes,
                    'status' => 'PENDING',
                ]);
                if ($usr) $assignedLabels[] = "មន្ត្រី៖ " . ($usr->name_kh ?? $usr->name);
            }

            // 4. Working Group assignments
            foreach ($wgIds as $wgId) {
                $wg = \App\Models\WorkingGroup::find($wgId);
                InboundDocumentAssignment::create([
                    'inbound_document_id' => $doc->id,
                    'assigned_by' => $viewer->id,
                    'target_type' => 'WORKING_GROUP',
                    'working_group_id' => $wgId,
                    'instruction' => $dispatchNotes,
                    'status' => 'PENDING',
                ]);
                if ($wg) $assignedLabels[] = "ក្រុមការងារ៖ {$wg->name_kh}";
            }

            // Maintain primary target fields on document for backward compatibility
            $primaryTargetType = 'MULTIPLE';
            $primaryDeptId = !empty($deptIds) ? $deptIds[0] : null;
            $primaryOfficeId = !empty($officeIds) ? $officeIds[0] : null;
            $primaryUserId = !empty($userIds) ? $userIds[0] : null;

            if (count($deptIds) === 1 && empty($officeIds) && empty($userIds)) {
                $primaryTargetType = 'DEPARTMENT';
            } elseif (count($officeIds) === 1 && empty($deptIds) && empty($userIds)) {
                $primaryTargetType = 'OFFICE';
                if (!$primaryDeptId && $primaryOfficeId) {
                    $off = \App\Models\Office::find($primaryOfficeId);
                    if ($off) $primaryDeptId = $off->department_id;
                }
            } elseif (count($userIds) === 1 && empty($deptIds) && empty($officeIds)) {
                $primaryTargetType = 'OFFICER';
                if ($primaryUserId) {
                    $usr = User::find($primaryUserId);
                    if ($usr) {
                        $primaryDeptId = $usr->department_id;
                        $primaryOfficeId = $usr->office_id;
                    }
                }
            }

            $doc->update([
                'target_type' => $primaryTargetType,
                'target_department_id' => $primaryDeptId,
                'target_office_id' => $primaryOfficeId,
                'target_user_id' => $primaryUserId,
                'dispatch_notes' => $dispatchNotes,
                'annotated_file_path' => $annotatedPath,
                'annotated_file_name' => $annotatedName,
                'dispatched_at' => Carbon::now(),
                'dispatched_by' => $viewer->id,
                'status' => $newStatus,
            ]);

            $targetSummary = !empty($assignedLabels) ? implode(', ', $assignedLabels) : 'ភាគីពាក់ព័ន្ធ';

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'DISPATCHED',
                'from_status' => 'DG_ANNOTATED',
                'to_status' => $newStatus,
                'comment' => "បាន Scan ចំណារ និងចាត់ចែងចែកចាយទៅកាន់៖ {$targetSummary}",
                'meta' => [
                    'targets' => $assignedLabels,
                    'department_ids' => $deptIds,
                    'office_ids' => $officeIds,
                    'user_ids' => $userIds,
                    'notes' => $dispatchNotes,
                ],
            ]);

            return $doc->fresh([
                'targetDepartment',
                'targetOffice',
                'targetUser',
                'dispatchedByUser',
                'assignments.department',
                'assignments.office',
                'assignments.user',
            ]);
        });

        // Telegram Notification for dispatched targets
        $this->telegram->notifyDispatched($doc);

        return response()->json([
            'message' => 'បានចែកចាយឯកសារទៅកាន់ភាគីពាក់ព័ន្ធរួចរាល់!',
            'document' => $doc,
        ]);
    }

    /**
     * 9B. ចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រម (Cascading Forward / Re-assign Downward)
     * ច្បាប់៖ អ្នកដែលមានតួនាទីនៅលើ អាចចំណារទៅអ្នកថ្នាក់ក្រោមបន្តបាន ឱ្យតែស្ថិតនៅក្នុងនាយកដ្ឋាន ការិយាល័យ ឬក្រុមការងារជាមួយគ្នា
     */
    public function forward($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $doc = InboundDocument::with(['targetDepartment', 'targetOffice', 'targetUser', 'assignments.workingGroup'])->findOrFail($id);

        if (in_array($doc->status, ['COMPLETED', 'CANCELLED'])) {
            return response()->json(['message' => 'ឯកសារនេះត្រូវបានបញ្ចប់រួចរាល់ហើយ មិនអាចចាត់ចែងបន្តបានទៀតទេ!'], 400);
        }

        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isSuper = $isAdmin || ($viewerPosLevel <= 2) || $viewer->hasPermission('inbound-documents-assistant');

        // Check if user is leader or deputy leader of an assigned working group
        $assignedWgIds = $doc->assignments->pluck('working_group_id')->filter()->unique()->toArray();
        $isWgLeader = false;
        if (!empty($assignedWgIds)) {
            $isWgLeader = \App\Models\WorkingGroupMember::whereIn('working_group_id', $assignedWgIds)
                ->where('user_id', $viewer->id)
                ->whereIn('role', ['LEADER', 'DEPUTY_LEADER'])
                ->where('status', 'ACTIVE')
                ->exists();
        }

        $isDeptDirector = ($viewerPosLevel <= 4) && !empty($viewer->department_id);
        $isDeptLeader = ($viewerPosLevel <= 5) && !empty($viewer->department_id);
        $isOfficeLeader = ($viewerPosLevel <= 8) && !empty($viewer->office_id); // Head of Division (6), Acting (7), Deputy Head (8)
        $isDirectlyAssigned = ((int)$doc->target_user_id === (int)$viewer->id)
            || $doc->assignments->contains(fn($a) => (int)$a->user_id === (int)$viewer->id);

        $hasDeptAssignment = $doc->assignments->contains(fn($a) => (int)$a->department_id === (int)$viewer->department_id);
        $hasOfficeAssignment = $doc->assignments->contains(fn($a) => (int)$a->office_id === (int)$viewer->office_id);

        $isAuthorized = $isSuper
            || $isWgLeader
            || ($isDeptLeader && (
                (int)$doc->target_department_id === (int)$viewer->department_id
                || $hasDeptAssignment
            ))
            || ($isOfficeLeader && (
                (int)$doc->target_office_id === (int)$viewer->office_id
                || $hasOfficeAssignment
            ))
            || $isDirectlyAssigned;

        if (!$isAuthorized) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់ចែងបន្តលើឯកសារនេះឡើយ!'], 403);
        }

        if (!$isAdmin) {
            $hasAlreadyForwarded = InboundDocumentAssignment::where('inbound_document_id', $doc->id)
                ->where('assigned_by', $viewer->id)
                ->where('status', '!=', 'RECALLED')
                ->exists();
            if ($hasAlreadyForwarded) {
                return response()->json(['message' => 'លោកអ្នកបានចាត់ចែង និងបញ្ជូនបន្តឯកសារនេះរួចហើយ! (ករណីចង់ចាត់ចែងឡើងវិញ សូមទាញយកការចាត់ចែងមកវិញជាមុនសិន)'], 400);
            }
        }

        $validator = Validator::make($request->all(), [
            'target_type' => 'nullable|in:DEPARTMENT,OFFICE,OFFICER,WORKING_GROUP,WG_MEMBER',
            'target_department_id' => 'nullable|exists:departments,id',
            'target_office_id' => 'nullable|exists:offices,id',
            'target_user_id' => 'nullable|exists:users,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'target_working_group_id' => 'nullable|exists:working_groups,id',
            'working_group_id' => 'nullable|exists:working_groups,id',
            'forwarding_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $userIds = $request->input('user_ids', []);
        if (empty($userIds) && $request->filled('target_user_id')) {
            $userIds[] = (int) $request->input('target_user_id');
        }

        $targetType = $request->input('target_type', 'OFFICER');
        $newOfficeId = $request->input('target_office_id');
        $newDeptId = $request->input('target_department_id');
        $newWgId = $request->input('target_working_group_id') ?: $request->input('working_group_id');

        // Enforce Workflow Sending Constraints (Sending Type Group) or Default Hierarchy
        $wfConfig = \App\Models\DocumentWorkflowConfig::where('user_id', $viewer->id)->first();

        if ($wfConfig) {
            if ($wfConfig->send_type === 'ALL' || $isSuper) {
                // Allowed to forward to all departments, offices, officers, and working groups without restriction
            } elseif ($wfConfig->send_type === 'CUSTOM') {
                $allowedDeptIds = array_map('intval', $wfConfig->send_dept_ids ?: []);
                $allowedOfficeIds = array_map('intval', $wfConfig->send_office_ids ?: []);
                $allowedUserIds = array_map('intval', $wfConfig->send_user_ids ?: []);
                $allowedWgIds = array_map('intval', $wfConfig->send_working_group_ids ?: []);

                if ($targetType === 'DEPARTMENT' || $newDeptId) {
                    if (!in_array((int)$newDeptId, $allowedDeptIds)) {
                        return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់បន្តទៅកាន់នាយកដ្ឋាននេះឡើយ (តាមការកំណត់ Sending Group)!'], 403);
                    }
                }
                if ($targetType === 'OFFICE' || $newOfficeId) {
                    if (!in_array((int)$newOfficeId, $allowedOfficeIds)) {
                        return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់បន្តទៅកាន់ការិយាល័យនេះឡើយ (តាមការកំណត់ Sending Group)!'], 403);
                    }
                }
                if ($targetType === 'WORKING_GROUP' || $newWgId) {
                    if (!in_array((int)$newWgId, $allowedWgIds)) {
                        return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់បន្តទៅកាន់ក្រុមការងារនេះឡើយ (តាមការកំណត់ Sending Group)!'], 403);
                    }
                }
                if (!empty($userIds)) {
                    foreach ($userIds as $uid) {
                        if (!in_array((int)$uid, $allowedUserIds)) {
                            $tu = User::find($uid);
                            $tuName = $tu ? ($tu->name_kh ?: $tu->name) : "ID {$uid}";
                            return response()->json(['message' => "លោកអ្នកមិនមានសិទ្ធិចាត់បន្តទៅកាន់មន្ត្រី {$tuName} ឡើយ (តាមការកំណត់ Sending Group)!"], 403);
                        }
                    }
                }
            } elseif ($wfConfig->send_type === 'DEPARTMENT') {
                $myDeptId = (int) $viewer->department_id;
                if ($targetType === 'DEPARTMENT' || ($newDeptId && (int)$newDeptId !== $myDeptId)) {
                    return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានតែក្នុងនាយកដ្ឋានរបស់លោកអ្នកប៉ុណ្ណោះ!'], 403);
                }
                if ($targetType === 'WORKING_GROUP' || $newWgId) {
                    return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់បន្តទៅកាន់ក្រុមការងារឡើយ!'], 403);
                }
                if ($targetType === 'OFFICE' || $newOfficeId) {
                    $targetOffice = \App\Models\Office::find($newOfficeId);
                    if (!$targetOffice || (int)$targetOffice->department_id !== $myDeptId) {
                        return response()->json(['message' => 'ការិយាល័យនេះមិនស្ថិតនៅក្នុងនាយកដ្ឋានរបស់លោកអ្នកឡើយ!'], 422);
                    }
                }
                if (!empty($userIds)) {
                    $targetUsers = User::whereIn('id', $userIds)->get();
                    foreach ($targetUsers as $tu) {
                        if ((int)$tu->department_id !== $myDeptId || $tu->id === $viewer->id) {
                            return response()->json(['message' => "មន្ត្រី {$tu->name_kh} មិនស្ថិតក្នុងនាយកដ្ឋានរបស់លោកអ្នកឡើយ!"], 422);
                        }
                    }
                }
            } elseif ($wfConfig->send_type === 'OFFICE') {
                $myOfficeId = (int) $viewer->office_id;
                if ($targetType === 'DEPARTMENT' || $newDeptId || $targetType === 'WORKING_GROUP' || $newWgId) {
                    return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានតែក្នុងការិយាល័យរបស់លោកអ្នកប៉ុណ្ណោះ!'], 403);
                }
                if ($targetType === 'OFFICE' && (int)$newOfficeId !== $myOfficeId) {
                    return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានតែក្នុងការិយាល័យរបស់លោកអ្នកប៉ុណ្ណោះ!'], 403);
                }
                if (!empty($userIds)) {
                    $targetUsers = User::whereIn('id', $userIds)->get();
                    foreach ($targetUsers as $tu) {
                        if ((int)$tu->office_id !== $myOfficeId || $tu->id === $viewer->id) {
                            return response()->json(['message' => "មន្ត្រី {$tu->name_kh} មិនស្ថិតក្នុងការិយាល័យរបស់លោកអ្នកឡើយ!"], 422);
                        }
                    }
                }
            } elseif ($wfConfig->send_type === 'SUBORDINATES') {
                if ($targetType === 'DEPARTMENT' || $newDeptId || $targetType === 'WORKING_GROUP' || $newWgId) {
                    return response()->json(['message' => 'លោកអ្នកមានសិទ្ធិចាត់បន្តបានត្រឹមតែមន្ត្រីក្រោមឱវាទប៉ុណ្ណោះ!'], 403);
                }
                if (!empty($userIds)) {
                    $targetUsers = User::with('position')->whereIn('id', $userIds)->get();
                    foreach ($targetUsers as $tu) {
                        $tuLevel = $tu->position ? (int) $tu->position->level : 999;
                        $sameDept = $viewer->department_id && (int)$tu->department_id === (int)$viewer->department_id;
                        $sameOffice = $viewer->office_id && (int)$tu->office_id === (int)$viewer->office_id;
                        if ((!$sameDept && !$sameOffice) || $tu->id === $viewer->id || $tuLevel <= $viewerPosLevel) {
                            return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានត្រឹមតែមន្ត្រីក្រោមឱវាទប៉ុណ្ណោះ!'], 422);
                        }
                    }
                }
            }
        } elseif (!$isSuper) {
            // Non-super cannot forward to other departments
            if ($targetType === 'DEPARTMENT' || $newDeptId) {
                return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់បន្តទៅកាន់នាយកដ្ឋានផ្សេងឡើយ!'], 403);
            }

            // Non-super cannot forward to an entire working group
            if ($targetType === 'WORKING_GROUP' || $newWgId) {
                return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់បន្តទៅកាន់ក្រុមការងារឡើយ!'], 403);
            }

            // Forwarding to an Office: Department Leadership (level <= 5) can forward to an office in their department
            if ($targetType === 'OFFICE' || $newOfficeId) {
                if (!$isDeptLeader) {
                    return response()->json(['message' => 'មានតែថ្នាក់ដឹកនាំនាយកដ្ឋានទេ ទើបអាចចាត់បន្តទៅកាន់ការិយាល័យបាន!'], 403);
                }
                $targetOffice = \App\Models\Office::find($newOfficeId);
                if (!$targetOffice || (int)$targetOffice->department_id !== (int)$viewer->department_id) {
                    return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានតែការិយាល័យចំណុះនាយកដ្ឋានរបស់លោកអ្នកប៉ុណ្ណោះ!'], 422);
                }
            }

            // Forwarding to Officers:
            if (!empty($userIds)) {
                $targetUsers = User::with('position')->whereIn('id', $userIds)->get();

                if ($targetType === 'WG_MEMBER' || ($isWgLeader && !$isDeptLeader && !$isOfficeLeader)) {
                    // Must be active members of the assigned working group
                    $activeMemberUserIds = \App\Models\WorkingGroupMember::whereIn('working_group_id', $assignedWgIds)
                        ->where('status', 'ACTIVE')
                        ->pluck('user_id')
                        ->toArray();

                    foreach ($targetUsers as $tu) {
                        if (!in_array($tu->id, $activeMemberUserIds) || $tu->id === $viewer->id) {
                            return response()->json(['message' => "មន្ត្រី {$tu->name_kh} មិនមែនជាសមាជិកក្នុងក្រុមការងារជាមួយគ្នាឡើយ!"], 422);
                        }
                    }
                } elseif ($isOfficeLeader && !$isDeptLeader) {
                    // Must be in the same office and strictly lower position level
                    foreach ($targetUsers as $tu) {
                        $tuLevel = $tu->position ? (int) $tu->position->level : 999;
                        if ((int)$tu->office_id !== (int)$viewer->office_id || $tu->id === $viewer->id || $tuLevel <= $viewerPosLevel) {
                            return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានត្រឹមតែមន្ត្រីក្រោមឱវាទក្នុងការិយាល័យជាមួយគ្នាតែប៉ុណ្ណោះ!'], 422);
                        }
                    }
                } elseif ($isDeptLeader) {
                    // Must be in the same department and strictly lower position level
                    foreach ($targetUsers as $tu) {
                        $tuLevel = $tu->position ? (int) $tu->position->level : 999;
                        if ((int)$tu->department_id !== (int)$viewer->department_id || $tu->id === $viewer->id || $tuLevel <= $viewerPosLevel) {
                            return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានត្រឹមតែមន្ត្រីក្រោមឱវាទក្នុងនាយកដ្ឋានជាមួយគ្នាតែប៉ុណ្ណោះ!'], 422);
                        }
                    }
                } else {
                    // Assigned officer forwarding to subordinates with strictly lower position level
                    foreach ($targetUsers as $tu) {
                        $tuLevel = $tu->position ? (int) $tu->position->level : 999;
                        $sameDept = $viewer->department_id && (int)$tu->department_id === (int)$viewer->department_id;
                        $sameOffice = $viewer->office_id && (int)$tu->office_id === (int)$viewer->office_id;
                        if ((!$sameDept && !$sameOffice) || $tu->id === $viewer->id || $tuLevel <= $viewerPosLevel) {
                            return response()->json(['message' => 'លោកអ្នកអាចចាត់បន្តបានត្រឹមតែមន្ត្រីក្រោមឱវាទជាមួយគ្នាតែប៉ុណ្ណោះ!'], 422);
                        }
                    }
                }
            }
        }

        $result = DB::transaction(function () use ($request, $viewer, $doc, $userIds, $targetType, $newOfficeId, $newDeptId, $newWgId, $isWgLeader, $assignedWgIds) {
            $forwardingNotes = $request->input('forwarding_notes');

            // Find parent assignment for viewer if exists
            $parentAssignment = InboundDocumentAssignment::where('inbound_document_id', $doc->id)
                ->where(function ($q) use ($viewer, $assignedWgIds) {
                    $q->where('user_id', $viewer->id);
                    if ($viewer->office_id) {
                        $q->orWhere('office_id', $viewer->office_id);
                    }
                    if ($viewer->department_id) {
                        $q->orWhere('department_id', $viewer->department_id);
                    }
                    if (!empty($assignedWgIds)) {
                        $q->orWhereIn('working_group_id', $assignedWgIds);
                    }
                })
                ->latest()
                ->first();

            $forwardedLabels = [];

            if (!empty($userIds)) {
                $wgIdForAssignment = ($targetType === 'WG_MEMBER' || $isWgLeader)
                    ? ($parentAssignment?->working_group_id ?? (!empty($assignedWgIds) ? $assignedWgIds[0] : null))
                    : null;

                foreach ($userIds as $uId) {
                    $usr = User::find($uId);
                    InboundDocumentAssignment::create([
                        'inbound_document_id' => $doc->id,
                        'assigned_by' => $viewer->id,
                        'parent_assignment_id' => $parentAssignment?->id,
                        'target_type' => 'USER',
                        'department_id' => $usr?->department_id,
                        'office_id' => $usr?->office_id,
                        'user_id' => $uId,
                        'working_group_id' => $wgIdForAssignment,
                        'instruction' => $forwardingNotes,
                        'status' => 'PENDING',
                    ]);
                    if ($usr) $forwardedLabels[] = "មន្ត្រី៖ " . ($usr->name_kh ?? $usr->name);
                }

                // Update doc primary target
                $primaryUsr = User::find($userIds[0]);
                if ($primaryUsr) {
                    $doc->update([
                        'target_type' => 'OFFICER',
                        'target_user_id' => $primaryUsr->id,
                        'target_office_id' => $primaryUsr->office_id ?? $doc->target_office_id,
                        'target_department_id' => $primaryUsr->department_id ?? $doc->target_department_id,
                        'status' => in_array($doc->status, ['SUBMITTED_TO_DG', 'SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED']) ? 'DISPATCHED' : $doc->status,
                        'dispatched_at' => $doc->dispatched_at ?: Carbon::now(),
                        'dispatched_by' => $doc->dispatched_by ?: $viewer->id,
                    ]);
                }
            } elseif ($newOfficeId) {
                $off = \App\Models\Office::find($newOfficeId);
                InboundDocumentAssignment::create([
                    'inbound_document_id' => $doc->id,
                    'assigned_by' => $viewer->id,
                    'parent_assignment_id' => $parentAssignment?->id,
                    'target_type' => 'OFFICE',
                    'department_id' => $off?->department_id,
                    'office_id' => $newOfficeId,
                    'instruction' => $forwardingNotes,
                    'status' => 'PENDING',
                ]);
                if ($off) $forwardedLabels[] = "ការិយាល័យ៖ {$off->name_kh}";

                $doc->update([
                    'target_type' => 'OFFICE',
                    'target_office_id' => $newOfficeId,
                    'target_department_id' => $off?->department_id ?? $doc->target_department_id,
                    'target_user_id' => null,
                    'status' => in_array($doc->status, ['SUBMITTED_TO_DG', 'SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED']) ? 'DISPATCHED' : $doc->status,
                    'dispatched_at' => $doc->dispatched_at ?: Carbon::now(),
                    'dispatched_by' => $doc->dispatched_by ?: $viewer->id,
                ]);
            } elseif ($newDeptId) {
                $dept = \App\Models\Department::find($newDeptId);
                InboundDocumentAssignment::create([
                    'inbound_document_id' => $doc->id,
                    'assigned_by' => $viewer->id,
                    'parent_assignment_id' => $parentAssignment?->id,
                    'target_type' => 'DEPARTMENT',
                    'department_id' => $newDeptId,
                    'instruction' => $forwardingNotes,
                    'status' => 'PENDING',
                ]);
                if ($dept) $forwardedLabels[] = "នាយកដ្ឋាន៖ {$dept->name_kh}";

                $doc->update([
                    'target_type' => 'DEPARTMENT',
                    'target_department_id' => $newDeptId,
                    'target_office_id' => null,
                    'target_user_id' => null,
                    'status' => in_array($doc->status, ['SUBMITTED_TO_DG', 'SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED']) ? 'DISPATCHED' : $doc->status,
                    'dispatched_at' => $doc->dispatched_at ?: Carbon::now(),
                    'dispatched_by' => $doc->dispatched_by ?: $viewer->id,
                ]);
            } elseif ($newWgId) {
                $wg = \App\Models\WorkingGroup::find($newWgId);
                InboundDocumentAssignment::create([
                    'inbound_document_id' => $doc->id,
                    'assigned_by' => $viewer->id,
                    'parent_assignment_id' => $parentAssignment?->id,
                    'target_type' => 'WORKING_GROUP',
                    'working_group_id' => $newWgId,
                    'instruction' => $forwardingNotes,
                    'status' => 'PENDING',
                ]);
                if ($wg) $forwardedLabels[] = "ក្រុមការងារ៖ {$wg->name_kh}";

                $doc->update([
                    'target_type' => 'WORKING_GROUP',
                    'status' => in_array($doc->status, ['SUBMITTED_TO_DG', 'SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED']) ? 'DISPATCHED' : $doc->status,
                    'dispatched_at' => $doc->dispatched_at ?: Carbon::now(),
                    'dispatched_by' => $doc->dispatched_by ?: $viewer->id,
                ]);
            }

            // Mark parent assignment status as IN_PROGRESS
            if ($parentAssignment && $parentAssignment->status === 'PENDING') {
                $parentAssignment->update(['status' => 'IN_PROGRESS']);
            }

            $targetSummary = !empty($forwardedLabels) ? implode(', ', $forwardedLabels) : 'មន្ត្រីក្រោមឱវាទ';
            $commentText = $forwardingNotes
                ? ("បានចាត់បន្តទៅកាន់ {$targetSummary} ៖ {$forwardingNotes}")
                : ("បានចាត់បន្តទៅកាន់ {$targetSummary}");

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'FORWARDED',
                'from_status' => $doc->status,
                'to_status' => $doc->status,
                'comment' => $commentText,
                'meta' => [
                    'from' => $viewer->name_kh ?? $viewer->name,
                    'to' => $targetSummary,
                    'notes' => $forwardingNotes,
                ],
            ]);

            return [
                $doc->fresh(['targetDepartment', 'targetOffice', 'targetUser', 'assignments.department', 'assignments.office', 'assignments.user', 'assignments.workingGroup', 'movements.user']),
                $targetSummary
            ];
        });

        [$freshDoc, $targetDesc] = $result;
        $this->telegram->notifyForwarded($freshDoc, $viewer, $targetDesc, $request->input('forwarding_notes'), $freshDoc->targetUser);

        return response()->json([
            'success' => true,
            'message' => 'បានចាត់ចែង និងបញ្ជូនបន្តដោយជោគជ័យ!',
            'document' => $freshDoc,
        ]);
    }

    /**
     * 10. ទទួលជ្រាប និងបញ្ចប់ដំណើរការ (Acknowledge for Path A)
     */
    public function acknowledge($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isLeadership = ($viewer->position && (int) $viewer->position->level <= 2);
        $isAuthorized = $isAdmin
            || $isLeadership
            || ($doc->target_user_id === $viewer->id)
            || ($doc->target_office_id && $doc->target_office_id === $viewer->office_id)
            || ($doc->target_department_id && $doc->target_department_id === $viewer->department_id)
            || $viewer->hasPermission('inbound-documents-assistant');

        if (!$isAuthorized) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិទទួលជ្រាបឯកសារនេះឡើយ!'], 403);
        }

        if ($doc->is_response_required) {
            return response()->json(['message' => 'ឯកសារនេះតម្រូវឱ្យមានលិខិតឆ្លើយតប មិនអាចចុចត្រឹមទទួលជ្រាបបានទេ!'], 400);
        }

        if (!$isAdmin) {
            $hasAlreadyForwarded = InboundDocumentAssignment::where('inbound_document_id', $doc->id)
                ->where('assigned_by', $viewer->id)
                ->where('status', '!=', 'RECALLED')
                ->exists();
            if ($hasAlreadyForwarded) {
                return response()->json(['message' => 'លោកអ្នកបានចាត់ចែង និងបញ្ជូនបន្តឯកសារនេះរួចហើយ ពុំអាចទទួលជ្រាបបញ្ចប់ឯកសារបានឡើយ!'], 400);
            }
        }

        $doc->update([
            'status' => 'COMPLETED',
            'acknowledged_at' => Carbon::now(),
            'acknowledged_by' => $viewer->id,
            'completed_at' => Carbon::now(),
        ]);

        InboundDocumentMovement::create([
            'inbound_document_id' => $doc->id,
            'user_id' => $viewer->id,
            'action' => 'ACKNOWLEDGED',
            'from_status' => 'DISPATCHED',
            'to_status' => 'COMPLETED',
            'comment' => $request->input('comment', 'បានទទួលជ្រាប និងបញ្ចប់ដំណើរការឯកសារ'),
        ]);

        return response()->json([
            'message' => 'បានទទួលជ្រាប និងបញ្ចប់ដំណើរការឯកសារដោយជោគជ័យ!',
            'document' => $doc->fresh(),
        ]);
    }

    /**
     * 11. បេក្ខភាពថ្នាក់ដឹកនាំសម្រាប់ឆ្លងលិខិតឆ្លើយតប (Get Next Reviewer Candidates)
     * គាំទ្រទាំងខ្សែរត់តាមឋានានុក្រមអង្គភាព (Officer -> Office Chief -> Dept Director -> Deputy DG -> DG)
     * និងខ្សែរត់តាមក្រុមការងារ (Member -> WG Leader -> DG)
     */
    public function getNextResponseApprovers($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::with(['assignments.workingGroup'])->findOrFail($id);

        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $targetDeptId = $viewer->department_id;
        $targetOfficeId = $viewer->office_id;

        $candidates = collect();

        // 0. Check explicit DocumentWorkflowConfig for Sending Type Group (DEPARTMENT / OFFICE)
        $wfConfig = \App\Models\DocumentWorkflowConfig::where('user_id', $viewer->id)->first();
        if ($wfConfig) {
            $sendType = $wfConfig->send_type ?? 'SUBORDINATES';
            if ($sendType === 'DEPARTMENT' && $viewer->department_id) {
                $candidates = User::where('department_id', $viewer->department_id)
                    ->where('id', '!=', $viewer->id)
                    ->whereNotIn('status', ['DISABLED', 'SUSPENDED'])
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get()
                    ->sortBy([
                        fn($a, $b) => ($a->position?->level ?? 999) <=> ($b->position?->level ?? 999),
                        fn($a, $b) => strcmp($a->name_kh ?? $a->name, $b->name_kh ?? $b->name),
                    ])
                    ->values();
                return response()->json([
                    'candidates' => $candidates,
                    'viewer_level' => $viewerPosLevel,
                    'is_dg' => false,
                ]);
            } elseif ($sendType === 'OFFICE' && $viewer->office_id) {
                $candidates = User::where('office_id', $viewer->office_id)
                    ->where('id', '!=', $viewer->id)
                    ->whereNotIn('status', ['DISABLED', 'SUSPENDED'])
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get()
                    ->sortBy([
                        fn($a, $b) => ($a->position?->level ?? 999) <=> ($b->position?->level ?? 999),
                        fn($a, $b) => strcmp($a->name_kh ?? $a->name, $b->name_kh ?? $b->name),
                    ])
                    ->values();
                return response()->json([
                    'candidates' => $candidates,
                    'viewer_level' => $viewerPosLevel,
                    'is_dg' => false,
                ]);
            }
        }

        // 1. Check if doc has an assigned working group and user is an active member
        $assignedWgIds = $doc->assignments->pluck('working_group_id')->filter()->unique()->toArray();
        $isWgMember = false;
        $isWgLeader = false;

        if (!empty($assignedWgIds)) {
            $wgMembership = \App\Models\WorkingGroupMember::whereIn('working_group_id', $assignedWgIds)
                ->where('user_id', $viewer->id)
                ->where('status', 'ACTIVE')
                ->first();

            if ($wgMembership) {
                $isWgMember = true;
                $isWgLeader = ($wgMembership->role === 'LEADER');
            }
        }

        // If user is a working group member (not leader), include Working Group Leader and Deputy Leaders
        if ($isWgMember && !$isWgLeader) {
            $wgLeaderUserIds = \App\Models\WorkingGroupMember::whereIn('working_group_id', $assignedWgIds)
                ->whereIn('role', ['LEADER', 'DEPUTY_LEADER'])
                ->where('status', 'ACTIVE')
                ->where('user_id', '!=', $viewer->id)
                ->pluck('user_id');

            $wgCandidates = User::whereIn('id', $wgLeaderUserIds)
                ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                ->get();

            $candidates = $candidates->merge($wgCandidates);
        }

        if ($viewerPosLevel === 1) {
            // DG (Level 1) - Final level, no next approver
            return response()->json([
                'candidates' => [],
                'viewer_level' => $viewerPosLevel,
                'is_dg' => true,
            ]);
        } elseif ($viewerPosLevel === 2 || ($isWgMember && $isWgLeader)) {
            // Deputy DG (Level 2) or Working Group Leader -> DG (Level 1)
            $dgCandidates = User::whereHas('position', fn($q) => $q->where('level', 1))
                ->where('id', '!=', $viewer->id)
                ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                ->get();
            $candidates = $candidates->merge($dgCandidates);
        } elseif ($viewerPosLevel <= 4 && $viewerPosLevel >= 3) {
            // Dept Director -> Deputy DG / DG (Level 1, 2)
            $leadershipCandidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                ->where('id', '!=', $viewer->id)
                ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                ->get();
            $candidates = $candidates->merge($leadershipCandidates);
        } elseif ($viewerPosLevel === 5) {
            // Deputy Dept Director -> Dept Director (Level 3, 4) or Leadership
            if ($targetDeptId) {
                $deptCandidates = User::where('department_id', $targetDeptId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
                $candidates = $candidates->merge($deptCandidates);
            }
            if ($candidates->isEmpty()) {
                $leadershipCandidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
                $candidates = $candidates->merge($leadershipCandidates);
            }
        } elseif ($viewerPosLevel <= 8 && $viewerPosLevel >= 6) {
            // Office Leadership (Office Chief 6, Acting 7, Deputy Chief 8) -> Dept Leadership (Level 3, 4, 5) or Leadership (Level 1, 2)
            if ($targetDeptId) {
                $deptCandidates = User::where('department_id', $targetDeptId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
                $candidates = $candidates->merge($deptCandidates);
            }
            if ($candidates->isEmpty()) {
                $leadershipCandidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
                $candidates = $candidates->merge($leadershipCandidates);
            }
        } else {
            // Regular Officer (Level >= 9) -> Office Leadership (Level 6, 7, 8) or Dept Leadership
            if ($targetOfficeId) {
                $officeCandidates = User::where('office_id', $targetOfficeId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [6, 7, 8]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
                $candidates = $candidates->merge($officeCandidates);
            }
            if ($candidates->isEmpty() && $targetDeptId) {
                $deptCandidates = User::where('department_id', $targetDeptId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
                $candidates = $candidates->merge($deptCandidates);
            }
            if ($candidates->isEmpty()) {
                $leadershipCandidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
                $candidates = $candidates->merge($leadershipCandidates);
            }
        }

        // Deduplicate and filter out viewer, sort by level ASC, name_kh ASC
        $uniqueCandidates = $candidates->filter(fn($u) => (int) $u->id !== (int) $viewer->id)
            ->unique('id')
            ->sortBy([
                fn($a, $b) => ($a->position?->level ?? 999) <=> ($b->position?->level ?? 999),
                fn($a, $b) => strcmp($a->name_kh ?? $a->name, $b->name_kh ?? $b->name),
            ])->values();

        return response()->json([
            'candidates' => $uniqueCandidates,
            'viewer_level' => $viewerPosLevel,
            'is_dg' => ($viewerPosLevel === 1),
        ]);
    }

    /**
     * 12. ដាក់ស្នើ ឬរក្សាទុកព្រាងលិខិតឆ្លើយតប (Submit or Save Draft Response)
     * គាំទ្រទាំងទម្រង់ របាយការណ៍ (REPORT) និង កំណត់បង្ហាញ (SUBMISSION_NOTE) ជាមួយ Word-like Rich Text
     */
    public function submitResponseDraft($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::with('assignments')->findOrFail($id);

        if (!$this->canUserAccessDocument($doc, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលមើលឯកសារនេះឡើយ!'], 403);
        }

        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isDg = ($viewerPosLevel === 1);

        // គ្រប់គ្នាដែលស្ថិតនៅក្រោមអគ្គនាយក សុទ្ធតែមានសិទ្ធិបង្កើតរបាយការណ៍
        if (!$isAdmin && $isDg) {
            return response()->json(['message' => 'ឯកឧត្តមអគ្គនាយកជាអ្នកពិនិត្យ និងឯកភាពលើរបាយការណ៍ មិនមែនជាអ្នករៀបចំឡើយ!'], 403);
        }

        $isDraft = filter_var($request->input('is_draft', false), FILTER_VALIDATE_BOOLEAN);

        $rules = [
            'title' => 'required|string',
            'response_type' => 'nullable|in:REPORT,SUBMISSION_NOTE,LETTER',
            'header_organization' => 'nullable|string',
            'reference_note' => 'nullable|string',
            'content' => 'nullable|string',
            'sign_title' => 'nullable|string',
            'sign_name' => 'nullable|string',
            'response_file' => 'nullable|file|max:25600',
            'comment' => 'nullable|string',
        ];

        if (!$isDraft) {
            $rules['forwarded_to_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = DB::transaction(function () use ($request, $viewer, $doc, $isDraft) {
            $filePath = null;
            $fileName = null;
            if ($request->hasFile('response_file')) {
                $file = $request->file('response_file');
                $fileName = $file->getClientOriginalName();
                $filePath = StorageService::store($file, 'inbound_documents/responses');
            }

            $responseType = $request->input('response_type', 'REPORT');
            $responseTypeKh = match ($responseType) {
                'SUBMISSION_NOTE' => 'កំណត់បង្ហាញ',
                'LETTER' => 'លិខិត',
                default => 'របាយការណ៍',
            };

            // Check if existing draft or returned response already exists by this author
            $response = InboundDocumentResponse::where('inbound_document_id', $doc->id)
                ->where('drafted_by', $viewer->id)
                ->whereIn('status', ['DRAFT', 'RETURNED'])
                ->latest()
                ->first();

            $updateData = [
                'response_type' => $responseType,
                'title' => $request->input('title'),
                'header_organization' => $request->input('header_organization'),
                'reference_note' => $request->input('reference_note'),
                'content' => $request->input('content'),
                'sign_title' => $request->input('sign_title'),
                'sign_name' => $request->input('sign_name', $viewer->name_kh ?? $viewer->name),
                'department_id' => $viewer->department_id,
                'office_id' => $viewer->office_id,
            ];

            if ($filePath) {
                $updateData['file_path'] = $filePath;
                $updateData['file_name'] = $fileName;
            }

            if ($isDraft) {
                // Save Draft Mode
                $updateData['status'] = 'DRAFT';
                $updateData['current_approver_id'] = $viewer->id;
                $updateData['current_stage'] = 'OFFICE';

                if ($response) {
                    $response->update($updateData);
                } else {
                    $updateData['inbound_document_id'] = $doc->id;
                    $updateData['drafted_by'] = $viewer->id;
                    $response = InboundDocumentResponse::create($updateData);
                }

                InboundDocumentMovement::create([
                    'inbound_document_id' => $doc->id,
                    'user_id' => $viewer->id,
                    'action' => 'RESPONSE_DRAFTED',
                    'from_status' => $doc->status,
                    'to_status' => $doc->status,
                    'comment' => "បានរក្សាទុកសេចក្តីព្រាង{$responseTypeKh} ៖ {$response->title}",
                ]);

                return [$response->load(['draftedByUser', 'currentApprover']), null, true];
            }

            // Submit Upward Mode
            $targetReviewer = User::with('position')->find($request->input('forwarded_to_id'));
            $reviewerLevel = $targetReviewer->position ? (int) $targetReviewer->position->level : 99;

            $stage = match (true) {
                $reviewerLevel === 1 => 'DG',
                $reviewerLevel === 2 => 'LEADERSHIP',
                $reviewerLevel <= 5 => 'DEPARTMENT',
                default => 'OFFICE',
            };

            $updateData['status'] = 'UNDER_REVIEW';
            $updateData['current_approver_id'] = $targetReviewer->id;
            $updateData['current_stage'] = $stage;
            $updateData['submitted_at'] = Carbon::now();

            if ($response) {
                $response->update($updateData);
            } else {
                $updateData['inbound_document_id'] = $doc->id;
                $updateData['drafted_by'] = $viewer->id;
                $response = InboundDocumentResponse::create($updateData);
            }

            InboundDocumentResponseApproval::create([
                'response_id' => $response->id,
                'user_id' => $viewer->id,
                'action' => 'SUBMIT',
                'stage' => $stage,
                'comment' => $request->input('comment', "បានរៀបចំ និងដាក់ស្នើឆ្លង{$responseTypeKh}"),
                'forwarded_to_id' => $targetReviewer->id,
            ]);

            $doc->update([
                'status' => 'IN_RESPONSE_PROGRESS',
                'is_response_required' => true,
            ]);

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'RESPONSE_FORWARDED',
                'from_status' => $doc->status,
                'to_status' => 'IN_RESPONSE_PROGRESS',
                'comment' => "បានដាក់ស្នើឆ្លង{$responseTypeKh} ទៅកាន់ {$targetReviewer->name_kh}",
            ]);

            return [$response->load(['draftedByUser', 'currentApprover']), $targetReviewer, false];
        });

        [$responseModel, $targetReviewer, $isDraftSaved] = $result;

        if (!$isDraftSaved && $targetReviewer) {
            $this->telegram->notifyResponseSubmitted($responseModel, $viewer, $targetReviewer);
        }

        return response()->json([
            'message' => $isDraftSaved
                ? 'បានរក្សាទុកសេចក្តីព្រាងដោយជោគជ័យ!'
                : "បានដាក់ស្នើព្រាងលិខិតឆ្លើយតបទៅកាន់ {$targetReviewer->name_kh} រួចរាល់!",
            'response' => $responseModel,
            'is_draft' => $isDraftSaved,
        ], 201);
    }

    /**
     * 12B. កែប្រែសេចក្តីព្រាងលិខិតឆ្លើយតប (Update Draft Response)
     */
    public function updateResponseDraft($responseId, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::findOrFail($responseId);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if (!$isAdmin && $response->drafted_by !== $viewer->id) {
            return response()->json(['message' => 'លោកអ្នកមិនមែនជាអ្នកតាក់តែងលិខិតឆ្លើយតបនេះឡើយ!'], 403);
        }

        if (!in_array($response->status, ['DRAFT', 'RETURNED'])) {
            return response()->json(['message' => 'មិនអាចកែប្រែលិខិតដែលកំពុងស្ថិតក្នុងដំណើរការពិនិត្យ ឬអនុម័តរួចបានទេ!'], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string',
            'response_type' => 'nullable|in:REPORT,SUBMISSION_NOTE,LETTER',
            'header_organization' => 'nullable|string',
            'reference_note' => 'nullable|string',
            'content' => 'nullable|string',
            'sign_title' => 'nullable|string',
            'sign_name' => 'nullable|string',
            'response_file' => 'nullable|file|max:25600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = $request->only([
            'title',
            'response_type',
            'header_organization',
            'reference_note',
            'content',
            'sign_title',
            'sign_name',
        ]);

        if ($request->hasFile('response_file')) {
            $file = $request->file('response_file');
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_path'] = StorageService::store($file, 'inbound_documents/responses');
        }

        $response->update($updateData);

        return response()->json([
            'message' => 'បានកែសម្រួលលិខិតឆ្លើយតបជោគជ័យ!',
            'response' => $response->fresh(['draftedByUser', 'currentApprover']),
        ]);
    }

    /**
     * 12C. មើលព័ត៌មានលម្អិតនៃលិខិតឆ្លើយតប (Get Response Detail)
     */
    public function getResponseDetail($responseId, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::with([
            'inboundDocument',
            'draftedByUser:id,name,name_kh,email,department_id,office_id,position_id',
            'draftedByUser.position:id,title_kh',
            'currentApprover:id,name,name_kh,email,position_id',
            'currentApprover.position:id,title_kh',
            'approvedByUser:id,name,name_kh',
            'approvals.user:id,name,name_kh,position_id',
            'approvals.user.position:id,title_kh',
            'approvals.forwardedTo:id,name,name_kh,position_id',
            'approvals.forwardedTo.position:id,title_kh',
        ])->findOrFail($responseId);

        return response()->json($response);
    }

    /**
     * 12D. ទាញយកព័ត៌មានតាមដានលំហូរឯកសារទាំងមូល (Get Visual Document Flow Tracking)
     */
    public function getTrackingFlow($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::with([
            'registeredByUser:id,name,name_kh,email,position_id',
            'registeredByUser.position:id,title_kh',
            'assistant:id,name,name_kh,email,position_id',
            'dispatchedByUser:id,name,name_kh,position_id',
            'targetDepartment:id,name_kh,name_en',
            'targetOffice:id,name_kh,name_en',
            'targetUser:id,name,name_kh,email,position_id',
            'targetUser.position:id,title_kh',
            'acknowledgedByUser:id,name,name_kh',
            'assignments.department:id,name_kh,name_en',
            'assignments.office:id,name_kh,name_en',
            'assignments.user:id,name,name_kh,email,position_id',
            'assignments.user.position:id,title_kh',
            'assignments.workingGroup:id,name_kh,name_en,code',
            'assignments.assignedByUser:id,name,name_kh,position_id',
            'assignments.assignedByUser.position:id,title_kh',
            'assignments.childAssignments.user:id,name,name_kh,position_id',
            'assignments.childAssignments.user.position:id,title_kh',
            'assignments.childAssignments.office:id,name_kh',
            'assignments.childAssignments.department:id,name_kh',
            'movements.user:id,name,name_kh,position_id',
            'movements.user.position:id,title_kh',
            'responses.draftedByUser:id,name,name_kh,position_id',
            'responses.draftedByUser.position:id,title_kh',
            'responses.currentApprover:id,name,name_kh,position_id',
            'responses.currentApprover.position:id,title_kh',
            'responses.approvedByUser:id,name,name_kh',
            'responses.approvals.user:id,name,name_kh,position_id',
            'responses.approvals.user.position:id,title_kh',
            'responses.approvals.forwardedTo:id,name,name_kh,position_id',
            'responses.approvals.forwardedTo.position:id,title_kh',
        ])->findOrFail($id);

        if (!$this->canUserAccessDocument($doc, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិមើលលំហូរឯកសារនេះឡើយ!'], 403);
        }

        // Determine current custodian (អ្នកទទួលបន្ទុកបច្ចុប្បន្ន)
        $currentCustodians = [];
        if ($doc->status === 'RECEPTION_DRAFT') {
            $currentCustodians[] = [
                'type' => 'DESK',
                'name' => 'មន្ត្រីទទួលឯកសារ (រដ្ឋបាល)',
                'detail' => $doc->registeredByUser ? ($doc->registeredByUser->name_kh ?? $doc->registeredByUser->name) : 'រង់ចាំចុះបញ្ជី',
            ];
        } elseif ($doc->status === 'SUBMITTED_TO_ASSISTANT') {
            $currentCustodians[] = [
                'type' => 'DESK',
                'name' => 'ជំនួយការអគ្គនាយក',
                'detail' => 'រង់ចាំចុះលេខ និងដាក់ជូនឯកឧត្តមអគ្គនាយក',
            ];
        } elseif ($doc->status === 'SUBMITTED_TO_DG') {
            $currentCustodians[] = [
                'type' => 'DESK',
                'name' => 'ឯកឧត្តមអគ្គនាយក',
                'detail' => 'រង់ចាំពិនិត្យ និងធ្វើចំណារ',
            ];
        } elseif ($doc->status === 'DG_ANNOTATED') {
            $currentCustodians[] = [
                'type' => 'DESK',
                'name' => 'ជំនួយការអគ្គនាយក',
                'detail' => 'រង់ចាំ Scan ចំណារ និងចាត់ចែងចែកចាយ',
            ];
        } elseif (in_array($doc->status, ['DISPATCHED', 'IN_RESPONSE_PROGRESS'])) {
            $latestResponse = $doc->responses->first();
            if ($latestResponse && $latestResponse->status === 'UNDER_REVIEW' && $latestResponse->currentApprover) {
                $currentCustodians[] = [
                    'type' => 'APPROVER',
                    'name' => $latestResponse->currentApprover->name_kh ?? $latestResponse->currentApprover->name,
                    'detail' => "កំពុងពិនិត្យ {$latestResponse->response_type_kh} ({$latestResponse->stage_kh})",
                ];
            } elseif ($latestResponse && in_array($latestResponse->status, ['DRAFT', 'RETURNED']) && $latestResponse->draftedByUser) {
                $currentCustodians[] = [
                    'type' => 'AUTHOR',
                    'name' => $latestResponse->draftedByUser->name_kh ?? $latestResponse->draftedByUser->name,
                    'detail' => $latestResponse->status === 'RETURNED' ? 'កំពុងកែសម្រួលតាមមតិណែនាំ' : 'កំពុងព្រាងលិខិតឆ្លើយតប',
                ];
            } else {
                // List assigned recipients
                if ($doc->assignments->isNotEmpty()) {
                    foreach ($doc->assignments as $assign) {
                        $currentCustodians[] = [
                            'type' => $assign->target_type,
                            'name' => $assign->target_label_kh,
                            'detail' => $assign->instruction ?: 'ទទួលបន្ទុកអនុវត្តតាមចំណារ',
                        ];
                    }
                } elseif ($doc->targetUser) {
                    $currentCustodians[] = [
                        'type' => 'USER',
                        'name' => $doc->targetUser->name_kh ?? $doc->targetUser->name,
                        'detail' => $doc->dispatch_notes ?: 'ទទួលបន្ទុកអនុវត្តតាមចំណារ',
                    ];
                } elseif ($doc->targetOffice) {
                    $currentCustodians[] = [
                        'type' => 'OFFICE',
                        'name' => $doc->targetOffice->name_kh,
                        'detail' => $doc->dispatch_notes ?: 'ការិយាល័យទទួលបន្ទុក',
                    ];
                } elseif ($doc->targetDepartment) {
                    $currentCustodians[] = [
                        'type' => 'DEPARTMENT',
                        'name' => $doc->targetDepartment->name_kh,
                        'detail' => $doc->dispatch_notes ?: 'នាយកដ្ឋានទទួលបន្ទុក',
                    ];
                }
            }
        } elseif ($doc->status === 'COMPLETED') {
            $currentCustodians[] = [
                'type' => 'COMPLETED',
                'name' => 'បានបញ្ចប់រួចរាល់',
                'detail' => $doc->completed_at ? Carbon::parse($doc->completed_at)->format('d/m/Y H:i') : 'បញ្ចប់ជាស្ថាពរ',
            ];
        }

        return response()->json([
            'document' => $doc,
            'current_custodians' => $currentCustodians,
        ]);
    }

    /**
     * 13. ដំណើរការពិនិត្យលិខិតឆ្លើយតបតាមឋានានុក្រម (Process Response Action: Forward, Return, DG Approve)
     */
    public function processResponseAction($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $doc = InboundDocument::findOrFail($id);
        $response = InboundDocumentResponse::where('inbound_document_id', $doc->id)->latest()->firstOrFail();

        // Must be current approver or admin
        if (!$isAdmin && $response->current_approver_id !== $viewer->id) {
            return response()->json(['message' => 'លោកអ្នកមិនមែនជាអ្នកពិនិត្យលិខិតឆ្លើយតបនេះឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:FORWARD,RETURN,DG_APPROVE',
            'comment' => 'nullable|string',
            'forwarded_to_id' => 'required_if:action,FORWARD|nullable|exists:users,id',
            'response_number' => 'nullable|string|max:50',
            'attachment' => 'nullable|file|max:25600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $viewer, $doc, $response) {
            $action = $request->input('action');
            $comment = $request->input('comment');

            $attachmentPath = null;
            $attachmentName = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentName = $file->getClientOriginalName();
                $attachmentPath = StorageService::store($file, 'inbound_documents/responses/reviews');
            }

            if ($action === 'FORWARD') {
                $nextReviewer = User::with('position')->findOrFail($request->input('forwarded_to_id'));
                $nextLevel = $nextReviewer->position ? (int) $nextReviewer->position->level : 99;
                $stage = match (true) {
                    $nextLevel === 1 => 'DG',
                    $nextLevel === 2 => 'LEADERSHIP',
                    $nextLevel <= 5 => 'DEPARTMENT',
                    default => 'OFFICE',
                };

                $response->update([
                    'current_approver_id' => $nextReviewer->id,
                    'current_stage' => $stage,
                    'status' => 'UNDER_REVIEW',
                ]);

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'FORWARD',
                    'stage' => $stage,
                    'comment' => $comment,
                    'attachment_path' => $attachmentPath,
                    'attachment_name' => $attachmentName,
                    'forwarded_to_id' => $nextReviewer->id,
                ]);

                InboundDocumentMovement::create([
                    'inbound_document_id' => $doc->id,
                    'user_id' => $viewer->id,
                    'action' => 'RESPONSE_FORWARDED',
                    'comment' => "បានបញ្ជូនលិខិតឆ្លើយតបបន្តទៅកាន់ {$nextReviewer->name_kh}",
                ]);

                $this->telegram->notifyResponseForwarded($response, $viewer, $nextReviewer, $comment);

                return response()->json([
                    'message' => "បានបញ្ជូនលិខិតឆ្លើយតបបន្តទៅកាន់ {$nextReviewer->name_kh} រួចរាល់!",
                    'response' => $response->fresh(['currentApprover', 'approvals']),
                ]);
            }

            if ($action === 'RETURN') {
                // Return back to drafter
                $response->update([
                    'current_approver_id' => $response->drafted_by,
                    'current_stage' => 'OFFICE',
                    'status' => 'RETURNED',
                ]);

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'RETURN',
                    'stage' => $response->current_stage,
                    'comment' => $comment,
                    'attachment_path' => $attachmentPath,
                    'attachment_name' => $attachmentName,
                    'forwarded_to_id' => $response->drafted_by,
                ]);

                InboundDocumentMovement::create([
                    'inbound_document_id' => $doc->id,
                    'user_id' => $viewer->id,
                    'action' => 'RESPONSE_RETURNED',
                    'comment' => "បានបញ្ជូនលិខិតឆ្លើយតបត្រឡប់ទៅអ្នករៀបចំវិញដើម្បីកែសម្រួល៖ {$comment}",
                ]);

                $drafter = User::find($response->drafted_by);
                if ($drafter) {
                    $this->telegram->notifyResponseReturned($response, $viewer, $drafter, $comment);
                }

                return response()->json([
                    'message' => 'បានបញ្ជូនលិខិតឆ្លើយតបត្រឡប់ទៅកាន់អ្នករៀបចំរួចរាល់!',
                    'response' => $response->fresh(['currentApprover', 'approvals']),
                ]);
            }

            if ($action === 'DG_APPROVE') {
                // DG approval completes the whole cycle!
                $responseNumber = $request->input('response_number');
                $response->update([
                    'status' => 'APPROVED_BY_DG',
                    'current_stage' => 'COMPLETED',
                    'current_approver_id' => null,
                    'response_number' => $responseNumber,
                    'approved_at' => Carbon::now(),
                    'approved_by' => $viewer->id,
                ]);

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'DG_APPROVE',
                    'stage' => 'DG',
                    'comment' => $comment ?? 'ឯកឧត្តមអគ្គនាយកបានឯកភាព និងចុះហត្ថលេខាលើលិខិតឆ្លើយតប',
                    'attachment_path' => $attachmentPath,
                    'attachment_name' => $attachmentName,
                ]);

                $doc->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now(),
                ]);

                InboundDocumentMovement::create([
                    'inbound_document_id' => $doc->id,
                    'user_id' => $viewer->id,
                    'action' => 'RESPONSE_APPROVED',
                    'from_status' => 'IN_RESPONSE_PROGRESS',
                    'to_status' => 'COMPLETED',
                    'comment' => "ឯកឧត្តមអគ្គនាយកបានឯកភាព និងចុះហត្ថលេខាលើលិខិតឆ្លើយតប" . ($responseNumber ? " (លេខ៖ {$responseNumber})" : ""),
                ]);

                $this->telegram->notifyResponseApproved($response, $viewer, $doc);

                return response()->json([
                    'message' => 'ឯកឧត្តមអគ្គនាយកបានឯកភាពលើលិខិតឆ្លើយតប និងបញ្ចប់ដំណើរការឯកសារ!',
                    'response' => $response->fresh(['approvedByUser', 'approvals']),
                    'document' => $doc->fresh(),
                ]);
            }

            return response()->json(['message' => 'សកម្មភាពមិនត្រឹមត្រូវ!'], 400);
        });
    }

    /**
     * 14. ទាញយកឯកសារដើម (Download Original File)
     */
    public function downloadOriginalFile($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);
        if (!$this->canUserAccessDocument($doc, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិទាញយកឯកសារនេះឡើយ!'], 403);
        }

        if (!$doc->original_file_path || !StorageService::exists($doc->original_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារដើមឡើយ!'], 404);
        }

        return StorageService::download(
            $doc->original_file_path,
            $doc->original_file_name ?? basename($doc->original_file_path)
        );
    }

    /**
     * 15. ទាញយកឯកសារមានចំណារអគ្គនាយក (Download Annotated File)
     */
    public function downloadAnnotatedFile($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);
        if (!$this->canUserAccessDocument($doc, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិទាញយកឯកសារនេះឡើយ!'], 403);
        }

        if (!$doc->annotated_file_path || !StorageService::exists($doc->annotated_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារមានចំណារឡើយ!'], 404);
        }

        return StorageService::download(
            $doc->annotated_file_path,
            $doc->annotated_file_name ?? basename($doc->annotated_file_path)
        );
    }

    /**
     * 16. ទាញយកឯកសារឆ្លើយតប (Download Response File)
     */
    public function downloadResponseFile($responseId, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::with('inboundDocument')->findOrFail($responseId);
        if ($response->inboundDocument && !$this->canUserAccessDocument($response->inboundDocument, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិទាញយកឯកសារឆ្លើយតបនេះឡើយ!'], 403);
        }

        if (!$response->file_path || !StorageService::exists($response->file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារឆ្លើយតបឡើយ!'], 404);
        }

        return StorageService::download(
            $response->file_path,
            $response->file_name ?? basename($response->file_path)
        );
    }

    /**
     * 17. កែប្រែព័ត៌មានឯកសារ (Update Inbound Document)
     * - អគ្គនាយក ឬ Admin អាចកែប្រែបានគ្រប់ពេល
     * - អ្នកទទួល ឬ ជំនួយការ អាចកែប្រែបានប្រសិនបើមិនទាន់បញ្ជូនទៅអគ្គនាយក
     */
    public function update($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        $doc = InboundDocument::findOrFail($id);

        if ($isAdmin || $isDg) {
            // DG or Admin can edit anytime
        } elseif ($isReceptionist || $isAssistant || $doc->registered_by === $viewer->id) {
            $notYetSubmittedToDg = in_array($doc->status, ['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT']);
            if (!$notYetSubmittedToDg) {
                return response()->json([
                    'message' => 'ឯកសារនេះត្រូវបានបញ្ជូនទៅអគ្គនាយករួចហើយ មិនអាចកែប្រែបានឡើយ! មានតែឯកឧត្តមអគ្គនាយក ឬអ្នកគ្រប់គ្រងប្រព័ន្ធទេទើបអាចកែប្រែបាន។'
                ], 403);
            }
        } else {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិកែប្រែឯកសារនេះឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'general_inbound_number' => 'sometimes|nullable|string|max:100',
            'received_date' => 'sometimes|date',
            'received_time' => 'sometimes',
            'deliverer_name' => 'sometimes|string|max:255',
            'deliverer_phone' => 'nullable|string|max:50',
            'sender_organization' => 'sometimes|string|max:255',
            'title' => 'sometimes|string',
            'document_type' => 'sometimes|string',
            'urgency' => 'sometimes|string',
            'confidentiality' => 'sometimes|string',
            'external_reference_number' => 'nullable|string|max:100',
            'external_document_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'receptionist_notes' => 'nullable|string',
            'original_file' => 'nullable|file|max:25600',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'general_inbound_number',
            'received_date',
            'received_time',
            'deliverer_name',
            'deliverer_phone',
            'sender_organization',
            'external_reference_number',
            'external_document_date',
            'deadline',
            'title',
            'document_type',
            'urgency',
            'confidentiality',
            'receptionist_notes',
        ]);

        if ($request->hasFile('original_file')) {
            if ($doc->original_file_path && StorageService::exists($doc->original_file_path)) {
                StorageService::delete($doc->original_file_path);
            }
            $file = $request->file('original_file');
            $data['original_file_name'] = $file->getClientOriginalName();
            $data['original_file_path'] = StorageService::store($file, 'inbound_documents/originals');
        }

        $doc->update($data);

        InboundDocumentMovement::create([
            'inbound_document_id' => $doc->id,
            'user_id' => $viewer->id,
            'action' => 'DOCUMENT_UPDATED',
            'from_status' => $doc->status,
            'to_status' => $doc->status,
            'comment' => "បានកែប្រែព័ត៌មានលម្អិតឯកសារ ដោយ {$viewer->name_kh}",
        ]);

        return response()->json([
            'message' => 'បានកែប្រែព័ត៌មានឯកសារចូលដោយជោគជ័យ!',
            'document' => $doc->fresh(['registeredByUser', 'assistant']),
        ]);
    }

    /**
     * 18. លុបឯកសារចូល (Delete Inbound Document)
     * - អគ្គនាយក ឬ Admin អាចលុបបាន
     * - អ្នកទទួល ឬជំនួយការ ករណីមិនទាន់បញ្ជូនទៅអគ្គនាយក អាចលុបចេញបាន
     */
    public function destroy($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        $doc = InboundDocument::findOrFail($id);

        // 1. អគ្គនាយក (DG) ឬ Admin អាចលុបឯកសារបានគ្រប់ដំណាក់កាល
        if ($isAdmin || $isDg) {
            // Authorized
        }
        // 2. អ្នកទទួល (Receptionist) ឬ ជំនួយការ (Assistant) ឬអ្នកចុះបញ្ជី ករណីមិនទាន់បញ្ជូនទៅអគ្គនាយក អាចលុបចេញបាន
        elseif ($isReceptionist || $isAssistant || $doc->registered_by === $viewer->id) {
            $notYetSubmittedToDg = in_array($doc->status, ['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT']);
            if (!$notYetSubmittedToDg) {
                return response()->json([
                    'message' => 'ឯកសារនេះត្រូវបានបញ្ជូនទៅអគ្គនាយករួចហើយ មិនអាចលុបបានឡើយ! មានតែឯកឧត្តមអគ្គនាយកទេទើបអាចលុបបាន។'
                ], 403);
            }
            // Authorized
        } else {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិលុបឯកសារនេះឡើយ!'], 403);
        }

        return DB::transaction(function () use ($doc) {
            // Soft delete assignments & responses
            $doc->assignments()->delete();
            foreach ($doc->responses as $resp) {
                $resp->approvals()->delete();
                $resp->delete();
            }
            $doc->movements()->delete();
            $doc->delete();

            return response()->json([
                'success' => true,
                'message' => 'បានលុបឯកសារចូលដោយជោគជ័យ!'
            ]);
        });
    }

    /**
     * 19. ព័ត៌មានលម្អិតសម្រាប់បោះពុម្ពសន្លឹកតាមដានឯកសារចូល (Routing Slip Data)
     */
    public function routingSlipData($id, Request $request)
    {
        $viewer = $request->user();
        $query = InboundDocument::with([
            'registeredByUser:id,name,name_kh,email,phone',
            'assistant:id,name,name_kh,email,phone',
            'dispatchedByUser:id,name,name_kh',
            'targetDepartment:id,name_kh,name_en',
            'targetOffice:id,name_kh,name_en',
            'targetUser:id,name,name_kh,email,phone',
            'acknowledgedByUser:id,name,name_kh',
            'movements' => function ($q) {
                $q->with('user:id,name,name_kh,position_id', 'user.position:id,title_kh,title_en')
                  ->orderBy('id', 'asc');
            },
            'latestResponse.currentApprover:id,name,name_kh',
            'latestResponse.draftedByUser:id,name,name_kh',
            'latestResponse.approvals' => function ($q) {
                $q->with('user:id,name,name_kh', 'forwardedTo:id,name,name_kh')
                  ->orderBy('id', 'asc');
            },
        ]);

        $this->scopeAccessibleByViewer($query, $viewer);

        $doc = $query->find($id);
        if (!$doc) {
            return response()->json(['message' => 'ឯកសារនេះមិនមាន ឬលោកអ្នកមិនមានសិទ្ធិចូលមើលឡើយ!'], 404);
        }

        $clientBaseUrl = config('app.url') ?? 'http://localhost:8000';
        $trackingUrl = "{$clientBaseUrl}/inbound-documents?id={$doc->id}&ref=" . urlencode($doc->general_inbound_number);

        return response()->json([
            'document' => $doc,
            'tracking_url' => $trackingUrl,
            'generated_at' => now()->setTimezone('Asia/Phnom_Penh')->format('d/m/Y H:i:s'),
            'organization_header' => [
                'kingdom' => 'ព្រះរាជាណាចក្រកម្ពុជា',
                'motto' => 'ជាតិ សាសនា ព្រះមហាក្សត្រ',
                'regulator_kh' => 'និយ័តករអាណាព្យាបាល',
                'regulator_en' => 'TRUST REGULATOR',
                'title_kh' => 'សន្លឹកតាមដានឯកសារចូល (ROUTING SLIP)',
            ],
        ]);
    }

    /**
     * 20. កំណត់ ឬកែសម្រួល Telegram Chat ID របស់គណនីបច្ចុប្បន្ន
     */
    public function updateTelegramSettings(Request $request)
    {
        $viewer = $request->user();
        $validator = Validator::make($request->all(), [
            'telegram_chat_id' => 'nullable|string|max:100',
            'telegram_username' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $viewer->update([
            'telegram_chat_id' => $request->input('telegram_chat_id'),
            'telegram_username' => $request->input('telegram_username'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'បានកែសម្រួលព័ត៌មាន Telegram ដោយជោគជ័យ!',
            'user' => $viewer->fresh(),
        ]);
    }

    /**
     * 21. សាកល្បងការតភ្ជាប់ផ្ញើសារ Telegram Bot (Test Telegram Connection)
     */
    public function testTelegram(Request $request)
    {
        $viewer = $request->user();
        $chatId = $request->input('chat_id') ?: $viewer->telegram_chat_id;

        $result = $this->telegram->testConnection($chatId);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * 22. បញ្ជីអង្គភាព/ស្ថាប័នផ្ញើឯកសារដែលបានកំណត់ទុកមុន (Get Preset Sender Organizations)
     */
    public function getSenderOrganizations(Request $request)
    {
        $all = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
        $query = InboundSenderOrganization::query();
        if (!$all) {
            $query->where('is_active', true);
        }
        $organizations = $query->orderBy('order_index', 'asc')->orderBy('id', 'asc')->get();

        return response()->json($organizations);
    }

    /**
     * 23. បន្ថែមអង្គភាព/ស្ថាប័នថ្មី (Store New Sender Organization)
     */
    public function storeSenderOrganization(Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-receptionist')) {
            return response()->json(['message' => 'មានតែអ្នកទទួលឯកសារទេទើបមានសិទ្ធិបន្ថែមស្ថាប័នផ្ញើ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name_kh' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $nameKh = trim($request->input('name_kh'));
        $existing = InboundSenderOrganization::where('name_kh', $nameKh)->first();
        if ($existing) {
            if (!$existing->is_active) {
                $existing->update(['is_active' => true]);
            }
            return response()->json([
                'message' => 'ស្ថាប័ននេះមានរួចហើយ!',
                'organization' => $existing,
            ]);
        }

        $maxOrder = InboundSenderOrganization::max('order_index') ?? 0;
        $org = InboundSenderOrganization::create([
            'name_kh' => $nameKh,
            'name_en' => $request->input('name_en'),
            'code' => $request->input('code'),
            'category' => $request->input('category', 'OTHER'),
            'order_index' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'បានបន្ថែមស្ថាប័នដោយជោគជ័យ!',
            'organization' => $org,
        ], 201);
    }

    /**
     * 24. លុបអង្គភាព/ស្ថាប័ន (Delete Sender Organization)
     */
    public function deleteSenderOrganization($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-receptionist')) {
            return response()->json(['message' => 'មានតែអ្នកទទួលឯកសារទេទើបមានសិទ្ធិលុបស្ថាប័នផ្ញើ!'], 403);
        }

        $org = InboundSenderOrganization::findOrFail($id);
        $org->delete();

        return response()->json(['message' => 'បានលុបស្ថាប័នដោយជោគជ័យ!']);
    }

    /**
     * 25. មើលឯកសារដើមក្នុង Browser (View Original File Inline)
     */
    public function viewOriginalFile($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);
        if (!$this->canUserAccessDocument($doc, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលមើលឯកសារនេះឡើយ!'], 403);
        }

        if (!$doc->original_file_path || !StorageService::exists($doc->original_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារដើមឡើយ!'], 404);
        }

        return StorageService::response(
            $doc->original_file_path,
            $doc->original_file_name ?? basename($doc->original_file_path)
        );
    }

    /**
     * 26. មើលឯកសារមានចំណារក្នុង Browser (View Annotated File Inline)
     */
    public function viewAnnotatedFile($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);
        if (!$this->canUserAccessDocument($doc, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលមើលឯកសារនេះឡើយ!'], 403);
        }

        if (!$doc->annotated_file_path || !StorageService::exists($doc->annotated_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារមានចំណារឡើយ!'], 404);
        }

        return StorageService::response(
            $doc->annotated_file_path,
            $doc->annotated_file_name ?? basename($doc->annotated_file_path)
        );
    }

    /**
     * 27. មើលឯកសារឆ្លើយតបក្នុង Browser (View Response File Inline)
     */
    public function viewResponseFile($responseId, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::with('inboundDocument')->findOrFail($responseId);
        if ($response->inboundDocument && !$this->canUserAccessDocument($response->inboundDocument, $viewer)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលមើលឯកសារឆ្លើយតបនេះឡើយ!'], 403);
        }

        if (!$response->file_path || !StorageService::exists($response->file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារឆ្លើយតបឡើយ!'], 404);
        }

        return StorageService::response(
            $response->file_path,
            $response->file_name ?? basename($response->file_path)
        );
    }
}
