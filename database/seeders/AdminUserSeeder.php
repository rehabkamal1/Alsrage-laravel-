<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@alsrage.com'],
            [
                'name' => 'Admin',
                'password' => 'admin123',
                'role' => 'admin',
                'phone' => '01000000000',
            ]
        );
    }
}
