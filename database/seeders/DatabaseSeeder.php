<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@alsrage.com'],
            [
                'name' => 'Admin Alsrage',
                'password' => 'admin123',
                'phone' => '0500000000',
                'role' => 'admin',
            ]
        );

        $orderStatuses = [
            ['key' => 'pending', 'label' => 'قيد الانتظار', 'color' => '#ffc107', 'sort_order' => 1],
            ['key' => 'processing', 'label' => 'قيد التنفيذ', 'color' => '#17a2b8', 'sort_order' => 2],
            ['key' => 'completed', 'label' => 'مكتمل', 'color' => '#28a745', 'sort_order' => 3],
            ['key' => 'canceled', 'label' => 'ملغي', 'color' => '#dc3545', 'sort_order' => 4],
        ];

        foreach ($orderStatuses as $status) {
            Setting::updateOrCreate(
                ['group' => 'order_status', 'key' => $status['key']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
