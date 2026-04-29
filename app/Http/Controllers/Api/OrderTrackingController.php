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
        $query = OrderTracking::with(['order.client', 'order.saudiOffice', 'attachments']);

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

        return (new OrderTrackingResource($tracking))
            ->response()
            ->setStatusCode(201);
    }

    public function show(OrderTracking $orderTracking)
    {
        return new OrderTrackingResource($orderTracking->load(['order.client', 'order.saudiOffice', 'attachments']));
    }

    public function update(UpdateOrderTrackingRequest $request, OrderTracking $orderTracking)
    {
        $orderTracking->update($request->validated());

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
