<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public const TTL = 3600;

    public function getGroup(string $group, array $select = ['id', 'key', 'label', 'color', 'sort_order', 'is_active']): Collection
    {
        $settings = Cache::remember("settings:v2:{$group}", self::TTL, function () use ($group, $select) {
            return Setting::query()
                ->where('group', $group)
                ->orderBy('sort_order')
                ->get($select)
                ->toArray();
        });

        return collect($settings);
    }

    public function ensureAuthenticationDefaults(): Collection
    {
        $statuses = $this->getGroup('authentication_status');

        if ($statuses->isNotEmpty()) {
            return $statuses;
        }

        $defaults = [
            ['key' => 'documented', 'label' => 'تم التوثيق', 'color' => '#10b981', 'sort_order' => 1],
            ['key' => 'under_process', 'label' => 'قيد التوثيق', 'color' => '#3b82f6', 'sort_order' => 2],
            ['key' => 'pending', 'label' => 'بانتظار التوثيق', 'color' => '#f59e0b', 'sort_order' => 3],
            ['key' => 'rejected', 'label' => 'مرفوض', 'color' => '#ef4444', 'sort_order' => 4],
            ['key' => 'cancelled', 'label' => 'ملغي', 'color' => '#6b7280', 'sort_order' => 5],
        ];

        foreach ($defaults as $status) {
            Setting::updateOrCreate(
                ['group' => 'authentication_status', 'key' => $status['key']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        Cache::forget('settings:v2:authentication_status');

        return $this->getGroup('authentication_status');
    }

    public function bulkUpsert(string $group, array $items, ?string $keyName = 'key'): void
    {
        foreach ($items as $item) {
            $payload = [
                'label' => $item['label'] ?? '',
                'color' => $item['color'] ?? '#6c757d',
                'sort_order' => $item['sort_order'] ?? 0,
                'is_active' => $item['is_active'] ?? true,
            ];

            if ($group === 'order_status' || $group === 'order_process_status') {
                $payload['target_days'] = $item['target_days'] ?? 60;
            }

            if ($group === 'arrival_destination') {
                $payload['nationality_key'] = $item['nationality_key'] ?? null;
            }

            Setting::updateOrCreate(
                ['group' => $group, 'key' => $item[$keyName] ?? ''],
                $payload
            );
        }

        Cache::forget("settings:v2:{$group}");

        if ($group === 'order_status') {
            Cache::forget('report_order_status_settings:v2');
        } elseif ($group === 'authentication_status') {
            Cache::forget('report_auth_status_settings:v2');
        }
    }
}
