<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderTrackingRequest;
use App\Http\Requests\UpdateOrderTrackingRequest;
use App\Http\Resources\OrderTrackingResource;
use App\Models\Order;
use App\Models\OrderTracking;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tracking = OrderTracking::query()
            ->when(
                $request->filled('order_id'),
                fn($query) => $query->where('order_id', $request->order_id)
            )
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 15))
            ->withQueryString();

        return OrderTrackingResource::collection($tracking);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderTrackingRequest $request)
    {
        // تحقق من أن الطلب لا يملك تتبع موجود بالفعل
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

    /**
     * Display the specified resource.
     */
    public function show(OrderTracking $orderTracking)
    {
        return new OrderTrackingResource($orderTracking);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateOrderTrackingRequest $request, OrderTracking $orderTracking)
    {
        $orderTracking->update($request->validated());

        return new OrderTrackingResource($orderTracking);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderTracking $orderTracking)
    {
        $orderTracking->delete();

        return response()->json([
            'message' => 'تم حذف التتبع بنجاح.',
        ]);
    }
}