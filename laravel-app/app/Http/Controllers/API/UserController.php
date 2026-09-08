<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\ReadUserRequest;
use App\Http\Requests\User\ToggleUserStatusRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\Position;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUsers(GetUserRequest $request)
    {
        $keyword = $request->input('keyword', null);
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $users = User::with(['department', 'office', 'position'])
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name_kh', 'like', "%{$keyword}%")
                      ->orWhere('name_en', 'like', "%{$keyword}%")
                      ->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhere('employee_code', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('employee_type'), fn($q) => $q->where('employee_type', $request->employee_type))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('officer_status'), fn($q) => $q->where('officer_status', $request->officer_status))
            ->orderBy(Position::select('level')
            ->whereColumn('positions.id', 'users.position_id')
            ->limit(1), 
        'asc')
        
        // ២. តម្រៀបតាម Level របស់ User បន្តបន្ទាប់ (បើមាន)
        ->orderBy('level', 'asc')
            ->latest()
            ->orderBy('level', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        // គណនាស្ថិតិចំនួនមន្ត្រីតាមស្ថានភាពនីមួយៗ
        $officerStatusCounts = User::select('officer_status', DB::raw('count(*) as total'))
            ->groupBy('officer_status')
            ->pluck('total', 'officer_status')
            ->toArray();

        return response([
            'success' => true,
            'users' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'officer_status_counts' => [
                'ALL' => User::count(),
                'ACTIVE' => $officerStatusCounts['ACTIVE'] ?? 0,
                'RESIGNED' => $officerStatusCounts['RESIGNED'] ?? 0,
                'RETIRED' => $officerStatusCounts['RETIRED'] ?? 0,
                'TRANSFERRED' => $officerStatusCounts['TRANSFERRED'] ?? 0,
                'SUSPENDED' => $officerStatusCounts['SUSPENDED'] ?? 0,
                'OTHER' => $officerStatusCounts['OTHER'] ?? 0,
            ],
        ], 200);
    }

    public function readUser(ReadUserRequest $request)
    {
        $user = User::with(['department', 'office', 'position', 'additionalPositions', 'outOfFrameworkStatuses', 'unpaidLeaves', 'publicWorkHistories', 'privateWorkHistories', 'userDecorations', 'disciplinaryActions', 'educations', 'languages', 'siblings', 'children'])
            ->where('id', $request->route('id'))
            ->firstOrFail();

        return response([
            'success' => true,
            'user' => new UserResource($user)
        ], 200);
    }

    public function createUser(CreateUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->except(['profile_image', 'national_id_file', 'passport_file']);

            $data['name'] = $request->name_kh ?? $request->name_en ?? 'System User';
            $data['level'] = $request->level ?? 'USER';
            $data['status'] = $request->status ?? 'ENABLED';

            if ($request->has('permissions')) {
                $perms = is_array($request->permissions) ? $request->permissions : json_decode($request->permissions, true);
                $data['permissions'] = is_array($perms) ? $perms : null;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // ✅ Upload រូបភាពចូល Storage Disk
            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // រក្សាទុកក្នុង storage/app/public/users/profile-images
                $file->storeAs('users/profile-images', $filename, 'public');

                // រក្សាទុកចម្លងទៅ thumbnails ផងដែរ
                $file->storeAs('users/profile-images/thumbnails', $filename, 'public');

                $data['profile_image'] = 'users/profile-images/' . $filename;
            }

            // ✅ Upload ឯកសារអត្តសញ្ញាណបណ្ណ
            if ($request->hasFile('national_id_file')) {
                $file = $request->file('national_id_file');
                $filename = time() . '_nid_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('users/national-ids', $filename, 'public');
                $data['national_id_file'] = 'users/national-ids/' . $filename;
            }

            // ✅ Upload ឯកសារលិខិតឆ្លងដែន
            if ($request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $filename = time() . '_passport_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('users/passports', $filename, 'public');
                $data['passport_file'] = 'users/passports/' . $filename;
            }

            $dateFields = [
                'dob', 'national_id_expired_date', 'passport_expired_date', 
                'first_service_date', 'first_appointment_date', 'current_appointment_date', 'current_position_date',
                'officer_status_date',
                'father_dob', 'mother_dob', 'spouse_dob'
            ];
            foreach ($dateFields as $f) {
                if (isset($data[$f]) && $data[$f] === '') {
                    $data[$f] = null;
                }
            }

            $user = User::create($data);
            
            if ($user->employee_type === 'CIVIL_SERVICE') {
                if ($request->filled('additional_positions')) {
                    $additionalPositions = json_decode($request->additional_positions, true);
                    if (is_array($additionalPositions)) {
                        $clean = array_map(function ($item) {
                            unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                            foreach (['date', 'start_date', 'end_date'] as $f) {
                                if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                            }
                            return $item;
                        }, $additionalPositions);
                        $user->additionalPositions()->createMany($clean);
                    }
                }

                if ($request->filled('out_of_framework_statuses')) {
                    $outOfFramework = json_decode($request->out_of_framework_statuses, true);
                    if (is_array($outOfFramework)) {
                        $clean = array_map(function ($item) {
                            unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                            foreach (['date', 'start_date', 'end_date'] as $f) {
                                if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                            }
                            return $item;
                        }, $outOfFramework);
                        $user->outOfFrameworkStatuses()->createMany($clean);
                    }
                }

                if ($request->filled('unpaid_leaves')) {
                    $unpaidLeaves = json_decode($request->unpaid_leaves, true);
                    if (is_array($unpaidLeaves)) {
                        $clean = array_map(function ($item) {
                            unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                            foreach (['date', 'start_date', 'end_date'] as $f) {
                                if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                            }
                            return $item;
                        }, $unpaidLeaves);
                        $user->unpaidLeaves()->createMany($clean);
                    }
                }
            }
            
            if ($request->filled('public_work_histories')) {
                $publicWork = json_decode($request->public_work_histories, true);
                if (is_array($publicWork)) {
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $publicWork);
                    $user->publicWorkHistories()->createMany($clean);
                }
            }

            if ($request->filled('private_work_histories')) {
                $privateWork = json_decode($request->private_work_histories, true);
                if (is_array($privateWork)) {
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $privateWork);
                    $user->privateWorkHistories()->createMany($clean);
                }
            }

            if ($request->filled('user_decorations')) {
                $decorations = json_decode($request->user_decorations, true);
                if (is_array($decorations)) {
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $decorations);
                    $user->userDecorations()->createMany($clean);
                }
            }

            if ($request->filled('disciplinary_actions')) {
                $disciplines = json_decode($request->disciplinary_actions, true);
                if (is_array($disciplines)) {
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $disciplines);
                    $user->disciplinaryActions()->createMany($clean);
                }
            }

            if ($request->filled('user_educations')) {
                $educations = json_decode($request->user_educations, true);
                if (is_array($educations)) {
                    $clean = [];
                    foreach ($educations as $idx => $item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        if ($request->hasFile("education_file_{$idx}")) {
                            $file = $request->file("education_file_{$idx}");
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('users/educations', $filename, 'public');
                            $item['certificate_file'] = 'users/educations/' . $filename;
                        }
                        $clean[] = $item;
                    }
                    $user->educations()->createMany($clean);
                }
            }

            if ($request->filled('user_languages')) {
                $languages = json_decode($request->user_languages, true);
                if (is_array($languages)) {
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        return $item;
                    }, $languages);
                    $user->languages()->createMany($clean);
                }
            }

            if ($request->filled('user_siblings')) {
                $siblings = json_decode($request->user_siblings, true);
                if (is_array($siblings)) {
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        if (isset($item['dob']) && $item['dob'] === '') { $item['dob'] = null; }
                        return $item;
                    }, $siblings);
                    $user->siblings()->createMany($clean);
                }
            }

            if ($request->filled('user_children')) {
                $children = json_decode($request->user_children, true);
                if (is_array($children)) {
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        if (isset($item['dob']) && $item['dob'] === '') { $item['dob'] = null; }
                        return $item;
                    }, $children);
                    $user->children()->createMany($clean);
                }
            }

            if ($request->filled('email')) {
                $user->markEmailAsVerified();
            }

            DB::commit();

            return response([
                'success' => true,
                'message' => 'បង្កើតព័ត៌មានមន្ត្រីដោយជោគជ័យ',
                'user' => new UserResource($user->load(['department', 'office', 'position', 'additionalPositions', 'outOfFrameworkStatuses', 'unpaidLeaves', 'publicWorkHistories', 'privateWorkHistories', 'userDecorations', 'disciplinaryActions', 'educations', 'languages', 'siblings', 'children']))
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការបង្កើតទិន្នន័យ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateUser(UpdateUserRequest $request)
    {
        $user = User::where('id', $request->route('id'))->firstOrFail();

        try {
            DB::beginTransaction();

            $data = $request->except(['password', 'profile_image', 'national_id_file', 'passport_file', '_method']);
            
            if ($request->filled('name_kh') || $request->filled('name_en')) {
                $data['name'] = $request->name_kh ?? $request->name_en;
            }

            if ($request->has('permissions')) {
                $perms = is_array($request->permissions) ? $request->permissions : json_decode($request->permissions, true);
                $data['permissions'] = is_array($perms) ? $perms : null;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
                $user->tokens()->delete();
            }

            // ✅ Handle Profile Image Update ចូល Storage Disk
            if ($request->hasFile('profile_image')) {
                // លុបរូបចាស់បើមាន
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                    $oldThumb = str_replace('users/profile-images/', 'users/profile-images/thumbnails/', $user->profile_image);
                    Storage::disk('public')->delete($oldThumb);
                }

                $file = $request->file('profile_image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // រក្សាទុកក្នុង storage/app/public/users/profile-images
                $file->storeAs('users/profile-images', $filename, 'public');

                // រក្សាទុកចម្លងទៅ thumbnails ផងដែរ
                $file->storeAs('users/profile-images/thumbnails', $filename, 'public');

                $data['profile_image'] = 'users/profile-images/' . $filename;
            }

            // ✅ Handle National ID File Update
            if ($request->hasFile('national_id_file')) {
                if ($user->national_id_file) {
                    Storage::disk('public')->delete($user->national_id_file);
                }
                $file = $request->file('national_id_file');
                $filename = time() . '_nid_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('users/national-ids', $filename, 'public');
                $data['national_id_file'] = 'users/national-ids/' . $filename;
            }

            // ✅ Handle Passport File Update
            if ($request->hasFile('passport_file')) {
                if ($user->passport_file) {
                    Storage::disk('public')->delete($user->passport_file);
                }
                $file = $request->file('passport_file');
                $filename = time() . '_passport_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('users/passports', $filename, 'public');
                $data['passport_file'] = 'users/passports/' . $filename;
            }

            $dateFields = [
                'dob', 'national_id_expired_date', 'passport_expired_date', 
                'first_service_date', 'first_appointment_date', 'current_appointment_date', 'current_position_date',
                'officer_status_date',
                'father_dob', 'mother_dob', 'spouse_dob'
            ];
            foreach ($dateFields as $f) {
                if (isset($data[$f]) && $data[$f] === '') {
                    $data[$f] = null;
                }
            }

            $user->update($data);

            if ($user->employee_type === 'CIVIL_SERVICE') {
                if ($request->has('additional_positions')) {
                    $additionalPositions = json_decode($request->additional_positions, true);
                    if (is_array($additionalPositions)) {
                        $user->additionalPositions()->delete();
                        $clean = array_map(function ($item) {
                            unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                            foreach (['date', 'start_date', 'end_date'] as $f) {
                                if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                            }
                            return $item;
                        }, $additionalPositions);
                        $user->additionalPositions()->createMany($clean);
                    }
                }

                if ($request->has('out_of_framework_statuses')) {
                    $outOfFramework = json_decode($request->out_of_framework_statuses, true);
                    if (is_array($outOfFramework)) {
                        $user->outOfFrameworkStatuses()->delete();
                        $clean = array_map(function ($item) {
                            unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                            foreach (['date', 'start_date', 'end_date'] as $f) {
                                if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                            }
                            return $item;
                        }, $outOfFramework);
                        $user->outOfFrameworkStatuses()->createMany($clean);
                    }
                }

                if ($request->has('unpaid_leaves')) {
                    $unpaidLeaves = json_decode($request->unpaid_leaves, true);
                    if (is_array($unpaidLeaves)) {
                        $user->unpaidLeaves()->delete();
                        $clean = array_map(function ($item) {
                            unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                            foreach (['date', 'start_date', 'end_date'] as $f) {
                                if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                            }
                            return $item;
                        }, $unpaidLeaves);
                        $user->unpaidLeaves()->createMany($clean);
                    }
                }
            } else {
                $user->additionalPositions()->delete();
                $user->outOfFrameworkStatuses()->delete();
                $user->unpaidLeaves()->delete();
            }

            if ($request->has('public_work_histories')) {
                $publicWork = json_decode($request->public_work_histories, true);
                if (is_array($publicWork)) {
                    $user->publicWorkHistories()->delete();
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $publicWork);
                    $user->publicWorkHistories()->createMany($clean);
                }
            }

            if ($request->has('private_work_histories')) {
                $privateWork = json_decode($request->private_work_histories, true);
                if (is_array($privateWork)) {
                    $user->privateWorkHistories()->delete();
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $privateWork);
                    $user->privateWorkHistories()->createMany($clean);
                }
            }

            if ($request->has('user_decorations')) {
                $decorations = json_decode($request->user_decorations, true);
                if (is_array($decorations)) {
                    $user->userDecorations()->delete();
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $decorations);
                    $user->userDecorations()->createMany($clean);
                }
            }

            if ($request->has('disciplinary_actions')) {
                $disciplines = json_decode($request->disciplinary_actions, true);
                if (is_array($disciplines)) {
                    $user->disciplinaryActions()->delete();
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        return $item;
                    }, $disciplines);
                    $user->disciplinaryActions()->createMany($clean);
                }
            }

            if ($request->has('user_educations')) {
                $educations = json_decode($request->user_educations, true);
                if (is_array($educations)) {
                    $user->educations()->delete();
                    $clean = [];
                    foreach ($educations as $idx => $item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        foreach (['date', 'start_date', 'end_date'] as $f) {
                            if (isset($item[$f]) && $item[$f] === '') { $item[$f] = null; }
                        }
                        if ($request->hasFile("education_file_{$idx}")) {
                            $file = $request->file("education_file_{$idx}");
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('users/educations', $filename, 'public');
                            $item['certificate_file'] = 'users/educations/' . $filename;
                        }
                        $clean[] = $item;
                    }
                    $user->educations()->createMany($clean);
                }
            }

            if ($request->has('user_languages')) {
                $languages = json_decode($request->user_languages, true);
                if (is_array($languages)) {
                    $user->languages()->delete();
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        return $item;
                    }, $languages);
                    $user->languages()->createMany($clean);
                }
            }

            if ($request->has('user_siblings')) {
                $siblings = json_decode($request->user_siblings, true);
                if (is_array($siblings)) {
                    $user->siblings()->delete();
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        if (isset($item['dob']) && $item['dob'] === '') { $item['dob'] = null; }
                        return $item;
                    }, $siblings);
                    $user->siblings()->createMany($clean);
                }
            }

            if ($request->has('user_children')) {
                $children = json_decode($request->user_children, true);
                if (is_array($children)) {
                    $user->children()->delete();
                    $clean = array_map(function ($item) {
                        unset($item['id'], $item['user_id'], $item['created_at'], $item['updated_at']);
                        if (isset($item['dob']) && $item['dob'] === '') { $item['dob'] = null; }
                        return $item;
                    }, $children);
                    $user->children()->createMany($clean);
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការកែប្រែទិន្នន័យ: ' . $e->getMessage()
            ], 500);
        }

        return response([
            'success' => true,
            'message' => 'ទិន្នន័យត្រូវបានកែប្រែដោយជោគជ័យ។',
            'user' => new UserResource($user->load(['department', 'office', 'position', 'additionalPositions', 'outOfFrameworkStatuses', 'unpaidLeaves', 'publicWorkHistories', 'privateWorkHistories', 'userDecorations', 'disciplinaryActions', 'educations', 'languages', 'siblings', 'children'])),
        ], 200);
    }

    public function deleteUser(DeleteUserRequest $request)
    {
        $user = User::where('id', $request->route('id'))->firstOrFail();

        try {
            DB::beginTransaction();

            // លុបរូបភាពពី Storage ពេលលុប User
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
                $thumb = str_replace('users/profile-images/', 'users/profile-images/thumbnails/', $user->profile_image);
                Storage::disk('public')->delete($thumb);
            }

            $user->tokens()->delete();
            $user->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការលុបទិន្នន័យ'
            ], 500);
        }

        return response([
            'success' => true,
            'message' => 'ទិន្នន័យត្រូវបានលុបដោយជោគជ័យ។'
        ], 200);
    }

    public function toggleUserStatus(ToggleUserStatusRequest $request)
    {
        $user = User::where('id', $request->route('id'))->firstOrFail();

        try {
            DB::beginTransaction();
            $user->status = $user->status === 'ENABLED' ? 'DISABLED' : 'ENABLED';

            if ($user->status === 'DISABLED') {
                $user->tokens()->delete();
            }

            $user->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'success' => false,
                'message' => 'មានបញ្ហាក្នុងការប្តូរស្ថានភាព'
            ], 500);
        }

        return response([
            'success' => true,
            'message' => 'ស្ថានភាពត្រូវបានប្តូរ។',
            'user' => new UserResource($user->load(['department', 'office', 'position']))
        ], 200);
    }
   
public function getProfile(Request $request)
{
    // Load ទំនាក់ទំនងជាមួយ Table ផ្សេងៗ (ឧ. department, position, ឫ info ផ្សេងទៀត)
    $user = $request->user()->load(['department', 'position']);

    return response()->json([
        'success' => true,
        'data' => $user
    ]);
}

    /**
     * កំណត់សិទ្ធិប្រើប្រាស់ម៉ឺនុយ (LeftSidebar Permissions)
     */
    public function updateUserPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        $user = User::findOrFail($id);

        // មានតែ ADMIN ប៉ុណ្ណោះដែលអាចកំណត់សិទ្ធិបាន
        if ($request->user()->level !== 'ADMIN') {
            return response([
                'success' => false,
                'message' => 'គ្មានសិទ្ធិកំណត់សិទ្ធិប្រើប្រាស់ឡើយ។'
            ], 403);
        }

        $user->permissions = $request->permissions ?? [];
        $user->save();

        return response([
            'success' => true,
            'message' => 'បានកំណត់សិទ្ធិប្រើប្រាស់ដោយជោគជ័យ',
            'user' => new UserResource($user->load(['department', 'office', 'position']))
        ], 200);
    }
}