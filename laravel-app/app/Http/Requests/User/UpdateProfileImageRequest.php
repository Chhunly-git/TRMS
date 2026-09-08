<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $currentUser = $this->user();
        $targetId = (int) $this->route('id');

        // បើគ្មាន route('id') មានន័យថាជាការ update profile ផ្ទាល់ខ្លួន
        if (!$targetId) {
            return true;
        }

        // បើមាន route('id'): ADMIN ឬអ្នកមានសិទ្ធិ users អាចប្តូរឱ្យអ្នកណាទាំងអស់ ឬ User អាចប្តូរឱ្យតែខ្លួនឯង
        return $currentUser && ($currentUser->level === 'ADMIN' || $currentUser->hasPermission('users') || $currentUser->id === $targetId);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'profile_image' => [
                'required',
                'file',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:5120', // អតិបរមា 5MB
                'dimensions:width=454,height=454', // ឬ 'dimensions:ratio=1/1'
            ],
        ];
    }

    /**
     * Custom validation messages in Khmer.
     */
    public function messages(): array
    {
        return [
            'profile_image.required' => 'សូមជ្រើសរើសរូបថតផ្ទាល់ខ្លួន',
            'profile_image.image' => 'ឯកសារត្រូវតែជាប្រភេទរូបភាព',
            'profile_image.mimes' => 'រូបភាពត្រូវតែជាប្រភេទ png, jpg, jpeg ឬ webp',
            'profile_image.max' => 'ទំហំរូបភាពមិនត្រូវលើសពី 5MB ឡើយ',
            'profile_image.dimensions' => 'រូបភាពត្រូវតែមានទំហំទទឹង និងកម្ពស់ 454x454 ភីកសែល',
        ];
    }
}