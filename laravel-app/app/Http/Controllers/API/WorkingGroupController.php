<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorkingGroup;
use App\Models\WorkingGroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WorkingGroupController extends Controller
{
    /**
     * 1. បញ្ជីក្រុមការងារទាំងអស់ (Index with search & filters)
     */
    public function index(Request $request)
    {
        $query = WorkingGroup::with([
            'members.user:id,name,name_kh,name_en,email,profile_image,position_id,department_id,office_id',
            'members.user.position:id,title_kh,level',
            'members.user.department:id,name_kh',
            'members.user.office:id,name_kh',
            'creator:id,name,name_kh',
        ]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name_kh', 'like', "%{$s}%")
                    ->orWhere('name_en', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('reference_document', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 15);
        $groups = $query->orderBy('order_index', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($groups);
    }

    /**
     * 2. បញ្ជីសង្ខេបសម្រាប់ Dropdown / Selectors
     */
    public function all(Request $request)
    {
        $groups = WorkingGroup::where('status', 'ACTIVE')
            ->with([
                'members' => function ($mq) {
                    $mq->where('status', 'ACTIVE')
                       ->orderBy('order_index', 'asc')
                       ->with('user:id,name,name_kh,email,profile_image,position_id');
                },
                'members.user.position:id,title_kh,level',
            ])
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($groups);
    }

    /**
     * 3. ព័ត៌មានលម្អិតក្រុមការងារ (Show)
     */
    public function show($id)
    {
        $group = WorkingGroup::with([
            'members.user:id,name,name_kh,name_en,email,profile_image,position_id,department_id,office_id',
            'members.user.position:id,title_kh,level',
            'members.user.department:id,name_kh',
            'members.user.office:id,name_kh',
            'creator:id,name,name_kh',
        ])->findOrFail($id);

        return response()->json($group);
    }

    /**
     * 4. បង្កើតក្រុមការងារថ្មី (Store)
     */
    public function store(Request $request)
    {
        $viewer = $request->user();

        // សម្អាតទិន្នន័យទទេឱ្យទៅជា null ដើម្បីកុំឱ្យជួបបញ្ហា Date ឬ String ទទេ
        $input = $request->all();
        if (isset($input['established_date']) && trim((string)$input['established_date']) === '') {
            $input['established_date'] = null;
        }
        if (isset($input['code']) && trim((string)$input['code']) === '') {
            $input['code'] = null;
        }
        if (isset($input['reference_document']) && trim((string)$input['reference_document']) === '') {
            $input['reference_document'] = null;
        }
        if (isset($input['name_en']) && trim((string)$input['name_en']) === '') {
            $input['name_en'] = null;
        }
        if (isset($input['description']) && trim((string)$input['description']) === '') {
            $input['description'] = null;
        }
        if (isset($input['members']) && is_array($input['members'])) {
            $input['members'] = array_values(array_filter($input['members'], function ($m) {
                return !empty($m['user_id']);
            }));
        }

        $validator = Validator::make($input, [
            'name_kh' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'reference_document' => 'nullable|string|max:255',
            'established_date' => 'nullable|date',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:ACTIVE,INACTIVE',
            'order_index' => 'nullable|integer',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role' => 'required|string',
            'members.*.custom_role_kh' => 'nullable|string|max:150',
            'members.*.order_index' => 'nullable|integer',
            'members.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $group = DB::transaction(function () use ($validated, $viewer) {
            $group = WorkingGroup::create([
                'name_kh' => $validated['name_kh'],
                'name_en' => $validated['name_en'] ?? null,
                'code' => $validated['code'] ?? null,
                'reference_document' => $validated['reference_document'] ?? null,
                'established_date' => $validated['established_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'ACTIVE',
                'order_index' => (int) ($validated['order_index'] ?? 0),
                'created_by' => $viewer->id,
            ]);

            if (!empty($validated['members']) && is_array($validated['members'])) {
                foreach ($validated['members'] as $idx => $m) {
                    WorkingGroupMember::create([
                        'working_group_id' => $group->id,
                        'user_id' => $m['user_id'],
                        'role' => $m['role'] ?? 'MEMBER',
                        'custom_role_kh' => $m['custom_role_kh'] ?? null,
                        'joined_date' => $m['joined_date'] ?? $validated['established_date'] ?? now(),
                        'order_index' => $m['order_index'] ?? ($idx + 1),
                        'status' => $m['status'] ?? 'ACTIVE',
                        'notes' => $m['notes'] ?? null,
                    ]);
                }
            }

            return $group;
        });

        return response()->json([
            'success' => true,
            'message' => 'បានបង្កើតក្រុមការងារដោយជោគជ័យ!',
            'data' => $this->show($group->id)->original,
        ], 201);
    }

    /**
     * 5. កែសម្រួលព័ត៌មានក្រុមការងារ (Update)
     */
    public function update(Request $request, $id)
    {
        $group = WorkingGroup::findOrFail($id);

        $input = $request->all();
        if (isset($input['established_date']) && trim((string)$input['established_date']) === '') {
            $input['established_date'] = null;
        }
        if (isset($input['code']) && trim((string)$input['code']) === '') {
            $input['code'] = null;
        }
        if (isset($input['reference_document']) && trim((string)$input['reference_document']) === '') {
            $input['reference_document'] = null;
        }
        if (isset($input['name_en']) && trim((string)$input['name_en']) === '') {
            $input['name_en'] = null;
        }
        if (isset($input['description']) && trim((string)$input['description']) === '') {
            $input['description'] = null;
        }
        if (isset($input['members']) && is_array($input['members'])) {
            $input['members'] = array_values(array_filter($input['members'], function ($m) {
                return !empty($m['user_id']);
            }));
        }

        $validator = Validator::make($input, [
            'name_kh' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'reference_document' => 'nullable|string|max:255',
            'established_date' => 'nullable|date',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:ACTIVE,INACTIVE',
            'order_index' => 'nullable|integer',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role' => 'required|string',
            'members.*.custom_role_kh' => 'nullable|string|max:150',
            'members.*.order_index' => 'nullable|integer',
            'members.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($group, $validated) {
            $group->update([
                'name_kh' => $validated['name_kh'],
                'name_en' => $validated['name_en'] ?? null,
                'code' => $validated['code'] ?? null,
                'reference_document' => $validated['reference_document'] ?? null,
                'established_date' => $validated['established_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? $group->status,
                'order_index' => isset($validated['order_index']) ? (int)$validated['order_index'] : $group->order_index,
            ]);

            // Sync members if provided
            if (isset($validated['members']) && is_array($validated['members'])) {
                $existingMembers = WorkingGroupMember::where('working_group_id', $group->id)->get()->keyBy('user_id');
                $keepUserIds = [];

                foreach ($validated['members'] as $idx => $m) {
                    $userId = $m['user_id'];
                    $keepUserIds[] = $userId;

                    $memberData = [
                        'working_group_id' => $group->id,
                        'user_id' => $userId,
                        'role' => $m['role'] ?? 'MEMBER',
                        'custom_role_kh' => $m['custom_role_kh'] ?? null,
                        'joined_date' => $m['joined_date'] ?? $validated['established_date'] ?? now(),
                        'order_index' => $m['order_index'] ?? ($idx + 1),
                        'status' => $m['status'] ?? 'ACTIVE',
                        'notes' => $m['notes'] ?? null,
                    ];

                    if (isset($existingMembers[$userId])) {
                        $existingMembers[$userId]->update($memberData);
                    } else {
                        WorkingGroupMember::create($memberData);
                    }
                }

                // Delete members not in the new list
                WorkingGroupMember::where('working_group_id', $group->id)
                    ->whereNotIn('user_id', $keepUserIds)
                    ->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'បានកែសម្រួលក្រុមការងារដោយជោគជ័យ!',
            'data' => $this->show($group->id)->original,
        ]);
    }

    /**
     * 6. លុបក្រុមការងារ (Destroy)
     */
    public function destroy($id)
    {
        $group = WorkingGroup::findOrFail($id);
        $group->members()->delete();
        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានលុបក្រុមការងារដោយជោគជ័យ!',
        ]);
    }

    /**
     * 7. បន្ថែមសមាជិកម្នាក់ចូលក្នុងក្រុមការងារ (Add Member)
     */
    public function addMember(Request $request, $id)
    {
        $group = WorkingGroup::findOrFail($id);

        $input = $request->all();
        if (isset($input['joined_date']) && trim((string)$input['joined_date']) === '') {
            $input['joined_date'] = null;
        }

        $validator = Validator::make($input, [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string',
            'custom_role_kh' => 'nullable|string|max:150',
            'joined_date' => 'nullable|date',
            'order_index' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $member = WorkingGroupMember::updateOrCreate(
            [
                'working_group_id' => $group->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'role' => $validated['role'],
                'custom_role_kh' => $validated['custom_role_kh'] ?? null,
                'joined_date' => $validated['joined_date'] ?? now(),
                'order_index' => (int) ($validated['order_index'] ?? 99),
                'status' => 'ACTIVE',
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'បានបញ្ចូលសមាជិកដោយជោគជ័យ!',
            'data' => $member->load('user.position'),
        ]);
    }

    /**
     * 8. លុបសមាជិកចេញពីក្រុមការងារ (Remove Member)
     */
    public function removeMember($id, $memberId)
    {
        $member = WorkingGroupMember::where('working_group_id', $id)
            ->where(function ($q) use ($memberId) {
                $q->where('id', $memberId)->orWhere('user_id', $memberId);
            })
            ->firstOrFail();

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានដកសមាជិកចេញពីក្រុមការងារដោយជោគជ័យ!',
        ]);
    }
}
