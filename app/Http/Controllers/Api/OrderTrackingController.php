<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderTrackingRequest;
use App\Http\Requests\UpdateOrderTrackingRequest;
use App\Http\Resources\OrderTrackingResource;
use App\Models\OrderTracking;
use App\Support\PermissionAccess;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderTracking::with([
            'order.client',
            'order.saudiOffice',
            'order.employee',
            'saudiOffice',
            'externalOffice',
            'attachments',
        ]);

        if (!$request->boolean('include_completed')) {
            $query->where('is_authenticated', false);
        }

        $query->whereNull('workflow_status');

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

        if ($request->filled('search')) {
            $search = $request->search;
            $hideDelegateNumbers = PermissionAccess::isHiddenFor(
                $request->user(),
                PermissionAccess::HIDE_DELEGATE_NUMBERS
            );
            $query->whereHas('order', function ($q) use ($search, $hideDelegateNumbers) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('visa_number', 'like', "%{$search}%")
                    ->orWhere('visa_holder_name', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('visa_holder_name', 'like', "%{$search}%")
                            ->orWhere('passport_number', 'like', "%{$search}%");
                    });
                if (!$hideDelegateNumbers) {
                    $q->orWhere('sponsor_number', 'like', "%{$search}%");
                }
            });
        }

        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $tracking = $query
            ->paginate((int) $request->integer('per_page', 15))
            ->withQueryString();

        return OrderTrackingResource::collection($tracking);
    }

    public function store(StoreOrderTrackingRequest $request)
    {
        $existingTracking = OrderTracking::where('order_id', $request->order_id)->first();
        if ($existingTracking) {
            return response()->json([
                'message' => 'هذا الطلب لديه تتبع موجود بالفعل.',
                'data' => new OrderTrackingResource($existingTracking),
            ], 409);
        }

        $tracking = OrderTracking::create($request->validated());

        return (new OrderTrackingResource($tracking))
            ->response()
            ->setStatusCode(201);
    }

    public function show(OrderTracking $orderTracking)
    {
        return new OrderTrackingResource($orderTracking->load([
            'order.client',
            'order.saudiOffice',
            'order.employee',
            'saudiOffice',
            'externalOffice',
            'attachments',
        ]));
    }

    public function update(UpdateOrderTrackingRequest $request, OrderTracking $orderTracking)
    {
        $data = $request->validated();

        if (PermissionAccess::isHiddenFor($request->user(), PermissionAccess::HIDE_DELEGATE_NUMBERS)) {
            unset($data['delegate_phone'], $data['sponsor_number']);
        }

        $orderTracking->update($data);

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
