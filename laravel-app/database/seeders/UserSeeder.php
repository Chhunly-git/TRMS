<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'name_kh' => 'ថ្នាក់ដឹកនាំប្រព័ន្ធ',
            'email' => 'chhunlymeng470@gmail.com',
            'password' => Hash::make('password'),
            'level' => 'ADMIN',
            'status' => 'ACTIVE',
            'gender' => 'Male',
            'employee_type' => 'CIVIL_SERVICE'
        ]);
    }
}