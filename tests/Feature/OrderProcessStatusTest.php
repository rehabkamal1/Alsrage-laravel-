<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderProcessStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_process_status_settings_are_separate_from_musaned_statuses(): void
    {
        Setting::create([
            'group' => 'order_status',
            'key' => 'musaned-pending',
            'label' => 'بانتظار سداد مساند',
            'is_active' => true,
        ]);
        Setting::create([
            'group' => 'order_process_status',
            'key' => 'in-progress',
            'label' => 'قيد التنفيذ',
            'is_active' => true,
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->getJson('/api/settings/order-statuses')
            ->assertOk()
            ->assertJsonPath('data.0.key', 'musaned-pending');
        $this->getJson('/api/settings/order-process-statuses')
            ->assertOk()
            ->assertJsonPath('data.0.key', 'in-progress');

        $this->postJson('/api/settings/order-process-statuses', [
            'statuses' => [
                [
                    'key' => 'completed',
                    'label' => 'مكتمل',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('settings', [
            'group' => 'order_process_status',
            'key' => 'completed',
        ]);
        $this->assertDatabaseMissing('settings', [
            'group' => 'order_status',
            'key' => 'completed',
        ]);
    }

    public function test_order_process_status_can_be_updated_and_filtered_and_must_be_active(): void
    {
        $activeStatus = Setting::create([
            'group' => 'order_process_status',
            'key' => 'in-progress',
            'label' => 'قيد التنفيذ',
            'is_active' => true,
        ]);
        Setting::create([
            'group' => 'order_process_status',
            'key' => 'inactive-status',
            'label' => 'غير نشط',
            'is_active' => false,
        ]);
        $order = Order::create([
            'visa_holder_name' => 'طلب تجريبي',
            'status' => 'musaned-pending',
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->patchJson("/api/orders/{$order->id}", [
            'order_status' => $activeStatus->key,
        ])->assertOk();

        $this->assertSame($activeStatus->key, $order->fresh()->order_status);
        $this->getJson('/api/orders?order_status=in-progress')
            ->assertOk()
            ->assertJsonPath('data.0.order_status', 'in-progress');

        $this->patchJson("/api/orders/{$order->id}", [
            'order_status' => 'inactive-status',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('order_status');
    }

    public function test_orders_can_be_filtered_by_exact_service_type(): void
    {
        $matchingOrder = Order::create([
            'visa_holder_name' => 'طلب خدمة تجديد',
            'service_type' => 'renewal',
        ]);
        Order::create([
            'visa_holder_name' => 'طلب خدمة تجديد سريع',
            'service_type' => 'renewal-fast',
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->getJson('/api/orders?service_type=renewal')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingOrder->id);
    }

    public function test_changing_order_statuses_does_not_remove_order_from_tracking(): void
    {
        Setting::create([
            'group' => 'order_process_status',
            'key' => 'in-progress',
            'label' => 'قيد التنفيذ',
            'is_active' => true,
        ]);
        Setting::create([
            'group' => 'order_status',
            'key' => 'musaned-paid',
            'label' => 'تم السداد',
            'is_active' => true,
        ]);
        $order = Order::create([
            'visa_holder_name' => 'طلب متابعة',
            'status' => 'pending',
        ]);
        OrderTracking::create([
            'order_id' => $order->id,
            'is_authenticated' => false,
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->patchJson("/api/orders/{$order->id}", [
            'status' => 'musaned-paid',
            'order_status' => 'in-progress',
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'musaned-paid',
            'order_status' => 'in-progress',
        ]);
        $this->getJson('/api/order-tracking')
            ->assertOk()
            ->assertJsonPath('data.0.order_id', $order->id);
    }

    public function test_tracking_workflow_status_routes_orders_to_the_correct_pages(): void
    {
        $reviewedOrder = Order::create([
            'visa_holder_name' => 'طلب تمت مراجعته',
        ]);
        $reviewedTracking = OrderTracking::create([
            'order_id' => $reviewedOrder->id,
        ]);
        $certifiedOrder = Order::create([
            'visa_holder_name' => 'طلب تم التصديق عليه',
        ]);
        $certifiedTracking = OrderTracking::create([
            'order_id' => $certifiedOrder->id,
        ]);
        $activeOrder = Order::create([
            'visa_holder_name' => 'طلب قيد المتابعة',
        ]);
        $activeTracking = OrderTracking::create([
            'order_id' => $activeOrder->id,
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->putJson("/api/order-tracking/{$reviewedTracking->id}", [
            'workflow_status' => OrderTracking::WORKFLOW_STATUS_REVIEWED,
        ])->assertOk();
        $this->putJson("/api/order-tracking/{$certifiedTracking->id}", [
            'workflow_status' => OrderTracking::WORKFLOW_STATUS_CERTIFIED,
        ])->assertOk();

        $this->getJson('/api/order-tracking')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_id', $activeOrder->id);
        $this->getJson('/api/orders?tracking_status=reviewed')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reviewedOrder->id);
        $this->getJson('/api/orders?tracking_status=certified')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $certifiedOrder->id);
    }

    public function test_tracking_pages_include_legacy_completed_records(): void
    {
        $reachedOrder = Order::create([
            'visa_holder_name' => 'طلب تمت خدمته قديماً',
        ]);
        OrderTracking::create([
            'order_id' => $reachedOrder->id,
            'is_authenticated' => true,
        ]);

        $completedOrder = Order::create([
            'visa_holder_name' => 'طلب مكتمل قديماً',
            'order_status' => 'completed',
        ]);

        $legacyPaymentCompletedOrder = Order::create([
            'visa_holder_name' => 'طلب مكتمل قبل فصل الحالات',
            'status' => 'مكتمل',
        ]);

        Sanctum::actingAs($this->createEmployee());

        $this->getJson('/api/orders?tracking_status=reviewed')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reachedOrder->id);

        $completedResponse = $this->getJson('/api/orders?tracking_status=certified')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $completedIds = collect($completedResponse->json('data'))->pluck('id');
        $this->assertTrue($completedIds->contains($completedOrder->id));
        $this->assertTrue($completedIds->contains($legacyPaymentCompletedOrder->id));
    }

    public function test_invalid_tracking_workflow_status_is_rejected(): void
    {
        $order = Order::create(['visa_holder_name' => 'طلب متابعة']);
        $tracking = OrderTracking::create(['order_id' => $order->id]);
        Sanctum::actingAs($this->createEmployee());

        $this->putJson("/api/order-tracking/{$tracking->id}", [
            'workflow_status' => 'unknown',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('workflow_status');
    }

    public function test_authenticating_tracking_does_not_change_order_status(): void
    {
        $order = Order::create([
            'visa_holder_name' => 'طلب توثيق',
            'status' => 'musaned-pending',
        ]);
        $tracking = OrderTracking::create([
            'order_id' => $order->id,
            'is_authenticated' => false,
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->putJson("/api/order-tracking/{$tracking->id}", [
            'is_authenticated' => true,
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'musaned-pending',
            'order_status' => null,
        ]);
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
