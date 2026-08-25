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
            ->orderBy(Position::select('level')
            ->whereColumn('positions.id', 'users.position_id')
            ->limit(1), 
        'asc')
        
        // ២. តម្រៀបតាម Level របស់ User បន្តបន្ទាប់ (បើមាន)
        ->orderBy('level', 'asc')
            ->latest()
            ->orderBy('level', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response([
            'success' => true,
            'users' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }

    public function readUser(ReadUserRequest $request)
    {
        $user = User::with(['department', 'office', 'position'])
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

            $data = $request->except(['profile_image']);

            $data['name'] = $request->name_kh ?? $request->name_en ?? 'System User';
            $data['level'] = $request->level ?? 'USER';
            $data['status'] = $request->status ?? 'ENABLED';

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

            $user = User::create($data);
            
            if ($request->filled('email')) {
                $user->markEmailAsVerified();
            }

            DB::commit();

            return response([
                'success' => true,
                'message' => 'បង្កើតព័ត៌មានមន្ត្រីដោយជោគជ័យ',
                'user' => new UserResource($user->load(['department', 'office', 'position']))
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

            $data = $request->except(['password', 'profile_image', '_method']);
            
            if ($request->filled('name_kh') || $request->filled('name_en')) {
                $data['name'] = $request->name_kh ?? $request->name_en;
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

            $user->update($data);
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
            'user' => new UserResource($user->load(['department', 'office', 'position'])),
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
}