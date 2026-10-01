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
        $query = Order::with(['client', 'employee', 'tracking', 'saudiOffice', 'externalOffice']);

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        } elseif ($request->filled('marketer_id')) {
            $query->where('employee_id', $request->marketer_id);
        }
        if ($request->filled('saudi_office_id')) {
            $query->where('saudi_office_id', $request->saudi_office_id);
        }
        if ($request->filled('external_office_id')) {
            $query->where('external_office_id', $request->external_office_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Only active orders for follow up when no specific status is selected
            $query->whereNotIn('status', ['completed', 'cancelled', 'مكتمل', 'ملغي']);
        }

        $orders = $query->get();

        // Load dynamic target SLA days and labels for each stage/status from settings
        $statusSettings = \App\Models\Setting::where('group', 'order_status')->get();
        $statusSettingsMap = $statusSettings
            ->mapWithKeys(function ($item) {
                return [$item->key => (int) ($item->target_days ?: 60)];
            })
            ->toArray();
        $statusLabelMap = $statusSettings
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->label];
            })
            ->toArray();

        $now = Carbon::now();

        $totalLate = 0;
        $withoutFollowup = 0;
        $exceededSla = 0;
        $totalDelayDays = 0;

        $processedOrders = $orders->map(function ($order) use ($statusSettingsMap, $statusLabelMap, $now, &$totalLate, &$withoutFollowup, &$exceededSla, &$totalDelayDays) {
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

            $statusLabel = $statusLabelMap[$order->status] ?? $order->status;

            return [
                'id' => $order->id,
                'order_number' => $order->id, // fallback if there is no order_number field
                'visa_number' => $order->visa_number ?: '-',
                'client' => ['name' => $order->client ? $order->client->name : '-'],
                'client_name' => $order->client ? $order->client->name : '-',
                'employee' => ['name' => $order->employee ? $order->employee->name : '-'],
                'employee_name' => $order->employee ? $order->employee->name : '-',
                'saudi_office' => $order->saudiOffice ? $order->saudiOffice->name : '-',
                'external_office' => $order->externalOffice ? $order->externalOffice->name : '-',
                'status' => [
                    'name' => $statusLabel ?: 'غير محدد',
                    'key' => $order->status,
                ],
                'status_name' => $statusLabel ?: 'غير محدد',
                'raw_status' => $order->status,
                'last_update_date' => $order->tracking && $order->tracking->last_action_date ? $order->tracking->last_action_date->format('Y-m-d') : '-',
                'delay_days' => $delayDays,
                'exceeded_sla' => $isLate,
                'is_without_followup' => $isWithoutFollowup,
                'contract_date' => $order->contract_date ? Carbon::parse($order->contract_date)->format('Y-m-d') : ($order->created_at ? $order->created_at->format('Y-m-d') : '-'),
            ];
        });

        $avgDelayDays = $totalLate > 0 ? round($totalDelayDays / $totalLate, 1) : 0;
        $totalOrders = $orders->count();
        $withinSla = max(0, $totalOrders - $exceededSla);

        return response()->json([
            'kpis' => [
                'total_orders' => $totalOrders,
                'total_late' => $totalLate,
                'without_followup' => $withoutFollowup,
                'exceeded_sla' => $exceededSla,
                'within_sla' => $withinSla,
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
        } elseif ($request->filled('marketer_id')) {
            $query->where('employee_id', $request->marketer_id);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
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
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $country = $request->country;
        $city = $request->city;
        $saudiOfficeId = $request->saudi_office_id;
        $externalOfficeId = $request->external_office_id;

        // Parse multi-status filter (comma separated string or array)
        $statusesFilter = [];
        if ($request->filled('statuses')) {
            $statusesFilter = is_array($request->statuses) ? $request->statuses : array_filter(explode(',', $request->statuses));
        } elseif ($request->filled('status')) {
            $statusesFilter = is_array($request->status) ? $request->status : array_filter(explode(',', $request->status));
        }

        // Retrieve configured order statuses from settings to map colors & labels
        $configuredStatuses = \App\Models\Setting::where('group', 'order_status')
            ->orderBy('sort_order')
            ->get(['key', 'label', 'color']);

        $statusColorMap = [];
        $statusLabelMap = [];

        foreach ($configuredStatuses as $st) {
            $statusColorMap[$st->key] = $st->color ?: '#6c757d';
            $statusColorMap[$st->label] = $st->color ?: '#6c757d';
            $statusLabelMap[$st->key] = $st->label;
            $statusLabelMap[$st->label] = $st->label;
        }

        // Fetch distinct available countries and cities for filters
        $countries = \App\Models\ExternalOffice::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country')
            ->values();

        $cities = \App\Models\SaudiOffice::whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->pluck('city')
            ->values();

        // Accumulator for overall status counts across all offices
        $globalStatusCounts = [];

        $processOfficeOrders = function ($officeQuery, $isSaudi = true) use ($dateFrom, $dateTo, $saudiOfficeId, $externalOfficeId, $statusesFilter, $statusColorMap, $statusLabelMap, &$globalStatusCounts) {
            return $officeQuery->get()->map(function ($office) use ($isSaudi, $dateFrom, $dateTo, $saudiOfficeId, $externalOfficeId, $statusesFilter, $statusColorMap, $statusLabelMap, &$globalStatusCounts) {
                $ordersQuery = $office->orders()->with(['client', 'employee']);

                if (!empty($dateFrom)) {
                    $ordersQuery->whereDate('created_at', '>=', $dateFrom);
                }
                if (!empty($dateTo)) {
                    $ordersQuery->whereDate('created_at', '<=', $dateTo);
                }
                if ($isSaudi && !empty($externalOfficeId)) {
                    $ordersQuery->where('external_office_id', $externalOfficeId);
                }
                if (!$isSaudi && !empty($saudiOfficeId)) {
                    $ordersQuery->where('saudi_office_id', $saudiOfficeId);
                }
                if (!empty($statusesFilter)) {
                    $ordersQuery->where(function($q) use ($statusesFilter) {
                        foreach (array_values($statusesFilter) as $idx => $stVal) {
                            $method = $idx === 0 ? 'where' : 'orWhere';
                            $q->$method(function($subQ) use ($stVal) {
                                $subQ->where('status', $stVal);
                                if ($stVal === 'completed' || $stVal === 'مكتمل') {
                                    $subQ->orWhereIn('status', ['completed', 'مكتمل']);
                                } elseif ($stVal === 'processing' || $stVal === 'قيد التنفيذ') {
                                    $subQ->orWhereIn('status', ['processing', 'قيد التنفيذ']);
                                } elseif ($stVal === 'pending' || $stVal === 'قيد الانتظار') {
                                    $subQ->orWhereIn('status', ['pending', 'قيد الانتظار']);
                                } elseif ($stVal === 'canceled' || $stVal === 'cancelled' || $stVal === 'ملغي') {
                                    $subQ->orWhereIn('status', ['canceled', 'cancelled', 'ملغي']);
                                }
                            });
                        }
                    });
                }

                $orders = $ordersQuery->get();

                $totalOrders = $orders->count();
                $completedOrders = $orders->filter(fn($o) => in_array($o->status, ['completed', 'مكتمل']))->count();
                $inProgressOrders = $orders->filter(fn($o) => !in_array($o->status, ['completed', 'cancelled', 'canceled', 'مكتمل', 'ملغي']))->count();
                $cancelledOrders = $orders->filter(fn($o) => in_array($o->status, ['cancelled', 'canceled', 'ملغي']))->count();

                $totalRevenue = (float) $orders->sum('total_price');
                $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

                // Status breakdown per office
                $statusCountsMap = [];

                $ordersDetails = $orders->map(function ($order) use ($statusColorMap, $statusLabelMap, &$statusCountsMap, &$globalStatusCounts) {
                    $rawStatus = $order->status ?: 'غير محدد';
                    $statusLabel = $statusLabelMap[$rawStatus] ?? $rawStatus;
                    $statusColor = $statusColorMap[$rawStatus] ?? ($statusColorMap[$statusLabel] ?? '#6c757d');

                    if (!isset($statusCountsMap[$statusLabel])) {
                        $statusCountsMap[$statusLabel] = [
                            'key' => $rawStatus,
                            'label' => $statusLabel,
                            'color' => $statusColor,
                            'count' => 0
                        ];
                    }
                    $statusCountsMap[$statusLabel]['count']++;

                    if (!isset($globalStatusCounts[$statusLabel])) {
                        $globalStatusCounts[$statusLabel] = [
                            'key' => $rawStatus,
                            'label' => $statusLabel,
                            'color' => $statusColor,
                            'count' => 0
                        ];
                    }
                    $globalStatusCounts[$statusLabel]['count']++;

                    return [
                        'id' => $order->id,
                        'visa_number' => $order->visa_number ?: '-',
                        'client_name' => $order->client ? $order->client->name : ($order->visa_holder_name ?: '-'),
                        'employee_name' => $order->employee ? $order->employee->name : '-',
                        'service_type' => $order->service_type ?: '-',
                        'total_price' => (float) ($order->total_price ?? 0),
                        'status' => $statusLabel,
                        'status_raw' => $rawStatus,
                        'status_color' => $statusColor,
                        'contract_date' => $order->contract_date ? Carbon::parse($order->contract_date)->format('Y-m-d') : ($order->created_at ? $order->created_at->format('Y-m-d') : '-'),
                    ];
                })->values();

                return [
                    'id' => $office->id,
                    'name' => $office->name,
                    'city' => $office->city ?: '-',
                    'country' => $office->country ?: '-',
                    'phone' => $office->phone ?: '-',
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'in_progress_orders' => $inProgressOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'status_counts' => array_values($statusCountsMap),
                    'total_revenue' => $totalRevenue,
                    'completion_rate' => $completionRate,
                    'orders_details' => $ordersDetails,
                ];
            });
        };

        // 1. Process Saudi Offices
        $saudiQuery = \App\Models\SaudiOffice::query();
        if (!empty($city)) {
            $saudiQuery->where('city', $city);
        }
        if (!empty($saudiOfficeId)) {
            $saudiQuery->where('id', $saudiOfficeId);
        }
        if (!empty($externalOfficeId)) {
            $saudiQuery->whereHas('orders', function ($q) use ($externalOfficeId) {
                $q->where('external_office_id', $externalOfficeId);
            });
        }
        $saudiOffices = $processOfficeOrders($saudiQuery, true);

        // 2. Process External Offices
        $externalQuery = \App\Models\ExternalOffice::query();
        if (!empty($country)) {
            $externalQuery->where('country', $country);
        }
        if (!empty($externalOfficeId)) {
            $externalQuery->where('id', $externalOfficeId);
        }
        if (!empty($saudiOfficeId)) {
            $externalQuery->whereHas('orders', function ($q) use ($saudiOfficeId) {
                $q->where('saudi_office_id', $saudiOfficeId);
            });
        }
        $externalOffices = $processOfficeOrders($externalQuery, false);

        // Summary KPIs
        $totalSaudiOrders = $saudiOffices->sum('total_orders');
        $totalExternalOrders = $externalOffices->sum('total_orders');
        $totalSaudiRevenue = $saudiOffices->sum('total_revenue');
        $totalExternalRevenue = $externalOffices->sum('total_revenue');

        // Available statuses list (settings + any actual found statuses)
        $statusesList = [];
        foreach ($configuredStatuses as $st) {
            $label = $st->label;
            $statusesList[$label] = [
                'key' => $st->key,
                'label' => $st->label,
                'color' => $st->color ?: '#6c757d',
                'count' => $globalStatusCounts[$label]['count'] ?? 0,
            ];
        }

        // Add any found status in globalStatusCounts that was not in settings
        foreach ($globalStatusCounts as $label => $stInfo) {
            if (!isset($statusesList[$label])) {
                $statusesList[$label] = $stInfo;
            }
        }

        return response()->json([
            'kpis' => [
                'total_saudi_offices' => $saudiOffices->count(),
                'total_external_offices' => $externalOffices->count(),
                'total_saudi_orders' => $totalSaudiOrders,
                'total_external_orders' => $totalExternalOrders,
                'total_saudi_revenue' => $totalSaudiRevenue,
                'total_external_revenue' => $totalExternalRevenue,
                'grand_total_orders' => $totalSaudiOrders + $totalExternalOrders,
                'grand_total_revenue' => $totalSaudiRevenue + $totalExternalRevenue,
                'status_counts' => array_values($globalStatusCounts),
            ],
            'statuses' => array_values($statusesList),
            'countries' => $countries,
            'cities' => $cities,
            'saudi_offices' => $saudiOffices,
            'external_offices' => $externalOffices,
        ]);
    }

    public function financialCollections(Request $request)
    {
        $query = Order::with(['client', 'employee.saudiOffice', 'saudiOffice', 'externalOffice']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('saudi_office_id')) {
            $query->where('saudi_office_id', $request->saudi_office_id);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('id', 'like', "%{$s}%")
                  ->orWhere('visa_number', 'like', "%{$s}%")
                  ->orWhere('visa_holder_name', 'like', "%{$s}%")
                  ->orWhereHas('client', function($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('employee', function($eq) use ($s) {
                      $eq->where('name', 'like', "%{$s}%");
                  });
            });
        }

        $orders = $query->latest()->get();

        $statusSettings = \App\Models\Setting::where('group', 'order_status')->get();
        $statusLabelMap = $statusSettings->mapWithKeys(function ($item) {
            return [$item->key => $item->label];
        })->toArray();
        $statusColorMap = $statusSettings->mapWithKeys(function ($item) {
            return [$item->key => $item->color ?? '#6c757d'];
        })->toArray();

        $totalContractValue = 0;
        $totalCollected = 0;
        $totalOutstanding = 0;

        $financialOrders = $orders->map(function ($order) use (&$totalContractValue, &$totalCollected, &$totalOutstanding, $statusLabelMap, $statusColorMap) {
            $price = (float) ($order->total_price ?? 0);
            $paid = (float) ($order->musaned_paid ?? 0);
            $remaining = max(0, $price - $paid);

            $totalContractValue += $price;
            $totalCollected += $paid;
            $totalOutstanding += $remaining;

            $rawStatus = $order->status ?: 'غير محدد';
            $statusLabel = $statusLabelMap[$rawStatus] ?? $rawStatus;
            $statusColor = $statusColorMap[$rawStatus] ?? ($statusColorMap[$statusLabel] ?? '#6c757d');

            $paymentStatus = "غير محصل";
            if ($paid >= $price && $price > 0) {
                $paymentStatus = "محصل بالكامل";
            } else if ($paid > 0) {
                $paymentStatus = "محصل جزئياً";
            }

            return [
                'id' => $order->id,
                'visa_number' => $order->visa_number ?: '-',
                'client_id' => $order->client_id,
                'client_name' => $order->client ? $order->client->name : ($order->visa_holder_name ?: '-'),
                'client_phone' => $order->client ? $order->client->phone : ($order->visa_holder_phone ?: '-'),
                'saudi_office_id' => $order->saudi_office_id,
                'saudi_office_name' => $order->saudiOffice ? $order->saudiOffice->name : '-',
                'external_office_id' => $order->external_office_id,
                'external_office_name' => $order->externalOffice ? $order->externalOffice->name : '-',
                'nationality' => $order->nationality ?: '-',
                'order_status' => $statusLabel,
                'order_status_raw' => $rawStatus,
                'order_status_color' => $statusColor,
                'employee_id' => $order->employee_id,
                'employee_name' => $order->employee ? $order->employee->name : '-',
                'total_price' => $price,
                'paid_amount' => $paid,
                'remaining_amount' => $remaining,
                'payment_status' => $paymentStatus,
                'created_at' => $order->created_at ? $order->created_at->format('Y-m-d') : '-',
            ];
        });

        // Grouping for Clients Summary
        $clientsMap = [];
        foreach ($financialOrders as $ord) {
            $cKey = $ord['client_id'] ? 'id_' . $ord['client_id'] : 'name_' . $ord['client_name'];
            if (!isset($clientsMap[$cKey])) {
                $clientsMap[$cKey] = [
                    'client_id' => $ord['client_id'],
                    'client_name' => $ord['client_name'],
                    'client_phone' => $ord['client_phone'],
                    'orders_count' => 0,
                    'total_contract_value' => 0,
                    'total_collected' => 0,
                    'total_outstanding' => 0,
                    'orders' => [],
                ];
            }
            $clientsMap[$cKey]['orders_count']++;
            $clientsMap[$cKey]['total_contract_value'] += $ord['total_price'];
            $clientsMap[$cKey]['total_collected'] += $ord['paid_amount'];
            $clientsMap[$cKey]['total_outstanding'] += $ord['remaining_amount'];
            $clientsMap[$cKey]['orders'][] = $ord;
        }

        $clientsSummary = array_values(array_map(function ($c) {
            $cPrice = $c['total_contract_value'];
            $cPaid = $c['total_collected'];
            $cStatus = "غير محصل";
            if ($cPaid >= $cPrice && $cPrice > 0) {
                $cStatus = "محصل بالكامل";
            } else if ($cPaid > 0) {
                $cStatus = "محصل جزئياً";
            }
            $c['payment_status'] = $cStatus;
            $c['collection_rate'] = $cPrice > 0 ? round(($cPaid / $cPrice) * 100, 1) : 0;
            return $c;
        }, $clientsMap));

        // Grouping for Marketers Summary & Detailed
        $marketersMap = [];
        foreach ($financialOrders as $ord) {
            $mKey = $ord['employee_id'] ? 'id_' . $ord['employee_id'] : 'name_' . $ord['employee_name'];
            if (!isset($marketersMap[$mKey])) {
                $marketersMap[$mKey] = [
                    'employee_id' => $ord['employee_id'],
                    'employee_name' => $ord['employee_name'] !== '-' ? $ord['employee_name'] : 'بدون مسوق',
                    'office_name' => $ord['saudi_office_name'],
                    'orders_count' => 0,
                    'total_contract_value' => 0,
                    'total_collected' => 0,
                    'total_outstanding' => 0,
                    'orders' => [],
                    'clients_map' => [],
                ];
            }
            $marketersMap[$mKey]['orders_count']++;
            $marketersMap[$mKey]['total_contract_value'] += $ord['total_price'];
            $marketersMap[$mKey]['total_collected'] += $ord['paid_amount'];
            $marketersMap[$mKey]['total_outstanding'] += $ord['remaining_amount'];
            $marketersMap[$mKey]['orders'][] = $ord;

            $cSubKey = $ord['client_id'] ? 'id_' . $ord['client_id'] : 'name_' . $ord['client_name'];
            if (!isset($marketersMap[$mKey]['clients_map'][$cSubKey])) {
                $marketersMap[$mKey]['clients_map'][$cSubKey] = [
                    'client_id' => $ord['client_id'],
                    'client_name' => $ord['client_name'],
                    'client_phone' => $ord['client_phone'],
                    'orders_count' => 0,
                    'total_contract_value' => 0,
                    'total_collected' => 0,
                    'total_outstanding' => 0,
                    'orders' => [],
                ];
            }
            $marketersMap[$mKey]['clients_map'][$cSubKey]['orders_count']++;
            $marketersMap[$mKey]['clients_map'][$cSubKey]['total_contract_value'] += $ord['total_price'];
            $marketersMap[$mKey]['clients_map'][$cSubKey]['total_collected'] += $ord['paid_amount'];
            $marketersMap[$mKey]['clients_map'][$cSubKey]['total_outstanding'] += $ord['remaining_amount'];
            $marketersMap[$mKey]['clients_map'][$cSubKey]['orders'][] = $ord;
        }

        $marketersSummary = array_values(array_map(function ($m) {
            $mPrice = $m['total_contract_value'];
            $mPaid = $m['total_collected'];
            $mStatus = "غير محصل";
            if ($mPaid >= $mPrice && $mPrice > 0) {
                $mStatus = "محصل بالكامل";
            } else if ($mPaid > 0) {
                $mStatus = "محصل جزئياً";
            }
            $m['payment_status'] = $mStatus;
            $m['collection_rate'] = $mPrice > 0 ? round(($mPaid / $mPrice) * 100, 1) : 0;
            $m['clients_count'] = count($m['clients_map']);
            $m['clients'] = array_values($m['clients_map']);
            unset($m['clients_map']);
            return $m;
        }, $marketersMap));

        $collectionRate = $totalContractValue > 0 ? round(($totalCollected / $totalContractValue) * 100, 1) : 0;

        return response()->json([
            'kpis' => [
                'total_contract_value' => $totalContractValue,
                'total_collected' => $totalCollected,
                'total_outstanding' => $totalOutstanding,
                'collection_rate' => $collectionRate,
                'total_orders' => count($financialOrders),
                'total_clients' => count($clientsSummary),
                'total_marketers' => count($marketersSummary),
            ],
            'orders' => $financialOrders,
            'clients_summary' => $clientsSummary,
            'marketers_summary' => $marketersSummary,
        ]);
    }

    public function employeesPerformance(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $saudiOfficeId = $request->input('saudi_office_id');

        // Query total orders within period and office filter
        $ordersQuery = Order::query();
        if ($dateFrom) {
            $ordersQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $ordersQuery->whereDate('created_at', '<=', $dateTo);
        }
        if ($saudiOfficeId) {
            $ordersQuery->where('saudi_office_id', $saudiOfficeId);
        }

        $totalPeriodOrders = (int) $ordersQuery->count();
        $totalPeriodSales = (float) $ordersQuery->sum('total_price');

        // Fetch employees with filtered orders relationship
        $employeesQuery = \App\Models\Employee::with(['saudiOffice', 'orders' => function ($q) use ($dateFrom, $dateTo, $saudiOfficeId) {
            $q->with(['client', 'saudiOffice', 'externalOffice']);
            if ($dateFrom) {
                $q->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            }
            if ($saudiOfficeId) {
                $q->where('saudi_office_id', $saudiOfficeId);
            }
        }]);

        if ($saudiOfficeId) {
            $employeesQuery->where(function ($q) use ($saudiOfficeId) {
                $q->where('saudi_office_id', $saudiOfficeId)
                  ->orWhereHas('orders', function ($oq) use ($saudiOfficeId) {
                      $oq->where('saudi_office_id', $saudiOfficeId);
                  });
            });
        }

        $employees = $employeesQuery->get()->map(function ($emp) use ($totalPeriodOrders, $totalPeriodSales) {
            $empOrders = $emp->orders;
            $totalOrders = $empOrders->count();
            $completedOrders = $empOrders->whereIn('status', ['completed', 'مكتمل'])->count();
            $activeOrders = $empOrders->whereNotIn('status', ['completed', 'cancelled', 'مكتمل', 'ملغي'])->count();
            $totalSales = (float) $empOrders->sum('total_price');

            $completionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;
            $ordersSharePercentage = $totalPeriodOrders > 0 ? round(($totalOrders / $totalPeriodOrders) * 100, 1) : 0;
            $salesSharePercentage = $totalPeriodSales > 0 ? round(($totalSales / $totalPeriodSales) * 100, 1) : 0;

            $ordersList = $empOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'visa_number' => $order->visa_number ?: '-',
                    'client_name' => $order->client ? $order->client->name : ($order->visa_holder_name ?: '-'),
                    'status' => $order->status ?: 'غير محدد',
                    'service_type' => $order->service_type ?: '-',
                    'saudi_office' => $order->saudiOffice ? $order->saudiOffice->name : '-',
                    'external_office' => $order->externalOffice ? $order->externalOffice->name : '-',
                    'total_price' => (float) ($order->total_price ?? 0),
                    'created_at' => $order->created_at ? $order->created_at->format('Y-m-d') : '-',
                    'contract_date' => $order->contract_date ? \Carbon\Carbon::parse($order->contract_date)->format('Y-m-d') : ($order->created_at ? $order->created_at->format('Y-m-d') : '-'),
                ];
            })->values();

            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'position' => $emp->position ?: 'موظف',
                'office_name' => $emp->saudiOffice ? $emp->saudiOffice->name : ($emp->office_name ?: '-'),
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'active_orders' => $activeOrders,
                'total_sales' => $totalSales,
                'completion_rate' => $completionRate,
                'orders_share_percentage' => $ordersSharePercentage,
                'sales_share_percentage' => $salesSharePercentage,
                'orders' => $ordersList,
            ];
        })->sortByDesc('total_orders')->values();

        $activeEmployeesCount = $employees->where('total_orders', '>', 0)->count();

        return response()->json([
            'kpis' => [
                'total_employees' => $employees->count(),
                'active_employees' => $activeEmployeesCount,
                'total_period_orders' => $totalPeriodOrders,
                'total_period_sales' => $totalPeriodSales,
                'avg_orders_per_emp' => $employees->count() > 0 ? round($totalPeriodOrders / $employees->count(), 1) : 0,
            ],
            'employees' => $employees,
        ]);
    }
}
