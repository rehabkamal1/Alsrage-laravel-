<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientOrderSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_a_clients_responsible_employee_updates_the_clients_orders_and_reports(): void
    {
        $previousEmployee = $this->createEmployee('موظف سابق');
        $newEmployee = $this->createEmployee('موظف جديد');
        $client = Client::create([
            'name' => 'مندوب قبل التعديل',
            'client_type' => 'individual',
            'phone' => '0501234567',
            'employee_id' => $previousEmployee->id,
        ]);
        $order = Order::create([
            'client_id' => $client->id,
            'employee_id' => $previousEmployee->id,
            'visa_holder_name' => 'صاحب التأشيرة',
            'total_price' => 100,
            'musaned_paid' => 50,
        ]);
        Sanctum::actingAs($this->createEmployee('موظف النظام'));

        $this->putJson("/api/clients/{$client->id}", [
            'name' => 'مندوب بعد التعديل',
            'phone' => '0507654321',
            'employee_id' => $newEmployee->id,
        ])->assertOk()
            ->assertJsonPath('data.name', 'مندوب بعد التعديل')
            ->assertJsonPath('data.phone', '0507654321');

        $this->assertSame($newEmployee->id, $order->fresh()->employee_id);
        $this->assertSame($client->id, $order->fresh()->client_id);

        $this->getJson("/api/reports/financial-collections?client_id={$client->id}&employee_id={$newEmployee->id}")
            ->assertOk()
            ->assertJsonPath('orders.0.id', $order->id)
            ->assertJsonPath('orders.0.client_name', 'مندوب بعد التعديل')
            ->assertJsonPath('orders.0.client_phone', '0507654321')
            ->assertJsonPath('orders.0.employee_id', $newEmployee->id);
    }

    private function createEmployee(string $name): Employee
    {
        return Employee::create([
            'name' => $name,
            'phone' => '050'.random_int(1000000, 9999999),
            'username' => 'employee-'.uniqid(),
            'password' => 'password',
            'permissions' => [],
        ]);
    }
}
