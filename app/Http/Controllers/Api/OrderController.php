<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::query()
            ->when(
                $request->filled('phone'),
                fn ($query) => $query->where('phone', 'like', '%'.$request->string('phone').'%')
            )
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        if (!$request->client_id && $request->new_client_name && $request->new_client_phone) {
            $client = Client::create([
                'name' => $request->new_client_name,
                'phone' => $request->new_client_phone,
            ]);
            $request->merge(['client_id' => $client->id]);
        }

        $order = Order::create($request->validated());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return new OrderResource($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }
}
