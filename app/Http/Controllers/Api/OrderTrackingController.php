<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderTrackingRequest;
use App\Http\Requests\UpdateOrderTrackingRequest;
use App\Http\Resources\OrderTrackingResource;
use App\Models\OrderTracking;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderTracking::with(['order.client', 'order.saudiOffice', 'order.employee', 'saudiOffice', 'externalOffice', 'attachments']);

        if (!$request->boolean('include_completed')) {
            $query->where('is_authenticated', false);
        }

        if ($request->boolean('without_tracking')) {
            $query->whereDoesntHave('order.tracking');
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->filled('priority_level')) {
            $query->where('priority_level', $request->priority_level);
        }

        if ($request->filled('passport_status')) {
            $query->where('passport_status', $request->passport_status);
        }

        if ($request->filled('transfer_status')) {
            $query->where('transfer_status', $request->transfer_status);
        }

        if ($request->filled('service_type')) {
            $query->whereHas('order', fn($q) => $q->where('service_type', $request->service_type));
        }

        if ($request->filled('saudi_office_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('saudi_office_id', $request->saudi_office_id)
                    ->orWhereHas('order', fn($oq) => $oq->where('saudi_office_id', $request->saudi_office_id));
            });
        }

        if ($request->filled('external_office_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('external_office_id', $request->external_office_id)
                    ->orWhereHas('order', fn($oq) => $oq->where('external_office_id', $request->external_office_id));
            });
        }

        if ($request->filled('client_id')) {
            $query->whereHas('order', fn($q) => $q->where('client_id', $request->client_id));
        }

        if ($request->filled('employee_id')) {
            $query->whereHas('order', fn($q) => $q->where('employee_id', $request->employee_id));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('date_range')) {
            $now = now();
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
                default:
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('visa_number', 'like', "%{$search}%")
                    ->orWhere('sponsor_number', 'like', "%{$search}%")
                    ->orWhere('visa_holder_name', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('visa_holder_name', 'like', "%{$search}%")
                            ->orWhere('passport_number', 'like', "%{$search}%");
                    });
            });
        }

        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $tracking = $query->paginate((int) $request->integer('per_page', 15))->withQueryString();

        return OrderTrackingResource::collection($tracking);
    }

    public function store(StoreOrderTrackingRequest $request)
    {
        $existingTracking = OrderTracking::where('order_id', $request->order_id)->first();
        if ($existingTracking) {
            return response()->json([
                'message' => 'هذا الطلب لديه تتبع موجود بالفعل.',
                'data' => new OrderTrackingResource($existingTracking)
            ], 409);
        }

        $tracking = OrderTracking::create($request->validated());

        if ($tracking->is_authenticated) {
            $tracking->order()->update(['status' => 'completed']);
        }

        return (new OrderTrackingResource($tracking))
            ->response()
            ->setStatusCode(201);
    }

    public function show(OrderTracking $orderTracking)
    {
        return new OrderTrackingResource($orderTracking->load(['order.client', 'order.saudiOffice', 'order.employee', 'saudiOffice', 'externalOffice', 'attachments']));
    }

    public function update(UpdateOrderTrackingRequest $request, OrderTracking $orderTracking)
    {
        $orderTracking->update($request->validated());

        if ($orderTracking->is_authenticated) {
            $orderTracking->order()->update(['status' => 'completed']);
        }

        return new OrderTrackingResource($orderTracking);
    }

    public function destroy(OrderTracking $orderTracking)
    {
        $orderTracking->delete();

        return response()->json([
            'message' => 'تم حذف التتبع بنجاح.',
        ]);
    }
}