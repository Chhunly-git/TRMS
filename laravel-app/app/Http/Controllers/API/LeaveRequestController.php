<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestApproval;
use App\Models\User;
use App\Models\Department;
use App\Models\Office;
use App\Models\Position;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * បង្ហាញបញ្ជីសំណើសុំច្បាប់ (My Requests, Pending Approvals, All)
     */
    public function index(Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $scope = $request->get('scope', 'my'); // 'my', 'pending_review', 'all'

        $query = LeaveRequest::with([
            'user:id,name,name_kh,name_en,profile_image,position_id,department_id,office_id,phone',
            'user.position:id,title_kh,title_en,level',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'currentApprover:id,name,name_kh,profile_image,position_id',
            'currentApprover.position:id,title_kh,title_en,level',
            'approvedBy:id,name,name_kh,profile_image,position_id',
            'approvedBy.position:id,title_kh,title_en,level',
            'rejectedBy:id,name,name_kh,profile_image,position_id',
            'approvals.user:id,name,name_kh,profile_image,position_id',
            'approvals.user.position:id,title_kh,title_en,level',
            'approvals.forwardedTo:id,name,name_kh,profile_image,position_id',
        ]);

        if ($scope === 'my') {
            // សំណើផ្ទាល់ខ្លួន
            $query->where('leave_requests.user_id', $viewer->id);
        } elseif ($scope === 'pending_review') {
            // សំណើដែលត្រូវពិនិត្យ និងអនុម័ត
            if ($isAdmin) {
                $query->where('leave_requests.status', 'PENDING');
            } elseif ($viewerPosLevel === 1) {
                // អគ្គនាយក (DG) មើលឃើញសំណើដែលចង្អុលមកខ្លួន ឬសំណើដែលដល់កម្រិត LEADERSHIP
                $query->where('leave_requests.status', 'PENDING')
                      ->where(function ($q) use ($viewer) {
                          $q->where('leave_requests.current_approver_id', $viewer->id)
                            ->orWhere('leave_requests.current_stage', 'LEADERSHIP');
                      });
            } elseif ($viewerPosLevel === 2) {
                // អគ្គនាយករង (DDG) មើលឃើញតែសំណើដែលចង្អុលមកខ្លួនប៉ុណ្ណោះ (មិនបង្ហាញសំណើដែលបានបញ្ជូនទៅអគ្គនាយករួចហើយ)
                $query->where('leave_requests.status', 'PENDING')
                      ->where('leave_requests.current_approver_id', $viewer->id);
            } elseif ($viewerPosLevel <= 4) {
                // ប្រធាននាយកដ្ឋាន
                $query->where('leave_requests.status', 'PENDING')
                      ->where(function ($q) use ($viewer) {
                          $q->where('leave_requests.current_approver_id', $viewer->id)
                            ->orWhere(function ($subQ) use ($viewer) {
                                $subQ->where('leave_requests.department_id', $viewer->department_id)
                                     ->where('leave_requests.current_stage', 'DEPARTMENT');
                            });
                      });
            } elseif ($viewerPosLevel === 5) {
                // អនុប្រធាននាយកដ្ឋាន
                $query->where('leave_requests.status', 'PENDING')
                      ->where('leave_requests.current_approver_id', $viewer->id);
            } elseif ($viewerPosLevel <= 7) {
                // ប្រធានការិយាល័យ
                $query->where('leave_requests.status', 'PENDING')
                      ->where(function ($q) use ($viewer) {
                          $q->where('leave_requests.current_approver_id', $viewer->id)
                            ->orWhere(function ($subQ) use ($viewer) {
                                $subQ->where('leave_requests.office_id', $viewer->office_id)
                                     ->where('leave_requests.current_stage', 'OFFICE');
                            });
                      });
            } elseif ($viewerPosLevel === 8) {
                // អនុប្រធានការិយាល័យ
                $query->where('leave_requests.status', 'PENDING')
                      ->where('leave_requests.current_approver_id', $viewer->id);
            } else {
                $query->whereRaw('1 = 0');
            }

            // មិនបង្ហាញសំណើដែលអ្នកពិនិត្យរូបនេះបានចារ ឬបញ្ជូនបន្តរួចហើយនោះទេ
            if (!$isAdmin) {
                $query->whereDoesntHave('approvals', function ($aQ) use ($viewer) {
                    $aQ->where('user_id', $viewer->id)
                       ->whereIn('action', ['FORWARDED', 'APPROVED', 'REJECTED']);
                });
            }
        } else {
            // All scope (សម្របតាមសិទ្ធិ)
            if (!$isAdmin) {
                if ($viewerPosLevel > 2) {
                    $query->where('leave_requests.department_id', $viewer->department_id);
                    if ($viewerPosLevel > 5) {
                        $query->where('leave_requests.office_id', $viewer->office_id);
                    }
                }
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('leave_requests.status', strtoupper($request->status));
        }
        if ($request->filled('leave_type')) {
            $query->where('leave_requests.leave_type', strtoupper($request->leave_type));
        }
        if ($request->filled('department_id')) {
            $query->where('leave_requests.department_id', $request->department_id);
        }
        if ($request->filled('office_id')) {
            $query->where('leave_requests.office_id', $request->office_id);
        }
        if ($request->filled('user_id')) {
            $query->where('leave_requests.user_id', $request->user_id);
        }
        if ($request->filled('year')) {
            $query->whereYear('leave_requests.start_date', (int)$request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('leave_requests.start_date', (int)$request->month);
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('leave_requests.request_number', 'like', "%{$search}%")
                  ->orWhere('leave_requests.reason', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uQ) use ($search) {
                      $uQ->where('name_kh', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy('leave_requests.created_at', 'desc')
                          ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $requests->items(),
            'current_page' => $requests->currentPage(),
            'last_page' => $requests->lastPage(),
            'total' => $requests->total(),
            'per_page' => $requests->perPage(),
        ]);
    }

    /**
     * ស្ថិតិសង្ខេបពាក្យសុំច្បាប់ (Stats)
     */
    public function stats(Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $currentYear = Carbon::now()->year;

        // ស្ថិតិផ្ទាល់ខ្លួន
        $myRequests = LeaveRequest::where('user_id', $viewer->id);
        $myTotal = (clone $myRequests)->count();
        $myDraft = (clone $myRequests)->where('status', 'DRAFT')->count();
        $myPending = (clone $myRequests)->where('status', 'PENDING')->count();
        $myApproved = (clone $myRequests)->where('status', 'APPROVED')->count();
        $myRejected = (clone $myRequests)->where('status', 'REJECTED')->count();

        // ចំនួនថ្ងៃច្បាប់ប្រចាំឆ្នាំដែលបានប្រើក្នុងឆ្នាំនេះ
        $annualDaysUsed = (clone $myRequests)
            ->where('leave_type', 'ANNUAL')
            ->where('status', 'APPROVED')
            ->whereYear('start_date', $currentYear)
            ->sum('duration_days');

        // ចំនួនសំណើដែលត្រូវពិនិត្យ (Pending Review Count)
        // ត្រូវស៊ីសង្វាក់គ្នា ១០០% ជាមួយ scope === 'pending_review' ក្នុង index()
        $pendingReviewsQuery = LeaveRequest::where('leave_requests.status', 'PENDING');
        if ($isAdmin) {
            // Admin មើលឃើញទាំងអស់
        } elseif ($viewerPosLevel === 1) {
            // អគ្គនាយក (DG)
            $pendingReviewsQuery->where(function ($q) use ($viewer) {
                $q->where('leave_requests.current_approver_id', $viewer->id)
                  ->orWhere('leave_requests.current_stage', 'LEADERSHIP');
            });
        } elseif ($viewerPosLevel === 2) {
            // អគ្គនាយករង (DDG) មើលឃើញតែសំណើដែលចង្អុលមកខ្លួនប៉ុណ្ណោះ
            $pendingReviewsQuery->where('leave_requests.current_approver_id', $viewer->id);
        } elseif ($viewerPosLevel <= 4) {
            // ប្រធាននាយកដ្ឋាន
            $pendingReviewsQuery->where(function ($q) use ($viewer) {
                $q->where('leave_requests.current_approver_id', $viewer->id)
                  ->orWhere(function ($subQ) use ($viewer) {
                      $subQ->where('leave_requests.department_id', $viewer->department_id)
                           ->where('leave_requests.current_stage', 'DEPARTMENT');
                  });
            });
        } elseif ($viewerPosLevel === 5) {
            // អនុប្រធាននាយកដ្ឋាន
            $pendingReviewsQuery->where('leave_requests.current_approver_id', $viewer->id);
        } elseif ($viewerPosLevel <= 7) {
            // ប្រធានការិយាល័យ
            $pendingReviewsQuery->where(function ($q) use ($viewer) {
                $q->where('leave_requests.current_approver_id', $viewer->id)
                  ->orWhere(function ($subQ) use ($viewer) {
                      $subQ->where('leave_requests.office_id', $viewer->office_id)
                           ->where('leave_requests.current_stage', 'OFFICE');
                  });
            });
        } elseif ($viewerPosLevel === 8) {
            // អនុប្រធានការិយាល័យ
            $pendingReviewsQuery->where('leave_requests.current_approver_id', $viewer->id);
        } else {
            $pendingReviewsQuery->whereRaw('1 = 0');
        }

        // មិនរាប់បញ្ចូលសំណើដែលអ្នកពិនិត្យរូបនេះបានចារ ឬបញ្ជូនបន្តរួចហើយ
        if (!$isAdmin) {
            $pendingReviewsQuery->whereDoesntHave('approvals', function ($aQ) use ($viewer) {
                $aQ->where('user_id', $viewer->id)
                   ->whereIn('action', ['FORWARDED', 'APPROVED', 'REJECTED']);
            });
        }
        $pendingReviewsCount = $pendingReviewsQuery->count();

        // ៣. ស្ថិតិនៃការពិនិត្យកន្លងមករបស់អ្នកប្រើប្រាស់ (Review Stats)
        $myApprovals = LeaveRequestApproval::where('user_id', $viewer->id);
        $reviewForwarded = (clone $myApprovals)->where('action', 'FORWARDED')->distinct('leave_request_id')->count('leave_request_id');
        $reviewApproved = (clone $myApprovals)->where('action', 'APPROVED')->distinct('leave_request_id')->count('leave_request_id');
        $reviewRejected = (clone $myApprovals)->whereIn('action', ['REJECTED', 'RETURNED'])->distinct('leave_request_id')->count('leave_request_id');

        // ៤. ស្ថិតិរួមតាមដែនសមត្ថកិច្ច (All Stats)
        $allScopeQuery = LeaveRequest::query();
        if (!$isAdmin) {
            if ($viewerPosLevel > 2) {
                $allScopeQuery->where('department_id', $viewer->department_id);
                if ($viewerPosLevel > 5) {
                    $allScopeQuery->where('office_id', $viewer->office_id);
                }
            }
        }
        $allTotal = (clone $allScopeQuery)->count();
        $allPending = (clone $allScopeQuery)->where('status', 'PENDING')->count();
        $allApproved = (clone $allScopeQuery)->where('status', 'APPROVED')->count();
        $allRejected = (clone $allScopeQuery)->where('status', 'REJECTED')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'my_stats' => [
                    'total' => $myTotal,
                    'draft' => $myDraft,
                    'pending' => $myPending,
                    'approved' => $myApproved,
                    'rejected' => $myRejected,
                    'annual_days_used' => (float)$annualDaysUsed,
                ],
                'review_stats' => [
                    'pending' => $pendingReviewsCount,
                    'forwarded' => $reviewForwarded,
                    'approved' => $reviewApproved,
                    'rejected' => $reviewRejected,
                ],
                'all_stats' => [
                    'total' => $allTotal,
                    'pending' => $allPending,
                    'approved' => $allApproved,
                    'rejected' => $allRejected,
                ],
                'pending_reviews_count' => $pendingReviewsCount,
                'can_review' => ($isAdmin || $viewerPosLevel <= 8),
                'is_dg' => ($viewerPosLevel === 1),
                'can_final_approve' => ($isAdmin || $viewerPosLevel === 1),
            ]
        ]);
    }

    /**
     * ទាញយកបញ្ជីថ្នាក់ដឹកនាំដែលមានសិទ្ធិទទួលពិនិត្យបន្ត ព្រមទាំងពិនិត្យមើលថាតើគាត់កំពុងឈប់សម្រាកដែរឬទេ
     * (Eligible Next Approver Candidates with On-Leave Status & Skip Support)
     */
    public function getNextApproverCandidates(Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $requestId = $request->get('request_id');
        $targetUserId = $request->get('user_id', $viewer->id);

        $startDate = $request->get('start_date', Carbon::today()->toDateString());
        $endDate = $request->get('end_date', $startDate);

        $applicant = null;
        $currentStage = 'OFFICE';
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $effectiveReviewerLevel = $viewer->position ? (int)$viewer->position->level : 99;

        if ($requestId) {
            $lr = LeaveRequest::with('user.position', 'currentApprover.position', 'department', 'office')->findOrFail($requestId);
            $applicant = $lr->user;
            $currentStage = $lr->current_stage;
            $startDate = $lr->start_date->toDateString();
            $endDate = $lr->end_date->toDateString();
            $targetOfficeId = $lr->office_id ?? $applicant?->office_id;
            $targetDeptId = $lr->department_id ?? $applicant?->department_id;

            if ($isAdmin || $effectiveReviewerLevel > 8) {
                if ($lr->currentApprover?->position) {
                    $effectiveReviewerLevel = (int)$lr->currentApprover->position->level;
                } elseif ($lr->current_stage === 'LEADERSHIP') {
                    $effectiveReviewerLevel = 2;
                } elseif ($lr->current_stage === 'DEPARTMENT') {
                    $effectiveReviewerLevel = 4;
                } else {
                    $effectiveReviewerLevel = 7;
                }
            }
        } else {
            $applicant = User::with('position', 'department', 'office')->find($targetUserId) ?? $viewer;
            $targetOfficeId = $applicant->office_id;
            $targetDeptId = $applicant->department_id;
        }

        $applicantPosLevel = $applicant->position ? (int)$applicant->position->level : 99;
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;

        // កំណត់កម្រិតថ្នាក់ដឹកនាំដែលត្រូវស្វែងរក
        // កម្រិតទី ១ (Immediate Candidates) និង កម្រិតបន្ទាប់ (Next Tier Candidates - សម្រាប់រំលង)
        $candidates = collect();
        $nextTierCandidates = collect();

        // ប្រសិនបើជាអ្នកដាក់ពាក្យដំបូង (Officer Level 9+)
        if (!$requestId || $viewer->id === $applicant->id) {
            if ($targetOfficeId) {
                // ថ្នាក់ដឹកនាំការិយាល័យ (អនុប្រធានការិយាល័យ Level 8, ប្រធានការិយាល័យ Level 6-7)
                $candidates = User::where('office_id', $targetOfficeId)
                    ->where('id', '!=', $applicant->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [6, 7, 8]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();

                // Next Tier: ថ្នាក់ដឹកនាំនាយកដ្ឋាន (សម្រាប់រំលង បើប្រធានការិយាល័យឈប់)
                if ($targetDeptId) {
                    $nextTierCandidates = User::where('department_id', $targetDeptId)
                        ->where('id', '!=', $applicant->id)
                        ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                        ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                        ->get();
                }
            } elseif ($targetDeptId) {
                // គ្មានការិយាល័យ -> ថ្នាក់ដឹកនាំនាយកដ្ឋានផ្ទាល់
                $candidates = User::where('department_id', $targetDeptId)
                    ->where('id', '!=', $applicant->id)
                    ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                    ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                    ->get();

                // Next Tier: អគ្គនាយកដ្ឋាន
                $nextTierCandidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $applicant->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            } else {
                // គ្មាននាយកដ្ឋាន -> អគ្គនាយកដ្ឋាន
                $candidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $applicant->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            }
        } else {
            // កំពុងឆ្លងពិនិត្យតាមដំណាក់កាល
            if ($effectiveReviewerLevel === 8) {
                // អនុប្រធានការិយាល័យ -> បញ្ជូនទៅប្រធានការិយាល័យ
                if ($targetOfficeId) {
                    $candidates = User::where('office_id', $targetOfficeId)
                        ->where('id', '!=', $viewer->id)
                        ->whereHas('position', fn($q) => $q->whereIn('level', [6, 7]))
                        ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                        ->get();
                }
                // Next Tier: ថ្នាក់ដឹកនាំនាយកដ្ឋាន
                if ($targetDeptId) {
                    $nextTierCandidates = User::where('department_id', $targetDeptId)
                        ->where('id', '!=', $viewer->id)
                        ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                        ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                        ->get();
                }
            } elseif ($effectiveReviewerLevel <= 7 && $effectiveReviewerLevel >= 6) {
                // ប្រធានការិយាល័យ -> បញ្ជូនទៅថ្នាក់ដឹកនាំនាយកដ្ឋាន (អនុប្រធាននាយកដ្ឋាន Level 5, ប្រធាននាយកដ្ឋាន Level 3-4)
                if ($targetDeptId) {
                    $candidates = User::where('department_id', $targetDeptId)
                        ->where('id', '!=', $viewer->id)
                        ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4, 5]))
                        ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                        ->get();
                }
                // Next Tier: ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន
                $nextTierCandidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            } elseif ($effectiveReviewerLevel === 5) {
                // អនុប្រធាននាយកដ្ឋាន -> បញ្ជូនទៅប្រធាននាយកដ្ឋាន
                if ($targetDeptId) {
                    $candidates = User::where('department_id', $targetDeptId)
                        ->where('id', '!=', $viewer->id)
                        ->whereHas('position', fn($q) => $q->whereIn('level', [3, 4]))
                        ->with('position:id,title_kh,title_en,level', 'office:id,name_kh', 'department:id,name_kh')
                        ->get();
                }
                // Next Tier: ថ្នាក់ដឹកនាំអគ្គនាយកដ្ឋាន
                $nextTierCandidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            } elseif ($effectiveReviewerLevel <= 4 && $effectiveReviewerLevel >= 3) {
                // ប្រធាននាយកដ្ឋាន -> បញ្ជូនទៅអគ្គនាយករង (Level 2) ឬ អគ្គនាយក (Level 1)
                $candidates = User::whereHas('position', fn($q) => $q->whereIn('level', [1, 2]))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            } elseif ($effectiveReviewerLevel === 2) {
                // អគ្គនាយករង -> បញ្ជូនទៅអគ្គនាយក (Level 1)
                $candidates = User::whereHas('position', fn($q) => $q->where('level', 1))
                    ->where('id', '!=', $viewer->id)
                    ->with('position:id,title_kh,title_en,level')
                    ->get();
            }
        }

        // មុខងារត្រួតពិនិត្យថាតើថ្នាក់ដឹកនាំម្នាក់ៗកំពុងឈប់សម្រាកដែរឬទេ (On-Leave Check)
        $checkLeaveStatus = function ($userList) use ($startDate, $endDate) {
            return $userList->map(function ($u) use ($startDate, $endDate) {
                // ១. ពិនិត្យក្នុងច្បាប់ឈប់សម្រាកដែលបាន APPROVED
                $hasApprovedLeave = LeaveRequest::where('user_id', $u->id)
                    ->where('status', 'APPROVED')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function ($subQ) use ($startDate, $endDate) {
                              $subQ->where('start_date', '<=', $startDate)
                                   ->where('end_date', '>=', $endDate);
                          });
                    })
                    ->first();

                // ២. ពិនិត្យក្នុងតារាងវត្តមានប្រចាំថ្ងៃ
                $hasAttendanceLeave = Attendance::where('user_id', $u->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereIn('status', ['PERMISSION', 'MISSION'])
                    ->first();

                $isOnLeave = ($hasApprovedLeave !== null || $hasAttendanceLeave !== null);
                $leaveNote = null;
                if ($hasApprovedLeave) {
                    $leaveNote = "កំពុងឈប់សម្រាក ({$hasApprovedLeave->leave_type_kh})";
                } elseif ($hasAttendanceLeave) {
                    $leaveNote = ($hasAttendanceLeave->status === 'MISSION') ? 'កំពុងបំពេញបេសកកម្ម' : 'ច្បាប់ឈប់សម្រាក';
                }

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'name_kh' => $u->name_kh ?? $u->name,
                    'position_title' => $u->position?->title_kh ?? 'មន្ត្រី',
                    'position_level' => $u->position?->level ?? 99,
                    'office_name' => $u->office?->name_kh,
                    'department_name' => $u->department?->name_kh,
                    'profile_image' => $u->profile_image,
                    'is_on_leave' => $isOnLeave,
                    'leave_note' => $leaveNote,
                ];
            })->sortBy('position_level')->values();
        };

        $formattedCandidates = $checkLeaveStatus($candidates);
        $formattedNextTier = $checkLeaveStatus($nextTierCandidates);

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_stage' => $currentStage,
                'candidates' => $formattedCandidates,
                'next_tier_candidates' => $formattedNextTier,
                'can_skip_to_next_tier' => $formattedNextTier->count() > 0,
            ]
        ]);
    }

    /**
     * បង្កើតសំណើសុំច្បាប់ថ្មី (Store Leave Request)
     */
    public function store(Request $request)
    {
        $user = $request->user()->loadMissing('position', 'department', 'office');

        $request->validate([
            'leave_type' => 'required|in:ANNUAL,SHORT_TERM,MATERNITY,SICK,PERSONAL',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'resume_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|in:FULL_DAY,MORNING,AFTERNOON',
            'duration_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string|max:2000',
            'contact_phone' => 'nullable|string|max:50',
            'attachment' => 'nullable|file|max:20480', // 20MB Max
            'submit_now' => 'nullable|boolean',
            'next_approver_id' => 'nullable|exists:users,id',
        ], [
            'leave_type.required' => 'សូមជ្រើសរើសប្រភេទច្បាប់ឈប់សម្រាក',
            'start_date.required' => 'សូមជ្រើសរើសកាលបរិច្ឆេទចាប់ផ្តើម',
            'end_date.required' => 'សូមជ្រើសរើសកាលបរិច្ឆេទបញ្ចប់',
            'resume_date.required' => 'សូមបញ្ជាក់កាលបរិច្ឆេទចូលធ្វើការវិញ',
            'reason.required' => 'សូមបញ្ចូលមូលហេតុនៃការសុំច្បាប់',
            'duration_days.required' => 'ចំនួនថ្ងៃសរុបមិនត្រឹមត្រូវ',
        ]);

        $year = Carbon::parse($request->start_date)->year;
        $countThisYear = LeaveRequest::whereYear('created_at', $year)->count() + 1;
        $requestNumber = sprintf("LR-%d-%04d", $year, $countThisYear);

        // បើមាន file ភ្ជាប់មកជាមួយ
        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('leave_requests', 'public');
        }

        $submitNow = $request->boolean('submit_now');
        $status = $submitNow ? 'PENDING' : 'DRAFT';
        $currentApproverId = $submitNow ? $request->next_approver_id : null;

        // កំណត់ Stage
        $stage = 'OFFICE';
        if ($currentApproverId) {
            $approver = User::with('position')->find($currentApproverId);
            $appLevel = $approver?->position?->level ?? 99;
            if ($appLevel <= 2) {
                $stage = 'LEADERSHIP';
            } elseif ($appLevel <= 5) {
                $stage = 'DEPARTMENT';
            } else {
                $stage = 'OFFICE';
            }
        }

        $leaveRequest = LeaveRequest::create([
            'request_number' => $requestNumber,
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'office_id' => $user->office_id,
            'position_id' => $user->position_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'resume_date' => $request->resume_date,
            'is_half_day' => $request->boolean('is_half_day', false),
            'half_day_type' => $request->get('half_day_type', 'FULL_DAY'),
            'duration_days' => $request->duration_days,
            'reason' => $request->reason,
            'contact_phone' => $request->contact_phone,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'current_approver_id' => $currentApproverId,
            'current_stage' => $stage,
            'status' => $status,
            'submitted_at' => $submitNow ? Carbon::now() : null,
        ]);

        if ($submitNow) {
            LeaveRequestApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'user_id' => $user->id,
                'position_id' => $user->position_id,
                'action' => 'SUBMITTED',
                'remarks' => 'បានដាក់ពាក្យស្នើសុំច្បាប់ឈប់សម្រាក',
                'forwarded_to_id' => $currentApproverId,
            ]);
        }

        $leaveRequest->load([
            'user:id,name,name_kh,name_en,profile_image',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'currentApprover:id,name,name_kh,position_id',
            'approvals.user:id,name,name_kh,position_id',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $submitNow ? 'បានដាក់ពាក្យស្នើសុំច្បាប់ដោយជោគជ័យ' : 'បានរក្សាទុកសេចក្តីព្រាងពាក្យសុំច្បាប់ដោយជោគជ័យ',
            'data' => $leaveRequest,
        ], 201);
    }

    /**
     * មើលព័ត៌មានលម្អិតនៃសំណើ (Show Details)
     */
    public function show($id, Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');

        $leaveRequest = LeaveRequest::with([
            'user:id,name,name_kh,name_en,profile_image,position_id,department_id,office_id,phone,employee_code',
            'user.position:id,title_kh,title_en,level',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'currentApprover:id,name,name_kh,profile_image,position_id',
            'currentApprover.position:id,title_kh,title_en,level',
            'approvedBy:id,name,name_kh,profile_image,position_id',
            'approvedBy.position:id,title_kh,title_en,level',
            'rejectedBy:id,name,name_kh,profile_image,position_id',
            'approvals.user:id,name,name_kh,profile_image,position_id',
            'approvals.user.position:id,title_kh,title_en,level',
            'approvals.forwardedTo:id,name,name_kh,profile_image,position_id',
            'approvals.forwardedTo.position:id,title_kh,title_en,level',
        ])->findOrFail($id);

        $isOwner = ($leaveRequest->user_id === $viewer->id);
        $isCurrentApprover = ($leaveRequest->current_approver_id === $viewer->id);

        // ពិនិត្យសិទ្ធិអាច Review
        $canReview = false;
        if ($leaveRequest->status === 'PENDING') {
            if ($isCurrentApprover || $isAdmin) {
                $canReview = true;
            } elseif ($viewerPosLevel === 1 && $leaveRequest->current_stage === 'LEADERSHIP') {
                $canReview = true;
            } elseif ($viewerPosLevel <= 4 && $leaveRequest->current_stage === 'DEPARTMENT' && $viewer->department_id === $leaveRequest->department_id) {
                $canReview = true;
            } elseif ($viewerPosLevel <= 7 && $leaveRequest->current_stage === 'OFFICE' && $viewer->office_id === $leaveRequest->office_id) {
                $canReview = true;
            }

            if (!$isAdmin) {
                $hasActed = $leaveRequest->approvals()->where('user_id', $viewer->id)->whereIn('action', ['FORWARDED', 'APPROVED', 'REJECTED'])->exists();
                if ($hasActed) {
                    $canReview = false;
                }
            }
        }

        // សិទ្ធិអាច Final Approve (DG Level 1 ឬ Admin)
        $canFinalApprove = ($isAdmin || $viewerPosLevel === 1);

        return response()->json([
            'status' => 'success',
            'data' => $leaveRequest,
            'permissions' => [
                'can_edit' => ($isOwner && in_array($leaveRequest->status, ['DRAFT'])) || $isAdmin,
                'can_cancel' => ($isOwner && $leaveRequest->status === 'PENDING') || $isAdmin,
                'can_review' => $canReview,
                'can_final_approve' => $canFinalApprove,
                'is_owner' => $isOwner,
            ]
        ]);
    }

    /**
     * កែសម្រួលពាក្យសុំច្បាប់ (Update Draft)
     */
    public function update($id, Request $request)
    {
        $viewer = $request->user();
        $leaveRequest = LeaveRequest::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isOwner = ($leaveRequest->user_id === $viewer->id);

        if (!$isAdmin && (!$isOwner || $leaveRequest->status !== 'DRAFT')) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកអាចកែសម្រួលបានតែពាក្យសុំច្បាប់ដែលស្ថិតនៅជាសេចក្តីព្រាង (DRAFT) ប៉ុណ្ណោះ។'
            ], 403);
        }

        $request->validate([
            'leave_type' => 'required|in:ANNUAL,SHORT_TERM,MATERNITY,SICK,PERSONAL',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'resume_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => 'nullable|in:FULL_DAY,MORNING,AFTERNOON',
            'duration_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string|max:2000',
            'contact_phone' => 'nullable|string|max:50',
            'attachment' => 'nullable|file|max:20480',
            'submit_now' => 'nullable|boolean',
            'next_approver_id' => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('attachment')) {
            if ($leaveRequest->attachment_path && Storage::disk('public')->exists($leaveRequest->attachment_path)) {
                Storage::disk('public')->delete($leaveRequest->attachment_path);
            }
            $file = $request->file('attachment');
            $leaveRequest->attachment_name = $file->getClientOriginalName();
            $leaveRequest->attachment_path = $file->store('leave_requests', 'public');
        } elseif ($request->boolean('remove_attachment')) {
            if ($leaveRequest->attachment_path && Storage::disk('public')->exists($leaveRequest->attachment_path)) {
                Storage::disk('public')->delete($leaveRequest->attachment_path);
            }
            $leaveRequest->attachment_name = null;
            $leaveRequest->attachment_path = null;
        }

        $leaveRequest->leave_type = $request->leave_type;
        $leaveRequest->start_date = $request->start_date;
        $leaveRequest->end_date = $request->end_date;
        $leaveRequest->resume_date = $request->resume_date;
        $leaveRequest->is_half_day = $request->boolean('is_half_day', false);
        $leaveRequest->half_day_type = $request->get('half_day_type', 'FULL_DAY');
        $leaveRequest->duration_days = $request->duration_days;
        $leaveRequest->reason = $request->reason;
        $leaveRequest->contact_phone = $request->contact_phone;

        if ($request->boolean('submit_now')) {
            $leaveRequest->status = 'PENDING';
            $leaveRequest->submitted_at = Carbon::now();
            $leaveRequest->current_approver_id = $request->next_approver_id;

            // Compute stage
            $approver = User::with('position')->find($request->next_approver_id);
            $appLevel = $approver?->position?->level ?? 99;
            if ($appLevel <= 2) {
                $leaveRequest->current_stage = 'LEADERSHIP';
            } elseif ($appLevel <= 5) {
                $leaveRequest->current_stage = 'DEPARTMENT';
            } else {
                $leaveRequest->current_stage = 'OFFICE';
            }

            LeaveRequestApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'user_id' => $viewer->id,
                'position_id' => $viewer->position_id,
                'action' => 'SUBMITTED',
                'remarks' => 'បានដាក់ពាក្យស្នើសុំច្បាប់ឈប់សម្រាក',
                'forwarded_to_id' => $request->next_approver_id,
            ]);
        }

        $leaveRequest->save();

        return response()->json([
            'status' => 'success',
            'message' => 'បានកែសម្រួលពាក្យសុំច្បាប់ដោយជោគជ័យ',
            'data' => $leaveRequest,
        ]);
    }

    /**
     * ដាក់ជូនពាក្យសុំច្បាប់ (Submit Draft Request)
     */
    public function submit($id, Request $request)
    {
        $viewer = $request->user();
        $leaveRequest = LeaveRequest::where('user_id', $viewer->id)->findOrFail($id);

        if ($leaveRequest->status !== 'DRAFT') {
            return response()->json([
                'status' => 'error',
                'message' => 'ពាក្យសុំច្បាប់នេះត្រូវបានដាក់ជូនរួចរាល់ហើយ។'
            ], 422);
        }

        $request->validate([
            'next_approver_id' => 'required|exists:users,id',
        ], [
            'next_approver_id.required' => 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដែលត្រូវដាក់ជូនពិនិត្យ',
        ]);

        $approver = User::with('position')->findOrFail($request->next_approver_id);
        $appLevel = $approver->position?->level ?? 99;
        $stage = ($appLevel <= 2) ? 'LEADERSHIP' : (($appLevel <= 5) ? 'DEPARTMENT' : 'OFFICE');

        $leaveRequest->status = 'PENDING';
        $leaveRequest->submitted_at = Carbon::now();
        $leaveRequest->current_approver_id = $approver->id;
        $leaveRequest->current_stage = $stage;
        $leaveRequest->save();

        LeaveRequestApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $viewer->id,
            'position_id' => $viewer->position_id,
            'action' => 'SUBMITTED',
            'remarks' => 'បានដាក់ពាក្យស្នើសុំច្បាប់ឈប់សម្រាក',
            'forwarded_to_id' => $approver->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'បានដាក់ជូនពាក្យសុំច្បាប់ដោយជោគជ័យ',
            'data' => $leaveRequest,
        ]);
    }

    /**
     * ថ្នាក់ដឹកនាំធ្វើចំណារ និងដំណើរការពិនិត្យ (Action: FORWARD, APPROVE, REJECT, RETURN)
     */
    public function processAction($id, Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');

        $leaveRequest = LeaveRequest::with('user')->findOrFail($id);

        if ($leaveRequest->status !== 'PENDING') {
            return response()->json([
                'status' => 'error',
                'message' => 'សំណើនេះមិនស្ថិតក្នុងស្ថានភាពរង់ចាំការពិនិត្យឡើយ។'
            ], 422);
        }

        // ផ្ទៀងផ្ទាត់សិទ្ធិ
        $isCurrentApprover = ($leaveRequest->current_approver_id === $viewer->id);
        $isAuthorizedOverride = false;

        if ($isAdmin) {
            $isAuthorizedOverride = true;
        } elseif ($viewerPosLevel <= 2) {
            $isAuthorizedOverride = true; // DG/DDG always has review authority
        } elseif ($viewerPosLevel <= 4 && $leaveRequest->department_id === $viewer->department_id) {
            $isAuthorizedOverride = true; // Dept Director can review within department
        } elseif ($viewerPosLevel <= 7 && $leaveRequest->office_id === $viewer->office_id) {
            $isAuthorizedOverride = true; // Office Chief can review within office
        }

        if (!$isCurrentApprover && !$isAuthorizedOverride) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកគ្មានសិទ្ធិពិនិត្យ ឬចារលើសំណើនេះឡើយ។'
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:FORWARD,APPROVE,REJECT,RETURN',
            'remarks' => 'nullable|string|max:2000',
            'next_approver_id' => 'required_if:action,FORWARD|nullable|exists:users,id',
            'rejection_reason' => 'required_if:action,REJECT|nullable|string|max:2000',
        ], [
            'action.required' => 'សូមបញ្ជាក់សកម្មភាពដែលត្រូវអនុវត្ត',
            'next_approver_id.required_if' => 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដែលត្រូវបញ្ជូនបន្តទៅ',
            'rejection_reason.required_if' => 'សូមបញ្ជាក់មូលហេតុនៃការបដិសេធ',
        ]);

        $action = $request->action;
        $remarks = $request->remarks ?? '';

        if ($action === 'APPROVE' && $viewerPosLevel !== 1 && !$isAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'មានតែឯកឧត្តមអគ្គនាយកប៉ុណ្ណោះដែលមានសិទ្ធិឯកភាព ឬអនុម័តជាផ្លូវការ។'
            ], 403);
        }

        DB::beginTransaction();
        try {
            if ($action === 'FORWARD') {
                $nextApprover = User::with('position')->findOrFail($request->next_approver_id);
                $nextLevel = $nextApprover->position?->level ?? 99;

                $nextStage = 'OFFICE';
                if ($nextLevel <= 2) {
                    $nextStage = 'LEADERSHIP';
                } elseif ($nextLevel <= 5) {
                    $nextStage = 'DEPARTMENT';
                }

                $leaveRequest->current_approver_id = $nextApprover->id;
                $leaveRequest->current_stage = $nextStage;
                $leaveRequest->save();

                LeaveRequestApproval::create([
                    'leave_request_id' => $leaveRequest->id,
                    'user_id' => $viewer->id,
                    'position_id' => $viewer->position_id,
                    'action' => 'FORWARDED',
                    'remarks' => $remarks ?: 'បានពិនិត្យ និងគោរពជូនបន្ត',
                    'forwarded_to_id' => $nextApprover->id,
                ]);

                $message = 'បានចារមតិ និងបញ្ជូនបន្តដោយជោគជ័យ';

            } elseif ($action === 'APPROVE') {
                $leaveRequest->status = 'APPROVED';
                $leaveRequest->approved_at = Carbon::now();
                $leaveRequest->approved_by = $viewer->id;
                $leaveRequest->current_approver_id = null;
                $leaveRequest->current_stage = 'COMPLETED';
                $leaveRequest->save();

                LeaveRequestApproval::create([
                    'leave_request_id' => $leaveRequest->id,
                    'user_id' => $viewer->id,
                    'position_id' => $viewer->position_id,
                    'action' => 'APPROVED',
                    'remarks' => $remarks ?: 'បានឯកភាព និងអនុម័តជាផ្លូវការ',
                    'forwarded_to_id' => null,
                ]);

                // 🟢 ការធ្វើសមកាលកម្មវត្តមានស្វ័យប្រវត្តិ (Attendance Integration)
                $startDate = Carbon::parse($leaveRequest->start_date);
                $endDate = Carbon::parse($leaveRequest->end_date);
                $period = CarbonPeriod::create($startDate, $endDate);

                $halfDaySuffix = '';
                if ($leaveRequest->is_half_day) {
                    $halfDaySuffix = ($leaveRequest->half_day_type === 'MORNING') ? ' (កន្លះថ្ងៃ - ព្រឹក)' : ' (កន្លះថ្ងៃ - រសៀល)';
                }

                $note = "ច្បាប់ឈប់សម្រាក៖ {$leaveRequest->leave_type_kh}{$halfDaySuffix} - មូលហេតុ៖ {$leaveRequest->reason}";

                foreach ($period as $date) {
                    // ប្រសិនបើជាច្បាប់ទូទៅ រំលងថ្ងៃសៅរ៍ និងអាទិត្យ
                    if ($leaveRequest->leave_type !== 'MATERNITY' && in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                        continue;
                    }

                    $dateStr = $date->toDateString();
                    Attendance::updateOrCreate(
                        [
                            'user_id' => $leaveRequest->user_id,
                            'date' => $dateStr,
                        ],
                        [
                            'status' => 'PERMISSION',
                            'note' => $note,
                        ]
                    );
                }

                $message = 'បានឯកភាព និងអនុម័តច្បាប់ឈប់សម្រាកជាផ្លូវការ រួចកត់ត្រាចូលវត្តមានដោយស្វ័យប្រវត្តិ';

            } elseif ($action === 'REJECT') {
                $rejectionReason = $request->rejection_reason ?: $remarks;

                $leaveRequest->status = 'REJECTED';
                $leaveRequest->rejected_at = Carbon::now();
                $leaveRequest->rejected_by = $viewer->id;
                $leaveRequest->rejection_reason = $rejectionReason;
                $leaveRequest->current_approver_id = null;
                $leaveRequest->save();

                LeaveRequestApproval::create([
                    'leave_request_id' => $leaveRequest->id,
                    'user_id' => $viewer->id,
                    'position_id' => $viewer->position_id,
                    'action' => 'REJECTED',
                    'remarks' => $rejectionReason,
                    'forwarded_to_id' => null,
                ]);

                $message = 'បានបដិសេធពាក្យសុំច្បាប់នេះ';

            } elseif ($action === 'RETURN') {
                $leaveRequest->status = 'DRAFT';
                $leaveRequest->current_approver_id = null;
                $leaveRequest->save();

                LeaveRequestApproval::create([
                    'leave_request_id' => $leaveRequest->id,
                    'user_id' => $viewer->id,
                    'position_id' => $viewer->position_id,
                    'action' => 'RETURNED',
                    'remarks' => $remarks ?: 'បានប្រគល់ត្រឡប់ជូនសាមីខ្លួនកែសម្រួលឡើងវិញ',
                    'forwarded_to_id' => $leaveRequest->user_id,
                ]);

                $message = 'បានប្រគល់សំណើត្រឡប់ជូនមន្ត្រីកែសម្រួលឡើងវិញ';
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $leaveRequest->fresh([
                    'user',
                    'approvals.user',
                    'approvals.forwardedTo',
                    'approvedBy',
                    'rejectedBy'
                ]),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'មានបញ្ហាក្នុងការដំណើរការ៖ ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * បោះបង់ពាក្យសុំច្បាប់ដោយមន្ត្រីសាមី (Cancel Request)
     */
    public function cancel($id, Request $request)
    {
        $viewer = $request->user();
        $leaveRequest = LeaveRequest::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isOwner = ($leaveRequest->user_id === $viewer->id);

        if (!$isAdmin && (!$isOwner || $leaveRequest->status !== 'PENDING')) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកអាចបោះបង់បានតែពាក្យសុំច្បាប់ដែលកំពុងរង់ចាំពិនិត្យ (PENDING) ប៉ុណ្ណោះ។'
            ], 403);
        }

        $leaveRequest->status = 'CANCELLED';
        $leaveRequest->current_approver_id = null;
        $leaveRequest->save();

        LeaveRequestApproval::create([
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $viewer->id,
            'position_id' => $viewer->position_id,
            'action' => 'CANCELLED',
            'remarks' => 'បានបោះបង់ពាក្យសុំច្បាប់នេះ',
            'forwarded_to_id' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'បានបោះបង់ពាក្យសុំច្បាប់ដោយជោគជ័យ',
            'data' => $leaveRequest,
        ]);
    }

    /**
     * លុបពាក្យសុំច្បាប់ (Destroy Draft)
     */
    public function destroy($id, Request $request)
    {
        $viewer = $request->user();
        $leaveRequest = LeaveRequest::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isOwner = ($leaveRequest->user_id === $viewer->id);

        if (!$isAdmin && (!$isOwner || $leaveRequest->status !== 'DRAFT')) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកអាចលុបបានតែពាក្យសុំច្បាប់ដែលស្ថិតនៅជាសេចក្តីព្រាង (DRAFT) ប៉ុណ្ណោះ។'
            ], 403);
        }

        if ($leaveRequest->attachment_path && Storage::disk('public')->exists($leaveRequest->attachment_path)) {
            Storage::disk('public')->delete($leaveRequest->attachment_path);
        }

        $leaveRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'បានលុបពាក្យសុំច្បាប់ដោយជោគជ័យ'
        ]);
    }

    /**
     * ទាញយកឯកសារភ្ជាប់ (Download Attachment)
     */
    public function downloadAttachment($id, Request $request)
    {
        $viewer = $request->user();
        $leaveRequest = LeaveRequest::findOrFail($id);

        if (!$leaveRequest->attachment_path || !Storage::disk('public')->exists($leaveRequest->attachment_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារភ្ជាប់ឡើយ'], 404);
        }

        return Storage::disk('public')->download(
            $leaveRequest->attachment_path,
            $leaveRequest->attachment_name ?? basename($leaveRequest->attachment_path)
        );
    }
}
