<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentWorkflowConfig;
use App\Models\Office;
use App\Models\User;
use App\Models\WorkingGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WorkflowConfigController extends Controller
{
    /**
     * អនុញ្ញាតតែ Admin និងអគ្គនាយក (DG - Position Level 1) ប៉ុណ្ណោះ
     */
    protected function authorizeAdminOrDg(Request $request): bool
    {
        $user = $request->user();
        if (!$user) return false;
        $isAdmin = (strtoupper($user->level ?? '') === 'ADMIN');
        $isDg = ($user->position && (int) $user->position->level === 1);
        return $isAdmin || $isDg;
    }

    /**
     * Get paginated or filtered list of users with their document workflow configuration
     */
    public function index(Request $request): JsonResponse
    {
        if (!$this->authorizeAdminOrDg($request)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលប្រើប្រាស់មុខងារនេះឡើយ! មានតែ Admin និងអគ្គនាយកប៉ុណ្ណោះដែលអាចកំណត់បាន'], 403);
        }

        $query = User::select('users.*')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->with([
                'department:id,name_kh,name_en,code',
                'office:id,name_kh,name_en,code,department_id',
                'position:id,title_kh,title_en,level',
                'workflowConfig',
            ])->whereIn(DB::raw('UPPER(users.status)'), ['ACTIVE', 'ENABLED']);

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->where('users.department_id', $request->department_id);
        }

        // Filter by Office
        if ($request->filled('office_id')) {
            $query->where('users.office_id', $request->office_id);
        }

        // Filter by View Type
        if ($request->filled('view_type')) {
            if ($request->view_type === 'UNSET') {
                $query->whereDoesntHave('workflowConfig');
            } else {
                $query->whereHas('workflowConfig', function ($q) use ($request) {
                    $q->where('view_type', $request->view_type);
                });
            }
        }

        // Filter by Send Type
        if ($request->filled('send_type')) {
            if ($request->send_type === 'UNSET') {
                $query->whereDoesntHave('workflowConfig');
            } else {
                $query->whereHas('workflowConfig', function ($q) use ($request) {
                    $q->where('send_type', $request->send_type);
                });
            }
        }

        // Search Keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.name_kh', 'like', "%{$search}%")
                    ->orWhere('users.name_en', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('positions.title_kh', 'like', "%{$search}%")
                    ->orWhere('positions.title_en', 'like', "%{$search}%");
            });
        }

        // Sort primarily by Position Level (Hierarchy Order: Level 1 -> Level 2 -> Level 3...), then by unit & name
        $query->orderByRaw('COALESCE(positions.level, 999) ASC')
              ->orderBy('users.department_id', 'asc')
              ->orderBy('users.office_id', 'asc')
              ->orderBy('users.name_kh', 'asc')
              ->orderBy('users.name', 'asc');

        $perPage = (int) $request->input('per_page', 25);
        $users = $query->paginate($perPage);

        // Stats for workflow setup overview
        $stats = [
            'total_users' => User::whereIn(DB::raw('UPPER(status)'), ['ACTIVE', 'ENABLED'])->count(),
            'configured_users' => DocumentWorkflowConfig::count(),
            'view_all' => DocumentWorkflowConfig::where('view_type', 'ALL')->count(),
            'view_department' => DocumentWorkflowConfig::where('view_type', 'DEPARTMENT')->count(),
            'view_office' => DocumentWorkflowConfig::where('view_type', 'OFFICE')->count(),
            'view_subordinates' => DocumentWorkflowConfig::where('view_type', 'SUBORDINATES')->count(),
            'view_self' => DocumentWorkflowConfig::where('view_type', 'SELF_ONLY')->count(),
            'view_custom' => DocumentWorkflowConfig::where('view_type', 'CUSTOM')->count(),
            'send_all' => DocumentWorkflowConfig::where('send_type', 'ALL')->count(),
            'send_department' => DocumentWorkflowConfig::where('send_type', 'DEPARTMENT')->count(),
            'send_office' => DocumentWorkflowConfig::where('send_type', 'OFFICE')->count(),
            'send_subordinates' => DocumentWorkflowConfig::where('send_type', 'SUBORDINATES')->count(),
            'send_custom' => DocumentWorkflowConfig::where('send_type', 'CUSTOM')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Return workflow configuration options (departments, offices, users, working groups)
     */
    public function options(Request $request): JsonResponse
    {
        if (!$this->authorizeAdminOrDg($request)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលប្រើប្រាស់មុខងារនេះឡើយ! មានតែ Admin និងអគ្គនាយកប៉ុណ្ណោះដែលអាចកំណត់បាន'], 403);
        }

        $departments = Department::select('id', 'name_kh', 'name_en', 'code')
            ->orderBy('id')
            ->get();

        $offices = Office::select('id', 'department_id', 'name_kh', 'name_en', 'code')
            ->orderBy('id')
            ->get();

        $users = User::select('users.id', 'users.name', 'users.name_kh', 'users.department_id', 'users.office_id', 'users.position_id', 'users.level', 'users.profile_image')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->with(['position:id,title_kh,title_en,level'])
            ->whereIn(DB::raw('UPPER(users.status)'), ['ACTIVE', 'ENABLED'])
            ->orderByRaw('COALESCE(positions.level, 999) ASC')
            ->orderBy('users.name_kh')
            ->orderBy('users.name')
            ->get();

        $workingGroups = WorkingGroup::select('id', 'name_kh', 'name_en', 'code', 'status')
            ->where('status', 'active')
            ->orderBy('order_index')
            ->orderBy('id')
            ->get();

        $viewTypes = [
            ['value' => 'ALL', 'label_kh' => 'ទាំងអស់ (គ្រប់ឯកសារក្នុងប្រព័ន្ធ)', 'description' => 'អាចមើលឃើញគ្រប់ឯកសារទាំងអស់ក្នុងស្ថាប័ន'],
            ['value' => 'DEPARTMENT', 'label_kh' => 'ក្នុងនាយកដ្ឋាន', 'description' => 'អាចមើលឃើញឯកសារទាំងអស់ដែលពាក់ព័ន្ធនឹងនាយកដ្ឋានរបស់ខ្លួន'],
            ['value' => 'OFFICE', 'label_kh' => 'ក្នុងការិយាល័យ', 'description' => 'អាចមើលឃើញឯកសារទាំងអស់ដែលពាក់ព័ន្ធនឹងការិយាល័យរបស់ខ្លួន'],
            ['value' => 'SUBORDINATES', 'label_kh' => 'មន្ត្រីក្រោមឱវាទ & ខ្លួនឯង', 'description' => 'អាចមើលឃើញឯកសាររបស់ខ្លួន និងមន្ត្រីក្រោមឱវាទ'],
            ['value' => 'SELF_ONLY', 'label_kh' => 'បុគ្គល (តែឯកសារផ្ទាល់ខ្លួន)', 'description' => 'អាចមើលឃើញតែឯកសារដែលបញ្ជូនចំឈ្មោះខ្លួន ឬខ្លួនបានចុះ/ទទួល'],
            ['value' => 'CUSTOM', 'label_kh' => 'កំណត់ដោយដៃ (Custom Selection)', 'description' => 'ជ្រើសរើសនាយកដ្ឋាន ការិយាល័យ ឬមន្ត្រីជាក់លាក់'],
        ];

        $sendTypes = [
            ['value' => 'ALL', 'label_kh' => 'ទាំងអស់ (អាចផ្ញើទៅគ្រប់ទីកន្លែង)', 'description' => 'អាចបញ្ជូន/ឆ្លងឯកសារទៅគ្រប់នាយកដ្ឋាន ការិយាល័យ និងមន្ត្រីទាំងអស់'],
            ['value' => 'DEPARTMENT', 'label_kh' => 'ក្នុងនាយកដ្ឋាន', 'description' => 'អាចបញ្ជូនទៅកាន់ការិយាល័យ និងមន្ត្រីក្នុងនាយកដ្ឋានរបស់ខ្លួនប៉ុណ្ណោះ'],
            ['value' => 'OFFICE', 'label_kh' => 'ក្នុងការិយាល័យ', 'description' => 'អាចបញ្ជូនទៅកាន់មន្ត្រីក្នុងការិយាល័យរបស់ខ្លួនប៉ុណ្ណោះ'],
            ['value' => 'SUBORDINATES', 'label_kh' => 'មន្ត្រីក្រោមឱវាទប៉ុណ្ណោះ', 'description' => 'អាចបញ្ជូនបន្តទៅកាន់តែមន្ត្រីដែលមានឋានៈក្រោមឱវាទ'],
            ['value' => 'CUSTOM', 'label_kh' => 'កំណត់ដោយដៃ (Custom Recipients)', 'description' => 'ជ្រើសរើសនាយកដ្ឋាន ការិយាល័យ មន្ត្រី ឬក្រុមការងារជាក់លាក់ដែលអាចបញ្ជូនទៅបាន'],
        ];

        return response()->json([
            'success' => true,
            'departments' => $departments,
            'offices' => $offices,
            'users' => $users,
            'working_groups' => $workingGroups,
            'view_types' => $viewTypes,
            'send_types' => $sendTypes,
        ]);
    }

    /**
     * Get single user's config
     */
    public function show(Request $request, int $userId): JsonResponse
    {
        if (!$this->authorizeAdminOrDg($request)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលប្រើប្រាស់មុខងារនេះឡើយ! មានតែ Admin និងអគ្គនាយកប៉ុណ្ណោះដែលអាចកំណត់បាន'], 403);
        }

        $user = User::with([
            'department:id,name_kh,name_en,code',
            'office:id,name_kh,name_en,code,department_id',
            'position:id,title_kh,title_en,level',
            'workflowConfig',
        ])->findOrFail($userId);

        return response()->json([
            'success' => true,
            'user' => $user,
            'config' => $user->workflowConfig,
        ]);
    }

    /**
     * Save or update workflow config for a single user
     */
    public function storeOrUpdate(Request $request): JsonResponse
    {
        if (!$this->authorizeAdminOrDg($request)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលប្រើប្រាស់មុខងារនេះឡើយ! មានតែ Admin និងអគ្គនាយកប៉ុណ្ណោះដែលអាចកំណត់បាន'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'view_type' => 'required|in:ALL,DEPARTMENT,OFFICE,SUBORDINATES,SELF_ONLY,CUSTOM',
            'view_dept_ids' => 'nullable|array',
            'view_dept_ids.*' => 'integer',
            'view_office_ids' => 'nullable|array',
            'view_office_ids.*' => 'integer',
            'view_user_ids' => 'nullable|array',
            'view_user_ids.*' => 'integer',
            'view_working_group_ids' => 'nullable|array',
            'view_working_group_ids.*' => 'integer',
            'send_type' => 'required|in:ALL,DEPARTMENT,OFFICE,SUBORDINATES,CUSTOM',
            'send_dept_ids' => 'nullable|array',
            'send_dept_ids.*' => 'integer',
            'send_office_ids' => 'nullable|array',
            'send_office_ids.*' => 'integer',
            'send_user_ids' => 'nullable|array',
            'send_user_ids.*' => 'integer',
            'send_working_group_ids' => 'nullable|array',
            'send_working_group_ids.*' => 'integer',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->input('user_id');

        $config = DocumentWorkflowConfig::updateOrCreate(
            ['user_id' => $userId],
            [
                'view_type' => $request->input('view_type'),
                'view_dept_ids' => $request->input('view_dept_ids', []),
                'view_office_ids' => $request->input('view_office_ids', []),
                'view_user_ids' => $request->input('view_user_ids', []),
                'view_working_group_ids' => $request->input('view_working_group_ids', []),
                'send_type' => $request->input('send_type'),
                'send_dept_ids' => $request->input('send_dept_ids', []),
                'send_office_ids' => $request->input('send_office_ids', []),
                'send_user_ids' => $request->input('send_user_ids', []),
                'send_working_group_ids' => $request->input('send_working_group_ids', []),
                'notes' => $request->input('notes'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'បានរក្សាទុកការកំណត់ Workflow ដោយជោគជ័យ',
            'data' => $config,
        ]);
    }

    /**
     * Batch update multiple users at once
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        if (!$this->authorizeAdminOrDg($request)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលប្រើប្រាស់មុខងារនេះឡើយ! មានតែ Admin និងអគ្គនាយកប៉ុណ្ណោះដែលអាចកំណត់បាន'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'view_type' => 'nullable|in:ALL,DEPARTMENT,OFFICE,SUBORDINATES,SELF_ONLY,CUSTOM',
            'view_dept_ids' => 'nullable|array',
            'view_dept_ids.*' => 'integer',
            'view_office_ids' => 'nullable|array',
            'view_office_ids.*' => 'integer',
            'view_user_ids' => 'nullable|array',
            'view_user_ids.*' => 'integer',
            'view_working_group_ids' => 'nullable|array',
            'view_working_group_ids.*' => 'integer',
            'send_type' => 'nullable|in:ALL,DEPARTMENT,OFFICE,SUBORDINATES,CUSTOM',
            'send_dept_ids' => 'nullable|array',
            'send_dept_ids.*' => 'integer',
            'send_office_ids' => 'nullable|array',
            'send_office_ids.*' => 'integer',
            'send_user_ids' => 'nullable|array',
            'send_user_ids.*' => 'integer',
            'send_working_group_ids' => 'nullable|array',
            'send_working_group_ids.*' => 'integer',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userIds = $request->input('user_ids');
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($userIds as $uid) {
                $existing = DocumentWorkflowConfig::firstOrNew(['user_id' => $uid]);

                if ($request->filled('view_type')) {
                    $existing->view_type = $request->input('view_type');
                    if ($request->has('view_dept_ids')) $existing->view_dept_ids = $request->input('view_dept_ids', []);
                    if ($request->has('view_office_ids')) $existing->view_office_ids = $request->input('view_office_ids', []);
                    if ($request->has('view_user_ids')) $existing->view_user_ids = $request->input('view_user_ids', []);
                    if ($request->has('view_working_group_ids')) $existing->view_working_group_ids = $request->input('view_working_group_ids', []);
                }

                if ($request->filled('send_type')) {
                    $existing->send_type = $request->input('send_type');
                    if ($request->has('send_dept_ids')) $existing->send_dept_ids = $request->input('send_dept_ids', []);
                    if ($request->has('send_office_ids')) $existing->send_office_ids = $request->input('send_office_ids', []);
                    if ($request->has('send_user_ids')) $existing->send_user_ids = $request->input('send_user_ids', []);
                    if ($request->has('send_working_group_ids')) $existing->send_working_group_ids = $request->input('send_working_group_ids', []);
                }

                if ($request->filled('notes')) {
                    $existing->notes = $request->input('notes');
                }

                $existing->save();
                $updatedCount++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "បានកំណត់ជោគជ័យសម្រាប់មន្ត្រីចំនួន {$updatedCount} នាក់",
                'updated_count' => $updatedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការកំណត់: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete config to revert user to standard system hierarchy
     */
    public function destroy(Request $request, int $userId): JsonResponse
    {
        if (!$this->authorizeAdminOrDg($request)) {
            return response()->json(['message' => 'លោកអ្នកមិនមានសិទ្ធិចូលប្រើប្រាស់មុខងារនេះឡើយ! មានតែ Admin និងអគ្គនាយកប៉ុណ្ណោះដែលអាចកំណត់បាន'], 403);
        }

        $config = DocumentWorkflowConfig::where('user_id', $userId)->first();
        if ($config) {
            $config->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'បានលុបការកំណត់ Workflow របស់មន្ត្រីនេះ (ត្រឡប់ទៅតាមឋានានុក្រមធម្មជាតិ)',
        ]);
    }

    /**
     * Get current user's workflow rules and restrictions
     */
    public function myConfig(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $config = DocumentWorkflowConfig::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'has_custom_config' => !is_null($config),
            'view_type' => $config?->view_type ?? 'DEFAULT',
            'send_type' => $config?->send_type ?? 'DEFAULT',
            'config' => $config,
        ]);
    }
}
