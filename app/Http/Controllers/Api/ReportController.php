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

        $SLA_DAYS = 60; // Assuming SLA is 60 days from contract date
        $now = Carbon::now();

        $totalLate = 0;
        $withoutFollowup = 0;
        $exceededSla = 0;
        $totalDelayDays = 0;

        $processedOrders = $orders->map(function ($order) use ($SLA_DAYS, $now, &$totalLate, &$withoutFollowup, &$exceededSla, &$totalDelayDays) {
            $startDate = $order->contract_date ? Carbon::parse($order->contract_date) : $order->created_at;
            $daysSinceStart = $startDate->diffInDays($now);
            $delayDays = max(0, $daysSinceStart - $SLA_DAYS);
            
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
}
