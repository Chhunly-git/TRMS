<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::first();
        $pos = Position::first();

        $types = ['CIVIL_SERVICE', 'STATUTORY', 'CONTRACT', 'OTHER'];
        $sampleNames = [
            ['kh' => 'សុខ ចាន់', 'en' => 'Sok Chan', 'gender' => 'ប្រុស'],
            ['kh' => 'កែវ ធីតា', 'en' => 'Keo Thida', 'gender' => 'ស្រី'],
            ['kh' => 'ជា វិបុល', 'en' => 'Chea Vibol', 'gender' => 'ប្រុស'],
            ['kh' => 'ហេង សុភា', 'en' => 'Heng Sophea', 'gender' => 'ស្រី'],
            ['kh' => 'ស៊ិន វណ្ណា', 'en' => 'Sin Vanna', 'gender' => 'ប្រុស'],
        ];

        foreach ($sampleNames as $index => $item) {
            Employee::create([
                'employee_type'    => $types[$index % 4],
                'person_id'        => 'EMP' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'official_id'      => 'OFF' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'name_kh'          => $item['kh'],
                'name_en'          => $item['en'],
                'gender'           => $item['gender'],
                'dob'              => '1992-05-15',
                'phone'            => '01234567' . $index,
                'email'            => 'employee' . ($index + 1) . '@example.com',
                'department_id'    => $dept?->id,
                'position_id'      => $pos?->id,
                'status'           => 'ACTIVE',
            ]);
        }
    }
}