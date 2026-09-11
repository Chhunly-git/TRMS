<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WeeklyReport;
use App\Models\WeeklyReportTask;
use App\Models\User;
use App\Models\Department;
use App\Models\Office;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WeeklyReportController extends Controller
{
    /**
     * បង្ហាញបញ្ជីរបាយការណ៍ប្រចាំសប្តាហ៍ (My Reports ឬ Subordinates Reports)
     */
    public function index(Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $scope = $request->get('scope', 'my'); // 'my', 'subordinates', 'all'

        $query = WeeklyReport::with([
            'user:id,name,name_kh,name_en,profile_image,position_id,department_id,office_id',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'reviewer:id,name,name_kh,profile_image,position_id',
            'reviewer.position:id,title_kh,title_en,level',
            'leadershipReviewer:id,name,name_kh,profile_image,position_id',
            'leadershipReviewer.position:id,title_kh,title_en,level',
            'tasks'
        ]);

        if ($scope === 'my') {
            // មើលតែរបាយការណ៍ផ្ទាល់ខ្លួន
            $query->where('weekly_reports.user_id', $viewer->id);
        } elseif ($scope === 'subordinates') {
            // មើលរបាយការណ៍របស់មន្ត្រីក្រោមឱវាទ
            $query->visibleTo($viewer)
                  ->where('weekly_reports.user_id', '!=', $viewer->id);
        } else {
            // ទូទៅ (សម្របតាមសិទ្ធិស្វ័យប្រវត្តិ)
            $query->visibleTo($viewer);
        }

        // Filter តាមឆ្នាំ និងខែ
        if ($request->filled('year')) {
            $query->where('weekly_reports.year', (int)$request->year);
        }
        if ($request->filled('month')) {
            $query->where('weekly_reports.month', (int)$request->month);
        }
        if ($request->filled('week_number')) {
            $query->where('weekly_reports.week_number', (int)$request->week_number);
        }

        // Filter តាមស្ថានភាព
        if ($request->filled('status')) {
            $query->where('weekly_reports.status', strtoupper($request->status));
        }

        // Filter តាមស្ថាប័ន (សម្រាប់ថ្នាក់លើ ឬ Admin)
        if ($request->filled('department_id')) {
            $query->where('weekly_reports.department_id', $request->department_id);
        }
        if ($request->filled('office_id')) {
            $query->where('weekly_reports.office_id', $request->office_id);
        }
        if ($request->filled('user_id')) {
            $query->where('weekly_reports.user_id', $request->user_id);
        }

        // ស្វែងរកតាមពាក្យគន្លឹះ (Search)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('weekly_reports.title', 'like', "%{$search}%")
                  ->orWhere('weekly_reports.completed_tasks', 'like', "%{$search}%")
                  ->orWhere('weekly_reports.planned_tasks', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uQ) use ($search) {
                      $uQ->where('name_kh', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // តម្រៀបតាមឆ្នាំ ខែ សប្តាហ៍ និងកាលបរិច្ឆេទបង្កើតថ្មីៗ
        $reports = $query->orderBy('weekly_reports.year', 'desc')
                         ->orderBy('weekly_reports.month', 'desc')
                         ->orderBy('weekly_reports.week_number', 'desc')
                         ->orderBy('weekly_reports.created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $reports->items(),
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage(),
            'total' => $reports->total(),
            'per_page' => $reports->perPage(),
        ]);
    }

    /**
     * ស្ថិតិសង្ខេបរបាយការណ៍ (Stats & Summary)
     */
    public function stats(Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $canReview = $isAdmin || ($viewerPosLevel <= 8);

        $year = (int)($request->get('year', Carbon::now()->year));
        $month = (int)($request->get('month', Carbon::now()->month));

        // ស្ថិតិផ្ទាល់ខ្លួន
        $myQuery = WeeklyReport::where('user_id', $viewer->id)
            ->where('year', $year)
            ->where('month', $month);

        if ($request->filled('week_number')) {
            $myQuery->where('week_number', (int)$request->week_number);
        }

        $myReportIds = (clone $myQuery)->pluck('id');
        $myTasksQuery = WeeklyReportTask::whereIn('weekly_report_id', $myReportIds);
        $myTotalTasks = (clone $myTasksQuery)->count();
        $myPendingTasks = (clone $myTasksQuery)->where('status', 'PENDING')->count();
        $myInProgressTasks = (clone $myTasksQuery)->where('status', 'IN_PROGRESS')->count();
        $myCompletedTasks = (clone $myTasksQuery)->where('status', 'COMPLETED')->count();

        $myStats = [
            'total' => (clone $myQuery)->count(),
            'draft' => (clone $myQuery)->where('status', 'DRAFT')->count(),
            'submitted' => (clone $myQuery)->where('status', 'SUBMITTED')->count(),
            'reviewed' => (clone $myQuery)->where('status', 'REVIEWED')->count(),
            'tasks_total' => $myTotalTasks,
            'tasks_pending' => $myPendingTasks,
            'tasks_in_progress' => $myInProgressTasks,
            'tasks_completed' => $myCompletedTasks,
            'tasks_completion_rate' => $myTotalTasks > 0 ? round(($myCompletedTasks / $myTotalTasks) * 100) : 0,
        ];

        // ស្ថិតិមន្ត្រីក្រោមឱវាទ (ប្រសិនបើជាថ្នាក់ដឹកនាំ)
        $subStats = [
            'total' => 0,
            'pending_review' => 0,
            'reviewed' => 0,
            'tasks_total' => 0,
            'tasks_pending' => 0,
            'tasks_in_progress' => 0,
            'tasks_completed' => 0,
            'tasks_completion_rate' => 0,
        ];

        if ($canReview) {
            $subQuery = WeeklyReport::visibleTo($viewer)
                ->where('weekly_reports.user_id', '!=', $viewer->id)
                ->where('weekly_reports.year', $year)
                ->where('weekly_reports.month', $month);

            if ($request->filled('week_number')) {
                $subQuery->where('weekly_reports.week_number', (int)$request->week_number);
            }
            if ($request->filled('department_id')) {
                $subQuery->where('weekly_reports.department_id', $request->department_id);
            }
            if ($request->filled('office_id')) {
                $subQuery->where('weekly_reports.office_id', $request->office_id);
            }
            if ($request->filled('user_id')) {
                $subQuery->where('weekly_reports.user_id', $request->user_id);
            }

            $subReportIds = (clone $subQuery)->pluck('weekly_reports.id');
            $subTasksQuery = WeeklyReportTask::whereIn('weekly_report_id', $subReportIds);
            $subTotalTasks = (clone $subTasksQuery)->count();
            $subPendingTasks = (clone $subTasksQuery)->where('status', 'PENDING')->count();
            $subInProgressTasks = (clone $subTasksQuery)->where('status', 'IN_PROGRESS')->count();
            $subCompletedTasks = (clone $subTasksQuery)->where('status', 'COMPLETED')->count();

            $subStats = [
                'total' => (clone $subQuery)->count(),
                'pending_review' => (clone $subQuery)->where('weekly_reports.status', 'SUBMITTED')->count(),
                'reviewed' => (clone $subQuery)->where('weekly_reports.status', 'REVIEWED')->count(),
                'tasks_total' => $subTotalTasks,
                'tasks_pending' => $subPendingTasks,
                'tasks_in_progress' => $subInProgressTasks,
                'tasks_completed' => $subCompletedTasks,
                'tasks_completion_rate' => $subTotalTasks > 0 ? round(($subCompletedTasks / $subTotalTasks) * 100) : 0,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'year' => $year,
                'month' => $month,
                'my_stats' => $myStats,
                'subordinates_stats' => $subStats,
                'can_review' => $canReview,
                'viewer_info' => [
                    'id' => $viewer->id,
                    'name_kh' => $viewer->name_kh,
                    'pos_title' => $viewer->position?->title_kh,
                    'pos_level' => $viewerPosLevel,
                    'dept_id' => $viewer->department_id,
                    'office_id' => $viewer->office_id,
                    'is_admin' => $isAdmin,
                ]
            ]
        ]);
    }

    /**
     * ជម្រើសសម្រាប់ Filter (នាយកដ្ឋាន ការិយាល័យ មន្ត្រី ស្របតាមកម្រិតសិទ្ធិ)
     */
    public function filterOptions(Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;

        $deptQuery = Department::query();
        $officeQuery = Office::query();

        if (!$isAdmin && $viewerPosLevel > 2 && !is_null($viewer->department_id)) {
            $deptQuery->where('id', $viewer->department_id);
            $officeQuery->where('department_id', $viewer->department_id);

            if ($viewerPosLevel > 5 && !is_null($viewer->office_id)) {
                $officeQuery->where('id', $viewer->office_id);
            }
        }

        $departments = $deptQuery->orderBy('id')->get(['id', 'name_kh', 'name_en']);
        $offices = $officeQuery->orderBy('id')->get(['id', 'department_id', 'name_kh', 'name_en']);

        // បញ្ជីមន្ត្រីក្រោមឱវាទ (សម្រាប់ Filter មើលតាមមន្ត្រីជាក់លាក់)
        $userQuery = User::query()
            ->where('users.id', '!=', $viewer->id)
            ->with(['position:id,title_kh,title_en,level']);

        if (!$isAdmin) {
            if ($viewerPosLevel >= 9) {
                $userQuery->whereRaw('1 = 0');
            } else {
                $userQuery->whereHas('position', function ($posQ) use ($viewerPosLevel) {
                    $posQ->where('level', '>', $viewerPosLevel);
                });

                if ($viewerPosLevel > 2 && !is_null($viewer->department_id)) {
                    $userQuery->where('users.department_id', $viewer->department_id);

                    if ($viewerPosLevel > 5 && !is_null($viewer->office_id)) {
                        $userQuery->where('users.office_id', $viewer->office_id);
                    }
                }
            }
        }

        $officers = $userQuery->orderBy('users.name_kh')
            ->get(['users.id', 'users.name_kh', 'users.name', 'users.department_id', 'users.office_id', 'users.position_id']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'departments' => $departments,
                'offices' => $offices,
                'officers' => $officers,
            ]
        ]);
    }

    /**
     * បង្កើតរបាយការណ៍ប្រចាំសប្តាហ៍ថ្មី
     */
    public function store(Request $request)
    {
        $user = $request->user()->loadMissing('position', 'department', 'office');

        $request->validate([
            'year' => 'required|integer|min:2020|max:2035',
            'month' => 'required|integer|min:1|max:12',
            'week_number' => 'required|integer|min:1|max:5',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'title' => 'required|string|max:255',
            'completed_tasks' => 'nullable|string',
            'planned_tasks' => 'nullable|string',
            'challenges' => 'nullable|string',
            'attachment' => 'nullable|file|max:20480', // Max 20MB
            'submit_now' => 'nullable|boolean',
        ], [
            'year.required' => 'សូមបញ្ជាក់ឆ្នាំ',
            'month.required' => 'សូមបញ្ជាក់ខែ',
            'week_number.required' => 'សូមជ្រើសរើសសប្តាហ៍ទី',
            'start_date.required' => 'សូមបញ្ជាក់កាលបរិច្ឆេទចាប់ផ្តើម',
            'end_date.required' => 'សូមបញ្ជាក់កាលបរិច្ឆេទបញ្ចប់',
            'title.required' => 'សូមបញ្ចូលចំណងជើងរបាយការណ៍',
        ]);

        // ពិនិត្យមើលថាតើមានរបាយការណ៍សប្តាហ៍នេះរួចហើយឬនៅ
        $exists = WeeklyReport::where('user_id', $user->id)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->where('week_number', $request->week_number)
            ->first();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកបានកត់ត្រារបាយការណ៍សម្រាប់សប្តាហ៍ទី' . $request->week_number . ' ខែនេះរួចរាល់ហើយ!'
            ], 422);
        }

        // ដំណើរការ tasks array ប្រសិនបើមាន
        $tasksData = $request->get('tasks');
        if (is_string($tasksData)) {
            $tasksData = json_decode($tasksData, true) ?? [];
        }
        if (!is_array($tasksData)) {
            $tasksData = [];
        }

        $completedTasksText = $request->completed_tasks;
        $plannedTasksText = $request->planned_tasks;

        // Auto-generate text summary if empty and tasks provided
        if (empty($completedTasksText) && count($tasksData) > 0) {
            $comp = array_values(array_filter($tasksData, fn($t) => ($t['status'] ?? '') === 'COMPLETED'));
            $completedTasksText = count($comp) > 0
                ? implode("\n", array_map(fn($t, $i) => ($i+1) . ". " . $t['task_name'] . (!empty($t['result_notes']) ? " (" . $t['result_notes'] . ")" : ""), $comp, array_keys($comp)))
                : "មិនមាន";
        }
        if (empty($plannedTasksText) && count($tasksData) > 0) {
            $incomp = array_values(array_filter($tasksData, fn($t) => ($t['status'] ?? '') !== 'COMPLETED'));
            $plannedTasksText = count($incomp) > 0
                ? implode("\n", array_map(fn($t, $i) => ($i+1) . ". " . $t['task_name'] . " (" . ($t['status'] === 'IN_PROGRESS' ? 'កំពុងធ្វើ ' . ($t['progress_percent'] ?? 0) . '%' : 'មិនទាន់ធ្វើ') . ")", $incomp, array_keys($incomp)))
                : "មិនមាន";
        }

        // Upload Attachment ប្រសិនបើមាន
        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('weekly_reports', 'public');
        }

        $submitNow = filter_var($request->get('submit_now', false), FILTER_VALIDATE_BOOLEAN);

        $report = WeeklyReport::create([
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'office_id' => $user->office_id,
            'position_id' => $user->position_id,
            'year' => (int)$request->year,
            'month' => (int)$request->month,
            'week_number' => (int)$request->week_number,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'title' => $request->title,
            'completed_tasks' => $completedTasksText ?? 'មិនមាន',
            'planned_tasks' => $plannedTasksText ?? 'មិនមាន',
            'challenges' => $request->challenges,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'status' => $submitNow ? 'SUBMITTED' : 'DRAFT',
            'submitted_at' => $submitNow ? Carbon::now() : null,
        ]);

        // រក្សាទុកកិច្ចការងារនីមួយៗចូលក្នុង weekly_report_tasks
        foreach ($tasksData as $t) {
            if (empty($t['task_name'])) continue;
            $report->tasks()->create([
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'office_id' => $user->office_id,
                'task_name' => $t['task_name'],
                'description' => $t['description'] ?? null,
                'status' => in_array($t['status'] ?? '', ['PENDING', 'IN_PROGRESS', 'COMPLETED']) ? $t['status'] : 'PENDING',
                'priority' => in_array($t['priority'] ?? '', ['LOW', 'MEDIUM', 'HIGH', 'URGENT']) ? $t['priority'] : 'MEDIUM',
                'progress_percent' => isset($t['progress_percent']) ? (int)$t['progress_percent'] : (($t['status'] ?? '') === 'COMPLETED' ? 100 : 0),
                'result_notes' => $t['result_notes'] ?? null,
                'due_date' => $t['due_date'] ?? null,
                'completed_at' => ($t['status'] ?? '') === 'COMPLETED' ? Carbon::now() : null,
            ]);
        }

        $report->load([
            'user:id,name,name_kh,name_en,profile_image',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'tasks',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $submitNow ? 'របាយការណ៍ត្រូវបានដាក់ជូនថ្នាក់ដឹកនាំដោយជោគជ័យ' : 'បានរក្សាទុកសេចក្តីព្រាងរបាយការណ៍ដោយជោគជ័យ',
            'data' => $report,
        ], 201);
    }

    /**
     * មើលព័ត៌មានលម្អិតនៃរបាយការណ៍
     */
    public function show($id, Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $report = WeeklyReport::with([
            'user:id,name,name_kh,name_en,profile_image,position_id,department_id,office_id',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'reviewer:id,name,name_kh,profile_image,position_id',
            'reviewer.position:id,title_kh,title_en,level',
            'leadershipReviewer:id,name,name_kh,profile_image,position_id',
            'leadershipReviewer.position:id,title_kh,title_en,level',
            'tasks',
        ])->findOrFail($id);

        if (!$report->canBeViewedBy($viewer)) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកគ្មានសិទ្ធិមើលរបាយការណ៍នេះឡើយ។'
            ], 403);
        }

        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');

        return response()->json([
            'status' => 'success',
            'data' => $report,
            'meta' => [
                'can_edit' => $report->canBeEditedBy($viewer),
                'can_review' => $report->canBeReviewedBy($viewer),
                'can_review_supervisor' => $report->canReviewAsSupervisor($viewer),
                'can_review_leadership' => $report->canAddLeadershipRemark($viewer),
                'is_leadership' => ($viewerPosLevel <= 2 || $isAdmin),
                'is_deputy_office_head' => ($viewerPosLevel == 8),
            ]
        ]);
    }

    /**
     * កែសម្រួលរបាយការណ៍
     */
    public function update($id, Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $report = WeeklyReport::findOrFail($id);

        if (!$report->canBeEditedBy($viewer)) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកគ្មានសិទ្ធិកែប្រែរបាយការណ៍នេះឡើយ (ឬរបាយការណ៍ត្រូវបានពិនិត្យរួចហើយ)។'
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'completed_tasks' => 'nullable|string',
            'planned_tasks' => 'nullable|string',
            'challenges' => 'nullable|string',
            'attachment' => 'nullable|file|max:20480',
            'submit_now' => 'nullable|boolean',
        ]);

        // ពិនិត្យមើលការផ្លាស់ប្តូរឯកសារភ្ជាប់
        if ($request->hasFile('attachment')) {
            if ($report->attachment_path && Storage::disk('public')->exists($report->attachment_path)) {
                Storage::disk('public')->delete($report->attachment_path);
            }
            $file = $request->file('attachment');
            $report->attachment_name = $file->getClientOriginalName();
            $report->attachment_path = $file->store('weekly_reports', 'public');
        } elseif ($request->boolean('remove_attachment')) {
            if ($report->attachment_path && Storage::disk('public')->exists($report->attachment_path)) {
                Storage::disk('public')->delete($report->attachment_path);
            }
            $report->attachment_path = null;
            $report->attachment_name = null;
        }

        if ($request->filled('title')) $report->title = $request->title;
        if ($request->has('completed_tasks') && !is_null($request->completed_tasks)) $report->completed_tasks = $request->completed_tasks;
        if ($request->has('planned_tasks') && !is_null($request->planned_tasks)) $report->planned_tasks = $request->planned_tasks;
        if ($request->has('challenges')) $report->challenges = $request->challenges;
        if ($request->filled('start_date')) $report->start_date = $request->start_date;
        if ($request->filled('end_date')) $report->end_date = $request->end_date;

        // កែសម្រួល tasks បើសិនជាមានផ្ញើមក
        if ($request->has('tasks')) {
            $tasksData = $request->get('tasks');
            if (is_string($tasksData)) {
                $tasksData = json_decode($tasksData, true) ?? [];
            }
            if (is_array($tasksData)) {
                $report->tasks()->forceDelete();
                foreach ($tasksData as $t) {
                    if (empty($t['task_name'])) continue;
                    $report->tasks()->create([
                        'user_id' => $report->user_id,
                        'department_id' => $report->department_id,
                        'office_id' => $report->office_id,
                        'task_name' => $t['task_name'],
                        'description' => $t['description'] ?? null,
                        'status' => in_array($t['status'] ?? '', ['PENDING', 'IN_PROGRESS', 'COMPLETED']) ? $t['status'] : 'PENDING',
                        'priority' => in_array($t['priority'] ?? '', ['LOW', 'MEDIUM', 'HIGH', 'URGENT']) ? $t['priority'] : 'MEDIUM',
                        'progress_percent' => isset($t['progress_percent']) ? (int)$t['progress_percent'] : (($t['status'] ?? '') === 'COMPLETED' ? 100 : 0),
                        'result_notes' => $t['result_notes'] ?? null,
                        'due_date' => $t['due_date'] ?? null,
                        'completed_at' => ($t['status'] ?? '') === 'COMPLETED' ? Carbon::now() : null,
                    ]);
                }

                // Refresh summary text if empty
                if (empty($report->completed_tasks) || $report->completed_tasks === 'មិនមាន') {
                    $comp = array_values(array_filter($tasksData, fn($t) => ($t['status'] ?? '') === 'COMPLETED'));
                    $report->completed_tasks = count($comp) > 0
                        ? implode("\n", array_map(fn($t, $i) => ($i+1) . ". " . $t['task_name'] . (!empty($t['result_notes']) ? " (" . $t['result_notes'] . ")" : ""), $comp, array_keys($comp)))
                        : "មិនមាន";
                }
                if (empty($report->planned_tasks) || $report->planned_tasks === 'មិនមាន') {
                    $incomp = array_values(array_filter($tasksData, fn($t) => ($t['status'] ?? '') !== 'COMPLETED'));
                    $report->planned_tasks = count($incomp) > 0
                        ? implode("\n", array_map(fn($t, $i) => ($i+1) . ". " . $t['task_name'] . " (" . ($t['status'] === 'IN_PROGRESS' ? 'កំពុងធ្វើ ' . ($t['progress_percent'] ?? 0) . '%' : 'មិនទាន់ធ្វើ') . ")", $incomp, array_keys($incomp)))
                        : "មិនមាន";
                }
            }
        }

        // ប្រសិនបើជ្រើសរើស Submit ឥឡូវនេះ
        if ($request->boolean('submit_now') && $report->status === 'DRAFT') {
            $report->status = 'SUBMITTED';
            $report->submitted_at = Carbon::now();
        }

        $report->save();

        $report->load([
            'user:id,name,name_kh,name_en,profile_image',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'reviewer:id,name,name_kh,profile_image,position_id',
            'reviewer.position:id,title_kh,title_en,level',
            'leadershipReviewer:id,name,name_kh,profile_image,position_id',
            'leadershipReviewer.position:id,title_kh,title_en,level',
            'tasks',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'បានកែប្រែទិន្នន័យរបាយការណ៍ដោយជោគជ័យ',
            'data' => $report,
        ]);
    }

    /**
     * ដាក់ជូនរបាយការណ៍ (Submit a Draft Report)
     */
    public function submit($id, Request $request)
    {
        $user = $request->user();
        $report = WeeklyReport::where('user_id', $user->id)->findOrFail($id);

        if ($report->status !== 'DRAFT') {
            return response()->json([
                'status' => 'error',
                'message' => 'របាយការណ៍នេះត្រូវបានដាក់ជូនរួចរាល់ហើយ។'
            ], 422);
        }

        $report->status = 'SUBMITTED';
        $report->submitted_at = Carbon::now();
        $report->save();

        return response()->json([
            'status' => 'success',
            'message' => 'របាយការណ៍ត្រូវបានដាក់ជូនថ្នាក់ដឹកនាំដោយជោគជ័យ',
            'data' => $report,
        ]);
    }

    /**
     * ថ្នាក់ដឹកនាំពិនិត្យ និងដាក់ចំណារ/មតិយោបល់ (Supervisor Review & Remarks / Leadership Additional Remarks)
     */
    public function review($id, Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $report = WeeklyReport::with('position', 'user.position')->findOrFail($id);

        if (!$report->canBeReviewedBy($viewer)) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកគ្មានសិទ្ធិពិនិត្យ ឬដាក់ចំណារលើរបាយការណ៍នេះឡើយ។'
            ], 403);
        }

        $request->validate([
            'supervisor_remarks' => 'nullable|string|max:2000',
            'leadership_remarks' => 'nullable|string|max:2000',
        ]);

        $viewerPosLevel = $viewer->position ? (int)$viewer->position->level : 99;
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isLeadership = ($viewerPosLevel <= 2 || $isAdmin);

        // ១. ប្រសិនបើជាអគ្គនាយក អគ្គនាយករង ឬ Admin (ចារបន្ថែមពីលើ)
        if ($isLeadership) {
            if ($request->has('leadership_remarks')) {
                $report->leadership_remarks = $request->leadership_remarks;
                $report->leadership_reviewed_by = $viewer->id;
                $report->leadership_reviewed_at = Carbon::now();
            }
            // ប្រសិនបើ DG/DDG ចង់បំពេញ supervisor_remarks លើរបាយការណ៍ដែលពុំទាន់មានចំណារ
            if (empty($report->supervisor_remarks) && $request->has('supervisor_remarks') && !empty($request->supervisor_remarks)) {
                $report->supervisor_remarks = $request->supervisor_remarks;
                $report->reviewed_by = $viewer->id;
                $report->reviewed_at = Carbon::now();
            }
        }

        // ២. ប្រសិនបើជាប្រធានការិយាល័យ ឬប្រធាននាយកដ្ឋាន (សរសេរចំណារផ្ទាល់)
        if ($report->canReviewAsSupervisor($viewer) && !$isLeadership) {
            if ($request->has('supervisor_remarks')) {
                $report->supervisor_remarks = $request->supervisor_remarks;
                $report->reviewed_by = $viewer->id;
                $report->reviewed_at = Carbon::now();
            }
        }

        $report->status = 'REVIEWED';
        $report->save();

        $report->load([
            'user:id,name,name_kh,name_en,profile_image',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,title_kh,title_en,level',
            'reviewer:id,name,name_kh,profile_image,position_id',
            'reviewer.position:id,title_kh,title_en,level',
            'leadershipReviewer:id,name,name_kh,profile_image,position_id',
            'leadershipReviewer.position:id,title_kh,title_en,level',
            'tasks',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'បានពិនិត្យ និងកត់ត្រាចំណារលើរបាយការណ៍ដោយជោគជ័យ',
            'data' => $report,
        ]);
    }

    /**
     * លុបរបាយការណ៍ (Delete Report)
     */
    public function destroy($id, Request $request)
    {
        $viewer = $request->user();
        $report = WeeklyReport::findOrFail($id);

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isOwner = ($report->user_id === $viewer->id);

        if (!$isAdmin && (!$isOwner || $report->status !== 'DRAFT')) {
            return response()->json([
                'status' => 'error',
                'message' => 'លោកអ្នកអាចលុបបានតែសេចក្តីព្រាង (DRAFT) ផ្ទាល់ខ្លួនប៉ុណ្ណោះ។'
            ], 403);
        }

        if ($report->attachment_path && Storage::disk('public')->exists($report->attachment_path)) {
            Storage::disk('public')->delete($report->attachment_path);
        }

        $report->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'បានលុបរបាយការណ៍ដោយជោគជ័យ'
        ]);
    }

    /**
     * ទាញយកឯកសារភ្ជាប់ (Download Attachment)
     */
    public function downloadAttachment($id, Request $request)
    {
        $viewer = $request->user()->loadMissing('position', 'department', 'office');
        $report = WeeklyReport::findOrFail($id);

        if (!$report->canBeViewedBy($viewer)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$report->attachment_path || !Storage::disk('public')->exists($report->attachment_path)) {
            return response()->json(['message' => 'រកមិនឃើញឯកសារភ្ជាប់ឡើយ'], 404);
        }

        return Storage::disk('public')->download(
            $report->attachment_path,
            $report->attachment_name ?? basename($report->attachment_path)
        );
    }

    /**
     * កែប្រែស្ថានភាពកិច្ចការងាររហ័ស (Quick update single task status)
     */
    public function updateTaskStatus($taskId, Request $request)
    {
        $viewer = $request->user();
        $task = WeeklyReportTask::findOrFail($taskId);

        if ($task->user_id !== $viewer->id && strtoupper($viewer->level ?? '') !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:PENDING,IN_PROGRESS,COMPLETED',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'result_notes' => 'nullable|string',
        ]);

        $task->status = $request->status;
        if ($request->has('progress_percent')) {
            $task->progress_percent = (int)$request->progress_percent;
        } elseif ($request->status === 'COMPLETED') {
            $task->progress_percent = 100;
            $task->completed_at = Carbon::now();
        }

        if ($request->has('result_notes')) {
            $task->result_notes = $request->result_notes;
        }
        $task->save();

        return response()->json([
            'status' => 'success',
            'message' => 'បានកែប្រែស្ថានភាពកិច្ចការងារដោយជោគជ័យ',
            'data' => $task,
        ]);
    }
}
