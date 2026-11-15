<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_seeder_creates_and_updates_the_admin_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $admin = User::where('email', 'admin@alsrage.com')->firstOrFail();

        $this->assertSame('Admin', $admin->name);
        $this->assertSame('01000000000', $admin->phone);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('admin123', $admin->password));
    }
}
