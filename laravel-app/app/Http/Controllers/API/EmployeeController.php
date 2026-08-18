<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a paginated listing of employees with search and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with([
            'user:id,name,email,profile_image,level,status',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,name_kh,name_en'
        ]);

        // Filter by Employee Type
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        // Filter by Department / Office / Position
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Global Search (Name, Code, IDs, Phone, National ID)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name_kh', 'LIKE', "%{$search}%")
                  ->orWhere('name_en', 'LIKE', "%{$search}%")
                  ->orWhere('person_id', 'LIKE', "%{$search}%")
                  ->orWhere('official_id', 'LIKE', "%{$search}%")
                  ->orWhere('mef_id', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('national_id', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['id', 'name_kh', 'name_en', 'dob', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $perPage = (int) $request->input('per_page', 15);
        $employees = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $employees,
        ]);
    }

    /**
     * Display the specified employee.
     */
    public function show($id): JsonResponse
    {
        $employee = Employee::with([
            'user:id,name,email,profile_image,level,status',
            'department:id,name_kh,name_en',
            'office:id,name_kh,name_en',
            'position:id,name_kh,name_en'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $employee,
        ]);
    }

    /**
     * Store a newly created employee (and optional linked user account).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // User Account Creation (Optional)
            'create_user_account' => 'boolean',
            'user_password'       => 'required_if:create_user_account,true|nullable|string|min:6',
            'user_level'          => ['nullable', Rule::in(['ADMIN', 'USER'])],

            // Core Identification
            'employee_type'       => ['required', Rule::in(['CIVIL_SERVICE', 'STATUTORY', 'CONTRACT', 'OTHER'])],
            'person_id'           => 'nullable|string|max:100|unique:employees,person_id',
            'official_id'         => 'nullable|string|max:100',
            'mef_id'              => 'nullable|string|max:100',
            'organization_code'   => 'nullable|string|max:100',
            'name_kh'             => 'required|string|max:255',
            'name_en'             => 'nullable|string|max:255',
            'gender'              => ['required', Rule::in(['ប្រុស', 'ស្រី'])],
            'nationality'         => 'nullable|string|max:100',
            'ethnicity'           => 'nullable|string|max:100',
            'dob'                 => 'nullable|date',

            // Address Objects (JSON)
            'pob'                 => 'nullable|array',
            'current_address'     => 'nullable|array',

            // Contact & Identity
            'email'               => 'nullable|email|max:255|unique:employees,email|unique:users,email',
            'phone'               => 'nullable|string|max:50|unique:employees,phone',
            'national_id'         => 'nullable|string|max:50|unique:employees,national_id',
            'passport_no'         => 'nullable|string|max:50',

            // Structure Relations
            'department_id'       => 'nullable|exists:departments,id',
            'office_id'           => 'nullable|exists:offices,id',
            'position_id'         => 'nullable|exists:positions,id',

            // Additional Info & Status
            'additional_info'     => 'nullable|array',
            'status'              => ['nullable', Rule::in(['ACTIVE', 'DISABLED', 'RETIRED', 'RESIGNED'])],
        ]);

        $employee = DB::transaction(function () use ($validated, $request) {
            $userId = null;

            // 1. Create linked User account if requested
            if ($request->boolean('create_user_account') && !empty($validated['email'])) {
                $user = User::create([
                    'name'     => $validated['name_kh'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['user_password'] ?? 'Default123!'),
                    'level'    => $validated['user_level'] ?? 'USER',
                    'status'   => 'ENABLED',
                ]);
                $userId = $user->id;
            }

            // 2. Prepare payload for Employee creation
            $employeeData = collect($validated)->except([
                'create_user_account',
                'user_password',
                'user_level'
            ])->toArray();

            $employeeData['user_id'] = $userId;
            $employeeData['nationality'] = $validated['nationality'] ?? 'ខ្មែរ';
            $employeeData['ethnicity'] = $validated['ethnicity'] ?? 'ខ្មែរ';
            $employeeData['status'] = $validated['status'] ?? 'ACTIVE';

            return Employee::create($employeeData);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'បានរក្សាទុកទិន្នន័យបុគ្គលិកដោយជោគជ័យ',
            'data'    => $employee->load(['user', 'department', 'office', 'position']),
        ], 201);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'employee_type'     => ['required', Rule::in(['CIVIL_SERVICE', 'STATUTORY', 'CONTRACT', 'OTHER'])],
            'person_id'         => ['nullable', 'string', 'max:100', Rule::unique('employees', 'person_id')->ignore($id)],
            'official_id'       => 'nullable|string|max:100',
            'mef_id'            => 'nullable|string|max:100',
            'organization_code' => 'nullable|string|max:100',
            'name_kh'           => 'required|string|max:255',
            'name_en'           => 'nullable|string|max:255',
            'gender'            => ['required', Rule::in(['ប្រុស', 'ស្រី'])],
            'nationality'       => 'nullable|string|max:100',
            'ethnicity'         => 'nullable|string|max:100',
            'dob'               => 'nullable|date',
            'pob'               => 'nullable|array',
            'current_address'   => 'nullable|array',
            'email'             => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($id)],
            'phone'             => ['nullable', 'string', 'max:50', Rule::unique('employees', 'phone')->ignore($id)],
            'national_id'       => ['nullable', 'string', 'max:50', Rule::unique('employees', 'national_id')->ignore($id)],
            'passport_no'       => 'nullable|string|max:50',
            'department_id'     => 'nullable|exists:departments,id',
            'office_id'         => 'nullable|exists:offices,id',
            'position_id'       => 'nullable|exists:positions,id',
            'additional_info'   => 'nullable|array',
            'status'            => ['nullable', Rule::in(['ACTIVE', 'DISABLED', 'RETIRED', 'RESIGNED'])],
        ]);

        $employee->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'បានកែប្រែទិន្នន័យបុគ្គលិកដោយជោគជ័យ',
            'data'    => $employee->fresh(['user', 'department', 'office', 'position']),
        ]);
    }

    /**
     * Toggle the employee status between ACTIVE and DISABLED.
     */
    public function toggleStatus($id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $newStatus = ($employee->status === 'ACTIVE') ? 'DISABLED' : 'ACTIVE';
        
        $employee->update(['status' => $newStatus]);

        // ប្រសិនបើមានភ្ជាប់ជាមួយ User Account ត្រូវប្តូរ status របស់ User ផងដែរ
        if ($employee->user) {
            $employee->user->update([
                'status' => ($newStatus === 'ACTIVE') ? 'ENABLED' : 'DISABLED'
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => "បានប្តូរស្ថានភាពទៅជា {$newStatus} ដោយជោគជ័យ",
            'data'    => ['status' => $newStatus],
        ]);
    }

    /**
     * Remove the specified employee from storage (Soft Delete).
     */
    public function destroy($id): JsonResponse
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'បានលុបទិន្នន័យបុគ្គលិកដោយជោគជ័យ',
        ]);
    }
}