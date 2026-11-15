<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingTargetDaysTest extends TestCase
{
    use RefreshDatabase;

    public function test_target_days_are_saved_and_returned_for_authentication_statuses_only(): void
    {
        Setting::create([
            'group' => 'authentication_status',
            'key' => 'waiting-auth',
            'label' => 'بانتظار التوثيق',
            'target_days' => 12,
            'is_active' => true,
        ]);
        Setting::create([
            'group' => 'order_status',
            'key' => 'waiting-musaned',
            'label' => 'بانتظار سداد مساند',
            'target_days' => 3,
            'is_active' => true,
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->getJson('/api/settings/order-statuses')
            ->assertOk()
            ->assertJsonMissingPath('data.0.target_days');
        $this->getJson('/api/settings/authentication-statuses')
            ->assertOk()
            ->assertJsonPath('data.0.target_days', 12);

        $this->postJson('/api/settings/authentication-statuses', [
            'statuses' => [
                [
                    'key' => 'waiting-auth',
                    'label' => 'بانتظار التوثيق',
                    'target_days' => 8,
                    'is_active' => true,
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('settings', [
            'group' => 'authentication_status',
            'key' => 'waiting-auth',
            'target_days' => 8,
        ]);
        $this->getJson('/api/settings/authentication-statuses')
            ->assertOk()
            ->assertJsonPath('data.0.target_days', 8);
    }

    public function test_order_follow_up_report_uses_authentication_target_days(): void
    {
        Carbon::setTestNow('2026-11-15 12:00:00');

        try {
            Setting::create([
                'group' => 'authentication_status',
                'key' => 'waiting-auth',
                'label' => 'بانتظار التوثيق',
                'target_days' => 5,
                'is_active' => true,
            ]);
            Setting::create([
                'group' => 'order_status',
                'key' => 'waiting-musaned',
                'label' => 'بانتظار سداد مساند',
                'target_days' => 20,
                'is_active' => true,
            ]);
            $order = Order::create([
                'visa_holder_name' => 'طلب اختبار المدد المستهدفة',
                'contract_date' => Carbon::now()->subDays(15)->toDateString(),
                'status' => 'waiting-musaned',
            ]);
            OrderTracking::create([
                'order_id' => $order->id,
                'authentication_status' => 'waiting-auth',
            ]);
            Sanctum::actingAs($this->createEmployee());

            $this->getJson('/api/reports/order-follow-up')
                ->assertOk()
                ->assertJsonPath('orders.0.delay_days', 10);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createEmployee(): Employee
    {
        return Employee::create([
            'name' => 'موظف اختبار',
            'phone' => '0500000000',
            'username' => 'employee-'.uniqid(),
            'password' => 'password',
            'permissions' => [],
        ]);
    }
}
