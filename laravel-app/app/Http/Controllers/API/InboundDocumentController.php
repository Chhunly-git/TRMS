<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InboundDocument;
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
     * ជម្រើសសម្រាប់ Dispatch (នាយកដ្ឋាន ការិយាល័យ និងមន្ត្រី)
     */
    public function recipientsOptions(Request $request)
    {
        $departments = \App\Models\Department::orderBy('id')->get(['id', 'name_kh', 'name_en']);
        $offices = \App\Models\Office::orderBy('id')->get(['id', 'department_id', 'name_kh', 'name_en']);
        $users = User::where('status', 1)
            ->with(['position:id,title_kh,title_en,level', 'department:id,name_kh', 'office:id,name_kh'])
            ->orderBy('name_kh')
            ->get(['id', 'name_kh', 'name_en', 'department_id', 'office_id', 'position_id', 'email', 'profile_image']);

        return response()->json([
            'departments' => $departments,
            'offices' => $offices,
            'users' => $users,
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
            'registeredByUser:id,name,name_kh,email',
            'assistant:id,name,name_kh,email',
            'targetDepartment:id,name_kh',
            'targetOffice:id,name_kh',
            'targetUser:id,name,name_kh,email',
            'acknowledgedByUser:id,name,name_kh',
            'latestResponse.currentApprover:id,name,name_kh',
        ]);

        // Scopes
        if ($scope === 'reception') {
            $query->where('status', 'RECEPTION_DRAFT');
        } elseif ($scope === 'assistant_inbox') {
            $query->whereIn('status', ['SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED']);
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
            $statusVal = $request->input('status');
            if ($statusVal === 'dispatched_all' || $statusVal === 'DISPATCHED_ALL') {
                $query->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS']);
            } else {
                $query->where('status', $statusVal);
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
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isLeadership = ($viewer->position && (int) $viewer->position->level <= 2);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        $isPrivileged = $isAdmin || $isLeadership || $isAssistant;

        // Base accessible query for viewer
        $baseAccessibleQuery = $this->scopeAccessibleByViewer(InboundDocument::query(), $viewer);

        $total = $isPrivileged
            ? InboundDocument::count()
            : (clone $baseAccessibleQuery)->count();

        $receptionPending = ($isAdmin || $isReceptionist)
            ? InboundDocument::where('status', 'RECEPTION_DRAFT')->count()
            : 0;

        $assistantPending = ($isAdmin || $isAssistant)
            ? InboundDocument::whereIn('status', ['SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED'])->count()
            : 0;

        $dgPending = ($isAdmin || $isLeadership)
            ? InboundDocument::where('status', 'SUBMITTED_TO_DG')->count()
            : 0;

        $dispatched = $isPrivileged
            ? InboundDocument::whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS'])->count()
            : (clone $baseAccessibleQuery)->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS'])->count();

        $inResponse = $isPrivileged
            ? InboundDocument::where('status', 'IN_RESPONSE_PROGRESS')->count()
            : (clone $baseAccessibleQuery)->where('status', 'IN_RESPONSE_PROGRESS')->count();

        $completed = $isPrivileged
            ? InboundDocument::where('status', 'COMPLETED')->count()
            : (clone $baseAccessibleQuery)->where('status', 'COMPLETED')->count();

        $myTodo = $this->scopeMyTodo(InboundDocument::query(), $viewer)->count();
        $myDone = $this->scopeMyDone(InboundDocument::query(), $viewer)->count();
        $myUnit = $this->scopeMyUnit(InboundDocument::query(), $viewer)->count();

        $today = Carbon::today();
        $deadlineBase = $isPrivileged ? InboundDocument::query() : (clone $baseAccessibleQuery);

        $overdueCount = (clone $deadlineBase)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', $today)
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->count();

        $dueSoonCount = (clone $deadlineBase)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', $today)
            ->whereDate('deadline', '<=', $today->copy()->addDays(3))
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->count();

        return response()->json([
            'total' => $total,
            'reception_pending' => $receptionPending,
            'assistant_pending' => $assistantPending,
            'dg_pending' => $dgPending,
            'dispatched' => $dispatched,
            'in_response' => $inResponse,
            'completed' => $completed,
            'assigned_to_me' => $myTodo,
            'my_todo' => $myTodo,
            'my_done' => $myDone,
            'my_unit' => $myUnit,
            'overdue' => $overdueCount,
            'due_soon' => $dueSoonCount,
        ]);
    }

    /**
     * Scope: ឯកសារដែលអាចមើលឃើញដោយអ្នកប្រើប្រាស់ (Accessible by Viewer)
     * Regular users can ONLY see documents they have received or are involved in.
     */
    protected function scopeAccessibleByViewer($query, $viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isLeadership = ($viewer->position && (int) $viewer->position->level <= 2);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');

        // Admin, Leadership (DG/DDG), and DG Assistant have full visibility across the system
        if ($isAdmin || $isLeadership || $isAssistant) {
            return $query;
        }

        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');
        $isDeptLeadership = ($viewer->position && (int) $viewer->position->level <= 4); // Dept Director / Deputy Director

        return $query->where(function ($q) use ($viewer, $isReceptionist, $isDeptLeadership) {
            // 1. Receptionist can see documents they registered or reception drafts
            if ($isReceptionist) {
                $q->orWhere('registered_by', $viewer->id)
                  ->orWhere('status', 'RECEPTION_DRAFT');
            }

            // 2. Documents specifically assigned to this user
            $q->orWhere('target_user_id', $viewer->id);

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

            // 7. For Department Director / Deputy Director:
            // Can see all documents received by/dispatched to their department
            if ($isDeptLeadership && $viewer->department_id) {
                $q->orWhere(function ($dq) use ($viewer) {
                    $dq->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'COMPLETED'])
                       ->where(function ($subDq) use ($viewer) {
                           $subDq->where('target_department_id', $viewer->department_id)
                                 ->orWhereHas('targetOffice', function ($oq) use ($viewer) {
                                     $oq->where('department_id', $viewer->department_id);
                                 })
                                 ->orWhereHas('targetUser', function ($uq) use ($viewer) {
                                     $uq->where('department_id', $viewer->department_id);
                                 });
                       });
                });
            } else {
                // 8. For Office Chief or Regular Officer:
                // If dispatched to user's office and not targeted to another specific user (or targeted to them)
                if ($viewer->office_id) {
                    $q->orWhere(function ($oq) use ($viewer) {
                        $oq->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'COMPLETED'])
                           ->where('target_office_id', $viewer->office_id)
                           ->where(function ($subOq) use ($viewer) {
                               $subOq->whereNull('target_user_id')
                                     ->orWhere('target_user_id', $viewer->id);
                           });
                    });
                }

                // If dispatched to user's department without specific office/officer
                if ($viewer->department_id) {
                    $q->orWhere(function ($dq) use ($viewer) {
                        $dq->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'COMPLETED'])
                           ->where('target_department_id', $viewer->department_id)
                           ->whereNull('target_office_id')
                           ->whereNull('target_user_id');
                    });
                }
            }
        });
    }

    /**
     * Check if a specific document is accessible by the viewer
     */
    protected function canUserAccessDocument($doc, $viewer): bool
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isLeadership = ($viewer->position && (int) $viewer->position->level <= 2);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');

        if ($isAdmin || $isLeadership || $isAssistant) {
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
        $isDg = ($viewer->position && (int)$viewer->position->level === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        return $query->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->where(function ($q) use ($viewer, $isAdmin, $isDg, $isAssistant, $isReceptionist) {
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

                // 5. Assigned to this office (not yet delegated to a specific officer)
                if ($viewer->office_id) {
                    $q->orWhere(function ($oq) use ($viewer) {
                        $oq->where('target_office_id', $viewer->office_id)
                           ->whereNull('target_user_id');
                    });
                }

                // 6. Assigned to this department (not yet delegated to an office or officer)
                // Only for Department leadership (level <= 4) or users without an office
                $isDeptDirector = ($viewer->position && (int)$viewer->position->level <= 4);
                if ($viewer->department_id && ($isDeptDirector || empty($viewer->office_id))) {
                    $q->orWhere(function ($dq) use ($viewer) {
                        $dq->where('target_department_id', $viewer->department_id)
                           ->whereNull('target_office_id')
                           ->whereNull('target_user_id');
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
        $isLeadership = ($viewer->position && (int)$viewer->position->level <= 2);

        // DG, Deputy DG, or Admin sees all organizational documents
        if ($isAdmin || $isLeadership) {
            return $query;
        }

        $isDeptDirector = ($viewer->position && (int)$viewer->position->level <= 4);

        return $query->where(function ($q) use ($viewer, $isDeptDirector) {
            // Only dispatched/in progress/completed documents
            $q->whereIn('status', ['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'COMPLETED']);

            if ($isDeptDirector && $viewer->department_id) {
                $q->where(function ($dq) use ($viewer) {
                    $dq->where('target_department_id', $viewer->department_id)
                       ->orWhereHas('targetOffice', fn($oq) => $oq->where('department_id', $viewer->department_id))
                       ->orWhereHas('targetUser', fn($uq) => $uq->where('department_id', $viewer->department_id));
                });
            } elseif ($viewer->office_id) {
                $q->where(function ($oq) use ($viewer) {
                    $oq->where('target_office_id', $viewer->office_id)
                       ->orWhereHas('targetUser', fn($uq) => $uq->where('office_id', $viewer->office_id));
                });
            } elseif ($viewer->department_id) {
                $q->where('target_department_id', $viewer->department_id);
            } else {
                $q->where('target_user_id', $viewer->id);
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
            'dispatchedByUser:id,name,name_kh',
            'targetDepartment:id,name_kh',
            'targetOffice:id,name_kh',
            'targetUser:id,name,name_kh,email',
            'acknowledgedByUser:id,name,name_kh',
            'movements.user:id,name,name_kh',
            'responses.draftedByUser:id,name,name_kh',
            'responses.currentApprover:id,name,name_kh',
            'responses.approvedByUser:id,name,name_kh',
            'responses.approvals.user:id,name,name_kh',
            'responses.approvals.forwardedTo:id,name,name_kh',
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
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-receptionist') && !$viewer->hasPermission('inbound-documents')) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិជាអ្នកទទួលឯកសារឡើយ!'], 403);
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
            return response()->json(['errors' => $validator->errors()], 422);
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
                $filePath = $file->store('inbound_documents/originals', 'public');
            }

            $initialStatus = $sendImmediately ? 'SUBMITTED_TO_ASSISTANT' : 'RECEPTION_DRAFT';

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
                'status' => $initialStatus,
            ]);

            // Movement log: Registered
            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'REGISTERED',
                'from_status' => null,
                'to_status' => 'RECEPTION_DRAFT',
                'comment' => "បានចុះលេខចូលទូទៅ៖ {$generalNumber}",
            ]);

            // If immediately sent to DG assistant
            if ($sendImmediately) {
                InboundDocumentMovement::create([
                    'inbound_document_id' => $doc->id,
                    'user_id' => $viewer->id,
                    'action' => 'FORWARDED_TO_ASSISTANT',
                    'from_status' => 'RECEPTION_DRAFT',
                    'to_status' => 'SUBMITTED_TO_ASSISTANT',
                    'comment' => 'បានបញ្ជូនទៅកាន់ជំនួយការអគ្គនាយក',
                ]);
            }

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

        $doc->update(['status' => 'SUBMITTED_TO_ASSISTANT']);

        InboundDocumentMovement::create([
            'inbound_document_id' => $doc->id,
            'user_id' => $viewer->id,
            'action' => 'FORWARDED_TO_ASSISTANT',
            'from_status' => 'RECEPTION_DRAFT',
            'to_status' => 'SUBMITTED_TO_ASSISTANT',
            'comment' => $request->input('comment', 'បានបញ្ជូនទៅកាន់ជំនួយការអគ្គនាយក'),
        ]);

        // Telegram Notification
        $this->telegram->notifyNewInbound($doc);

        return response()->json([
            'message' => 'បានបញ្ជូនឯកសារទៅកាន់ជំនួយការអគ្គនាយករួចរាល់!',
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
            return response()->json(['errors' => $validator->errors()], 422);
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
     * 8. ឯកឧត្តមអគ្គនាយកធ្វើចំណារ (DG Annotation)
     */
    public function dgAnnotate($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isDg = ($viewer->position && (int) $viewer->position->level === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');

        if (!$isAdmin && !$isDg && !$isAssistant) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិកត់ត្រាចំណាររបស់អគ្គនាយកឡើយ!'], 403);
        }

        $doc = InboundDocument::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'dg_annotation' => 'required|string',
            'is_response_required' => 'required|boolean',
            'deadline' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = [
            'dg_annotation' => $request->input('dg_annotation'),
            'is_response_required' => (bool) $request->input('is_response_required'),
            'dg_annotated_at' => Carbon::now(),
            'status' => 'DG_ANNOTATED',
        ];

        if ($request->has('deadline')) {
            $updateData['deadline'] = $request->input('deadline');
        }

        $doc->update($updateData);

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

        return response()->json([
            'message' => 'បានកត់ត្រាចំណារឯកឧត្តមអគ្គនាយករួចរាល់!',
            'document' => $doc->fresh(),
        ]);
    }

    /**
     * 9. ជំនួយការ Scan ឯកសារមានចំណារ និងចែកចាយទៅភាគីពាក់ព័ន្ធ (Assistant Scan & Dispatch)
     */
    public function assistantDispatch($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-assistant')) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិជាជំនួយការអគ្គនាយកឡើយ!'], 403);
        }

        $doc = InboundDocument::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'target_type' => 'required|in:DEPARTMENT,OFFICE,OFFICER',
            'target_department_id' => 'nullable|exists:departments,id',
            'target_office_id' => 'nullable|exists:offices,id',
            'target_user_id' => 'nullable|exists:users,id',
            'dispatch_notes' => 'nullable|string',
            'annotated_file' => 'nullable|file|max:25600',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doc = DB::transaction(function () use ($request, $viewer, $doc) {
            $annotatedPath = $doc->annotated_file_path;
            $annotatedName = $doc->annotated_file_name;

            if ($request->hasFile('annotated_file')) {
                if ($annotatedPath && Storage::disk('public')->exists($annotatedPath)) {
                    Storage::disk('public')->delete($annotatedPath);
                }
                $file = $request->file('annotated_file');
                $annotatedName = $file->getClientOriginalName();
                $annotatedPath = $file->store('inbound_documents/annotated', 'public');
            }

            // If response is required: IN_RESPONSE_PROGRESS; else: DISPATCHED
            $newStatus = $doc->is_response_required ? 'IN_RESPONSE_PROGRESS' : 'DISPATCHED';

            $targetType = $request->input('target_type');
            $deptId = $request->input('target_department_id');
            $officeId = $request->input('target_office_id');
            $userId = $request->input('target_user_id');

            if ($targetType === 'OFFICE' && $officeId && !$deptId) {
                $off = \App\Models\Office::find($officeId);
                if ($off) $deptId = $off->department_id;
            } elseif ($targetType === 'OFFICER' && $userId) {
                $usr = User::find($userId);
                if ($usr) {
                    if (!$deptId) $deptId = $usr->department_id;
                    if (!$officeId) $officeId = $usr->office_id;
                }
            }

            $doc->update([
                'target_type' => $targetType,
                'target_department_id' => $deptId,
                'target_office_id' => $officeId,
                'target_user_id' => $userId,
                'dispatch_notes' => $request->input('dispatch_notes'),
                'annotated_file_path' => $annotatedPath,
                'annotated_file_name' => $annotatedName,
                'dispatched_at' => Carbon::now(),
                'dispatched_by' => $viewer->id,
                'status' => $newStatus,
            ]);

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'DISPATCHED',
                'from_status' => 'DG_ANNOTATED',
                'to_status' => $newStatus,
                'comment' => "បាន Scan ចំណារ និងចែកចាយទៅកាន់ភាគីពាក់ព័ន្ធ",
                'meta' => [
                    'target_type' => $targetType,
                    'target_department_id' => $deptId,
                    'target_office_id' => $officeId,
                    'target_user_id' => $userId,
                ],
            ]);

            return $doc->fresh(['targetDepartment', 'targetOffice', 'targetUser', 'dispatchedByUser']);
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
     * ឧ. អគ្គនាយករង -> ប្រធាននាយកដ្ឋាន -> ប្រធានការិយាល័យ -> អនុប្រធាន -> មន្ត្រី
     */
    public function forward($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $doc = InboundDocument::with(['targetDepartment', 'targetOffice', 'targetUser'])->findOrFail($id);

        if (in_array($doc->status, ['COMPLETED', 'CANCELLED'])) {
            return response()->json(['message' => 'ឯកសារនេះត្រូវបានបញ្ចប់រួចរាល់ហើយ មិនអាចចាត់ចែងបន្តបានទៀតទេ!'], 400);
        }

        // Check authorization: Admin, DG/DDG (level <= 2), current assignee, or department/office head of current target
        $isAuthorized = $isAdmin
            || ($viewer->position && (int) $viewer->position->level <= 2)
            || ($doc->target_user_id === $viewer->id)
            || ($doc->target_department_id && $doc->target_department_id === $viewer->department_id)
            || ($doc->target_office_id && $doc->target_office_id === $viewer->office_id)
            || $viewer->hasPermission('inbound-documents-assistant');

        if (!$isAuthorized) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចាត់ចែងបន្តលើឯកសារនេះឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'target_type' => 'required|in:DEPARTMENT,OFFICE,OFFICER',
            'target_department_id' => 'nullable|exists:departments,id',
            'target_office_id' => 'nullable|exists:offices,id',
            'target_user_id' => 'nullable|exists:users,id',
            'forwarding_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = DB::transaction(function () use ($request, $viewer, $doc) {
            $prevTargetDesc = '';
            if ($doc->target_type === 'OFFICER' && $doc->targetUser) {
                $prevTargetDesc = 'មន្ត្រី៖ ' . ($doc->targetUser->name_kh ?? $doc->targetUser->name);
            } elseif ($doc->target_type === 'OFFICE' && $doc->targetOffice) {
                $prevTargetDesc = 'ការិយាល័យ៖ ' . $doc->targetOffice->name_kh;
            } elseif ($doc->target_type === 'DEPARTMENT' && $doc->targetDepartment) {
                $prevTargetDesc = 'នាយកដ្ឋាន៖ ' . $doc->targetDepartment->name_kh;
            }

            $newTargetType = $request->input('target_type');
            $newDeptId = $request->input('target_department_id');
            $newOfficeId = $request->input('target_office_id');
            $newUserId = $request->input('target_user_id');

            if ($newTargetType === 'OFFICE' && $newOfficeId && !$newDeptId) {
                $off = \App\Models\Office::find($newOfficeId);
                if ($off) $newDeptId = $off->department_id;
            } elseif ($newTargetType === 'OFFICER' && $newUserId) {
                $usr = User::find($newUserId);
                if ($usr) {
                    if (!$newDeptId) $newDeptId = $usr->department_id;
                    if (!$newOfficeId) $newOfficeId = $usr->office_id;
                }
            }

            $doc->update([
                'target_type' => $newTargetType,
                'target_department_id' => $newDeptId,
                'target_office_id' => $newOfficeId,
                'target_user_id' => $newUserId,
            ]);

            $doc->load(['targetDepartment', 'targetOffice', 'targetUser']);
            $newTargetDesc = '';
            if ($newTargetType === 'OFFICER' && $doc->targetUser) {
                $newTargetDesc = 'មន្ត្រី៖ ' . ($doc->targetUser->name_kh ?? $doc->targetUser->name);
            } elseif ($newTargetType === 'OFFICE' && $doc->targetOffice) {
                $newTargetDesc = 'ការិយាល័យ៖ ' . $doc->targetOffice->name_kh;
            } elseif ($newTargetType === 'DEPARTMENT' && $doc->targetDepartment) {
                $newTargetDesc = 'នាយកដ្ឋាន៖ ' . $doc->targetDepartment->name_kh;
            }

            $commentText = $request->input('forwarding_notes')
                ? ('ចាត់ចែងបន្តទៅកាន់ ' . $newTargetDesc . ' ៖ ' . $request->input('forwarding_notes'))
                : ('ចាត់ចែងបន្តទៅកាន់ ' . $newTargetDesc);

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'FORWARDED',
                'from_status' => $doc->status,
                'to_status' => $doc->status,
                'comment' => $commentText,
                'meta' => [
                    'from' => $prevTargetDesc ?: ($viewer->name_kh ?? $viewer->name),
                    'to' => $newTargetDesc,
                    'notes' => $request->input('forwarding_notes'),
                ],
            ]);

            return [$doc->fresh(['targetDepartment', 'targetOffice', 'targetUser', 'movements.user']), $newTargetDesc];
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
     */
    public function getNextResponseApprovers($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);

        // Reviewer position level
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $targetDeptId = $viewer->department_id;
        $targetOfficeId = $viewer->office_id;

        $candidates = collect();

        if ($viewerPosLevel === 1) {
            // DG (Level 1) - Final level
            $candidates = collect();
        } elseif ($viewerPosLevel === 2) {
            // Deputy DG (Level 2) -> DG (Level 1)
            $candidates = User::whereHas('position', fn($q) => $q->where('level', 1))
                ->where('id', '!=', $viewer->id)
                ->with('position:id,title_kh,title_en,level')
                ->get();
        } elseif ($viewerPosLevel <= 4 && $viewerPosLevel >= 3) {
            // Dept Director -> Deputy DG / DG (Level 1, 2)
            $candidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                ->where('id', '!=', $viewer->id)
                ->with('position:id,title_kh,title_en,level')
                ->get();
        } elseif ($viewerPosLevel === 5) {
            // Deputy Dept Director -> Dept Director (Level 3, 4)
            if ($targetDeptId) {
                $candidates = User::where('department_id', $targetDeptId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
            }
            if ($candidates->isEmpty()) {
                $candidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            }
        } elseif ($viewerPosLevel <= 7 && $viewerPosLevel >= 6) {
            // Office Chief -> Dept Leadership (Level 3, 4, 5)
            if ($targetDeptId) {
                $candidates = User::where('department_id', $targetDeptId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
            }
            if ($candidates->isEmpty()) {
                $candidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            }
        } else {
            // Officer (Level 8, 9+) -> Office Chief (Level 6, 7) or Dept Leadership
            if ($targetOfficeId) {
                $candidates = User::where('office_id', $targetOfficeId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [6, 7, 8]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
            }
            if ($candidates->isEmpty() && $targetDeptId) {
                $candidates = User::where('department_id', $targetDeptId)
                    ->where('id', '!=', $viewer->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();
            }
            if ($candidates->isEmpty()) {
                $candidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            }
        }

        return response()->json([
            'candidates' => $candidates,
            'viewer_level' => $viewerPosLevel,
            'is_dg' => ($viewerPosLevel === 1),
        ]);
    }

    /**
     * 12. ដាក់ស្នើព្រាងលិខិតឆ្លើយតប (Submit Draft Response for Path B)
     */
    public function submitResponseDraft($id, Request $request)
    {
        $viewer = $request->user();
        $doc = InboundDocument::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isAuthorized = $isAdmin
            || ($doc->target_user_id === $viewer->id)
            || ($doc->target_office_id && $doc->target_office_id === $viewer->office_id)
            || ($doc->target_department_id && $doc->target_department_id === $viewer->department_id)
            || $viewer->hasPermission('inbound-documents-assistant');

        if (!$isAuthorized) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិរៀបចំលិខិតឆ្លើយតបលើឯកសារនេះឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'nullable|string',
            'forwarded_to_id' => 'required|exists:users,id',
            'response_file' => 'nullable|file|max:25600',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = DB::transaction(function () use ($request, $viewer, $doc) {
            $filePath = null;
            $fileName = null;
            if ($request->hasFile('response_file')) {
                $file = $request->file('response_file');
                $fileName = $file->getClientOriginalName();
                $filePath = $file->store('inbound_documents/responses', 'public');
            }

            $targetReviewer = User::with('position')->find($request->input('forwarded_to_id'));
            $reviewerLevel = $targetReviewer->position ? (int) $targetReviewer->position->level : 99;

            $stage = match (true) {
                $reviewerLevel === 1 => 'DG',
                $reviewerLevel === 2 => 'LEADERSHIP',
                $reviewerLevel <= 5 => 'DEPARTMENT',
                default => 'OFFICE',
            };

            $response = InboundDocumentResponse::create([
                'inbound_document_id' => $doc->id,
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'drafted_by' => $viewer->id,
                'department_id' => $viewer->department_id,
                'office_id' => $viewer->office_id,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'current_approver_id' => $targetReviewer->id,
                'current_stage' => $stage,
                'status' => 'UNDER_REVIEW',
                'submitted_at' => Carbon::now(),
            ]);

            InboundDocumentResponseApproval::create([
                'response_id' => $response->id,
                'user_id' => $viewer->id,
                'action' => 'SUBMIT',
                'stage' => $stage,
                'comment' => $request->input('comment', 'បានរៀបចំ និងដាក់ស្នើព្រាងលិខិតឆ្លើយតប'),
                'forwarded_to_id' => $targetReviewer->id,
            ]);

            $doc->update(['status' => 'IN_RESPONSE_PROGRESS']);

            InboundDocumentMovement::create([
                'inbound_document_id' => $doc->id,
                'user_id' => $viewer->id,
                'action' => 'RESPONSE_DRAFTED',
                'from_status' => $doc->status,
                'to_status' => 'IN_RESPONSE_PROGRESS',
                'comment' => "បានដាក់ស្នើព្រាងលិខិតឆ្លើយតបទៅកាន់ {$targetReviewer->name_kh}",
            ]);

            return [$response->load(['draftedByUser', 'currentApprover']), $targetReviewer];
        });

        [$responseModel, $targetReviewer] = $result;
        $this->telegram->notifyResponseSubmitted($responseModel, $viewer, $targetReviewer);

        return response()->json([
            'message' => "បានដាក់ស្នើព្រាងលិខិតឆ្លើយតបទៅកាន់ {$targetReviewer->name_kh} រួចរាល់!",
            'response' => $responseModel,
        ], 201);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $viewer, $doc, $response) {
            $action = $request->input('action');
            $comment = $request->input('comment');

            $attachmentPath = null;
            $attachmentName = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentName = $file->getClientOriginalName();
                $attachmentPath = $file->store('inbound_documents/responses/reviews', 'public');
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

        if (!$doc->original_file_path || !Storage::disk('public')->exists($doc->original_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារដើមឡើយ!'], 404);
        }

        return Storage::disk('public')->download(
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

        if (!$doc->annotated_file_path || !Storage::disk('public')->exists($doc->annotated_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារមានចំណារឡើយ!'], 404);
        }

        return Storage::disk('public')->download(
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

        if (!$response->file_path || !Storage::disk('public')->exists($response->file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារឆ្លើយតបឡើយ!'], 404);
        }

        return Storage::disk('public')->download(
            $response->file_path,
            $response->file_name ?? basename($response->file_path)
        );
    }

    /**
     * 17. កែប្រែព័ត៌មានឯកសារ (Update Inbound Document)
     */
    public function update($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $doc = InboundDocument::findOrFail($id);

        if (!$isAdmin && $doc->registered_by !== $viewer->id && !$viewer->hasPermission('inbound-documents-assistant')) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិកែប្រែឯកសារនេះឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
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
            if ($doc->original_file_path && Storage::disk('public')->exists($doc->original_file_path)) {
                Storage::disk('public')->delete($doc->original_file_path);
            }
            $file = $request->file('original_file');
            $data['original_file_name'] = $file->getClientOriginalName();
            $data['original_file_path'] = $file->store('inbound_documents/originals', 'public');
        }

        $doc->update($data);

        return response()->json([
            'message' => 'បានកែប្រែព័ត៌មានឯកសារចូលដោយជោគជ័យ!',
            'document' => $doc->fresh(['registeredByUser', 'assistant']),
        ]);
    }

    /**
     * 18. លុបឯកសារចូល (Delete Inbound Document)
     */
    public function destroy($id, Request $request)
    {
        $viewer = $request->user();
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $doc = InboundDocument::findOrFail($id);

        if (!$isAdmin && $doc->registered_by !== $viewer->id) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិលុបឯកសារនេះឡើយ!'], 403);
        }

        $doc->delete();

        return response()->json(['message' => 'បានលុបឯកសារចូលដោយជោគជ័យ!']);
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
            return response()->json(['errors' => $validator->errors()], 422);
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
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-receptionist') && !$viewer->hasPermission('inbound-documents')) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិបន្ថែមស្ថាប័នឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name_kh' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
        if (!$isAdmin && !$viewer->hasPermission('inbound-documents-receptionist') && !$viewer->hasPermission('inbound-documents')) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិលុបស្ថាប័នឡើយ!'], 403);
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

        if (!$doc->original_file_path || !Storage::disk('public')->exists($doc->original_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារដើមឡើយ!'], 404);
        }

        $fullPath = Storage::disk('public')->path($doc->original_file_path);
        $mimeType = Storage::disk('public')->mimeType($doc->original_file_path) ?: 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($doc->original_file_name ?? basename($doc->original_file_path)) . '"',
        ]);
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

        if (!$doc->annotated_file_path || !Storage::disk('public')->exists($doc->annotated_file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារមានចំណារឡើយ!'], 404);
        }

        $fullPath = Storage::disk('public')->path($doc->annotated_file_path);
        $mimeType = Storage::disk('public')->mimeType($doc->annotated_file_path) ?: 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($doc->annotated_file_name ?? basename($doc->annotated_file_path)) . '"',
        ]);
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

        if (!$response->file_path || !Storage::disk('public')->exists($response->file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារឆ្លើយតបឡើយ!'], 404);
        }

        $fullPath = Storage::disk('public')->path($response->file_path);
        $mimeType = Storage::disk('public')->mimeType($response->file_path) ?: 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . addslashes($response->file_name ?? basename($response->file_path)) . '"',
        ]);
    }
}
