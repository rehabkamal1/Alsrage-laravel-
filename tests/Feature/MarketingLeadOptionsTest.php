<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\MarketingLeadController;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingLeadOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_status_options_include_the_default_status(): void
    {
        $response = app(MarketingLeadController::class)->getStatuses();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('new', $response->getData(true)['data'][0]['key']);
        $this->assertSame('جديد', $response->getData(true)['data'][0]['label']);
    }

    public function test_marketing_priority_options_use_active_priority_settings(): void
    {
        Setting::create([
            'group' => 'priority_level',
            'key' => 'high',
            'label' => 'عالية',
            'color' => '#ef4444',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Setting::create([
            'group' => 'priority_level',
            'key' => 'inactive',
            'label' => 'غير نشطة',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $response = app(MarketingLeadController::class)->getPriorityLevels();
        $options = $response->getData(true)['data'];

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $options);
        $this->assertSame('high', $options[0]['key']);
    }
}
