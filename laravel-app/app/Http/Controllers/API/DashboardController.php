<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {

    
    // ឆែកមើលថាតើអ្នកដែល Request មកនេះមាន level ជា ADMIN ដែរឬទេ
        if ($request->user()->level !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. អ្នកមិនមានសិទ្ធិចូលមើលទិន្នន័យនេះទេ។'
            ], 403); // បោះកូដ 403 (Forbidden)
        }

        // ១. ទាញយកស្ថិតិសរុប
        $stats = [
            'totalEmployees' => User::count(),
            'civilServants' => User::where('employee_type', 'CIVIL_SERVICE')->count(),
            'statutory' => User::where('employee_type', 'STATUTORY')->count(),
            'contractStaff' => User::where('employee_type', 'CONTRACT')->count(),
        ];

        // ២. រកមើលអ្នកមានខួបកំណើតក្នុងខែនេះ
        $currentMonth = Carbon::now()->month;
        $today = Carbon::now()->format('m-d');
        
        $birthdays = User::whereNotNull('dob')
            ->whereMonth('dob', $currentMonth)
            ->with('position') // សន្មតថាបងបានចង Relatiship 'position' ក្នុង User Model
            ->get()
            ->map(function ($user) use ($today) {
                $dob = Carbon::parse($user->dob);
                $isToday = $dob->format('m-d') === $today;
                
                // បម្លែងខែជាអក្សរខ្មែរ
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