<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePriorityLevelsRequest;
use App\Http\Requests\UpdatePassportStatusesRequest;
use App\Http\Requests\UpdateTransferStatusesRequest;
use App\Http\Requests\UpdateMarketingStatusesRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getPriorityLevels()
    {
        $levels = Setting::where('group', 'priority_level')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $levels]);
    }

    public function getPassportStatuses()
    {
        $statuses = Setting::where('group', 'passport_status')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $statuses]);
    }

    public function getTransferStatuses()
    {
        $statuses = Setting::where('group', 'transfer_status')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $statuses]);
    }

    public function getPaymentMethods()
    {
        $methods = Setting::where('group', 'payment_method')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $methods]);
    }

    public function getBankNames()
    {
        $banks = Setting::where('group', 'bank_name')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $banks]);
    }

    public function getMarketingStatuses()
    {
        $statuses = Setting::where('group', 'marketing_status')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $statuses]);
    }

    public function getOrderStatuses()
    {
        $statuses = Setting::where('group', 'order_status')
            ->orderBy('sort_order')
            ->get(['id', 'key', 'label', 'color', 'sort_order', 'is_active']);

        return response()->json(['data' => $statuses]);
    }

    public function updatePriorityLevels(UpdatePriorityLevelsRequest $request)
    {
        $levels = $request->input('levels', []);

        foreach ($levels as $level) {
            Setting::updateOrCreate(
                ['group' => 'priority_level', 'key' => $level['key']],
                [
                    'label' => $level['label'],
                    'color' => $level['color'] ?? '#6c757d',
                    'sort_order' => $level['sort_order'] ?? 0,
                    'is_active' => $level['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ درجات الأهمية بنجاح']);
    }

    public function updatePassportStatuses(UpdatePassportStatusesRequest $request)
    {
        $statuses = $request->input('statuses', []);

        foreach ($statuses as $status) {
            Setting::updateOrCreate(
                ['group' => 'passport_status', 'key' => $status['key']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'] ?? '#6c757d',
                    'sort_order' => $status['sort_order'] ?? 0,
                    'is_active' => $status['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ حالات ترشيح الجواز بنجاح']);
    }

    public function updateTransferStatuses(UpdateTransferStatusesRequest $request)
    {
        $statuses = $request->input('statuses', []);

        foreach ($statuses as $status) {
            Setting::updateOrCreate(
                ['group' => 'transfer_status', 'key' => $status['key']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'] ?? '#6c757d',
                    'sort_order' => $status['sort_order'] ?? 0,
                    'is_active' => $status['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ حالات التحويل بنجاح']);
    }

    public function updatePaymentMethods(Request $request)
    {
        $methods = $request->input('methods', []);

        foreach ($methods as $method) {
            Setting::updateOrCreate(
                ['group' => 'payment_method', 'key' => $method['key']],
                [
                    'label' => $method['label'],
                    'color' => $method['color'] ?? '#6c757d',
                    'sort_order' => $method['sort_order'] ?? 0,
                    'is_active' => $method['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ طرق الدفع بنجاح']);
    }

    public function updateBankNames(Request $request)
    {
        $banks = $request->input('banks', []);

        foreach ($banks as $bank) {
            Setting::updateOrCreate(
                ['group' => 'bank_name', 'key' => $bank['key']],
                [
                    'label' => $bank['label'],
                    'color' => $bank['color'] ?? '#6c757d',
                    'sort_order' => $bank['sort_order'] ?? 0,
                    'is_active' => $bank['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ أسماء البنوك بنجاح']);
    }

    public function updateMarketingStatuses(UpdateMarketingStatusesRequest $request)
    {
        $statuses = $request->input('statuses', []);

        foreach ($statuses as $status) {
            Setting::updateOrCreate(
                ['group' => 'marketing_status', 'key' => $status['key']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'] ?? '#6c757d',
                    'sort_order' => $status['sort_order'] ?? 0,
                    'is_active' => $status['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ حالات التسويق بنجاح']);
    }

    public function updateOrderStatuses(Request $request)
    {
        $statuses = $request->input('statuses', []);

        foreach ($statuses as $status) {
            Setting::updateOrCreate(
                ['group' => 'order_status', 'key' => $status['key']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'] ?? '#6c757d',
                    'sort_order' => $status['sort_order'] ?? 0,
                    'is_active' => $status['is_active'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ حالات الطلبات بنجاح']);
    }

    public function deletePriorityLevel(int $id)
    {
        $setting = Setting::where('group', 'priority_level')->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }
        $setting->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function deletePassportStatus(int $id)
    {
        $setting = Setting::where('group', 'passport_status')->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }
        $setting->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function deleteTransferStatus(int $id)
    {
        $setting = Setting::where('group', 'transfer_status')->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }
        $setting->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function deletePaymentMethod(int $id)
    {
        $setting = Setting::where('group', 'payment_method')->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }
        $setting->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function deleteBankName(int $id)
    {
        $setting = Setting::where('group', 'bank_name')->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }
        $setting->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function deleteMarketingStatus(int $id)
    {
        $setting = Setting::where('group', 'marketing_status')->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }
        $setting->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }

    public function deleteOrderStatus(int $id)
    {
        $setting = Setting::where('group', 'order_status')->find($id);
        if (!$setting) {
            return response()->json(['message' => 'العنصر غير موجود'], 404);
        }
        $setting->delete();
        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}