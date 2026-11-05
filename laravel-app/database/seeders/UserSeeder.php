<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('SEED_ADMIN_PASSWORD');
        $isGenerated = false;

        if (empty($adminPassword)) {
            $adminPassword = Str::random(24);
            $isGenerated = true;
        }

        $user = User::firstOrCreate(
            ['email' => 'chhunlymeng470@gmail.com'],
            [
                'name' => 'Admin User',
                'name_kh' => 'ថ្នាក់ដឹកនាំប្រព័ន្ធ',
                'password' => Hash::make($adminPassword),
                'level' => 'ADMIN',
                'status' => 'ACTIVE',
                'gender' => 'Male',
                'employee_type' => 'CIVIL_SERVICE'
            ]
        );

        if ($isGenerated) {
            $this->command?->info("Admin user created/verified ({$user->email}) with generated password: {$adminPassword}");
            $this->command?->warn("Save this password immediately; it will not be displayed again.");
        } else {
            $this->command?->info("Admin user created/verified ({$user->email}) with password from SEED_ADMIN_PASSWORD.");
        }
    }
}