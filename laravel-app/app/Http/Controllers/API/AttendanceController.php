<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    // GET: ទាញយកបញ្ជីវត្តមាន និងមន្ត្រីទាំងអស់
public function index(Request $request)
{
    $date = $request->input('date', Carbon::today()->toDateString());
    
    $users = \App\Models\User::with(['department', 'position'])
        ->join('positions', 'users.position_id', '=', 'positions.id')
        ->orderBy('positions.level', 'asc')
        ->select('users.*')
        ->get();

    $attendances = Attendance::where('date', $date)->get()->keyBy('user_id');

    $result = $users->map(function ($user) use ($attendances, $date) {
        $attendance = $attendances->get($user->id);
        
        $status = 'PRESENT';
        $checkIn = $attendance ? $attendance->check_in_time : '08:00';

        if ($attendance) {
            $status = $attendance->status;
            
            // កែសម្រួលត្រង់នេះ៖ ប្រើប្រាស់សញ្ញា > ធម្មតា (ចាប់ពី 09:01 បាន LATE)
            if ($checkIn && Carbon::parse($checkIn)->format('H:i') > '09:00') {
                $status = 'LATE';
            } else {
                // ຖ້າម៉ោង 09:00 ស្មើ ឬតិចជាង គឺบังคับឱ្យជា PRESENT
                $status = 'PRESENT';
            }
        }

        return [
            'id' => $attendance ? $attendance->id : null,
            'user_id' => $user->id,
            'date' => $date,
            'status' => $status,
            'check_in_time' => $checkIn,
            'check_out_time' => $attendance ? $attendance->check_out_time : null,
            'note' => $attendance ? $attendance->note : null,
            'user' => $user
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $result
    ]);
}

    // POST: កត់ត្រាវត្តមាន (រួមទាំង Note/មូលហេតុ)
public function store(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'date' => 'required|date',
        'status' => 'required|string',
        'check_in_time' => 'nullable',
        'check_out_time' => 'nullable',
        'note' => 'nullable|string'
    ]);

    $status = $validated['status'];
    $checkIn = ($status === 'PRESENT' || $status === 'LATE') ? $validated['check_in_time'] : null;
    $checkOut = ($status === 'PRESENT' || $status === 'LATE') ? $validated['check_out_time'] : null;

    // Auto Late Logic: បើ STATUS គឺ PRESENT តែម៉ោងចូលលើស 09:00 កែជា LATE
    if ($status === 'PRESENT' && $checkIn && Carbon::parse($checkIn)->format('H:i') >= '09:00') {
        $status = 'LATE';
    }

    Attendance::updateOrCreate(
        ['user_id' => $validated['user_id'], 'date' => $validated['date']],
        [
            'status' => $status,
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'note' => $validated['note']
        ]
    );

    return response()->json(['success' => true]);
}

    // GET: បញ្ជីសង្ខេបសម្រាប់ Dashboard (Public Route)
public function getDashboardSummary(Request $request)
{
    $date = $request->input('date', today());

    $attendances = Attendance::with('user.department', 'user.position')
        ->where('date', $date)
        ->get();

    // កែសម្រួលត្រង់នេះ៖ ដកសញ្ញា $ ចេញពីមុខពាក្យ function
    $attendances->transform(function ($attendance) {
        $checkIn = $attendance->check_in_time;
        
        // ប្រសិនបើ Status ជា LATE តែម៉ោងចូលតិចជាង ឬស្មើ 09:00 ឱ្យកែជា PRESENT វិញក្នុង Response
        if ($attendance->status === 'LATE' && $checkIn) {
            $timeOnly = \Carbon\Carbon::parse($checkIn)->format('H:i');
            if ($timeOnly <= '09:00') {
                $attendance->status = 'PRESENT';
            }
        }
        
        return $attendance;
    });

    return response()->json([
        'success' => true,
        'attendances' => $attendances
    ]);
}

// ទាញយកប្រវត្តវត្តមានរបស់ User ម្នាក់ៗ (អាច Filter តាមខែ ឬឆ្នាំបាន)
public function myAttendances(Request $request)
{
    $user = $request->user(); // User ដែលកំពុង Login
    $month = $request->input('month', Carbon::now()->format('Y-m'));

    $attendances = Attendance::where('user_id', $user->id)
        ->where('date', 'LIKE', "$month%")
        ->orderBy('date', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $attendances
    ]);
}
}