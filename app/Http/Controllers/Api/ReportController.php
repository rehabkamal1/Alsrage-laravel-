<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function orderFollowUp(Request $request)
    {
        $query = Order::with(['client', 'employee', 'tracking']);

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('saudi_office_id')) {
            $query->where('saudi_office_id', $request->saudi_office_id);
        }
        if ($request->filled('external_office_id')) {
            $query->where('external_office_id', $request->external_office_id);
        }
        if ($request->filled('marketer_id')) {
            // Assuming marketers are employees who created/are assigned to clients
            // or just use employee_id if there is no distinct marketer field
            $query->where('employee_id', $request->marketer_id);
        }

        // Only active orders for follow up
        $query->whereNotIn('status', ['completed', 'cancelled', 'مكتمل', 'ملغي']);

        $orders = $query->get();

        // Load dynamic target SLA days for each stage/status from settings
        $statusSettingsMap = \App\Models\Setting::where('group', 'order_status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => (int) ($item->target_days ?: 60)];
            })
            ->toArray();

        $now = Carbon::now();

        $totalLate = 0;
        $withoutFollowup = 0;
        $exceededSla = 0;
        $totalDelayDays = 0;

        $processedOrders = $orders->map(function ($order) use ($statusSettingsMap, $now, &$totalLate, &$withoutFollowup, &$exceededSla, &$totalDelayDays) {
            $startDate = $order->contract_date ? Carbon::parse($order->contract_date) : $order->created_at;
            $daysSinceStart = (int) $startDate->diffInDays($now);
            
            // Get stage SLA target days from settings (default to 60 if not configured)
            $stageTargetDays = (int) ($statusSettingsMap[$order->status] ?? 60);
            $delayDays = max(0, $daysSinceStart - $stageTargetDays);
            
            $isLate = $delayDays > 0;
            $isWithoutFollowup = !$order->tracking || !$order->tracking->last_action_date;
            
            if ($isLate) {
                $totalLate++;
                $exceededSla++;
                $totalDelayDays += $delayDays;
            }
            if ($isWithoutFollowup) {
                $withoutFollowup++;
            }

            return [
                'id' => $order->id,
                'order_number' => $order->id, // fallback if there is no order_number field
                'client' => ['name' => $order->client ? $order->client->name : '-'],
                'employee' => ['name' => $order->employee ? $order->employee->name : '-'],
                'status' => ['name' => $order->status ?: 'غير محدد'],
                'last_update_date' => $order->tracking && $order->tracking->last_action_date ? $order->tracking->last_action_date->format('Y-m-d') : '-',
                'delay_days' => $delayDays,
                'exceeded_sla' => $isLate,
            ];
        });

        $avgDelayDays = $totalLate > 0 ? round($totalDelayDays / $totalLate, 1) : 0;

        return response()->json([
            'kpis' => [
                'total_late' => $totalLate,
                'without_followup' => $withoutFollowup,
                'exceeded_sla' => $exceededSla,
                'avg_delay_days' => $avgDelayDays,
            ],
            'orders' => $processedOrders,
        ]);
    }

    public function completedOrders(Request $request)
    {
        $query = Order::with(['client', 'employee', 'saudiOffice', 'externalOffice'])
            ->whereIn('status', ['completed', 'مكتمل']);

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('saudi_office_id')) {
            $query->where('saudi_office_id', $request->saudi_office_id);
        }
        if ($request->filled('external_office_id')) {
            $query->where('external_office_id', $request->external_office_id);
        }

        $orders = $query->latest('updated_at')->get();

        $SLA_DAYS = 60;
        $totalCompleted = $orders->count();
        $totalRevenue = 0;
        $totalCompletionDays = 0;
        $withinSlaCount = 0;

        $processedOrders = $orders->map(function ($order) use ($SLA_DAYS, &$totalRevenue, &$totalCompletionDays, &$withinSlaCount) {
            $startDate = $order->contract_date ? Carbon::parse($order->contract_date) : $order->created_at;
            $completedDate = $order->updated_at;
            $completionDays = max(1, $startDate->diffInDays($completedDate));

            $totalRevenue += (float) ($order->total_price ?? 0);
            $totalCompletionDays += $completionDays;

            if ($completionDays <= $SLA_DAYS) {
                $withinSlaCount++;
            }

            return [
                'id' => $order->id,
                'visa_number' => $order->visa_number ?: "-",
                'client_name' => $order->client ? $order->client->name : ($order->visa_holder_name ?: "-"),
                'employee_name' => $order->employee ? $order->employee->name : "-",
                'saudi_office' => $order->saudiOffice ? $order->saudiOffice->name : "-",
                'external_office' => $order->externalOffice ? $order->externalOffice->name : "-",
                'service_type' => $order->service_type ?: "-",
                'total_price' => (float) ($order->total_price ?? 0),
                'completion_days' => $completionDays,
                'completed_at' => $completedDate ? $completedDate->format('Y-m-d') : "-",
                'within_sla' => $completionDays <= $SLA_DAYS,
            ];
        });

        $avgCompletionDays = $totalCompleted > 0 ? round($totalCompletionDays / $totalCompleted, 1) : 0;
        $slaComplianceRate = $totalCompleted > 0 ? round(($withinSlaCount / $totalCompleted) * 100, 1) : 100;

        return response()->json([
            'kpis' => [
                'total_completed' => $totalCompleted,
                'total_revenue' => $totalRevenue,
                'avg_completion_days' => $avgCompletionDays,
                'sla_compliance_rate' => $slaComplianceRate,
            ],
            'orders' => $processedOrders,
        ]);
    }

    public function officesPerformance(Request $request)
    {
        $saudiOffices = \App\Models\SaudiOffice::with(['orders'])->get()->map(function ($office) {
            $totalOrders = $office->orders->count();
            $completedOrders = $office->orders->whereIn('status', ['completed', 'مكتمل'])->count();
            $inProgressOrders = $office->orders->whereNotIn('status', ['completed', 'cancelled', 'مكتمل', 'ملغي'])->count();
            $totalRevenue = $office->orders->sum('total_price');
            $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

            return [
                'id' => $office->id,
                'name' => $office->name,
                'city' => $office->city ?: '-',
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'in_progress_orders' => $inProgressOrders,
                'total_revenue' => (float) $totalRevenue,
                'completion_rate' => $completionRate,
            ];
        });

        $externalOffices = \App\Models\ExternalOffice::with(['orders'])->get()->map(function ($office) {
            $totalOrders = $office->orders->count();
            $completedOrders = $office->orders->whereIn('status', ['completed', 'مكتمل'])->count();
            $inProgressOrders = $office->orders->whereNotIn('status', ['completed', 'cancelled', 'مكتمل', 'ملغي'])->count();
            $totalRevenue = $office->orders->sum('total_price');
            $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

            return [
                'id' => $office->id,
                'name' => $office->name,
                'country' => $office->country ?: '-',
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'in_progress_orders' => $inProgressOrders,
                'total_revenue' => (float) $totalRevenue,
                'completion_rate' => $completionRate,
            ];
        });

        return response()->json([
            'kpis' => [
                'total_saudi_offices' => $saudiOffices->count(),
                'total_external_offices' => $externalOffices->count(),
            ],
            'saudi_offices' => $saudiOffices,
            'external_offices' => $externalOffices,
        ]);
    }

    public function financialCollections(Request $request)
    {
        $query = Order::with(['client', 'employee']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->get();

        $totalContractValue = 0;
        $totalCollected = 0;
        $totalOutstanding = 0;

        $financialOrders = $orders->map(function ($order) use (&$totalContractValue, &$totalCollected, &$totalOutstanding) {
            $price = (float) ($order->total_price ?? 0);
            $paid = (float) ($order->musaned_paid ?? 0);
            $remaining = max(0, $price - $paid);

            $totalContractValue += $price;
            $totalCollected += $paid;
            $totalOutstanding += $remaining;

            $paymentStatus = "غير محصل";
            if ($paid >= $price && $price > 0) {
                $paymentStatus = "محصل بالكامل";
            } else if ($paid > 0) {
                $paymentStatus = "محصل جزئياً";
            }

            return [
                'id' => $order->id,
                'visa_number' => $order->visa_number ?: '-',
                'client_name' => $order->client ? $order->client->name : ($order->visa_holder_name ?: '-'),
                'total_price' => $price,
                'paid_amount' => $paid,
                'remaining_amount' => $remaining,
                'payment_status' => $paymentStatus,
                'created_at' => $order->created_at ? $order->created_at->format('Y-m-d') : '-',
            ];
        });

        $collectionRate = $totalContractValue > 0 ? round(($totalCollected / $totalContractValue) * 100, 1) : 0;

        return response()->json([
            'kpis' => [
                'total_contract_value' => $totalContractValue,
                'total_collected' => $totalCollected,
                'total_outstanding' => $totalOutstanding,
                'collection_rate' => $collectionRate,
            ],
            'orders' => $financialOrders,
        ]);
    }

    public function employeesPerformance(Request $request)
    {
        $employees = \App\Models\Employee::with(['orders'])->get()->map(function ($emp) {
            $totalOrders = $emp->orders->count();
            $completedOrders = $emp->orders->whereIn('status', ['completed', 'مكتمل'])->count();
            $activeOrders = $emp->orders->whereNotIn('status', ['completed', 'cancelled', 'مكتمل', 'ملغي'])->count();
            $totalSales = $emp->orders->sum('total_price');
            $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'position' => $emp->position ?: 'موظف',
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'active_orders' => $activeOrders,
                'total_sales' => (float) $totalSales,
                'completion_rate' => $completionRate,
            ];
        });

        return response()->json([
            'kpis' => [
                'total_employees' => $employees->count(),
                'avg_orders_per_emp' => $employees->count() > 0 ? round($employees->sum('total_orders') / $employees->count(), 1) : 0,
            ],
            'employees' => $employees,
        ]);
    }
}
