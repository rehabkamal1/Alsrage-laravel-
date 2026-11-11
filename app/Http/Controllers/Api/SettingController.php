<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePriorityLevelsRequest;
use App\Http\Requests\UpdatePassportStatusesRequest;
use App\Http\Requests\UpdateTransferStatusesRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService)
    {
    }

    private function getGroupData(string $group, array $select = ['id', 'key', 'label', 'color', 'sort_order', 'is_active'])
    {
        return $this->settingService->getGroup($group, $select);
    }

    private function bulkUpdate(string $group, array $items, string $message, ?string $keyField = 'key')
    {
        $this->settingService->bulkUpsert($group, $items, $keyField);

        return response()->json(['message' => $message]);
    }

    private function deleteSetting(string $group, int $id)
    {
        $setting = Setting::where('group', $group)->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }

        $setting->delete();
        Cache::forget("settings:{$group}");

        if ($group === 'order_status') {
            Cache::forget('report_order_status_settings');
        } elseif ($group === 'authentication_status') {
            Cache::forget('report_auth_status_settings');
        }

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function getPriorityLevels()
    {
        return response()->json(['data' => $this->getGroupData('priority_level')]);
    }

    public function getPassportStatuses()
    {
        return response()->json(['data' => $this->getGroupData('passport_status')]);
    }

    public function getTransferStatuses()
    {
        return response()->json(['data' => $this->getGroupData('transfer_status')]);
    }

    public function getPaymentMethods()
    {
        return response()->json(['data' => $this->getGroupData('payment_method')]);
    }

    public function getBankNames()
    {
        return response()->json(['data' => $this->getGroupData('bank_name')]);
    }

    public function getServiceTypes()
    {
        return response()->json(['data' => $this->getGroupData('service_type')]);
    }

    public function getOrderStatuses()
    {
        return response()->json(['data' => $this->getGroupData('order_status', ['id', 'key', 'label', 'color', 'sort_order', 'target_days', 'is_active'])]);
    }

    public function getOrderProcessStatuses()
    {
        return response()->json(['data' => $this->getGroupData('order_process_status')]);
    }

    public function getAuthenticationStatuses()
    {
        $statuses = $this->settingService->ensureAuthenticationDefaults();

        return response()->json(['data' => $statuses]);
    }

    public function getAuthorizationStatuses()
    {
        return response()->json(['data' => $this->getGroupData('authorization_status')]);
    }

    public function updatePriorityLevels(UpdatePriorityLevelsRequest $request)
    {
        return $this->bulkUpdate('priority_level', $request->input('levels', []), 'تم حفظ درجات الأهمية بنجاح');
    }

    public function updatePassportStatuses(UpdatePassportStatusesRequest $request)
    {
        return $this->bulkUpdate('passport_status', $request->input('statuses', []), 'تم حفظ حالات ترشيح الجواز بنجاح');
    }

    public function updateTransferStatuses(UpdateTransferStatusesRequest $request)
    {
        return $this->bulkUpdate('transfer_status', $request->input('statuses', []), 'تم حفظ حالات التحويل بنجاح');
    }

    public function updatePaymentMethods(Request $request)
    {
        return $this->bulkUpdate('payment_method', $request->input('methods', []), 'تم حفظ طرق الدفع بنجاح');
    }

    public function updateBankNames(Request $request)
    {
        return $this->bulkUpdate('bank_name', $request->input('banks', []), 'تم حفظ أسماء البنوك بنجاح');
    }

    public function updateOrderStatuses(Request $request)
    {
        return $this->bulkUpdate('order_status', $request->input('statuses', []), 'تم حفظ حالات الطلبات بنجاح');
    }

    public function updateOrderProcessStatuses(Request $request)
    {
        $request->validate([
            'statuses' => ['required', 'array'],
            'statuses.*.key' => ['required', 'string', 'max:255'],
            'statuses.*.label' => ['required', 'string', 'max:255'],
            'statuses.*.color' => ['nullable', 'string', 'max:20'],
            'statuses.*.sort_order' => ['nullable', 'integer'],
            'statuses.*.is_active' => ['sometimes', 'boolean'],
        ]);

        return $this->bulkUpdate('order_process_status', $request->input('statuses', []), 'تم حفظ حالات الطلب بنجاح');
    }

    public function deletePriorityLevel(int $id)
    {
        return $this->deleteSetting('priority_level', $id);
    }

    public function deletePassportStatus(int $id)
    {
        return $this->deleteSetting('passport_status', $id);
    }

    public function deleteTransferStatus(int $id)
    {
        return $this->deleteSetting('transfer_status', $id);
    }

    public function deletePaymentMethod(int $id)
    {
        return $this->deleteSetting('payment_method', $id);
    }

    public function deleteBankName(int $id)
    {
        return $this->deleteSetting('bank_name', $id);
    }

    public function updateServiceTypes(Request $request)
    {
        return $this->bulkUpdate('service_type', $request->input('types', $request->input('statuses', [])), 'تم حفظ أنواع الخدمات بنجاح');
    }

    public function updateAuthenticationStatuses(Request $request)
    {
        return $this->bulkUpdate('authentication_status', $request->input('statuses', []), 'تم حفظ حالات التوثيق بنجاح');
    }

    public function updateAuthorizationStatuses(Request $request)
    {
        return $this->bulkUpdate('authorization_status', $request->input('statuses', []), 'تم حفظ حالات التفويض بنجاح');
    }

    public function deleteOrderStatus(int $id)
    {
        return $this->deleteSetting('order_status', $id);
    }

    public function deleteOrderProcessStatus(int $id)
    {
        return $this->deleteSetting('order_process_status', $id);
    }

    public function deleteServiceType(int $id)
    {
        return $this->deleteSetting('service_type', $id);
    }

    public function deleteAuthenticationStatus(int $id)
    {
        return $this->deleteSetting('authentication_status', $id);
    }

    public function deleteAuthorizationStatus(int $id)
    {
        return $this->deleteSetting('authorization_status', $id);
    }

    public function getNationalities()
    {
        $nationalities = Setting::where('group', 'nationality')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $nationalities]);
    }

    public function getProfessions()
    {
        $professions = Setting::where('group', 'profession')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $professions]);
    }

    public function getArrivalDestinations()
    {
        $destinations = Setting::where('group', 'arrival_destination')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'nationality_key', 'is_active']);

        return response()->json(['data' => $destinations]);
    }

    public function updateArrivalDestinations(Request $request)
    {
        $request->validate([
            'destinations' => ['required', 'array'],
            'destinations.*.key' => ['required', 'string', 'max:255'],
            'destinations.*.label' => ['required', 'string', 'max:255'],
            'destinations.*.nationality_key' => [
                'required',
                'string',
                'max:255',
                'exists:settings,key',
            ],
        ], [
            'destinations.*.nationality_key.required' => 'يجب اختيار الجنسية لجهة الوصول',
            'destinations.*.nationality_key.exists' => 'الجنسية المحددة غير موجودة في الإعدادات',
        ]);

        $destinations = $request->input('destinations', []);

        $validNationalities = Setting::where('group', 'nationality')
            ->where('is_active', true)
            ->pluck('key')
            ->all();

        foreach ($destinations as $destination) {
            if (!in_array($destination['nationality_key'], $validNationalities, true)) {
                return response()->json([
                    'message' => 'الجنسية المحددة غير موجودة في الإعدادات',
                ], 422);
            }

            Setting::updateOrCreate(
                ['group' => 'arrival_destination', 'key' => $destination['key'] ?? ''],
                [
                    'label' => $destination['label'] ?? '',
                    'color' => $destination['color'] ?? '#6c757d',
                    'sort_order' => $destination['sort_order'] ?? 0,
                    'nationality_key' => $destination['nationality_key'],
                    'is_active' => $destination['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ جهات القدوم بنجاح']);
    }

    public function deleteArrivalDestination(int $id)
    {
        return $this->deleteSetting('arrival_destination', $id);
    }

    public function updateNationalities(Request $request)
    {
        $nationalities = $request->input('nationalities', $request->input('statuses', []));

        foreach ($nationalities as $nationality) {
            Setting::updateOrCreate(
                ['group' => 'nationality', 'key' => $nationality['key'] ?? ''],
                [
                    'label' => $nationality['label'] ?? '',
                    'color' => $nationality['color'] ?? '#6c757d',
                    'sort_order' => $nationality['sort_order'] ?? 0,
                    'is_active' => $nationality['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ الجنسيات بنجاح']);
    }

    public function updateProfessions(Request $request)
    {
        $professions = $request->input('professions', $request->input('statuses', []));

        foreach ($professions as $profession) {
            Setting::updateOrCreate(
                ['group' => 'profession', 'key' => $profession['key'] ?? ''],
                [
                    'label' => $profession['label'] ?? '',
                    'color' => $profession['color'] ?? '#6c757d',
                    'sort_order' => $profession['sort_order'] ?? 0,
                    'is_active' => $profession['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ المهن بنجاح']);
    }

    public function deleteNationality(int $id)
    {
        return $this->deleteSetting('nationality', $id);
    }

    public function deleteProfession(int $id)
    {
        return $this->deleteSetting('profession', $id);
    }
}
