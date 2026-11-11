<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ReportController;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SettingServiceCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_cache_remains_a_collection_when_cache_disallows_serialized_classes(): void
    {
        $cache = app('cache');
        $originalStore = $cache->getDefaultDriver();
        $cache->setDefaultDriver('database');
        $cache->purge('database');

        try {
            Setting::create([
                'group' => 'service_type',
                'key' => 'test-service',
                'label' => 'خدمة اختبار',
                'is_active' => true,
                'sort_order' => 1,
            ]);

            $service = app(SettingService::class);
            $service->getGroup('service_type');
            $cachedSettings = $service->getGroup('service_type');

            $this->assertInstanceOf(Collection::class, $cachedSettings);
            $this->assertSame('test-service', $cachedSettings->first()['key']);

            $reportController = app(ReportController::class);
            $request = Request::create('/api/reports/order-follow-up', 'GET');
            $reportController->orderFollowUp($request);
            $reportResponse = $reportController->orderFollowUp($request);

            $this->assertSame(200, $reportResponse->getStatusCode());
            $this->assertSame([], $reportResponse->getData(true)['authentication_statuses']);
        } finally {
            $cache->setDefaultDriver($originalStore);
        }
    }
}
