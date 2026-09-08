<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $currentUser = $this->user();
        $targetId = (int) $this->route('id');

        // អនុញ្ញាតឱ្យ Admin ឬ User ដែលមានសិទ្ធិ users កែសម្រួលបាន ឬ User ធម្មតាកែសម្រួលបានត្រឹមតែគណនីខ្លួនឯង
        if ($currentUser && ($currentUser->level === 'ADMIN' || $currentUser->hasPermission('users'))) {
            return true;
        }

        return $currentUser && $currentUser->id === $targetId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            // ព័ត៌មានគណនី
            'name_kh' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:6|max:255',
            'level' => 'nullable|string|in:ADMIN,USER',
            'status' => 'nullable|string|in:ENABLED,DISABLED',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

            // អង្គភាព & តួនាទី
            'employee_code' => 'nullable|string|max:50|unique:users,employee_code,' . $userId,
            'mef_card_number' => 'nullable|string|max:50|unique:users,mef_card_number,' . $userId,
            'employee_type' => 'nullable|string|in:CIVIL_SERVICE,STATUTORY,CONTRACT,OTHER',
            'officer_status' => 'nullable|string|in:ACTIVE,RESIGNED,RETIRED,TRANSFERRED,SUSPENDED,OTHER',
            'officer_status_date' => 'nullable|date',
            'officer_status_reason' => 'nullable|string|max:500',
            'department_id' => 'nullable|integer|exists:departments,id',
            'office_id' => 'nullable|integer|exists:offices,id',
            'position_id' => 'nullable|integer|exists:positions,id',

            // ព័ត៌មានផ្ទាល់ខ្លួន
            'gender' => 'nullable|string|in:MALE,FEMALE,M,F',
            'marital_status' => 'nullable|string|in:SINGLE,MARRIED,DIVORCED',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string|max:30',
            'birth_place' => 'nullable|string|max:255',
            'current_address' => 'nullable|string|max:500',

            // អត្តសញ្ញាណប័ណ្ណ & លិខិតឆ្លងដែន
            'national_id_number' => 'nullable|string|max:50',
            'national_id_expired_date' => 'nullable|date',
            'passport_number' => 'nullable|string|max:50',
            'passport_expired_date' => 'nullable|date',

            // ១. ព័ត៌មានបម្រើការងាររដ្ឋដំបូង
            'first_service_date' => 'nullable|date',
            'first_appointment_date' => 'nullable|date',
            'initial_framework' => 'nullable|string|max:255',
            'initial_position' => 'nullable|string|max:255',
            'initial_ministry' => 'nullable|string|max:255',
            'initial_unit' => 'nullable|string|max:255',
            'initial_department' => 'nullable|string|max:255',
            'initial_office' => 'nullable|string|max:255',

            // ២. ស្ថានភាពមុខងារបច្ចុប្បន្ន
            'current_framework' => 'nullable|string|max:255',
            'current_appointment_date' => 'nullable|date',
            'current_position_date' => 'nullable|date',
        ];
    }
}