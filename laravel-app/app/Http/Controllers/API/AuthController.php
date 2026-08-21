<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\CreatePasswordRequest;
use App\Http\Requests\User\SendResetPasswordEmailRequest;
use App\Http\Requests\User\SetNewPasswordRequest;
use App\Http\Requests\User\SigninRequest;
use App\Http\Requests\User\UpdateProfileImageRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\ImageClassService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function signin(SigninRequest $request)
    {
        $user = User::with(['department', 'office', 'position'])
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'រកមិនឃើញគណនីដែលមានអ៊ីមែលនេះឡើយ។',
            ]);
        }

        if ($user->status === 'DISABLED') {
            throw ValidationException::withMessages([
                'email' => 'គណនីរបស់អ្នកត្រូវបានផ្អាកដំណើរការ។ សូមទាក់ទងអ្នកគ្រប់គ្រងប្រព័ន្ធ។',
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'ពាក្យសម្ងាត់មិនត្រឹមត្រូវឡើយ។',
            ]);
        }

        // បង្កើត Token ថ្មី
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'ចូលប្រើប្រាស់ប្រព័ន្ធដោយជោគជ័យ',
            'user' => new UserResource($user),
            'token' => $token
        ], 200);
    }

    public function signout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'បានចាកចេញពីប្រព័ន្ធដោយជោគជ័យ'
        ], 200);
    }

    public function sendResetPasswordEmail(SendResetPasswordEmailRequest $request)
    {
        $status = Password::sendResetLink(
            ['email' => $request->email],
            function ($user, $token) use ($request) {
                $user->sendPasswordResetNotification($token, $request->callback_url);
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'តំណភ្ជាប់កំណត់ពាក្យសម្ងាត់ឡើងវិញត្រូវបានផ្ញើទៅកាន់អ៊ីមែលរបស់អ្នក'
        ], 200);
    }

    public function setNewPassword(SetNewPasswordRequest $request)
    {
        $status = Password::reset(
            [
                'token' => $request->token,
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation
            ],
            function ($user, $password) {
                $user->password = $password; // Auto-hashed by casts
                $user->save();
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'password' => [__($status)],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'ពាក្យសម្ងាត់ត្រូវបានផ្លាស់ប្តូរដោយជោគជ័យ'
        ], 200);
    }

    public function createPassword(CreatePasswordRequest $request)
    {
        $user = $request->user();
        if (!empty($user->password)) {
            throw ValidationException::withMessages([
                'new_password' => 'ពាក្យសម្ងាត់ត្រូវបានបង្កើតរួចរាល់ហើយ',
            ]);
        }

        $user->password = $request->new_password; // Auto-hashed by casts
        $user->save();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានបង្កើតពាក្យសម្ងាត់ដោយជោគជ័យ'
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'ពាក្យសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវឡើយ',
            ]);
        }

        if ($request->current_password === $request->new_password) {
            throw ValidationException::withMessages([
                'new_password' => 'ពាក្យសម្ងាត់ថ្មីមិនត្រូវដូចពាក្យសម្ងាត់ចាស់ឡើយ',
            ]);
        }

        $user->password = $request->new_password; // Auto-hashed by casts
        $user->save();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'បានផ្លាស់ប្តូរពាក្យសម្ងាត់ដោយជោគជ័យ'
        ], 200);
    }

    public function updateProfileImage(UpdateProfileImageRequest $request)
    {
        $imageClass = ImageClassService::forUserModel();
        $user = $request->user();
        $oldImage = $user->getRawOriginal('profile_image');
        $newImage = null;

        try {
            $newImage = $imageClass->store($request->file('profile_image'));
            $user->profile_image = $newImage;
            $user->save();
        } catch (Exception $e) {
            if ($newImage) {
                $imageClass->delete($newImage);
            }
            throw $e;
        }

        if ($oldImage) {
            $imageClass->delete($oldImage);
        }

        return response()->json([
            'success' => true,
            'message' => 'រូបភាព Profile ត្រូវបានផ្លាស់ប្តូរដោយជោគជ័យ',
            'profile_image' => $user->profile_image,
            'profile_thumbnail' => $user->profile_thumbnail,
        ], 200);
    }

    public function deleteProfileImage(Request $request)
    {
        $imageClass = ImageClassService::forUserModel();
        $user = $request->user();
        $oldImage = $user->getRawOriginal('profile_image');

        $user->profile_image = null;
        $user->save();

        if ($oldImage) {
            $imageClass->delete($oldImage);
        }

        return response()->json([
            'success' => true,
            'message' => 'បានលុបរូបភាព Profile ដោយជោគជ័យ',
        ], 200);
    }
    public function getProfile(Request $request)
{
    // ទាញយកទិន្នន័យ User ដែលកំពុង Login
    $user = $request->user();

    // បើបងមានចង Relationship (belongsTo) ជាមួយ តួនាទី នាយកដ្ឋាន ការិយាល័យ 
    // បងអាច Load វាចូលដើម្បីទាញយកឈ្មោះមកបង្ហាញ
    $user->load(['position', 'department', 'office']);

    // រៀបចំទិន្នន័យបន្តិចបន្តួច ដើម្បីឲ្យត្រូវនឹងអ្វីដែល Vue (Profile.vue) ចង់បាន
    $profileData = [
        'id' => $user->id,
        'name' => $user->name,
        'name_kh' => $user->name_kh,
        'email' => $user->email,
        'employee_code' => $user->employee_code,
        'gender' => $user->gender,
        'dob' => $user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : null,
        'phone' => $user->phone,
        'current_address' => $user->current_address,
        'profile_image' => $user->profile_image,
        
        // ចាប់យកឈ្មោះតាមរយៈ Relationship (បើគ្មានទេ វានឹងចេញ null)
        'position_name' => $user->position ? $user->position->title_kh : null,
        'department_name' => $user->department ? $user->department->name_kh : null,
        'office_name' => $user->office ? $user->office->name_kh : null,
        'employee_type' => $user->employee_type,
    'marital_status' => $user->marital_status,
    'birth_place' => $user->birth_place,
    'national_id_number' => $user->national_id_number,
    'national_id_expired_date' => $user->national_id_expired_date,
    'passport_number' => $user->passport_number,
    'passport_expired_date' => $user->passport_expired_date,
    'mef_card_number' => $user->mef_card_number,
    ];

    return response()->json([
        'success' => true,
        'user' => $profileData
    ]);
}
}