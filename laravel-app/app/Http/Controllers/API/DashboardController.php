<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\InboundDocument;
use App\Models\LeaveRequest;
use App\Models\RoomBooking;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Models\WeeklyReportTask;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get Comprehensive Dashboard Statistics & Workflows
     * Adaptive for both regular Officers/Users and Admins/Leadership
     */
    public function getStats(Request $request)
    {
        $viewer = $request->user();
        if (!$viewer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $posLevel = $viewer->position ? (int) $viewer->position->level : 99;
        $isLeadership = ($isAdmin || $posLevel <= 2);
        $isDeptHead = in_array($posLevel, [3, 4, 5]);

        $today = Carbon::today('Asia/Phnom_Penh');
        $todayDateStr = $today->toDateString();
        $threeDaysLaterStr = $today->copy()->addDays(3)->toDateString();
        $currentHour = Carbon::now('Asia/Phnom_Penh')->hour;

        // 1. Khmer Calendar & Greeting Helper
        $khmerDays = ['អាទិត្យ', 'ចន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៍', 'សុក្រ', 'សៅរ៍'];
        $khmerMonths = ['', 'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];

        $dayOfWeekKh = $khmerDays[$today->dayOfWeek];
        $monthKh = $khmerMonths[$today->month];
        $dateFormattedKh = "ថ្ងៃ{$dayOfWeekKh} ទី" . sprintf('%02d', $today->day) . " ខែ{$monthKh} ឆ្នាំ" . $today->year;

        $greetingKh = match (true) {
            $currentHour < 12 => 'អរុណសួស្តី',
            $currentHour < 18 => 'ទិវាសួស្តី',
            default => 'សាយណ្ហសួស្តី',
        };

        $userContext = [
            'id' => $viewer->id,
            'name_kh' => $viewer->name_kh ?? $viewer->name,
            'name_en' => $viewer->name_en ?? '',
            'position_title' => $viewer->position ? $viewer->position->title_kh : 'មន្ត្រី',
            'department_name' => $viewer->department ? $viewer->department->name_kh : '',
            'office_name' => $viewer->office ? $viewer->office->name_kh : '',
            'profile_image' => $viewer->profile_thumbnail ?: $viewer->profile_image,
            'is_admin' => $isAdmin,
            'is_leadership' => $isLeadership,
            'is_dept_head' => $isDeptHead,
            'greeting_kh' => $greetingKh,
            'today_date_kh' => $dateFormattedKh,
            'today_date' => $todayDateStr,
        ];

        // 2. Inbound Documents (ឯកសារចូល)
        $inboundStats = $this->calculateInboundStats($viewer, $isAdmin, $todayDateStr, $threeDaysLaterStr);

        // 3. Leave Requests (ច្បាប់ឈប់សម្រាក)
        $leaveStats = $this->calculateLeaveStats($viewer, $isAdmin, $todayDateStr);

        // 4. Weekly Reports (របាយការណ៍ការងារប្រចាំសប្តាហ៍)
        $weeklyReportStats = $this->calculateWeeklyReportStats($viewer, $isAdmin, $today);

        // 5. Room Bookings & Schedules for Today (បន្ទប់ប្រជុំ និងកាលវិភាគ)
        $roomAndScheduleStats = $this->calculateRoomAndScheduleStats($viewer, $isAdmin, $todayDateStr);

        // 6. Attendance Summary Today (វត្តមានថ្ងៃនេះ)
        $attendanceStats = $this->calculateAttendanceStats($viewer, $isAdmin, $todayDateStr);

        // 7. HR Statistics (for Admin overview)
        $hrStats = [
            'totalEmployees' => User::where('status', '!=', 'DISABLED')->count(),
            'fEmployees' => User::where('status', '!=', 'DISABLED')->whereIn('gender', ['Female', 'FEMALE', 'F'])->count(),
            'civilServants' => User::where('status', '!=', 'DISABLED')->where('employee_type', 'CIVIL_SERVICE')->count(),
            'fcivilServants' => User::where('status', '!=', 'DISABLED')->where('employee_type', 'CIVIL_SERVICE')->whereIn('gender', ['Female', 'FEMALE', 'F'])->count(),
            'statutory' => User::where('status', '!=', 'DISABLED')->where('employee_type', 'STATUTORY')->count(),
            'fstatutory' => User::where('status', '!=', 'DISABLED')->where('employee_type', 'STATUTORY')->whereIn('gender', ['Female', 'FEMALE', 'F'])->count(),
            'contractStaff' => User::where('status', '!=', 'DISABLED')->where('employee_type', 'CONTRACT')->count(),
            'fcontractStaff' => User::where('status', '!=', 'DISABLED')->where('employee_type', 'CONTRACT')->whereIn('gender', ['Female', 'FEMALE', 'F'])->count(),
        ];

        // 8. Birthdays This Month
        $currentMonth = $today->month;
        $todayMonthDay = $today->format('m-d');

        $birthdays = User::where('status', '!=', 'DISABLED')
            ->whereNotNull('dob')
            ->whereMonth('dob', $currentMonth)
            ->with('position:id,title_kh')
            ->get()
            ->map(function ($u) use ($todayMonthDay, $khmerMonths) {
                $dob = Carbon::parse($u->dob);
                $isToday = $dob->format('m-d') === $todayMonthDay;
                $dobFormatted = $dob->format('d') . ' ' . $khmerMonths[$dob->month];

                return [
                    'id' => $u->id,
                    'name_kh' => $u->name_kh ?? $u->name,
                    'profile_image' => $u->profile_thumbnail ?: $u->profile_image,
                    'position_name' => $u->position ? $u->position->title_kh : '---',
                    'dob_formatted' => $dobFormatted,
                    'age' => $dob->age,
                    'is_today' => $isToday,
                    'dob_day' => (int) $dob->format('d'),
                ];
            })
            ->sortByDesc('is_today')
            ->values();

        return response()->json([
            'success' => true,
            'user_context' => $userContext,
            'inbound_stats' => $inboundStats,
            'leave_stats' => $leaveStats,
            'weekly_report_stats' => $weeklyReportStats,
            'room_stats' => $roomAndScheduleStats,
            'attendance_stats' => $attendanceStats,
            'hr_stats' => $hrStats,
            'stats' => $hrStats,
            'birthdays' => $birthdays,
        ]);
    }

    /**
     * Inbound Documents Calculation
     */
    protected function calculateInboundStats($viewer, bool $isAdmin, string $todayDateStr, string $threeDaysLaterStr): array
    {
        // User's To-Do Documents Query
        $myTodoQuery = $this->buildMyTodoQuery($viewer);
        $myTodoCount = (clone $myTodoQuery)->count();

        // User's Done Documents Count
        $myDoneCount = InboundDocument::where(function ($q) use ($viewer) {
            $q->where('acknowledged_by', $viewer->id)
                ->orWhere(function ($q2) use ($viewer) {
                    $q2->where('status', 'COMPLETED')
                       ->where('target_user_id', $viewer->id);
                })
                ->orWhereHas('responses', function ($rq) use ($viewer) {
                    $rq->where('drafted_by', $viewer->id);
                })
                ->orWhereHas('responses.approvals', function ($aq) use ($viewer) {
                    $aq->where('user_id', $viewer->id);
                })
                ->orWhereHas('movements', function ($mq) use ($viewer) {
                    $mq->where('user_id', $viewer->id)
                       ->whereIn('action', ['FORWARDED', 'DISPATCHED', 'DG_ANNOTATED', 'ACKNOWLEDGED']);
                });
        })->count();

        // User's Due Soon and Overdue in To-Do list
        $myDueSoonCount = (clone $myTodoQuery)
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$todayDateStr, $threeDaysLaterStr])
            ->count();

        $myOverdueCount = (clone $myTodoQuery)
            ->whereNotNull('deadline')
            ->where('deadline', '<', $todayDateStr)
            ->count();

        // Top 5 actionable to-do documents for user
        $recentTodoDocs = (clone $myTodoQuery)
            ->with(['targetDepartment:id,name_kh', 'targetOffice:id,name_kh', 'targetUser:id,name,name_kh'])
            ->orderByRaw("CASE WHEN deadline IS NOT NULL AND deadline < '{$todayDateStr}' THEN 1 WHEN deadline IS NOT NULL AND deadline <= '{$threeDaysLaterStr}' THEN 2 ELSE 3 END")
            ->orderBy('id', 'desc')
            ->take(5)
            ->get([
                'id',
                'general_inbound_number',
                'dg_inbound_number',
                'title',
                'sender_organization',
                'urgency',
                'deadline',
                'status',
                'target_type',
                'target_department_id',
                'target_office_id',
                'target_user_id'
            ]);

        // System-wide statistics for Admin / Leadership
        $adminStats = [];
        if ($isAdmin || ($viewer->position && (int) $viewer->position->level <= 2)) {
            $totalActive = InboundDocument::whereNotIn('status', ['COMPLETED', 'CANCELLED'])->count();
            $receptionPending = InboundDocument::whereIn('status', ['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT'])->count();
            $dgPending = InboundDocument::where('status', 'SUBMITTED_TO_DG')->count();
            $dgAnnotated = InboundDocument::where('status', 'DG_ANNOTATED')->count();
            $inResponse = InboundDocument::where('status', 'IN_RESPONSE_PROGRESS')->count();
            $dispatched = InboundDocument::where('status', 'DISPATCHED')->count();
            $completed = InboundDocument::where('status', 'COMPLETED')->count();

            $systemDueSoon = InboundDocument::whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->whereNotNull('deadline')
                ->whereBetween('deadline', [$todayDateStr, $threeDaysLaterStr])
                ->count();

            $systemOverdue = InboundDocument::whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->whereNotNull('deadline')
                ->where('deadline', '<', $todayDateStr)
                ->count();

            $recentInbound = InboundDocument::with(['targetDepartment:id,name_kh', 'targetOffice:id,name_kh', 'targetUser:id,name,name_kh'])
                ->orderBy('id', 'desc')
                ->take(6)
                ->get([
                    'id',
                    'general_inbound_number',
                    'dg_inbound_number',
                    'title',
                    'sender_organization',
                    'urgency',
                    'deadline',
                    'status',
                    'target_type',
                    'received_date'
                ]);

            $adminStats = [
                'total_active' => $totalActive,
                'total_all' => InboundDocument::count(),
                'reception_pending' => $receptionPending,
                'dg_pending' => $dgPending,
                'dg_annotated' => $dgAnnotated,
                'in_response' => $inResponse,
                'dispatched' => $dispatched,
                'completed' => $completed,
                'system_due_soon' => $systemDueSoon,
                'system_overdue' => $systemOverdue,
                'recent_inbound' => $recentInbound,
            ];
        }

        return [
            'my_todo_count' => $myTodoCount,
            'my_done_count' => $myDoneCount,
            'my_due_soon_count' => $myDueSoonCount,
            'my_overdue_count' => $myOverdueCount,
            'recent_todo_docs' => $recentTodoDocs,
            'admin' => $adminStats,
        ];
    }

    /**
     * Build My To-Do query matching InboundDocumentController logic
     */
    protected function buildMyTodoQuery($viewer)
    {
        $isAdmin = (strtoupper($viewer->level ?? '') === 'ADMIN');
        $isDg = ($viewer->position && (int) $viewer->position->level === 1);
        $isAssistant = $viewer->hasPermission('inbound-documents-assistant');
        $isReceptionist = $viewer->hasPermission('inbound-documents-receptionist');

        return InboundDocument::whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->where(function ($q) use ($viewer, $isAdmin, $isDg, $isAssistant, $isReceptionist) {
                if ($isAdmin || $isReceptionist) {
                    $q->orWhere('status', 'RECEPTION_DRAFT');
                }
                if ($isAdmin || $isAssistant) {
                    $q->orWhereIn('status', ['SUBMITTED_TO_ASSISTANT', 'DG_ANNOTATED']);
                }
                if ($isAdmin || $isDg) {
                    $q->orWhere('status', 'SUBMITTED_TO_DG');
                }
                $q->orWhere('target_user_id', $viewer->id);
                if ($viewer->office_id) {
                    $q->orWhere(function ($oq) use ($viewer) {
                        $oq->where('target_office_id', $viewer->office_id)
                           ->whereNull('target_user_id');
                    });
                }
                $isDeptDirector = ($viewer->position && (int)$viewer->position->level <= 4);
                if ($viewer->department_id && ($isDeptDirector || empty($viewer->office_id))) {
                    $q->orWhere(function ($dq) use ($viewer) {
                        $dq->where('target_department_id', $viewer->department_id)
                           ->whereNull('target_office_id')
                           ->whereNull('target_user_id');
                    });
                }
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
     * Leave Requests Calculation
     */
    protected function calculateLeaveStats($viewer, bool $isAdmin, string $todayDateStr): array
    {
        $myPending = LeaveRequest::where('user_id', $viewer->id)
            ->whereIn('status', ['PENDING', 'DRAFT'])
            ->count();

        $currentYear = Carbon::parse($todayDateStr)->year;
        $myApprovedDaysThisYear = (float) LeaveRequest::where('user_id', $viewer->id)
            ->where('status', 'APPROVED')
            ->whereYear('start_date', $currentYear)
            ->sum('duration_days');

        $myRecentLeaves = LeaveRequest::where('user_id', $viewer->id)
            ->orderBy('id', 'desc')
            ->take(3)
            ->get(['id', 'leave_type', 'start_date', 'end_date', 'duration_days', 'status', 'current_stage']);

        // Approvals pending for supervisors or admin
        $pendingApprovalsCount = 0;
        if ($isAdmin) {
            $pendingApprovalsCount = LeaveRequest::where('status', 'PENDING')->count();
        } else {
            $pendingApprovalsCount = LeaveRequest::where('status', 'PENDING')
                ->where('current_approver_id', $viewer->id)
                ->count();
        }

        // People on approved leave today
        $onLeaveTodayQuery = LeaveRequest::with(['user:id,name,name_kh,profile_image,department_id', 'user.department:id,name_kh'])
            ->where('status', 'APPROVED')
            ->whereDate('start_date', '<=', $todayDateStr)
            ->whereDate('end_date', '>=', $todayDateStr);

        $onLeaveTodayCount = (clone $onLeaveTodayQuery)->count();
        $onLeaveTodayList = $onLeaveTodayQuery->take(5)->get(['id', 'user_id', 'leave_type', 'start_date', 'end_date', 'duration_days']);

        return [
            'my_pending_count' => $myPending,
            'my_approved_days_year' => $myApprovedDaysThisYear,
            'my_recent_leaves' => $myRecentLeaves,
            'pending_approvals_count' => $pendingApprovalsCount,
            'on_leave_today_count' => $onLeaveTodayCount,
            'on_leave_today_list' => $onLeaveTodayList,
        ];
    }

    /**
     * Weekly Reports Calculation
     */
    protected function calculateWeeklyReportStats($viewer, bool $isAdmin, Carbon $today): array
    {
        $year = $today->year;
        $weekNumber = $today->weekOfYear;

        $myReportThisWeek = WeeklyReport::where('user_id', $viewer->id)
            ->where('year', $year)
            ->where('week_number', $weekNumber)
            ->first(['id', 'title', 'status', 'submitted_at', 'week_number', 'month', 'year']);

        $pendingReviewsCount = 0;
        if ($isAdmin) {
            $pendingReviewsCount = WeeklyReport::where('status', 'SUBMITTED')->count();
        } elseif ($viewer->position && (int) $viewer->position->level <= 5 && $viewer->department_id) {
            $pendingReviewsCount = WeeklyReport::where('status', 'SUBMITTED')
                ->where('department_id', $viewer->department_id)
                ->where('user_id', '!=', $viewer->id)
                ->count();
        }

        $submittedThisWeekCount = WeeklyReport::where('year', $year)
            ->where('week_number', $weekNumber)
            ->where('status', 'SUBMITTED')
            ->count();

        return [
            'current_week_number' => $weekNumber,
            'current_year' => $year,
            'my_report_this_week' => $myReportThisWeek,
            'pending_reviews_count' => $pendingReviewsCount,
            'submitted_this_week_count' => $submittedThisWeekCount,
        ];
    }

    /**
     * Room Bookings & Work Schedules Calculation
     */
    protected function calculateRoomAndScheduleStats($viewer, bool $isAdmin, string $todayDateStr): array
    {
        // Today's Approved Room Bookings
        $todayBookings = RoomBooking::with([
                'room:id,name,capacity,location',
                'preferredRoom:id,name,capacity,location',
                'user:id,name,name_kh,profile_image'
            ])
            ->whereDate('booking_date', $todayDateStr)
            ->where('status', 'APPROVED')
            ->orderBy('start_time')
            ->get(['id', 'room_id', 'preferred_room_id', 'user_id', 'title', 'booking_date', 'start_time', 'end_time', 'participants_count', 'status']);

        // Today's Work Schedules / Meetings for user or public
        $todaySchedules = WorkSchedule::where(function ($q) use ($viewer) {
                $q->where('user_id', $viewer->id)
                  ->orWhere('is_all_day', 1);
            })
            ->whereDate('start_time', '<=', $todayDateStr)
            ->whereDate('end_time', '>=', $todayDateStr)
            ->orderBy('start_time')
            ->take(5)
            ->get(['id', 'title', 'type', 'start_time', 'end_time', 'location', 'priority', 'status', 'is_all_day']);

        $pendingRoomBookingsCount = RoomBooking::where('status', 'PENDING')->count();

        return [
            'today_bookings' => $todayBookings,
            'today_schedules' => $todaySchedules,
            'pending_room_bookings_count' => $pendingRoomBookingsCount,
        ];
    }

    /**
     * Attendance Statistics Calculation
     */
    protected function calculateAttendanceStats($viewer, bool $isAdmin, string $todayDateStr): array
    {
        $myAttendanceToday = Attendance::where('user_id', $viewer->id)
            ->whereDate('date', $todayDateStr)
            ->first(['id', 'date', 'status', 'check_in_time', 'check_out_time', 'note']);

        // Adjust LATE vs PRESENT based on <= 09:00 logic
        if ($myAttendanceToday && $myAttendanceToday->status === 'LATE' && $myAttendanceToday->check_in_time) {
            $timeOnly = Carbon::parse($myAttendanceToday->check_in_time)->format('H:i');
            if ($timeOnly <= '09:00') {
                $myAttendanceToday->status = 'PRESENT';
            }
        }

        // Overall summary today
        $attendancesToday = Attendance::whereDate('date', $todayDateStr)->get();
        $totalUsers = User::where('status', '!=', 'DISABLED')->count();

        $presentCount = 0;
        $lateCount = 0;
        $absentOrPermissionCount = 0;
        $missionCount = 0;

        foreach ($attendancesToday as $att) {
            $st = $att->status;
            if ($st === 'LATE' && $att->check_in_time && Carbon::parse($att->check_in_time)->format('H:i') <= '09:00') {
                $st = 'PRESENT';
            }

            if ($st === 'PRESENT') {
                $presentCount++;
            } elseif ($st === 'LATE') {
                $lateCount++;
            } elseif (in_array($st, ['ABSENT', 'PERMISSION'])) {
                $absentOrPermissionCount++;
            } elseif ($st === 'MISSION') {
                $missionCount++;
            }
        }

        return [
            'my_attendance' => $myAttendanceToday,
            'summary_today' => [
                'total_employees' => $totalUsers,
                'present' => $presentCount,
                'late' => $lateCount,
                'absent_permission' => $absentOrPermissionCount,
                'mission' => $missionCount,
            ]
        ];
    }
}