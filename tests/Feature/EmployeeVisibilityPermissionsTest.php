<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\Transaction;
use App\Support\PermissionAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeVisibilityPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactions_are_not_available_when_hidden_for_employee(): void
    {
        Sanctum::actingAs($this->createEmployee([
            PermissionAccess::HIDE_TRANSACTIONS,
        ]));

        $this->getJson('/api/transactions')->assertForbidden();
        $this->getJson('/api/finance/summary')->assertForbidden();
        $this->getJson('/api/reports/financial-collections')->assertForbidden();
    }

    public function test_delegate_numbers_are_hidden_and_preserved_when_restricted(): void
    {
        $employee = $this->createEmployee([
            PermissionAccess::HIDE_DELEGATE_NUMBERS,
        ]);
        Sanctum::actingAs($employee);

        $client = Client::create([
            'name' => 'مندوب تجريبي',
            'client_type' => 'فردي',
            'phone' => '0501234567',
            'additional_phone' => '0507654321',
        ]);
        $order = Order::create([
            'client_id' => $client->id,
            'total_price' => 200,
            'musaned_paid' => 100,
        ]);
        $tracking = OrderTracking::create([
            'order_id' => $order->id,
            'delegate_phone' => '0501234567',
            'sponsor_number' => '0507654321',
        ]);
        Transaction::create([
            'type' => 'receipt',
            'amount' => 125,
            'client_id' => $client->id,
        ]);

        $this->getJson("/api/clients/{$client->id}")
            ->assertOk()
            ->assertJsonPath('data.phone', null)
            ->assertJsonPath('data.additional_phone', null);
        $this->getJson('/api/clients')
            ->assertOk()
            ->assertJsonPath('data.0.transactions.0.amount', '125.00');
        $this->getJson('/api/order-tracking')
            ->assertOk()
            ->assertJsonPath('data.0.delegate_phone', null)
            ->assertJsonPath('data.0.sponsor_number', null);
        $this->getJson('/api/reports/financial-collections')
            ->assertOk()
            ->assertJsonPath('orders.0.client_phone', null)
            ->assertJsonPath('clients.0.phone', null);

        $this->putJson("/api/clients/{$client->id}", [
            'phone' => '0509999999',
            'additional_phone' => '0508888888',
        ])->assertOk();
        $this->putJson("/api/order-tracking/{$tracking->id}", [
            'delegate_phone' => '0509999999',
            'sponsor_number' => '0508888888',
            'notes' => 'تحديث عادي',
        ])->assertOk();

        $this->assertSame('0501234567', $client->fresh()->phone);
        $this->assertSame('0507654321', $client->fresh()->additional_phone);
        $this->assertSame('0501234567', $tracking->fresh()->delegate_phone);
        $this->assertSame('0507654321', $tracking->fresh()->sponsor_number);
    }

    public function test_legacy_employees_keep_existing_data_visibility_by_default(): void
    {
        $employee = $this->createEmployee();
        Sanctum::actingAs($employee);

        $client = Client::create([
            'name' => 'مندوب تجريبي',
            'client_type' => 'فردي',
            'phone' => '0501234567',
            'additional_phone' => '0507654321',
        ]);

        $this->getJson('/api/transactions')->assertOk();
        $this->getJson('/api/finance/summary')->assertOk();
        $this->getJson("/api/clients/{$client->id}")
            ->assertOk()
            ->assertJsonPath('data.phone', '0501234567')
            ->assertJsonPath('data.additional_phone', '0507654321');
    }

    private function createEmployee(array $permissions = []): Employee
    {
        return Employee::create([
            'name' => 'موظف تجريبي',
            'phone' => '0500000000',
            'username' => 'employee-'.uniqid(),
            'password' => 'password',
            'permissions' => $permissions,
        ]);
    }
}
