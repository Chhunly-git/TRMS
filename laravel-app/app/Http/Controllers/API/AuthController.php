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
use App\Services\StorageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function getSignatureImage(Request $request, $id = null)
    {
        $targetUser = $id ? User::findOrFail($id) : $request->user();
        $sigPath = $targetUser->getRawOriginal('signature_image');

        if (empty($sigPath) || !StorageService::exists($sigPath)) {
            return response()->json(['message' => 'No signature found'], 404);
        }

        return StorageService::response($sigPath);
    }

    public function updateSignatureImage(Request $request)
    {
        $user = $request->user();
        $oldSignature = $user->getRawOriginal('signature_image');
        $newSignature = null;
        $disk = Storage::disk(StorageService::diskName());

        try {
            if ($request->hasFile('signature_image')) {
                $file = $request->file('signature_image');
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $filename = 'sig_' . Str::uuid() . '.' . $ext;
                $newSignature = $disk->putFileAs('users/signatures', $file, $filename);
            } elseif ($request->filled('signature_base64')) {
                $base64Data = $request->input('signature_base64');
                $type = 'png';
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                    $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $type = strtolower($matches[1]);
                }
                $data = base64_decode($base64Data);
                if ($data === false) {
                    return response()->json(['message' => 'ទិន្នន័យរូបភាពហត្ថលេខាមិនត្រឹមត្រូវ!'], 422);
                }
                $filename = 'sig_' . Str::uuid() . '.' . $type;
                $newSignature = 'users/signatures/' . $filename;
                $disk->put($newSignature, $data);
            } else {
                return response()->json(['message' => 'សូមជ្រើសរើសរូបភាព ឬគូសហត្ថលេខា!'], 422);
            }

            $user->signature_image = $newSignature;
            $user->save();
        } catch (Exception $e) {
            if ($newSignature && $disk->exists($newSignature)) {
                $disk->delete($newSignature);
            }
            throw $e;
        }

        if ($oldSignature && $disk->exists($oldSignature)) {
            $disk->delete($oldSignature);
        }

        return response()->json([
            'success' => true,
            'message' => 'ហត្ថលេខាត្រូវបានរក្សាទុកដោយជោគជ័យ',
            'signature_image' => $user->signature_image,
        ], 200);
    }

    public function deleteSignatureImage(Request $request)
    {
        $user = $request->user();
        $oldSignature = $user->getRawOriginal('signature_image');
        $disk = Storage::disk(StorageService::diskName());

        $user->signature_image = null;
        $user->save();

        if ($oldSignature && $disk->exists($oldSignature)) {
            $disk->delete($oldSignature);
        }

        return response()->json([
            'success' => true,
            'message' => 'បានលុបហត្ថលេខាដោយជោគជ័យ',
        ], 200);
    }
 public function getProfile(Request $request)
{
    // ទាញយកទិន្នន័យ User ដែលកំពុង Login ព្រមទាំង Load Relationships ជាមួយ
    $user = $request->user()->load(['department', 'office', 'position', 'additionalPositions', 'outOfFrameworkStatuses', 'unpaidLeaves', 'publicWorkHistories', 'privateWorkHistories', 'userDecorations', 'disciplinaryActions', 'educations', 'languages', 'siblings', 'children']);

    return response()->json([
        'success' => true,
        'user' => new UserResource($user) // 🟢 ប្រើប្រាស់ UserResource ជំនួសឱ្យការកាត់ត Array ផ្ទាល់ខ្លួន
    ], 200);
}
}