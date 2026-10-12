<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@cashflow.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@cashflow.com',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'is_email_verified' => true,
                'is_active' => true,
                'customer_id' => 'ADMIN-001',
            ]
        );

        $this->command->info('Admin created: admin@cashflow.com / Admin@123');
    }
}
