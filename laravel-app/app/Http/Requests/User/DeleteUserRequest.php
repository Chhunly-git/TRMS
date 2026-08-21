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

        // ១. ត្រូវតែជា ADMIN ទើបមានសិទ្ធិលុប
        if (!$currentUser || $currentUser->level !== 'ADMIN') {
            return false;
        }

        // ២. មិនអនុញ្ញាតឱ្យ ADMIN លុបគណនីរបស់ខ្លួនឯងឡើយ
        return $currentUser->id !== $targetId;
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