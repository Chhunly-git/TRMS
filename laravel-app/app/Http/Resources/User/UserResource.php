<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ព័ត៌មានគណនី
            'id' => $this->id,
            'name' => $this->name,
            'name_kh' => $this->name_kh,
            'name_en' => $this->name_en,
            'email' => $this->email,
            'profile_image' => $this->profile_image,
            'profile_thumbnail' => $this->profile_thumbnail,
            'password_null' => $this->password_null,
            'level' => $this->level,
            'permissions' => $this->permissions ?? ($this->level === 'ADMIN' ? ['all'] : ['profile', 'my-attendances', 'document-templates']),
            'status' => $this->status,

            // អង្គភាព & តួនាទី
            'employee_code' => $this->employee_code,
            'mef_card_number' => $this->mef_card_number,
            'employee_type' => $this->employee_type,
            'officer_status' => $this->officer_status ?? 'ACTIVE',
            'officer_status_date' => $this->officer_status_date ? (is_string($this->officer_status_date) ? $this->officer_status_date : $this->officer_status_date->format('Y-m-d')) : null,
            'officer_status_reason' => $this->officer_status_reason,
            'service_duration' => $this->service_duration,
            'service_duration_formatted' => $this->service_duration_formatted,
            'department_id' => $this->department_id,
            'office_id' => $this->office_id,
            'position_id' => $this->position_id,

            // Relationships (បង្ហាញឈ្មោះ នាយកដ្ឋាន, ការិយាល័យ, តួនាទី)
            'department' => $this->whenLoaded('department'),
            'office' => $this->whenLoaded('office'),
            'position' => $this->whenLoaded('position'),

            // ព័ត៌មានផ្ទាល់ខ្លួន
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'dob' => $this->dob ? $this->dob->format('Y-m-d') : null,
            'phone' => $this->phone,
            'birth_place' => $this->birth_place,
            'current_address' => $this->current_address,

            // អត្តសញ្ញាណប័ណ្ណ & លិខិតឆ្លងដែន
            'national_id_number' => $this->national_id_number,
            'national_id_expired_date' => $this->national_id_expired_date ? $this->national_id_expired_date->format('Y-m-d') : null,
            'national_id_file' => $this->national_id_file,
            'passport_number' => $this->passport_number,
            'passport_expired_date' => $this->passport_expired_date ? $this->passport_expired_date->format('Y-m-d') : null,
            'passport_file' => $this->passport_file,
            
            'first_service_date' => $this->first_service_date,
        'first_appointment_date' => $this->first_appointment_date,
        'initial_framework' => $this->initial_framework,
        'initial_position' => $this->initial_position,
        'initial_ministry' => $this->initial_ministry,
        'initial_unit' => $this->initial_unit,
        'initial_department' => $this->initial_department,
        'initial_office' => $this->initial_office,

        'current_framework' => $this->current_framework,
        'current_appointment_date' => $this->current_appointment_date,
        'current_position_date' => $this->current_position_date,

        // ៧. ស្ថានភាពគ្រួសារ
        'father_name' => $this->father_name,
        'father_latin_name' => $this->father_latin_name,
        'father_status' => $this->father_status,
        'father_dob' => $this->father_dob,
        'father_nationality' => $this->father_nationality,
        'father_address' => $this->father_address,
        'father_occupation' => $this->father_occupation,
        'father_unit' => $this->father_unit,

        'mother_name' => $this->mother_name,
        'mother_latin_name' => $this->mother_latin_name,
        'mother_status' => $this->mother_status,
        'mother_dob' => $this->mother_dob,
        'mother_nationality' => $this->mother_nationality,
        'mother_address' => $this->mother_address,
        'mother_occupation' => $this->mother_occupation,
        'mother_unit' => $this->mother_unit,

        'spouse_name' => $this->spouse_name,
        'spouse_latin_name' => $this->spouse_latin_name,
        'spouse_status' => $this->spouse_status,
        'spouse_dob' => $this->spouse_dob,
        'spouse_nationality' => $this->spouse_nationality,
        'spouse_birthplace' => $this->spouse_birthplace,
        'spouse_occupation' => $this->spouse_occupation,
        'spouse_unit' => $this->spouse_unit,
        'spouse_allowance' => $this->spouse_allowance,
        'spouse_phone' => $this->spouse_phone,

        'siblings' => $this->relationLoaded('siblings') ? $this->siblings : [],
        'children' => $this->relationLoaded('children') ? $this->children : [],

        'additional_positions' => $this->relationLoaded('additionalPositions') ? $this->additionalPositions : [],
        'out_of_framework_statuses' => $this->relationLoaded('outOfFrameworkStatuses') ? $this->outOfFrameworkStatuses : [],
        'unpaid_leaves' => $this->relationLoaded('unpaidLeaves') ? $this->unpaidLeaves : [],

        'public_work_histories' => $this->relationLoaded('publicWorkHistories') ? $this->publicWorkHistories : [],
        'private_work_histories' => $this->relationLoaded('privateWorkHistories') ? $this->privateWorkHistories : [],
        'user_decorations' => $this->relationLoaded('userDecorations') ? $this->userDecorations : [],
        'disciplinary_actions' => $this->relationLoaded('disciplinaryActions') ? $this->disciplinaryActions : [],
        'educations' => $this->relationLoaded('educations') ? $this->educations : [],
        'languages' => $this->relationLoaded('languages') ? $this->languages : [],

        'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}