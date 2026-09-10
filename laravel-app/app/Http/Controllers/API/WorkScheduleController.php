<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkScheduleController extends Controller
{
    /**
     * ទាញយកបញ្ជីកាលវិភាគការងារ (Work Schedules)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = WorkSchedule::with('user:id,name,name_kh,name_en,profile_image');

        // កំណត់សិទ្ធិមើល៖ Admin អាចមើលទាំងអស់ ឬមើលតាមមន្ត្រីជាក់លាក់; User មើលតែកាលវិភាគខ្លួនឯង
        if ($user->level === 'ADMIN' && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } elseif ($user->level === 'ADMIN' && $request->get('scope') === 'all') {
            // Admin មើលកាលវិភាគទាំងអស់
        } else {
            $query->where('user_id', $user->id);
        }

        // Filter តាមខែ និងឆ្នាំ (Month & Year) ឧ. 2026-09
        if ($request->filled('month_year')) {
            try {
                $dt = Carbon::parse($request->month_year);
                $query->whereYear('start_time', $dt->year)
                      ->whereMonth('start_time', $dt->month);
            } catch (Exception $e) {}
        } elseif ($request->filled('year') && $request->filled('month')) {
            $query->whereYear('start_time', $request->year)
                  ->whereMonth('start_time', $request->month);
        }

        // Filter តាមចន្លោះកាលបរិច្ឆេទ (Start & End Date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_time', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        // Filter តាមប្រភេទ (Type)
        if ($request->filled('type')) {
            $query->where('type', strtoupper($request->type));
        }

        // Filter តាមស្ថានភាព (Status)
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        // Filter តាមកម្រិតអាទិភាព (Priority)
        if ($request->filled('priority')) {
            $p = strtoupper($request->priority);
            if ($p === 'MEDIUM') $p = 'NORMAL';
            $query->where('priority', $p);
        }

        // ស្វែងរកតាមពាក្យគន្លឹះ (Keyword)
        if ($request->filled('keyword') || $request->filled('search')) {
            $kw = $request->keyword ?: $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                  ->orWhere('location', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        $schedules = $query->orderBy('start_time', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ], 200);
    }

    /**
     * ស្ថិតិសង្ខេបកាលវិភាគប្រចាំខែ (Monthly Summary Stats)
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        $targetUserId = ($user->level === 'ADMIN' && $request->filled('user_id')) 
            ? $request->user_id 
            : $user->id;

        if ($request->filled('month_year')) {
            $targetMonth = Carbon::parse($request->month_year);
        } elseif ($request->filled('year') && $request->filled('month')) {
            $targetMonth = Carbon::createFromDate($request->year, $request->month, 1);
        } else {
            $targetMonth = Carbon::now();
        }

        $baseQuery = WorkSchedule::where('user_id', $targetUserId);
        if ($user->level === 'ADMIN' && $request->get('scope') === 'all') {
            $baseQuery = WorkSchedule::query();
        }

        $monthQuery = (clone $baseQuery)
            ->whereYear('start_time', $targetMonth->year)
            ->whereMonth('start_time', $targetMonth->month);

        $totalMonth = (clone $monthQuery)->count();
        $meetings = (clone $monthQuery)->where('type', 'MEETING')->count();
        $missionsAndWorkshops = (clone $monthQuery)->whereIn('type', ['MISSION', 'WORKSHOP'])->count();
        $completed = (clone $monthQuery)->where('status', 'COMPLETED')->count();
        
        $upcoming = (clone $baseQuery)
            ->where('start_time', '>=', now())
            ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalMonth,
                'total_month' => $totalMonth,
                'meetings' => $meetings,
                'missions' => $missionsAndWorkshops,
                'missions_workshops' => $missionsAndWorkshops,
                'completed' => $completed,
                'upcoming' => $upcoming,
                'current_month_name' => $targetMonth->translatedFormat('F Y'),
            ]
        ], 200);
    }

    /**
     * មើលព័ត៌មានកាលវិភាគតែមួយ
     */
    public function show(Request $request, $id)
    {
        $schedule = WorkSchedule::with('user:id,name,name_kh,name_en,profile_image')
            ->findOrFail($id);

        $this->checkScheduleAccess($request->user(), $schedule);

        return response()->json([
            'success' => true,
            'data' => $schedule
        ], 200);
    }

    /**
     * បង្កើតកាលវិភាគការងារថ្មី
     */
    public function store(Request $request)
    {
        if ($request->has('start_datetime') && !$request->has('start_time')) {
            $request->merge(['start_time' => $request->start_datetime]);
        }
        if ($request->has('end_datetime') && !$request->has('end_time')) {
            $request->merge(['end_time' => $request->end_datetime]);
        }
        if ($request->has('venue') && !$request->has('location')) {
            $request->merge(['location' => $request->venue]);
        }
        if ($request->has('all_day') && !$request->has('is_all_day')) {
            $request->merge(['is_all_day' => $request->boolean('all_day')]);
        }
        if ($request->has('type')) {
            $request->merge(['type' => strtoupper($request->type)]);
        }
        if ($request->has('status')) {
            $request->merge(['status' => strtoupper($request->status)]);
        }
        if ($request->has('priority')) {
            $p = strtoupper($request->priority);
            if ($p === 'MEDIUM') $p = 'NORMAL';
            $request->merge(['priority' => $p]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:MEETING,MISSION,WORKSHOP,TASK,APPOINTMENT,OTHER',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'is_all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:SCHEDULED,IN_PROGRESS,COMPLETED,POSTPONED,CANCELLED',
            'priority' => 'nullable|string|in:LOW,NORMAL,HIGH,URGENT',
            'color' => 'nullable|string|max:20',
            'remind_at' => 'nullable|date',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $user = $request->user();
        $targetUserId = ($user->level === 'ADMIN' && $request->filled('user_id')) 
            ? $request->user_id 
            : $user->id;

        $data = $request->only([
            'title', 'type', 'start_time', 'end_time', 'is_all_day',
            'location', 'meeting_link', 'description', 'status',
            'priority', 'color', 'remind_at'
        ]);

        $data['user_id'] = $targetUserId;
        $data['status'] = $data['status'] ?? 'SCHEDULED';
        $data['priority'] = $data['priority'] ?? 'NORMAL';
        $data['color'] = $data['color'] ?? $this->getDefaultColorForType($data['type']);
        $data['is_all_day'] = $request->boolean('is_all_day', false);

        $schedule = WorkSchedule::create($data);

        return response()->json([
            'success' => true,
            'message' => 'បានបង្កើតកាលវិភាគថ្មីដោយជោគជ័យ',
            'data' => $schedule->load('user:id,name,name_kh,name_en,profile_image')
        ], 201);
    }

    /**
     * កែសម្រួលកាលវិភាគការងារ
     */
    public function update(Request $request, $id)
    {
        $schedule = WorkSchedule::findOrFail($id);
        $this->checkScheduleAccess($request->user(), $schedule);

        if ($request->has('start_datetime') && !$request->has('start_time')) {
            $request->merge(['start_time' => $request->start_datetime]);
        }
        if ($request->has('end_datetime') && !$request->has('end_time')) {
            $request->merge(['end_time' => $request->end_datetime]);
        }
        if ($request->has('venue') && !$request->has('location')) {
            $request->merge(['location' => $request->venue]);
        }
        if ($request->has('all_day') && !$request->has('is_all_day')) {
            $request->merge(['is_all_day' => $request->boolean('all_day')]);
        }
        if ($request->has('type')) {
            $request->merge(['type' => strtoupper($request->type)]);
        }
        if ($request->has('status')) {
            $request->merge(['status' => strtoupper($request->status)]);
        }
        if ($request->has('priority')) {
            $p = strtoupper($request->priority);
            if ($p === 'MEDIUM') $p = 'NORMAL';
            $request->merge(['priority' => $p]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:MEETING,MISSION,WORKSHOP,TASK,APPOINTMENT,OTHER',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'is_all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:SCHEDULED,IN_PROGRESS,COMPLETED,POSTPONED,CANCELLED',
            'priority' => 'nullable|string|in:LOW,NORMAL,HIGH,URGENT',
            'color' => 'nullable|string|max:20',
            'remind_at' => 'nullable|date',
        ]);

        $data = $request->only([
            'title', 'type', 'start_time', 'end_time', 'is_all_day',
            'location', 'meeting_link', 'description', 'status',
            'priority', 'color', 'remind_at'
        ]);

        $data['is_all_day'] = $request->boolean('is_all_day', false);
        $schedule->update($data);

        return response()->json([
            'success' => true,
            'message' => 'បានកែសម្រួលកាលវិភាគដោយជោគជ័យ',
            'data' => $schedule->load('user:id,name,name_kh,name_en,profile_image')
        ], 200);
    }

    /**
     * កែប្រែស្ថានភាពរហ័ស (Quick Status Update)
     */
    public function updateStatus(Request $request, $id)
    {
        $schedule = WorkSchedule::findOrFail($id);
        $this->checkScheduleAccess($request->user(), $schedule);

        if ($request->has('status')) {
            $request->merge(['status' => strtoupper($request->status)]);
        }

        $request->validate([
            'status' => 'required|string|in:SCHEDULED,IN_PROGRESS,COMPLETED,POSTPONED,CANCELLED',
        ]);

        $schedule->status = $request->status;
        $schedule->save();

        return response()->json([
            'success' => true,
            'message' => 'បានផ្លាស់ប្តូរស្ថានភាពកាលវិភាគដោយជោគជ័យ',
            'data' => $schedule
        ], 200);
    }

    /**
     * លុបកាលវិភាគ
     */
    public function destroy(Request $request, $id)
    {
        $schedule = WorkSchedule::findOrFail($id);
        $this->checkScheduleAccess($request->user(), $schedule);

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានលុបកាលវិភាគដោយជោគជ័យ'
        ], 200);
    }

    /**
     * ផ្ទៀងផ្ទាត់សិទ្ធិកែប្រែ ឬលុបកាលវិភាគ
     */
    private function checkScheduleAccess($user, WorkSchedule $schedule): void
    {
        if ($user->level === 'ADMIN') {
            return;
        }

        if ($schedule->user_id !== $user->id) {
            abort(403, 'អ្នកមិនមានសិទ្ធិកែប្រែកាលវិភាគរបស់អ្នកដទៃឡើយ។');
        }
    }

    /**
     * កំណត់ពណ៌លំនាំដើមតាមប្រភេទកាលវិភាគ
     */
    private function getDefaultColorForType(string $type): string
    {
        return match ($type) {
            'MEETING' => '#3b82f6', // ពណ៌ខៀវ (Blue)
            'MISSION' => '#f59e0b', // ពណ៌លឿងទុំ (Amber/Orange)
            'WORKSHOP' => '#8b5cf6', // ពណ៌ស្វាយ (Purple)
            'TASK' => '#10b981', // ពណ៌បៃតង (Emerald)
            'APPOINTMENT' => '#ec4899', // ពណ៌ផ្កាឈូក (Pink)
            default => '#6b7280', // ពណ៌ប្រផេះ (Gray)
        };
    }
}
