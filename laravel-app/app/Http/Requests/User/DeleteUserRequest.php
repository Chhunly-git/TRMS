<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DeleteUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $currentUser = $this->user();
        $targetId = (int) $this->route('id');

        // ១. ត្រូវតែជា ADMIN ឬអ្នកដែលមានសិទ្ធិ users ទើបមានសិទ្ធិលុប
        if (!$currentUser) {
            return false;
        }

        if (strtoupper($currentUser->level ?? '') === 'ADMIN' || $currentUser->hasPermission('users')) {
            // មិនអនុញ្ញាតឱ្យលុបគណនីរបស់ខ្លួនឯងឡើយ
            if ($currentUser->id === $targetId) {
                return false;
            }

            // ប្រសិនបើមិនមែនជា ADMIN មិនអាចលុបគណនី ADMIN បានឡើយ
            if (strtoupper($currentUser->level ?? '') !== 'ADMIN') {
                $targetUser = \App\Models\User::find($targetId);
                if ($targetUser && strtoupper($targetUser->level ?? '') === 'ADMIN') {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}