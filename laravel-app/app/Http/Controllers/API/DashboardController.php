<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        if ($request->user()->level !== 'ADMIN' && !$request->user()->hasPermission('dashboard')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. អ្នកមិនមានសិទ្ធិចូលមើលទិន្នន័យនេះទេ។'
            ], 403);
        }

        $stats = [
            'totalEmployees' => User::count(),
            'fEmployees' => User::where('gender', 'Female')->count(),
            'civilServants' => User::where('employee_type', 'CIVIL_SERVICE')->count(),
            'fcivilServants' => User::where('employee_type', 'CIVIL_SERVICE')->where('gender', 'Female')->count(),
            'statutory' => User::where('employee_type', 'STATUTORY')->count(),
            'fstatutory' => User::where('employee_type', 'STATUTORY')->where('gender', 'Female')->count(),
            'contractStaff' => User::where('employee_type', 'CONTRACT')->count(),
            'fcontractStaff' => User::where('employee_type', 'CONTRACT')->where('gender', 'Female')->count(),
        ];

        $currentMonth = Carbon::now()->month;
        $today = Carbon::now()->format('m-d');
        
        $birthdays = User::whereNotNull('dob')
            ->whereMonth('dob', $currentMonth)
            ->with('position')
            ->get()
            ->map(function ($user) use ($today) {
                $dob = Carbon::parse($user->dob);
                $isToday = $dob->format('m-d') === $today;
                
                $khmerMonths = ['', 'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
                $dobFormatted = $dob->format('d') . ' ' . $khmerMonths[$dob->month];

                return [
                    'id' => $user->id,
                    'name_kh' => $user->name_kh ?? $user->name,
                    'profile_image' => $user->profile_image,
                    'position_name' => $user->position ? $user->position->title_kh : '---',
                    'dob_formatted' => $dobFormatted,
                    'age' => $dob->age,
                    'is_today' => $isToday
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'birthdays' => $birthdays
        ]);
    }
}