<?php

namespace Tests\Feature;

use App\Models\ExternalOffice;
use App\Models\SaudiOffice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeLicenseNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_external_office_license_number_is_rejected_with_an_arabic_message(): void
    {
        $this->actingAs(User::factory()->create());
        ExternalOffice::create([
            'name' => 'مكتب قائم',
            'license_number' => 'EXT-123',
        ]);

        $response = $this->postJson('/api/external-offices', [
            'name' => 'مكتب جديد',
            'license_number' => ' EXT-123 ',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مستخدم بالفعل لمكتب سعودي أو خارجي.');
    }

    public function test_duplicate_saudi_office_license_number_is_rejected_with_an_arabic_message(): void
    {
        $this->actingAs(User::factory()->create());
        SaudiOffice::create([
            'name' => 'مكتب قائم',
            'license_number' => 'SA-123',
        ]);

        $response = $this->postJson('/api/saudi-offices', [
            'name' => 'مكتب جديد',
            'license_number' => 'SA-123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مستخدم بالفعل لمكتب سعودي أو خارجي.');
    }

    public function test_license_number_is_required_for_both_office_types(): void
    {
        $this->actingAs(User::factory()->create());

        $this->postJson('/api/external-offices', [
            'name' => 'مكتب خارجي',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مطلوب.');

        $this->postJson('/api/saudi-offices', [
            'name' => 'مكتب سعودي',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مطلوب.');
    }

    public function test_marketing_office_creation_routes_also_reject_duplicate_license_numbers(): void
    {
        $this->actingAs(User::factory()->create());
        ExternalOffice::create([
            'name' => 'مكتب خارجي قائم',
            'license_number' => 'EXT-123',
        ]);
        SaudiOffice::create([
            'name' => 'مكتب سعودي قائم',
            'license_number' => 'SA-123',
        ]);

        $this->postJson('/api/marketing-leads/external-office', [
            'name' => 'مكتب خارجي جديد',
            'license_number' => 'EXT-123',
            'country' => 'السودان',
            'phone' => '123456',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مستخدم بالفعل لمكتب سعودي أو خارجي.');

        $this->postJson('/api/marketing-leads/saudi-office', [
            'name' => 'مكتب سعودي جديد',
            'license_number' => 'SA-123',
            'mobile' => '0550000000',
            'responsible_employee' => 'موظف',
            'destination' => 'الرياض',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مستخدم بالفعل لمكتب سعودي أو خارجي.');
    }

    public function test_duplicate_license_number_is_rejected_across_the_two_office_types(): void
    {
        $this->actingAs(User::factory()->create());
        ExternalOffice::create([
            'name' => 'مكتب خارجي قائم',
            'license_number' => 'SHARED-123',
        ]);

        $this->postJson('/api/saudi-offices', [
            'name' => 'مكتب سعودي جديد',
            'license_number' => 'SHARED-123',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مستخدم بالفعل لمكتب سعودي أو خارجي.');
    }

    public function test_an_office_can_keep_its_own_license_number_when_updated(): void
    {
        $this->actingAs(User::factory()->create());
        $externalOffice = ExternalOffice::create([
            'name' => 'مكتب خارجي',
            'license_number' => 'EXT-123',
        ]);
        $saudiOffice = SaudiOffice::create([
            'name' => 'مكتب سعودي',
            'license_number' => 'SA-123',
        ]);

        $this->putJson("/api/external-offices/{$externalOffice->id}", [
            'name' => 'مكتب خارجي محدث',
            'license_number' => 'EXT-123',
        ])->assertOk();

        $this->putJson("/api/saudi-offices/{$saudiOffice->id}", [
            'name' => 'مكتب سعودي محدث',
            'license_number' => 'SA-123',
        ])->assertOk();

        ExternalOffice::create([
            'name' => 'مكتب خارجي آخر',
            'license_number' => 'EXT-456',
        ]);

        $this->putJson("/api/saudi-offices/{$saudiOffice->id}", [
            'name' => 'مكتب سعودي محدث',
            'license_number' => 'EXT-456',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.license_number.0', 'رقم الترخيص مستخدم بالفعل لمكتب سعودي أو خارجي.');
    }
}
