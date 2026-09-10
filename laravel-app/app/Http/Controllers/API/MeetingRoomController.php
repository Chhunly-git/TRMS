<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use App\Models\RoomBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MeetingRoomController extends Controller
{
    /**
     * ទាញយកបញ្ជីបន្ទប់ប្រជុំទាំងអស់
     */
    public function index(Request $request)
    {
        $query = MeetingRoom::with('manager:id,name,name_kh,name_en,profile_image');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } elseif (!$request->boolean('all')) {
            // តាមលំនាំដើមបង្ហាញបន្ទប់ដែលកំពុងដំណើរការ
            $query->where('status', 'ACTIVE');
        }

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('location', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        $rooms = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $rooms
        ], 200);
    }

    /**
     * ពិនិត្យមើលភាពទំនេរនៃបន្ទប់ប្រជុំទាំងអស់សម្រាប់ចន្លោះកាលបរិច្ឆេទ & ម៉ោងជាក់លាក់
     * (ប្រើប្រាស់ក្នុងផ្ទាំងអ្នកគ្រប់គ្រងចាត់តាំងបន្ទប់)
     */
    public function checkAvailability(Request $request)
    {
        // Normalize request inputs if start_datetime/end_datetime or booking_date are provided
        if (!$request->filled('date') && $request->filled('booking_date')) {
            $request->merge(['date' => $request->booking_date]);
        }

        if ((!$request->filled('date') || !$request->filled('start_time')) && $request->filled('start_datetime')) {
            try {
                $startDt = Carbon::parse($request->start_datetime);
                if (!$request->filled('date')) {
                    $request->merge(['date' => $startDt->toDateString()]);
                }
                if (!$request->filled('start_time')) {
                    $request->merge(['start_time' => $startDt->format('H:i')]);
                }
            } catch (\Exception $e) {
                // let validator handle
            }
        }

        if (!$request->filled('end_time') && $request->filled('end_datetime')) {
            try {
                $endDt = Carbon::parse($request->end_datetime);
                $request->merge(['end_time' => $endDt->format('H:i')]);
            } catch (\Exception $e) {
                // let validator handle
            }
        }

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'exclude_booking_id' => 'nullable|integer',
        ]);

        $startDatetime = Carbon::parse("{$request->date} {$request->start_time}");
        $endDatetime = Carbon::parse("{$request->date} {$request->end_time}");
        $excludeId = $request->exclude_booking_id;

        $rooms = MeetingRoom::with('manager:id,name,name_kh,name_en,profile_image')
            ->where('status', 'ACTIVE')
            ->orderBy('capacity', 'asc')
            ->get();

        $results = $rooms->map(function ($room) use ($startDatetime, $endDatetime, $excludeId) {
            $conflicts = RoomBooking::with('user:id,name,name_kh')
                ->where('room_id', $room->id)
                ->where('status', 'APPROVED')
                ->where(function ($q) use ($startDatetime, $endDatetime) {
                    $q->where('start_datetime', '<', $endDatetime)
                      ->where('end_datetime', '>', $startDatetime);
                })
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->get();

            $conflictList = $conflicts->map(function ($c) {
                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'subject' => $c->title,
                    'leader_name' => $c->leader_name,
                    'leader' => $c->leader_name,
                    'start_time' => $c->start_time,
                    'end_time' => $c->end_time,
                    'start_datetime' => $c->start_datetime ? (is_string($c->start_datetime) ? $c->start_datetime : $c->start_datetime->toIso8601String()) : null,
                    'end_datetime' => $c->end_datetime ? (is_string($c->end_datetime) ? $c->end_datetime : $c->end_datetime->toIso8601String()) : null,
                    'user_name' => $c->user?->name_kh ?? $c->user?->name ?? 'មន្ត្រី',
                ];
            })->values()->all();

            $firstConflict = !empty($conflictList) ? $conflictList[0] : null;

            return [
                'id' => $room->id,
                'name' => $room->name,
                'location' => $room->location,
                'capacity' => $room->capacity,
                'facilities' => $room->facilities_list,
                'color' => $room->color,
                'status' => $room->status,
                'is_available' => empty($conflictList),
                'conflict' => $firstConflict,
                'conflicts' => $conflictList,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results
        ], 200);
    }

    /**
     * មើលព័ត៌មានលម្អិតបន្ទប់ប្រជុំតែមួយ
     */
    public function show($id)
    {
        $room = MeetingRoom::with([
            'manager:id,name,name_kh,name_en,profile_image',
            'bookings' => function ($q) {
                $q->where('status', 'APPROVED')
                  ->where('booking_date', '>=', now()->toDateString())
                  ->orderBy('start_datetime', 'asc')
                  ->take(10);
            }
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $room
        ], 200);
    }

    /**
     * បង្កើតបន្ទប់ប្រជុំថ្មី
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->canManageRooms()) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិបង្កើតបន្ទប់ប្រជុំឡើយ!'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable',
            'color' => 'nullable|string|max:20',
            'manager_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable|string|in:ACTIVE,MAINTENANCE,INACTIVE',
            'description' => 'nullable|string',
        ]);

        $data = $request->only([
            'name', 'location', 'capacity', 'facilities', 'color',
            'manager_id', 'status', 'description'
        ]);

        if (is_array($data['facilities'] ?? null)) {
            $data['facilities'] = json_encode($data['facilities'], JSON_UNESCAPED_UNICODE);
        }

        $data['status'] = $data['status'] ?? 'ACTIVE';
        $data['color'] = $data['color'] ?? '#3b82f6';

        $room = MeetingRoom::create($data);

        return response()->json([
            'success' => true,
            'message' => 'បានបង្កើតបន្ទប់ប្រជុំថ្មីដោយជោគជ័យ!',
            'data' => $room->load('manager:id,name,name_kh,name_en,profile_image')
        ], 201);
    }

    /**
     * កែសម្រួលព័ត៌មានបន្ទប់ប្រជុំ
     */
    public function update(Request $request, $id)
    {
        $room = MeetingRoom::findOrFail($id);
        $user = $request->user();

        if (!$user->canManageRooms($room->id)) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិកែប្រែបន្ទប់ប្រជុំនេះឡើយ!'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable',
            'color' => 'nullable|string|max:20',
            'manager_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable|string|in:ACTIVE,MAINTENANCE,INACTIVE',
            'description' => 'nullable|string',
        ]);

        $data = $request->only([
            'name', 'location', 'capacity', 'facilities', 'color',
            'manager_id', 'status', 'description'
        ]);

        if (is_array($data['facilities'] ?? null)) {
            $data['facilities'] = json_encode($data['facilities'], JSON_UNESCAPED_UNICODE);
        }

        $room->update($data);

        return response()->json([
            'success' => true,
            'message' => 'បានកែសម្រួលបន្ទប់ប្រជុំដោយជោគជ័យ!',
            'data' => $room->load('manager:id,name,name_kh,name_en,profile_image')
        ], 200);
    }

    /**
     * លុបបន្ទប់ប្រជុំ
     */
    public function destroy(Request $request, $id)
    {
        $room = MeetingRoom::findOrFail($id);
        $user = $request->user();

        if (!$user->canManageRooms($room->id)) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិលុបបន្ទប់ប្រជុំនេះឡើយ!'
            ], 403);
        }

        // ត្រួតពិនិត្យក្រែងមានការកក់ដែលកំពុងដំណើរការ ឬរង់ចាំ
        $activeBookingsCount = RoomBooking::where('room_id', $room->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where('booking_date', '>=', now()->toDateString())
            ->count();

        if ($activeBookingsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "មិនអាចលុបបន្ទប់នេះបានទេ ព្រោះមានការកក់ចំនួន {$activeBookingsCount} ដែលកំពុងរង់ចាំ ឬបានអនុម័តរួច!"
            ], 422);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានលុបបន្ទប់ប្រជុំដោយជោគជ័យ!'
        ], 200);
    }
}
