<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DocumentWorkflowConfig;
use App\Models\InboundDocument;
use App\Models\InboundDocumentMovement;
use App\Models\InboundDocumentResponse;
use App\Models\InboundDocumentResponseApproval;
use App\Models\InboundDocumentResponseRevision;
use App\Models\User;
use App\Services\StorageService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OutboundDocumentController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * 1. ជម្រើសសម្រាប់បង្កើតឯកសារចេញ / របាយការណ៍ / កំណត់បង្ហាញ (Options)
     */
    public function options(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userPosLevel = $user->position ? (int) $user->position->level : 99;
        $viewerPosLevel = $userPosLevel;
        $isDg = ($userPosLevel <= 1) || (strtoupper($user->level ?? '') === 'ADMIN');

        // ឯកសារចូលដែលអាចជ្រើសរើសជាយោង/ឧបសម្ព័ន្ធ (លុះត្រាតែឯកសារនោះនៅក្នុងដៃរបស់ខ្លួន/ទទួលបានពីថ្នាក់លើមក)
        $inboundDocsQuery = InboundDocument::whereNotIn('status', ['COMPLETED', 'CANCELLED', 'RECEPTION_DRAFT']);

        if (!$isDg) {
            $inboundDocsQuery->where(function ($q) use ($user, $userPosLevel) {
                // 1. ចាត់ចែងផ្ទាល់ដល់មន្ត្រី
                $q->where('target_user_id', $user->id)
                  ->orWhereHas('assignments', fn($aq) => $aq->where('user_id', $user->id));

                // 2. ចាត់ចែងដល់ការិយាល័យរបស់ខ្លួន
                if ($user->office_id) {
                    $q->orWhere('target_office_id', $user->office_id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('office_id', $user->office_id));
                }

                // 3. ចាត់ចែងដល់នាយកដ្ឋាន
                if ($user->department_id) {
                    $q->orWhere('target_department_id', $user->department_id)
                      ->orWhereHas('assignments', fn($aq) => $aq->where('department_id', $user->department_id));
                }

                // 4. ចាត់ចែងដល់ក្រុមការងារដែលខ្លួនជាសមាជិក ឬប្រធាន
                $q->orWhereHas('assignments', function ($aq) use ($user) {
                    $aq->whereNotNull('working_group_id')
                       ->whereHas('workingGroup.members', fn($mq) => $mq->where('user_id', $user->id));
                });
            });
        }

        $inboundDocs = $inboundDocsQuery
            ->orderBy('id', 'desc')
            ->select('id', 'general_inbound_number', 'dg_inbound_number', 'title', 'received_date', 'sender_organization', 'status', 'dg_annotation')
            ->limit(100)
            ->get();

        $recipientsQuery = User::whereNotIn('users.status', ['DISABLED', 'SUSPENDED'])
            ->where('users.id', '!=', $user->id)
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->select('users.id', 'users.name', 'users.name_kh', 'users.email', 'users.position_id', 'users.department_id', 'users.office_id', 'users.profile_image')
            ->with(['position:id,title_kh,title_en,level', 'department:id,name_kh', 'office:id,name_kh'])
            ->orderByRaw('COALESCE(positions.level, 999) ASC')
            ->orderBy('users.name_kh', 'ASC');

        // Check Workflow Setup for sending constraints
        $config = DocumentWorkflowConfig::where('user_id', $user->id)->first();
        if (!$isDg && $config) {
            $sendType = $config->send_type ?? 'SUBORDINATES';
            if ($sendType === 'CUSTOM') {
                $recipientsQuery->where(function ($q) use ($config) {
                    if (!empty($config->send_dept_ids)) {
                        $q->orWhereIn('users.department_id', $config->send_dept_ids);
                    }
                    if (!empty($config->send_office_ids)) {
                        $q->orWhereIn('users.office_id', $config->send_office_ids);
                    }
                    if (!empty($config->send_user_ids)) {
                        $q->orWhereIn('users.id', $config->send_user_ids);
                    }
                    if (!empty($config->send_working_group_ids)) {
                        $q->orWhereHas('workingGroups', function ($wgQ) use ($config) {
                            $wgQ->whereIn('working_groups.id', $config->send_working_group_ids);
                        });
                    }
                });
            } elseif ($sendType === 'OFFICE' && $user->office_id) {
                // Strictly within same office
                $recipientsQuery->where('users.office_id', $user->office_id);
            } elseif ($sendType === 'DEPARTMENT' && $user->department_id) {
                // Strictly within same department
                $recipientsQuery->where('users.department_id', $user->department_id);
            } elseif ($sendType === 'SUBORDINATES') {
                // Superiors in hierarchy or DG leadership
                $recipientsQuery->where(function ($q) use ($user, $viewerPosLevel) {
                    $q->where(function ($sub) use ($user, $viewerPosLevel) {
                        $sub->where('positions.level', '<', $viewerPosLevel)
                            ->where(function ($deptOrOff) use ($user) {
                                if ($user->department_id) {
                                    $deptOrOff->where('users.department_id', $user->department_id);
                                }
                                if ($user->office_id) {
                                    $deptOrOff->orWhere('users.office_id', $user->office_id);
                                }
                            });
                    })->orWhere('positions.level', '<=', 2);
                });
            }
        } elseif (!$isDg) {
            // Default when no config: superiors in hierarchy or DG leadership
            $recipientsQuery->where(function ($q) use ($user, $viewerPosLevel) {
                $q->where(function ($sub) use ($user, $viewerPosLevel) {
                    $sub->where('positions.level', '<', $viewerPosLevel)
                        ->where(function ($deptOrOff) use ($user) {
                            if ($user->department_id) {
                                $deptOrOff->where('users.department_id', $user->department_id);
                            }
                            if ($user->office_id) {
                                $deptOrOff->orWhere('users.office_id', $user->office_id);
                            }
                        });
                })->orWhere('positions.level', '<=', 2);
            });
        }

        $recipients = $recipientsQuery->get()->filter(fn($u) => (int) $u->id !== (int) $user->id)->values();

        return response()->json([
            'inbound_documents' => $inboundDocs,
            'recipients' => $recipients,
            'my_workflow_config' => $config,
        ]);
    }

    /**
     * 2. បញ្ជីឯកសារចេញ / លិខិតឆ្លើយតប តាម ៣ Tabs (Doing, In Progress, Complete)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $tab = strtolower($request->input('tab', 'all')); // all, doing, in_progress, complete
        $viewerPosLevel = $user->position ? (int) $user->position->level : 99;
        $isDg = ($viewerPosLevel <= 1) || (strtoupper($user->level ?? '') === 'ADMIN');

        $baseQuery = InboundDocumentResponse::with([
            'inboundDocument:id,general_inbound_number,dg_inbound_number,title,received_date,status,sender_organization,dg_annotation,original_file_path,original_file_name,annotated_file_path,annotated_file_name',
            'draftedByUser:id,name,name_kh,email,position_id,department_id,office_id,profile_image',
            'draftedByUser.position:id,title_kh,level',
            'draftedByUser.department:id,name_kh',
            'draftedByUser.office:id,name_kh',
            'currentApprover:id,name,name_kh,email,position_id,department_id,office_id,profile_image',
            'currentApprover.position:id,title_kh,level',
            'approvedByUser:id,name,name_kh,profile_image',
            'department:id,name_kh',
            'office:id,name_kh',
            'approvals.user:id,name,name_kh,position_id,profile_image,signature_image',
            'approvals.user.position:id,title_kh,level',
            'approvals.forwardedTo:id,name,name_kh,position_id',
            'approvals.forwardedTo.position:id,title_kh,level',
        ]);

        $query = clone $baseQuery;

        if ($tab === 'all') {
            // Tab 0: All Accessible Documents (ឯកសារទាំងអស់ដែលខ្លួនមានសិទ្ធិមើល)
            $this->scopeAccessibleByViewer($query, $user);
        } elseif ($tab === 'complete' || $tab === 'completed') {
            // Tab 3: Complete (បានអនុម័តដោយអគ្គនាយក)
            $query->where('status', 'APPROVED_BY_DG');
            $this->scopeAccessibleByViewer($query, $user);
        } elseif ($tab === 'in_progress') {
            // Tab 2: In Progress (កំពុងដំណើរការ/បានឆ្លងបន្ត និងមិនទាន់ចប់)
            $query->where('status', 'UNDER_REVIEW')
                ->where(function ($q) use ($user) {
                    $q->whereNull('current_approver_id')
                      ->orWhere('current_approver_id', '!=', $user->id);
                });
            $this->scopeAccessibleByViewer($query, $user);
        } elseif ($tab === 'doing') {
            // Tab 1: Doing (ត្រូវធ្វើ / កំពុងរៀបចំ / ត្រូវពិនិត្យ)
            // 1. Current reviewer waiting to review
            // 2. Or drafter with DRAFT or RETURNED
            $query->where(function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('current_approver_id', $user->id)
                        ->where('status', 'UNDER_REVIEW');
                })->orWhere(function ($sub) use ($user) {
                    $sub->where('drafted_by', $user->id)
                        ->whereIn('status', ['DRAFT', 'RETURNED']);
                });
            });
        } else {
            // Default: All Accessible Documents
            $this->scopeAccessibleByViewer($query, $user);
        }

        // Search & Filters
        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('response_number', 'like', "%{$s}%")
                  ->orWhere('reference_note', 'like', "%{$s}%")
                  ->orWhere('content', 'like', "%{$s}%")
                  ->orWhereHas('inboundDocument', function ($iq) use ($s) {
                      $iq->where('title', 'like', "%{$s}%")
                         ->orWhere('general_inbound_number', 'like', "%{$s}%")
                         ->orWhere('dg_inbound_number', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('response_type')) {
            $query->where('response_type', $request->input('response_type'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $docs = $query->orderBy('id', 'desc')->paginate($perPage);

        // Stats calculation respecting Workflow View permissions
        $allCount = $this->scopeAccessibleByViewer(InboundDocumentResponse::query(), $user)->count();

        $doingCount = InboundDocumentResponse::where(function ($q) use ($user) {
            $q->where(function ($sub) use ($user) {
                $sub->where('current_approver_id', $user->id)->where('status', 'UNDER_REVIEW');
            })->orWhere(function ($sub) use ($user) {
                $sub->where('drafted_by', $user->id)->whereIn('status', ['DRAFT', 'RETURNED']);
            });
        })->count();

        $inProgressQuery = InboundDocumentResponse::where('status', 'UNDER_REVIEW')
            ->where(function ($q) use ($user) {
                $q->whereNull('current_approver_id')
                  ->orWhere('current_approver_id', '!=', $user->id);
            });
        $inProgressCount = $this->scopeAccessibleByViewer($inProgressQuery, $user)->count();

        $completeQuery = InboundDocumentResponse::where('status', 'APPROVED_BY_DG');
        $completeCount = $this->scopeAccessibleByViewer($completeQuery, $user)->count();

        return response()->json([
            'tab' => $tab,
            'data' => $docs->items(),
            'current_page' => $docs->currentPage(),
            'last_page' => $docs->lastPage(),
            'per_page' => $docs->perPage(),
            'total' => $docs->total(),
            'stats' => [
                'all' => $allCount,
                'doing' => $doingCount,
                'in_progress' => $inProgressCount,
                'complete' => $completeCount,
            ],
        ]);
    }

    /**
     * 3. បង្កើតឯកសារចេញ / របាយការណ៍ / កំណត់បង្ហាញ (Create / Store)
     */
    public function store(Request $request)
    {
        $viewer = $request->user();
        if (!$viewer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isDraft = filter_var($request->input('is_draft', false), FILTER_VALIDATE_BOOLEAN);

        $rules = [
            'inbound_document_id' => 'nullable|exists:inbound_documents,id',
            'response_type' => 'required|in:REPORT,SUBMISSION_NOTE,LETTER',
            'title' => 'required|string|max:500',
            'header_organization' => 'nullable|string|max:255',
            'reference_note' => 'nullable|string',
            'content' => 'nullable|string',
            'sign_title' => 'nullable|string|max:255',
            'sign_name' => 'nullable|string|max:255',
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

        $response = DB::transaction(function () use ($request, $viewer, $isDraft) {
            $filePath = null;
            $fileName = null;
            if ($request->hasFile('response_file')) {
                $file = $request->file('response_file');
                $fileName = $file->getClientOriginalName();
                $filePath = StorageService::store($file, 'inbound_documents/responses');
            }

            $inboundDocId = $request->input('inbound_document_id') ? (int) $request->input('inbound_document_id') : null;
            $inboundDoc = $inboundDocId ? InboundDocument::find($inboundDocId) : null;

            $responseType = $request->input('response_type', 'REPORT');
            $responseTypeKh = match ($responseType) {
                'SUBMISSION_NOTE' => 'កំណត់បង្ហាញ',
                'LETTER' => 'លិខិត',
                default => 'របាយការណ៍',
            };

            $createData = [
                'inbound_document_id' => $inboundDocId,
                'response_type' => $responseType,
                'title' => $request->input('title'),
                'header_organization' => $request->input('header_organization') ?: ($viewer->department?->name_kh ?? 'និយ័តករបរធនបាលកិច្ច'),
                'reference_note' => $request->input('reference_note'),
                'content' => $request->input('content'),
                'sign_title' => $request->input('sign_title') ?: ($viewer->position?->title_kh ?? 'មន្ត្រី'),
                'sign_name' => $request->input('sign_name') ?: ($viewer->name_kh ?? $viewer->name),
                'drafted_by' => $viewer->id,
                'department_id' => $viewer->department_id,
                'office_id' => $viewer->office_id,
                'file_path' => $filePath,
                'file_name' => $fileName,
            ];

            if ($isDraft) {
                $createData['status'] = 'DRAFT';
                $createData['current_approver_id'] = $viewer->id;
                $createData['current_stage'] = 'OFFICE';

                $resp = InboundDocumentResponse::create($createData);

                // Record Revision 1
                \App\Models\InboundDocumentResponseRevision::create([
                    'response_id' => $resp->id,
                    'user_id' => $viewer->id,
                    'revision_number' => 1,
                    'title' => $resp->title,
                    'header_organization' => $resp->header_organization,
                    'reference_note' => $resp->reference_note,
                    'content' => $resp->content,
                    'sign_title' => $resp->sign_title,
                    'sign_name' => $resp->sign_name,
                    'change_summary' => 'បង្កើតសេចក្តីព្រាង (Draft)',
                ]);

                if ($inboundDoc) {
                    InboundDocumentMovement::create([
                        'inbound_document_id' => $inboundDoc->id,
                        'user_id' => $viewer->id,
                        'action' => 'RESPONSE_DRAFTED',
                        'from_status' => $inboundDoc->status,
                        'to_status' => $inboundDoc->status,
                        'comment' => "បានរក្សាទុកសេចក្តីព្រាង{$responseTypeKh} ៖ {$resp->title}",
                    ]);
                }

                return $resp;
            }

            // Submit Upward Mode
            $targetReviewer = User::with('position')->findOrFail($request->input('forwarded_to_id'));
            $reviewerLevel = $targetReviewer->position ? (int) $targetReviewer->position->level : 99;

            $stage = match (true) {
                $reviewerLevel === 1 => 'DG',
                $reviewerLevel === 2 => 'LEADERSHIP',
                $reviewerLevel <= 5 => 'DEPARTMENT',
                default => 'OFFICE',
            };

            $createData['status'] = 'UNDER_REVIEW';
            $createData['current_approver_id'] = $targetReviewer->id;
            $createData['current_stage'] = $stage;
            $createData['submitted_at'] = Carbon::now();

            // Assign department report number if forwarding beyond department upon creation
            $docDeptId = $viewer->department_id;
            $isLeaving = $this->isForwardingBeyondDepartment($docDeptId, $targetReviewer);
            $assignedNotice = '';

            if ($isLeaving) {
                if ($request->filled('response_number')) {
                    $customNum = trim($request->input('response_number'));
                    $isDup = DB::table('inbound_document_responses')
                        ->where('department_id', $docDeptId)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->where('response_number', $customNum)
                        ->exists();
                    if ($isDup) {
                        abort(422, "លេខរបាយការណ៍ «{$customNum}» មានរួចហើយនៅក្នុងនាយកដ្ឋាននេះ មិនអាចដាក់ស្ទួនគ្នាបានឡើយ!");
                    }
                    $createData['response_number'] = $customNum;
                    $assignedNotice = " (បានចុះលេខរបាយការណ៍៖ {$customNum})";
                } else {
                    $gen = self::generateNextDepartmentReportNumber($docDeptId);
                    $createData['response_number'] = $gen['full_number'];
                    $createData['content'] = $this->injectNumberIntoContent($createData['content'], $gen['seq']);
                    $assignedNotice = " (បានចុះលេខរបាយការណ៍៖ {$gen['full_number']})";
                }
            }

            $resp = InboundDocumentResponse::create($createData);

            // Record Revision 1
            \App\Models\InboundDocumentResponseRevision::create([
                'response_id' => $resp->id,
                'user_id' => $viewer->id,
                'revision_number' => 1,
                'title' => $resp->title,
                'header_organization' => $resp->header_organization,
                'reference_note' => $resp->reference_note,
                'content' => $resp->content,
                'sign_title' => $resp->sign_title,
                'sign_name' => $resp->sign_name,
                'change_summary' => 'បង្កើត និងដាក់ស្នើដំបូង (Original Submission)',
            ]);

            $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
            $viewerStage = match (true) {
                $viewerPosLevel === 1 => 'DG',
                $viewerPosLevel === 2 => 'LEADERSHIP',
                $viewerPosLevel <= 5 => 'DEPARTMENT',
                default => 'OFFICE',
            };

            InboundDocumentResponseApproval::create([
                'response_id' => $resp->id,
                'user_id' => $viewer->id,
                'action' => 'SUBMIT',
                'stage' => $viewerStage,
                'comment' => ($request->input('comment', "បានរៀបចំ និងដាក់ស្នើឆ្លង{$responseTypeKh}")) . $assignedNotice,
                'forwarded_to_id' => $targetReviewer->id,
            ]);

            if ($inboundDoc) {
                $inboundDoc->update([
                    'status' => 'IN_RESPONSE_PROGRESS',
                    'is_response_required' => true,
                ]);

                InboundDocumentMovement::create([
                    'inbound_document_id' => $inboundDoc->id,
                    'user_id' => $viewer->id,
                    'action' => 'RESPONSE_SUBMITTED',
                    'from_status' => $inboundDoc->status,
                    'to_status' => 'IN_RESPONSE_PROGRESS',
                    'comment' => "បានដាក់ស្នើ{$responseTypeKh} ៖ {$resp->title} ទៅកាន់ " . ($targetReviewer->name_kh ?? $targetReviewer->name),
                ]);
            }

            // Telegram Notification
            $this->telegram->notifyResponseSubmitted($resp, $viewer, $targetReviewer);

            return $resp;
        });

        return response()->json([
            'message' => $isDraft ? 'បានរក្សាទុកសេចក្តីព្រាងដោយជោគជ័យ!' : 'បានដាក់ស្នើឆ្លងលិខិតទៅកាន់ថ្នាក់ដឹកនាំដោយជោគជ័យ!',
            'data' => $response->load(['draftedByUser', 'currentApprover', 'inboundDocument']),
        ]);
    }

    /**
     * 4. មើលព័ត៌មានលម្អិតឯកសារចេញ (Show Detail)
     */
    public function show($id, Request $request)
    {
        $viewer = $request->user();
        if (!$viewer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Enforce Workflow View permissions
        $checkQuery = InboundDocumentResponse::where('id', $id);
        $this->scopeAccessibleByViewer($checkQuery, $viewer);
        if (!$checkQuery->exists()) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិមើលឯកសារនេះឡើយ ស្របតាមការកំណត់ Workflow មើលឯកសារ!'], 403);
        }

        $response = InboundDocumentResponse::with([
            'inboundDocument:id,general_inbound_number,dg_inbound_number,title,received_date,status,sender_organization,dg_annotation,original_file_path,original_file_name,annotated_file_path,annotated_file_name',
            'draftedByUser:id,name,name_kh,email,position_id,department_id,office_id,profile_image',
            'draftedByUser.position:id,title_kh,level',
            'draftedByUser.department:id,name_kh',
            'draftedByUser.office:id,name_kh',
            'currentApprover:id,name,name_kh,email,position_id,department_id,office_id,profile_image',
            'currentApprover.position:id,title_kh,level',
            'approvedByUser:id,name,name_kh,position_id,profile_image',
            'approvedByUser.position:id,title_kh,level',
            'department:id,name_kh',
            'office:id,name_kh',
            'approvals.user:id,name,name_kh,position_id,profile_image,signature_image',
            'approvals.user.position:id,title_kh,level',
            'approvals.forwardedTo:id,name,name_kh,position_id',
            'approvals.forwardedTo.position:id,title_kh,level',
            'revisions.user:id,name,name_kh,position_id',
            'revisions.user.position:id,title_kh,level',
        ])->findOrFail($id);

        // Auto-seed Revision 1 if missing for backwards compatibility
        if ($response->revisions->isEmpty()) {
            \App\Models\InboundDocumentResponseRevision::create([
                'response_id' => $response->id,
                'user_id' => $response->drafted_by,
                'revision_number' => 1,
                'title' => $response->title,
                'header_organization' => $response->header_organization,
                'reference_note' => $response->reference_note,
                'content' => $response->content,
                'sign_title' => $response->sign_title,
                'sign_name' => $response->sign_name,
                'change_summary' => 'បង្កើតសេចក្តីព្រាងដើម (Original Draft)',
                'created_at' => $response->created_at,
            ]);
            $response->load(['revisions.user:id,name,name_kh,position_id', 'revisions.user.position:id,title_kh,level']);
        }

        return response()->json(['data' => $response]);
    }

    /**
     * 5. កែប្រែសេចក្តីព្រាង ឬលិខិតដែលរុញត្រឡប់ (Update Draft / Returned / Approver Edit)
     */
    public function update($id, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::findOrFail($id);

        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel <= 1) || (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isDrafter = ((int) $response->drafted_by === (int) $viewer->id);
        $isCurrentApprover = ((int) $response->current_approver_id === (int) $viewer->id && $response->status === 'UNDER_REVIEW');

        if (!$isDrafter && !$isCurrentApprover && !$isDg) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិកែប្រែលិខិតនេះឡើយ!'], 403);
        }

        if ($isDrafter && !in_array($response->status, ['DRAFT', 'RETURNED']) && !$isCurrentApprover && !$isDg) {
            return response()->json(['message' => 'លិខិតនេះបានដាក់ស្នើឆ្លងហើយ មិនអាចកែសម្រួលផ្ទាល់បានឡើយ (ត្រូវទាញយកមកវិញសិន)!'], 400);
        }

        $isDraft = filter_var($request->input('is_draft', false), FILTER_VALIDATE_BOOLEAN);

        $rules = [
            'inbound_document_id' => 'nullable|exists:inbound_documents,id',
            'response_type' => 'required|in:REPORT,SUBMISSION_NOTE,LETTER',
            'title' => 'required|string|max:500',
            'header_organization' => 'nullable|string|max:255',
            'reference_note' => 'nullable|string',
            'content' => 'nullable|string',
            'sign_title' => 'nullable|string|max:255',
            'sign_name' => 'nullable|string|max:255',
            'response_file' => 'nullable|file|max:25600',
            'comment' => 'nullable|string',
            'change_summary' => 'nullable|string|max:500',
        ];

        if (!$isDraft && $isDrafter && !$response->current_approver_id) {
            $rules['forwarded_to_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $updated = DB::transaction(function () use ($request, $viewer, $response, $isDraft, $isDrafter, $isCurrentApprover, $isDg, $viewerPosLevel) {
            $oldContent = $response->content;
            $oldTitle = $response->title;
            $oldRef = $response->reference_note;

            $filePath = $response->file_path;
            $fileName = $response->file_name;
            if ($request->hasFile('response_file')) {
                if ($filePath && StorageService::exists($filePath)) {
                    StorageService::delete($filePath);
                }
                $file = $request->file('response_file');
                $fileName = $file->getClientOriginalName();
                $filePath = StorageService::store($file, 'inbound_documents/responses');
            }

            $responseType = $request->input('response_type', $response->response_type);
            $responseTypeKh = match ($responseType) {
                'SUBMISSION_NOTE' => 'កំណត់បង្ហាញ',
                'LETTER' => 'លិខិត',
                default => 'របាយការណ៍',
            };

            $updateData = [
                'response_type' => $responseType,
                'title' => $request->input('title'),
                'header_organization' => $request->input('header_organization', $response->header_organization),
                'reference_note' => $request->input('reference_note', $response->reference_note),
                'content' => $request->input('content', $response->content),
                'sign_title' => $request->input('sign_title', $response->sign_title),
                'sign_name' => $request->input('sign_name', $response->sign_name),
                'file_path' => $filePath,
                'file_name' => $fileName,
            ];

            if ($request->has('inbound_document_id')) {
                $updateData['inbound_document_id'] = $request->input('inbound_document_id') ? (int) $request->input('inbound_document_id') : null;
            }

            // Record Revision when content/title/ref changed
            $newContent = $request->input('content');
            $newTitle = $request->input('title');
            $newRef = $request->input('reference_note');

            $hasChanged = (trim((string)$oldContent) !== trim((string)$newContent))
                || ($oldTitle !== $newTitle)
                || ($oldRef !== $newRef);

            if ($hasChanged) {
                $latestRevNum = \App\Models\InboundDocumentResponseRevision::where('response_id', $response->id)->max('revision_number') ?: 1;
                $roleLabel = $isDrafter ? 'អ្នករៀបចំ' : ($isDg ? 'ឯកឧត្តមអគ្គនាយក' : 'ថ្នាក់ដឹកនាំ');
                $summary = $request->input('change_summary') ?: "{$roleLabel} ({$viewer->name_kh}) បានកែសម្រួលខ្លឹមសារ";

                \App\Models\InboundDocumentResponseRevision::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'revision_number' => $latestRevNum + 1,
                    'title' => $newTitle,
                    'header_organization' => $updateData['header_organization'],
                    'reference_note' => $newRef,
                    'content' => $newContent,
                    'sign_title' => $updateData['sign_title'],
                    'sign_name' => $updateData['sign_name'],
                    'change_summary' => $summary,
                ]);

                \App\Models\InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'EDIT',
                    'stage' => $response->current_stage,
                    'comment' => "បានកែសម្រួលខ្លឹមសារ{$responseTypeKh} (កំណែទី " . ($latestRevNum + 1) . ")",
                    'forwarded_to_id' => $response->current_approver_id,
                ]);
            }

            if ($isDraft && $isDrafter) {
                $updateData['status'] = 'DRAFT';
                $updateData['current_approver_id'] = $viewer->id;
                $response->update($updateData);
                return $response;
            }

            // If approver editing while UNDER_REVIEW and not selecting a forward target:
            if ($isCurrentApprover && !$request->filled('forwarded_to_id')) {
                $response->update($updateData);
                return $response;
            }

            // Submit / Forward Upward
            if ($request->filled('forwarded_to_id')) {
                $targetReviewer = User::with('position')->findOrFail($request->input('forwarded_to_id'));
                $reviewerLevel = $targetReviewer->position ? (int) $targetReviewer->position->level : 99;

                $stage = match (true) {
                    $reviewerLevel === 1 => 'DG',
                    $reviewerLevel === 2 => 'LEADERSHIP',
                    $reviewerLevel <= 5 => 'DEPARTMENT',
                    default => 'OFFICE',
                };

                // Check if forwarding beyond department
                $docDeptId = $response->department_id ?: $viewer->department_id;
                $isLeaving = $this->isForwardingBeyondDepartment($docDeptId, $targetReviewer);
                $assignedNotice = '';

                if ($isLeaving && empty($response->response_number)) {
                    if ($request->filled('response_number')) {
                        $customNum = trim($request->input('response_number'));
                        $isDup = DB::table('inbound_document_responses')
                            ->where('department_id', $docDeptId)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->where('response_number', $customNum)
                            ->where('id', '!=', $response->id)
                            ->exists();
                        if ($isDup) {
                            abort(422, "លេខរបាយការណ៍ «{$customNum}» មានរួចហើយនៅក្នុងនាយកដ្ឋាននេះ មិនអាចដាក់ស្ទួនគ្នាបានឡើយ!");
                        }
                        $updateData['response_number'] = $customNum;
                        $assignedNotice = " (បានចុះលេខរបាយការណ៍៖ {$customNum})";
                    } else {
                        $gen = self::generateNextDepartmentReportNumber($docDeptId, $response->id);
                        $updateData['response_number'] = $gen['full_number'];
                        $updateData['content'] = $this->injectNumberIntoContent($updateData['content'] ?? $response->content, $gen['seq']);
                        $assignedNotice = " (បានចុះលេខរបាយការណ៍៖ {$gen['full_number']})";
                    }
                }

                $updateData['status'] = 'UNDER_REVIEW';
                $updateData['current_approver_id'] = $targetReviewer->id;
                $updateData['current_stage'] = $stage;
                $updateData['submitted_at'] = Carbon::now();

                $response->update($updateData);

                $viewerStage = match (true) {
                    $viewerPosLevel === 1 => 'DG',
                    $viewerPosLevel === 2 => 'LEADERSHIP',
                    $viewerPosLevel <= 5 => 'DEPARTMENT',
                    default => 'OFFICE',
                };

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => $isDrafter ? 'SUBMIT' : 'FORWARD',
                    'stage' => $viewerStage,
                    'comment' => ($request->input('comment', "បានកែសម្រួល និងចារឆ្លងបន្ត{$responseTypeKh}")) . $assignedNotice,
                    'forwarded_to_id' => $targetReviewer->id,
                ]);

                if ($response->inbound_document_id) {
                    InboundDocumentMovement::create([
                        'inbound_document_id' => $response->inbound_document_id,
                        'user_id' => $viewer->id,
                        'action' => $isDrafter ? 'RESPONSE_SUBMITTED' : 'RESPONSE_FORWARDED',
                        'comment' => "បានកែសម្រួល និងចារឆ្លង{$responseTypeKh} ទៅកាន់ " . ($targetReviewer->name_kh ?? $targetReviewer->name) . $assignedNotice . ($request->input('comment') ? " ៖ «" . $request->input('comment') . "»" : ""),
                    ]);
                }

                // Telegram Notification
                $this->telegram->notifyResponseSubmitted($response, $viewer, $targetReviewer);
            } else {
                $response->update($updateData);

                if ($response->inbound_document_id && $isCurrentApprover) {
                    InboundDocumentMovement::create([
                        'inbound_document_id' => $response->inbound_document_id,
                        'user_id' => $viewer->id,
                        'action' => 'DOCUMENT_UPDATED',
                        'comment' => "ថ្នាក់ដឹកនាំ (" . ($viewer->name_kh ?? $viewer->name) . ") បានកែសម្រួលខ្លឹមសារ{$responseTypeKh}",
                    ]);
                }
            }

            return $response;
        });

        return response()->json([
            'message' => $isDraft ? 'បានរក្សាទុកសេចក្តីព្រាងដោយជោគជ័យ!' : 'បានកែសម្រួល និងរក្សាទុកដោយជោគជ័យ!',
            'data' => $updated->load(['draftedByUser', 'currentApprover', 'inboundDocument', 'revisions.user.position']),
        ]);
    }

    /**
     * Quick update of response content and positioning of draggable opinion boxes
     */
    public function updateContent($id, Request $request)
    {
        $viewer = $request->user() ?: Auth::user();
        if (!$viewer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $response = InboundDocumentResponse::findOrFail($id);

        // Check locked status: completed/approved docs or numbered docs are locked
        $isLocked = in_array($response->status, ['APPROVED_BY_DG', 'COMPLETED', 'CANCELLED']);
        if ($isLocked) {
            return response()->json(['message' => 'ឯកសារត្រូវបានចាក់សោ មិនអាចកែប្រែបានឡើយ!'], 403);
        }

        $isDrafter = ((int) $response->drafted_by === (int) $viewer->id);
        $isCurrentApprover = ((int) $response->current_approver_id === (int) $viewer->id);

        // Allow only: drafter in DRAFT/RETURNED; current approver while UNDER_REVIEW; never when locked
        $canEdit = ($isDrafter && in_array($response->status, ['DRAFT', 'RETURNED']))
            || ($isCurrentApprover && $response->status === 'UNDER_REVIEW');

        if (!$canEdit) {
            return response()->json(['message' => 'លោកអ្នកពុំមានសិទ្ធិកែប្រែខ្លឹមសារ ឬទីតាំងហត្ថលេខានៃលិខិតនេះឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $newContent = $request->input('content');

        DB::transaction(function () use ($response, $viewer, $isDrafter, $newContent) {
            $response->content = $newContent;
            $response->save();

            $latestRevNum = InboundDocumentResponseRevision::where('response_id', $response->id)->max('revision_number') ?: 1;
            $roleLabel = $isDrafter ? 'អ្នកព្រាង' : 'អ្នកពិនិត្យ';
            $viewerName = $viewer->name_kh ?: $viewer->name;
            $summary = "{$roleLabel} ({$viewerName}) បានកែប្រែខ្លឹមសារ";

            InboundDocumentResponseRevision::create([
                'response_id' => $response->id,
                'user_id' => $viewer->id,
                'revision_number' => $latestRevNum + 1,
                'title' => $response->title,
                'header_organization' => $response->header_organization,
                'reference_note' => $response->reference_note,
                'content' => $newContent,
                'sign_title' => $response->sign_title,
                'sign_name' => $response->sign_name,
                'change_summary' => $summary,
            ]);
        });

        return response()->json([
            'message' => 'បានរក្សាទុកទីតាំង និងខ្លឹមសារឯកសារដោយជោគជ័យ!',
            'content' => $response->content,
        ]);
    }

    /**
     * 6. សកម្មភាពពិនិត្យឆ្លង (Review Action: FORWARD, RETURN, DG_APPROVE)
     */
    public function reviewAction($id, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::with('inboundDocument')->findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isDg = ($viewerPosLevel <= 1) || $isAdmin;

        if ($response->status !== 'UNDER_REVIEW') {
            return response()->json(['message' => 'លិខិតនេះមិនស្ថិតក្នុងការពិនិត្យឆ្លងឡើយ!'], 400);
        }

        if ($response->current_approver_id !== $viewer->id && !$isAdmin) {
            return response()->json(['message' => 'លោកអ្នកពុំមែនជាថ្នាក់ដឹកនាំទទួលបន្ទុកពិនិត្យលិខិតនេះឡើយ!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:FORWARD,RETURN,DG_APPROVE,HOLD',
            'forwarded_to_id' => 'required_if:action,FORWARD|nullable|exists:users,id',
            'comment' => 'nullable|string',
            'response_number' => 'nullable|string|max:100',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $action = $request->input('action');
        $comment = $request->input('comment');

        DB::transaction(function () use ($action, $comment, $request, $viewer, $response, $isDg, $viewerPosLevel) {
            // If approver attached / updated report content with their opinion & signature
            if ($request->filled('content') && trim((string)$response->content) !== trim((string)$request->input('content'))) {
                $newContent = $request->input('content');
                $response->content = $newContent;
                $response->save();

                $latestRevNum = \App\Models\InboundDocumentResponseRevision::where('response_id', $response->id)->max('revision_number') ?: 1;
                $roleLabel = ($isDg ? 'ឯកឧត្តមអគ្គនាយក' : 'ថ្នាក់ដឹកនាំ');
                $summary = "{$roleLabel} ({$viewer->name_kh}) បានបញ្ចូលមតិយោបល់ និងហត្ថលេខាលើឯកសារ";

                \App\Models\InboundDocumentResponseRevision::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'revision_number' => $latestRevNum + 1,
                    'title' => $response->title,
                    'header_organization' => $response->header_organization,
                    'reference_note' => $response->reference_note,
                    'content' => $newContent,
                    'sign_title' => $response->sign_title,
                    'sign_name' => $response->sign_name,
                    'change_summary' => $summary,
                ]);
            }
            if ($action === 'FORWARD') {
                $nextReviewer = User::with('position')->findOrFail($request->input('forwarded_to_id'));
                $reviewerLevel = $nextReviewer->position ? (int) $nextReviewer->position->level : 99;

                $nextStage = match (true) {
                    $reviewerLevel === 1 => 'DG',
                    $reviewerLevel === 2 => 'LEADERSHIP',
                    $reviewerLevel <= 5 => 'DEPARTMENT',
                    default => 'OFFICE',
                };

                $viewerStage = match (true) {
                    $viewerPosLevel === 1 => 'DG',
                    $viewerPosLevel === 2 => 'LEADERSHIP',
                    $viewerPosLevel <= 5 => 'DEPARTMENT',
                    default => 'OFFICE',
                };

                $updateFields = [
                    'current_approver_id' => $nextReviewer->id,
                    'current_stage' => $nextStage,
                    'status' => 'UNDER_REVIEW',
                ];

                // Check if forwarding beyond department
                $docDeptId = $response->department_id ?: $viewer->department_id;
                $isLeaving = $this->isForwardingBeyondDepartment($docDeptId, $nextReviewer);
                $assignedNotice = '';

                if ($isLeaving && empty($response->response_number)) {
                    if ($request->filled('response_number')) {
                        $customNum = trim($request->input('response_number'));
                        $isDup = DB::table('inbound_document_responses')
                            ->where('department_id', $docDeptId)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->where('response_number', $customNum)
                            ->where('id', '!=', $response->id)
                            ->exists();
                        if ($isDup) {
                            abort(422, "លេខរបាយការណ៍ «{$customNum}» មានរួចហើយនៅក្នុងនាយកដ្ឋាននេះ មិនអាចដាក់ស្ទួនគ្នាបានឡើយ!");
                        }
                        $updateFields['response_number'] = $customNum;
                        $assignedNotice = " (បានចុះលេខរបាយការណ៍៖ {$customNum})";
                    } else {
                        $gen = self::generateNextDepartmentReportNumber($docDeptId, $response->id);
                        $updateFields['response_number'] = $gen['full_number'];
                        $updateFields['content'] = $this->injectNumberIntoContent($response->content, $gen['seq']);
                        $assignedNotice = " (បានចុះលេខរបាយការណ៍៖ {$gen['full_number']})";
                    }
                }

                $response->update($updateFields);

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'FORWARD',
                    'stage' => $viewerStage,
                    'comment' => ($comment ?: "បានពិនិត្យ និងចារបញ្ជូនបន្តជូន {$nextReviewer->name_kh}") . $assignedNotice,
                    'forwarded_to_id' => $nextReviewer->id,
                ]);

                if ($response->inbound_document_id) {
                    $responseTypeKh = match ($response->response_type) {
                        'SUBMISSION_NOTE' => 'កំណត់បង្ហាញ',
                        'LETTER' => 'លិខិត',
                        default => 'របាយការណ៍',
                    };
                    InboundDocumentMovement::create([
                        'inbound_document_id' => $response->inbound_document_id,
                        'user_id' => $viewer->id,
                        'action' => 'RESPONSE_FORWARDED',
                        'comment' => "បានពិនិត្យ និងបញ្ជូនបន្ត{$responseTypeKh} ទៅកាន់ " . ($nextReviewer->name_kh ?? $nextReviewer->name) . $assignedNotice . ($comment ? " ៖ «{$comment}»" : ""),
                    ]);
                }

                $this->telegram->notifyResponseForwarded($response, $viewer, $nextReviewer, $comment);
            } elseif ($action === 'RETURN') {
                $drafter = User::findOrFail($response->drafted_by);

                $response->update([
                    'current_approver_id' => $response->drafted_by,
                    'current_stage' => 'OFFICE',
                    'status' => 'RETURNED',
                ]);

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'RETURN',
                    'stage' => 'OFFICE',
                    'comment' => $comment ?: 'បានបញ្ជូនត្រឡប់ដើម្បីកែសម្រួលឡើងវិញ',
                    'forwarded_to_id' => $response->drafted_by,
                ]);

                if ($response->inbound_document_id) {
                    $responseTypeKh = match ($response->response_type) {
                        'SUBMISSION_NOTE' => 'កំណត់បង្ហាញ',
                        'LETTER' => 'លិខិត',
                        default => 'របាយការណ៍',
                    };
                    InboundDocumentMovement::create([
                        'inbound_document_id' => $response->inbound_document_id,
                        'user_id' => $viewer->id,
                        'action' => 'RESPONSE_RETURNED',
                        'comment' => "បានបញ្ជូន{$responseTypeKh} ត្រឡប់ទៅកាន់ " . ($drafter->name_kh ?? $drafter->name) . " វិញដើម្បីកែសម្រួល" . ($comment ? " ៖ «{$comment}»" : ""),
                    ]);
                }

                $this->telegram->notifyResponseReturned($response, $viewer, $drafter, $comment);
            } elseif ($action === 'DG_APPROVE') {
                if (!$isDg) {
                    abort(403, 'មានតែឯកឧត្តមអគ្គនាយកទេ ទើបមានសិទ្ធិឯកភាពលើលិខិតឆ្លើយតបនេះបាន!');
                }

                $docDeptId = $response->department_id ?: $viewer->department_id;
                $responseNumber = $request->input('response_number') ?: $response->response_number;

                if (empty($responseNumber)) {
                    $gen = self::generateNextDepartmentReportNumber($docDeptId, $response->id);
                    $responseNumber = $gen['full_number'];
                    $response->content = $this->injectNumberIntoContent($response->content, $gen['seq']);
                }

                $response->update([
                    'response_number' => $responseNumber,
                    'content' => $response->content,
                    'status' => 'APPROVED_BY_DG',
                    'current_stage' => 'COMPLETED',
                    'approved_at' => Carbon::now(),
                    'approved_by' => $viewer->id,
                    'current_approver_id' => null,
                ]);

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'DG_APPROVE',
                    'stage' => 'COMPLETED',
                    'comment' => $comment ?: "ឯកឧត្តមអគ្គនាយកបានពិនិត្យ និងឯកភាព" . ($responseNumber ? " (លេខចេញ៖ {$responseNumber})" : ""),
                ]);

                // IF LINKED TO INBOUND DOCUMENT: AUTO COMPLETE THE INBOUND DOCUMENT
                if ($response->inbound_document_id) {
                    $doc = InboundDocument::find($response->inbound_document_id);
                    if ($doc && $doc->status !== 'COMPLETED') {
                        $doc->update([
                            'status' => 'COMPLETED',
                            'completed_at' => Carbon::now(),
                        ]);

                        InboundDocumentMovement::create([
                            'inbound_document_id' => $doc->id,
                            'user_id' => $viewer->id,
                            'action' => 'COMPLETED',
                            'from_status' => $doc->status,
                            'to_status' => 'COMPLETED',
                            'comment' => "ឯកឧត្តមអគ្គនាយកបានឯកភាពលើលិខិតឆ្លើយតប" . ($responseNumber ? " លេខ {$responseNumber}" : "") . "។ ឯកសារត្រូវបានបញ្ចប់ជាស្ថាពរ។",
                        ]);

                        $this->telegram->notifyResponseApproved($response, $viewer, $doc);
                    }
                }
            } elseif ($action === 'HOLD') {
                $response->update([
                    'status' => 'UNDER_REVIEW',
                    'current_approver_id' => $viewer->id,
                ]);

                InboundDocumentResponseApproval::create([
                    'response_id' => $response->id,
                    'user_id' => $viewer->id,
                    'action' => 'EDIT',
                    'stage' => $response->current_stage,
                    'comment' => $comment ?: "បានកត់ត្រាមតិយោបល់ និងរក្សាទុកក្នុងដៃសិន",
                    'forwarded_to_id' => $viewer->id,
                ]);
            }
        });

        $successMsg = ($action === 'HOLD') 
            ? 'បានរក្សាទុកមតិយោបល់ និងខ្លឹមសារក្នុងដៃដោយជោគជ័យ! លោកអ្នកអាចចារឆ្លងទៅថ្នាក់លើនៅពេលក្រោយ។' 
            : 'បានអនុវត្តសកម្មភាពពិនិត្យឆ្លងដោយជោគជ័យ!';

        return response()->json([
            'message' => $successMsg,
            'data' => $response->fresh(['draftedByUser', 'currentApprover', 'inboundDocument', 'approvals.user', 'approvals.forwardedTo']),
        ]);
    }

    /**
     * 7. ទាញយកការដាក់ស្នើមកវិញ (Recall Response Draft)
     */
    public function recall($id, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if ($response->drafted_by !== $viewer->id && !$isAdmin) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិទាញយកលិខិតនេះមកវិញឡើយ!'], 403);
        }

        if ($response->status !== 'UNDER_REVIEW') {
            return response()->json(['message' => 'អាចទាញយកមកវិញបានតែលិខិតដែលកំពុងស្ថិតក្នុងការពិនិត្យប៉ុណ្ណោះ!'], 400);
        }

        DB::transaction(function () use ($viewer, $response) {
            $response->update([
                'status' => 'DRAFT',
                'current_approver_id' => $viewer->id,
            ]);

            InboundDocumentResponseApproval::create([
                'response_id' => $response->id,
                'user_id' => $viewer->id,
                'action' => 'RECALL',
                'stage' => 'OFFICE',
                'comment' => 'បានទាញយកសេចក្តីព្រាងមកវិញដើម្បីកែសម្រួលឡើងវិញ',
            ]);
        });

        return response()->json([
            'message' => 'បានទាញយកលិខិតមកវិញដោយជោគជ័យ!',
            'data' => $response->fresh(),
        ]);
    }

    /**
     * 8. លុបសេចក្តីព្រាងលិខិត (Delete Draft)
     */
    public function destroy($id, Request $request)
    {
        $viewer = $request->user();
        $response = InboundDocumentResponse::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        if ((int) $response->drafted_by !== (int) $viewer->id && !$isAdmin) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិលុបលិខិតនេះឡើយ!'], 403);
        }

        // Allow deleting DRAFT or RETURNED (when leaders return it for edit) or by Admin
        $canDeleteStatus = in_array($response->status, ['DRAFT', 'RETURNED']) || $isAdmin;
        if (!$canDeleteStatus) {
            return response()->json(['message' => 'អាចលុបបានតែសេចក្តីព្រាង ឬលិខិតដែលបានបញ្ជូនត្រឡប់មកវិញប៉ុណ្ណោះ!'], 400);
        }

        if ($response->file_path && StorageService::exists($response->file_path)) {
            StorageService::delete($response->file_path);
        }

        $inboundDocId = $response->inbound_document_id;

        $response->delete();

        // If this response was linked to an inbound document, update status if no other active responses exist
        if ($inboundDocId) {
            $remainingCount = InboundDocumentResponse::where('inbound_document_id', $inboundDocId)->count();
            if ($remainingCount === 0) {
                $inboundDoc = \App\Models\InboundDocument::find($inboundDocId);
                if ($inboundDoc && in_array($inboundDoc->status, ['IN_RESPONSE', 'IN_RESPONSE_PROGRESS'])) {
                    $inboundDoc->update(['status' => 'DOING']);
                }
            }
        }

        return response()->json(['message' => 'បានលុបឯកសារចេញដោយជោគជ័យ!']);
    }

    /**
     * 9. Download Attachment File
     */
    public function downloadFile($id, Request $request)
    {
        $viewer = $request->user();
        if (!$viewer) {
            return response()->json(['message' => 'សូមចូលប្រព័ន្ធជាមុនសិន!'], 401);
        }

        $checkQuery = InboundDocumentResponse::where('id', $id);
        $this->scopeAccessibleByViewer($checkQuery, $viewer);
        if (!$checkQuery->exists()) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិទាញយកឯកសារនេះឡើយ!'], 403);
        }

        $response = InboundDocumentResponse::findOrFail($id);
        if (!$response->file_path || !StorageService::exists($response->file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារភ្ជាប់ឡើយ!'], 404);
        }
        return StorageService::download($response->file_path, $response->file_name ?: 'attachment');
    }

    /**
     * 10. View Attachment File (Inline)
     */
    public function viewFile($id, Request $request)
    {
        $viewer = $request->user();
        if (!$viewer) {
            return response()->json(['message' => 'សូមចូលប្រព័ន្ធជាមុនសិន!'], 401);
        }

        $checkQuery = InboundDocumentResponse::where('id', $id);
        $this->scopeAccessibleByViewer($checkQuery, $viewer);
        if (!$checkQuery->exists()) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិមើលឯកសារនេះឡើយ!'], 403);
        }

        $response = InboundDocumentResponse::findOrFail($id);
        if (!$response->file_path || !StorageService::exists($response->file_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារភ្ជាប់ឡើយ!'], 404);
        }
        return StorageService::response($response->file_path, $response->file_name);
    }

    /**
     * ស្វែងរកអក្សរកាត់នាយកដ្ឋានសម្រាប់កន្ទុយលេខឯកសារ (e.g. ន.ប.ធ./ច.ប.ច.)
     */
    public static function getDepartmentAbbreviation($departmentId)
    {
        if (!$departmentId) {
            return 'ន.ក.ទ.';
        }
        $dept = DB::table('departments')->find($departmentId);
        if (!$dept) {
            return 'ន.ក.ទ.';
        }
        $code = strtoupper($dept->code ?? '');
        $name = $dept->name_kh ?? '';

        if ($departmentId == 2 || $code === 'TRC' || str_contains($name, 'ចុះបញ្ជីបរធនបាលកិច្ច')) {
            return 'ច.ប.ច.';
        }
        if ($departmentId == 1 || $code === 'GAD' || str_contains($name, 'កិច្ចការទូទៅ')) {
            return 'ន.ក.ទ.';
        }
        if ($departmentId == 3 || $code === 'LID' || str_contains($name, 'កិច្ចការគតិយុត្ត') || str_contains($name, 'អធិការកិច្ច')) {
            return 'គ.អ.';
        }
        if ($departmentId == 4 || $code === 'TRCD' || str_contains($name, 'ស្រាវជ្រាវ') || str_contains($name, 'សហប្រតិបត្តិការ')) {
            return 'ស.ប.ស.';
        }

        return 'ន.ក.ទ.';
    }

    /**
     * បង្កើតលេខរបាយការណ៍បន្ទាប់សម្រាប់នាយកដ្ឋាន ដោយធានាមិនឱ្យស្ទួនគ្នាដាច់ខាត (Unique per department per year)
     */
    public static function generateNextDepartmentReportNumber($departmentId, $excludeId = null)
    {
        $year = Carbon::now()->year;
        $deptAbbr = self::getDepartmentAbbreviation($departmentId);

        // Lock existing records for this department in current year to prevent concurrency collisions
        $existingNumbers = DB::table('inbound_document_responses')
            ->where('department_id', $departmentId)
            ->whereYear('created_at', $year)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->whereNotNull('response_number')
            ->where('response_number', '!=', '')
            ->lockForUpdate()
            ->pluck('response_number');

        $maxSeq = 0;
        foreach ($existingNumbers as $num) {
            if (preg_match('/^(\d+)/', trim($num), $matches)) {
                $seqVal = (int) $matches[1];
                if ($seqVal > $maxSeq) {
                    $maxSeq = $seqVal;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        $paddedSeq = sprintf('%03d', $nextSeq);
        $fullNumber = "{$paddedSeq} ន.ប.ធ./{$deptAbbr}";

        return [
            'seq' => $paddedSeq,
            'abbr' => $deptAbbr,
            'full_number' => $fullNumber,
        ];
    }

    /**
     * ពិនិត្យមើលថាតើការបញ្ជូននេះ គឺបញ្ជូនផុតពីនាយកដ្ឋានដែរឬទេ
     * (ផុតពីនាយកដ្ឋាន៖ បញ្ជូនទៅឯកឧត្តមអគ្គនាយក ឬអគ្គនាយករង (level <= 2) ឬបញ្ជូនទៅនាយកដ្ឋានផ្សេង)
     */
    protected function isForwardingBeyondDepartment($docDepartmentId, $targetReviewer)
    {
        if (!$targetReviewer) {
            return false;
        }

        $reviewerLevel = $targetReviewer->position ? (int) $targetReviewer->position->level : 99;

        // 1. បញ្ជូនទៅថ្នាក់ដឹកនាំ (អគ្គនាយក ឬអគ្គនាយករង level <= 2)
        if ($reviewerLevel <= 2) {
            return true;
        }

        // 2. បញ្ជូនទៅកាន់នាយកដ្ឋានផ្សេង
        if ($targetReviewer->department_id && $docDepartmentId && (int) $targetReviewer->department_id !== (int) $docDepartmentId) {
            return true;
        }

        return false;
    }

    /**
     * អាប់ដេតលេខរបាយការណ៍ចូលក្នុង Content HTML
     */
    protected function injectNumberIntoContent($content, $seqNumber)
    {
        if (empty($content)) {
            return $content;
        }

        // Replace span placeholder <span class="doc-number-input"...>.......</span>
        $patternSpan = '/(<span class="doc-number-input"[^>]*>)([\s\S]*?)(<\/span>)/i';
        if (preg_match($patternSpan, $content)) {
            return preg_replace($patternSpan, '${1}' . $seqNumber . '${3}', $content);
        }

        // Fallback: លេខ៖ ....... ន.ប.ធ.
        $patternFallback = '/(លេខ៖\s*)([\.\s]+)(\s*ន\.ប\.ធ\.)/u';
        if (preg_match($patternFallback, $content)) {
            return preg_replace($patternFallback, '${1}' . $seqNumber . ' ${3}', $content);
        }

        return $content;
    }

    /**
     * Scope: ឯកសារចេញដែលអាចមើលឃើញដោយអ្នកប្រើប្រាស់ ស្របតាម DocumentWorkflowConfig (View Type)
     */
    protected function scopeAccessibleByViewer($query, User $viewer)
    {
        return $query->accessibleByViewer($viewer);
    }
}
