<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionPaymentMethodValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_method_configured_in_settings_requires_beneficiary_bank(): void
    {
        $client = $this->createClient();
        Setting::create([
            'group' => 'payment_method',
            'key' => 'custom-wire',
            'label' => 'تحويل بنكي فوري',
            'is_active' => true,
        ]);
        Sanctum::actingAs($this->createEmployee());

        $this->postJson('/api/transactions', [
            'type' => 'receipt',
            'amount' => 100,
            'client_id' => $client->id,
            'payment_method' => 'custom-wire',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bank_name');
    }

    public function test_non_bank_method_does_not_require_and_clears_beneficiary_bank(): void
    {
        $client = $this->createClient();
        Setting::create([
            'group' => 'payment_method',
            'key' => 'cash-desk',
            'label' => 'نقدي',
            'is_active' => true,
        ]);
        Sanctum::actingAs($this->createEmployee());

        $response = $this->postJson('/api/transactions', [
            'type' => 'receipt',
            'amount' => 100,
            'client_id' => $client->id,
            'payment_method' => 'cash-desk',
            'bank_name' => 'old-bank',
        ])->assertCreated();

        $this->assertDatabaseHas('transactions', [
            'id' => $response->json('data.id'),
            'bank_name' => null,
        ]);
    }

    public function test_bank_method_remains_valid_for_partial_updates_with_existing_bank(): void
    {
        $client = $this->createClient();
        $employee = $this->createEmployee();
        Setting::create([
            'group' => 'payment_method',
            'key' => 'bank-method',
            'label' => 'تحويل عبر البنك',
            'is_active' => true,
        ]);
        $transaction = Transaction::create([
            'type' => 'receipt',
            'amount' => 100,
            'client_id' => $client->id,
            'payment_method' => 'bank-method',
            'bank_name' => 'alrajhi',
        ]);
        Sanctum::actingAs($employee);

        $this->patchJson("/api/transactions/{$transaction->id}", [
            'notes' => 'تحديث ملاحظة',
        ])->assertOk();
    }

    private function createClient(): Client
    {
        return Client::create([
            'name' => 'مندوب اختبار',
            'client_type' => 'فردي',
            'phone' => '0501234567',
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
