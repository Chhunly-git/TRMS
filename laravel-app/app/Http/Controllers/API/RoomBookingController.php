<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MeetingRoom;
use App\Models\RoomBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomBookingController extends Controller
{
    /**
     * ទាញយកបញ្ជីការកក់បន្ទប់ប្រជុំ (មាន Filter)
     */
    public function index(Request $request)
    {
        $query = RoomBooking::with([
            'user:id,name,name_kh,name_en,profile_image',
            'room:id,name,location,capacity,color',
            'preferredRoom:id,name,location,capacity',
            'approver:id,name,name_kh,name_en'
        ]);

        $user = $request->user();

        // Filter តាមបន្ទប់
        if ($request->filled('room_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('room_id', $request->room_id)
                  ->orWhere('preferred_room_id', $request->room_id);
            });
        }

        // Filter តាមស្ថានភាព
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        // Filter តាមកាលបរិច្ឆេទជាក់លាក់
        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->date);
        } elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereYear('booking_date', $request->year)
                  ->whereMonth('booking_date', $request->month);
        } elseif ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('booking_date', [$request->from_date, $request->to_date]);
        }

        // Filter តាមពាក្យគន្លឹះ (ប្រធានបទ, អ្នកដឹកនាំ, អ្នកស្នើសុំ)
        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                  ->orWhere('leader_name', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%")
                  ->orWhereHas('user', function ($uq) use ($kw) {
                      $uq->where('name', 'like', "%{$kw}%")
                         ->orWhere('name_kh', 'like', "%{$kw}%")
                         ->orWhere('email', 'like', "%{$kw}%");
                  });
            });
        }

        // បញ្ជាលំដាប់លំដោយ
        $bookings = $query->orderBy('booking_date', 'desc')
                          ->orderBy('start_time', 'asc')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200);
    }

    /**
     * ទាញយកកាលវិភាគបន្ទប់ប្រជុំទាំងអស់សម្រាប់ Timetable View
     */
    public function timetable(Request $request)
    {
        $rooms = MeetingRoom::with('manager:id,name,name_kh,name_en,profile_image')
            ->where('status', 'ACTIVE')
            ->orderBy('capacity', 'asc')
            ->get();

        $query = RoomBooking::with([
            'user:id,name,name_kh,name_en,profile_image',
            'room:id,name,location,capacity,color',
            'preferredRoom:id,name,location,capacity'
        ]);

        // Filter តាមបន្ទប់
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter តាមកាលបរិច្ឆេទ / ខែ
        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->date);
        } elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereYear('booking_date', $request->year)
                  ->whereMonth('booking_date', $request->month);
        } elseif ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('booking_date', [$request->from_date, $request->to_date]);
        }

        // កាលវិភាគបន្ទប់ប្រជុំ បង្ហាញតែការកក់ដែលបានអនុម័ត (APPROVED) និងមានបន្ទប់កំណត់រួចរាល់ប៉ុណ្ណោះ
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        } else {
            $query->where('status', 'APPROVED');
        }
        $query->whereNotNull('room_id');

        $bookings = $query->orderBy('booking_date', 'asc')
                          ->orderBy('start_time', 'asc')
                          ->get();

        $totalApproved = $bookings->where('status', 'APPROVED')->count();
        $totalPending = $bookings->where('status', 'PENDING')->count();

        return response()->json([
            'success' => true,
            'data' => $bookings,
            'bookings' => $bookings,
            'rooms' => $rooms,
            'summary' => [
                'total_rooms' => $rooms->count(),
                'approved_bookings' => $totalApproved,
                'pending_bookings' => $totalPending,
            ]
        ], 200);
    }

    /**
     * បញ្ជីការកក់របស់មន្ត្រីផ្ទាល់ខ្លួន (My Bookings)
     */
    public function myBookings(Request $request)
    {
        $user = $request->user();

        $query = RoomBooking::with([
            'room:id,name,location,capacity,color',
            'preferredRoom:id,name,location,capacity',
            'approver:id,name,name_kh'
        ])
        ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('search')) {
            $kw = $request->search;
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                  ->orWhere('leader_name', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%");
            });
        }

        $bookings = $query->orderBy('booking_date', 'desc')
                          ->orderBy('start_time', 'desc')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200);
    }

    /**
     * ជំនួយការសម្រួលឈ្មោះ field (title <-> subject, leader_name <-> leader, etc.)
     */
    protected function normalizeBookingInput(Request $request): void
    {
        if ($request->filled('subject') && !$request->filled('title')) {
            $request->merge(['title' => $request->subject]);
        }
        if ($request->filled('leader') && !$request->filled('leader_name')) {
            $request->merge(['leader_name' => $request->leader]);
        }
        if ($request->filled('meeting_date') && !$request->filled('booking_date')) {
            $request->merge(['booking_date' => $request->meeting_date]);
        }
        if ($request->filled('start_datetime') && !$request->filled('booking_date')) {
            $dt = Carbon::parse($request->start_datetime);
            $request->merge([
                'booking_date' => $dt->toDateString(),
                'start_time' => $dt->format('H:i')
            ]);
        }
        if ($request->filled('end_datetime') && !$request->filled('end_time')) {
            $dt = Carbon::parse($request->end_datetime);
            $request->merge(['end_time' => $dt->format('H:i')]);
        }
        if ($request->has('required_equipment') && !$request->has('equipment_needed')) {
            $eq = $request->required_equipment;
            $request->merge(['equipment_needed' => is_array($eq) ? json_encode($eq, JSON_UNESCAPED_UNICODE) : $eq]);
        }
        if ($request->filled('notes') && !$request->filled('description')) {
            $request->merge(['description' => $request->notes]);
        }
        if ($request->filled('admin_note') && !$request->filled('manager_note')) {
            $request->merge(['manager_note' => $request->admin_note]);
        }
    }

    /**
     * ដាក់ពាក្យស្នើសុំកក់បន្ទប់ប្រជុំថ្មី
     */
    public function store(Request $request)
    {
        $this->normalizeBookingInput($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'leader_name' => 'required|string|max:255',
            'booking_date' => 'required|date',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'end_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'participants_count' => 'required|integer|min:1',
            'preferred_room_id' => 'nullable|integer|exists:meeting_rooms,id',
            'equipment_needed' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $startDatetime = Carbon::parse("{$request->booking_date} {$request->start_time}");
        $endDatetime = Carbon::parse("{$request->booking_date} {$request->end_time}");

        if ($endDatetime->lte($startDatetime)) {
            return response()->json([
                'success' => false,
                'message' => 'ម៉ោងបញ្ចប់ត្រូវតែនៅក្រោយម៉ោងចាប់ផ្តើម!'
            ], 422);
        }

        $user = $request->user();
        $data = $request->only([
            'title', 'leader_name', 'booking_date', 'start_time', 'end_time',
            'participants_count', 'preferred_room_id', 'equipment_needed', 'description'
        ]);

        $data['user_id'] = $user->id;
        $data['start_datetime'] = $startDatetime;
        $data['end_datetime'] = $endDatetime;
        $data['status'] = 'PENDING';

        // ប្រសិនបើ Admin ឬ Room Manager ជាអ្នកបង្កើត ហើយបានបញ្ជាក់ room_id ផ្ទាល់ អាច auto-approve តែម្តង
        if ($request->filled('room_id') && $user->canManageRooms($request->room_id)) {
            $conflict = RoomBooking::conflictingWith($request->room_id, $startDatetime, $endDatetime)->first();
            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => "បន្ទប់នេះមានការកក់រួចហើយ ({$conflict->start_time} - {$conflict->end_time}: {$conflict->title}) សូមជ្រើសរើសបន្ទប់ ឬម៉ោងផ្សេង!"
                ], 422);
            }
            $data['room_id'] = $request->room_id;
            $data['status'] = 'APPROVED';
            $data['approved_by'] = $user->id;
            $data['approved_at'] = now();
        }

        $booking = RoomBooking::create($data);

        return response()->json([
            'success' => true,
            'message' => 'បានដាក់ពាក្យស្នើសុំកក់បន្ទប់ដោយជោគជ័យ! សូមរង់ចាំការពិនិត្យ និងចាត់ចែងបន្ទប់ពីអ្នកគ្រប់គ្រង។',
            'data' => $booking->load([
                'room:id,name,location,capacity,color',
                'preferredRoom:id,name,location,capacity'
            ])
        ], 201);
    }

    /**
     * មើលព័ត៌មានលម្អិតនៃការកក់
     */
    public function show($id)
    {
        $booking = RoomBooking::with([
            'user:id,name,name_kh,name_en,profile_image',
            'room:id,name,location,capacity,facilities,color',
            'preferredRoom:id,name,location,capacity',
            'approver:id,name,name_kh'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking
        ], 200);
    }

    /**
     * កែសម្រួលព័ត៌មានស្នើសុំកក់ (មន្ត្រីអាចកែបានពេលនៅ PENDING)
     */
    public function update(Request $request, $id)
    {
        $booking = RoomBooking::findOrFail($id);
        $user = $request->user();

        // ពិនិត្យសិទ្ធិ៖ សាមីខ្លួនអាចកែបានពេលនៅ PENDING ឬ Admin/Manager អាចកែបាន
        $isOwner = $booking->user_id === $user->id;
        $isManager = $user->canManageRooms($booking->room_id);

        if (!$isOwner && !$isManager) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិកែប្រែការកក់នេះឡើយ!'
            ], 403);
        }

        if ($isOwner && !$isManager && $booking->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'មិនអាចកែប្រែសំណើដែលបានអនុម័តរួច ឬត្រូវបានបដិសេធឡើយ!'
            ], 422);
        }

        $this->normalizeBookingInput($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'leader_name' => 'required|string|max:255',
            'booking_date' => 'required|date',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'end_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'participants_count' => 'required|integer|min:1',
            'preferred_room_id' => 'nullable|integer|exists:meeting_rooms,id',
            'equipment_needed' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $startDatetime = Carbon::parse("{$request->booking_date} {$request->start_time}");
        $endDatetime = Carbon::parse("{$request->booking_date} {$request->end_time}");

        if ($endDatetime->lte($startDatetime)) {
            return response()->json([
                'success' => false,
                'message' => 'ម៉ោងបញ្ចប់ត្រូវតែនៅក្រោយម៉ោងចាប់ផ្តើម!'
            ], 422);
        }

        // ប្រសិនបើកែប្រែពេល APPROVED ហើយ ត្រូវ check conflict
        if ($booking->status === 'APPROVED' && $booking->room_id) {
            $conflict = RoomBooking::conflictingWith($booking->room_id, $startDatetime, $endDatetime, $booking->id)->first();
            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => "បន្ទប់នេះមានការកក់ជាន់ម៉ោងគ្នា ({$conflict->start_time} - {$conflict->end_time}) មិនអាចប្តូរម៉ោងបានទេ!"
                ], 422);
            }
        }

        $data = $request->only([
            'title', 'leader_name', 'booking_date', 'start_time', 'end_time',
            'participants_count', 'preferred_room_id', 'equipment_needed', 'description'
        ]);

        $data['start_datetime'] = $startDatetime;
        $data['end_datetime'] = $endDatetime;

        $booking->update($data);

        return response()->json([
            'success' => true,
            'message' => 'បានកែសម្រួលសំណើកក់បន្ទប់ដោយជោគជ័យ!',
            'data' => $booking->load([
                'room:id,name,location,capacity,color',
                'preferredRoom:id,name,location,capacity'
            ])
        ], 200);
    }

    /**
     * សាមីខ្លួនស្នើសុំលុប/បោះបង់ការកក់ (Cancel)
     */
    public function cancel(Request $request, $id)
    {
        $booking = RoomBooking::findOrFail($id);
        $user = $request->user();

        if ($booking->user_id !== $user->id && !$user->canManageRooms($booking->room_id)) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិបោះបង់ការកក់នេះឡើយ!'
            ], 403);
        }

        $booking->status = 'CANCELLED';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'បានបោះបង់ការកក់បន្ទប់ដោយជោគជ័យ!',
            'data' => $booking
        ], 200);
    }

    /**
     * លុបទិន្នន័យការកក់ (Delete)
     */
    public function destroy(Request $request, $id)
    {
        $booking = RoomBooking::findOrFail($id);
        $user = $request->user();

        $isOwner = $booking->user_id === $user->id;
        $isManager = $user->canManageRooms($booking->room_id);

        if (!$isOwner && !$isManager) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិលុបការកក់នេះឡើយ!'
            ], 403);
        }

        // សាមីខ្លួនអាចលុបបានតែពេល PENDING ឬ CANCELLED ប៉ុណ្ណោះ
        if ($isOwner && !$isManager && !in_array($booking->status, ['PENDING', 'CANCELLED'])) {
            return response()->json([
                'success' => false,
                'message' => 'មិនអាចលុបការកក់ដែលបានអនុម័តរួចឡើយ! សូមប្រើប្រាស់មុខងារបោះបង់ (Cancel) ជំនួសវិញ។'
            ], 422);
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានលុបទិន្នន័យការកក់ដោយជោគជ័យ!'
        ], 200);
    }

    /**
     * អនុម័ត និងកំណត់បន្ទប់ប្រជុំ (Approve & Assign Room)
     */
    public function approve(Request $request, $id)
    {
        $this->normalizeBookingInput($request);

        $booking = RoomBooking::findOrFail($id);
        $user = $request->user();

        $request->validate([
            'room_id' => 'required|integer|exists:meeting_rooms,id',
            'manager_note' => 'nullable|string',
        ]);

        $roomId = $request->room_id;

        if (!$user->canManageRooms($roomId)) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិគ្រប់គ្រង ឬចាត់តាំងបន្ទប់នេះឡើយ!'
            ], 403);
        }

        // ត្រួតពិនិត្យក្រែងបន្ទប់ដែលត្រូវចាត់តាំងជាន់ម៉ោងនឹងការកក់ដែលបានអនុម័តផ្សេងទៀត
        $conflict = RoomBooking::conflictingWith($roomId, $booking->start_datetime, $booking->end_datetime, $booking->id)->first();
        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => "បន្ទប់នេះមានការកក់ជាន់ម៉ោងគ្នារួចហើយ ({$conflict->start_time} - {$conflict->end_time}: {$conflict->title})! សូមជ្រើសរើសបន្ទប់ផ្សេង។"
            ], 422);
        }

        $booking->room_id = $roomId;
        $booking->status = 'APPROVED';
        $booking->approved_by = $user->id;
        $booking->approved_at = now();
        $booking->manager_note = $request->manager_note;
        $booking->rejection_reason = null;
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'បានអនុម័ត និងចាត់តាំងបន្ទប់ប្រជុំដោយជោគជ័យ!',
            'data' => $booking->load([
                'room:id,name,location,capacity,color',
                'user:id,name,name_kh,profile_image',
                'approver:id,name,name_kh'
            ])
        ], 200);
    }

    /**
     * បដិសេធសំណើកក់បន្ទប់ (Reject)
     */
    public function reject(Request $request, $id)
    {
        $booking = RoomBooking::findOrFail($id);
        $user = $request->user();

        if (!$user->canManageRooms($booking->room_id ?: $booking->preferred_room_id)) {
            return response()->json([
                'success' => false,
                'message' => 'លោកអ្នកមិនមានសិទ្ធិបដិសេធការកក់នេះឡើយ!'
            ], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $booking->status = 'REJECTED';
        $booking->rejection_reason = $request->rejection_reason;
        $booking->approved_by = $user->id;
        $booking->approved_at = now();
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'បានបដិសេធសំណើកក់បន្ទប់ដោយជោគជ័យ!',
            'data' => $booking->load([
                'room:id,name,location,capacity,color',
                'user:id,name,name_kh,profile_image',
                'approver:id,name,name_kh'
            ])
        ], 200);
    }
}
