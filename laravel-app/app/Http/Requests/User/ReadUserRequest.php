<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ReadUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $currentUser = $this->user();
        $targetId = (int) $this->route('id');

        // ១. បើជា Admin អាចមើលបានទាំងអស់
        if ($currentUser && $currentUser->level === 'ADMIN') {
            return true;
        }

        // ២. បើជា User ធម្មតា អាចមើលបានតែគណនីផ្ទាល់ខ្លួន
        return $currentUser && $currentUser->id === $targetId;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}