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
            'status' => $this->status,

            // អង្គភាព & តួនាទី
            'employee_code' => $this->employee_code,
            'mef_card_number' => $this->mef_card_number,
            'employee_type' => $this->employee_type,
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
            'passport_number' => $this->passport_number,
            'passport_expired_date' => $this->passport_expired_date ? $this->passport_expired_date->format('Y-m-d') : null,
            
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}