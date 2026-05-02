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
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['client', 'saudiOffice', 'supplier', 'externalOffice', 'employee', 'tracking', 'transactions', 'attachments'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('visa_holder_name', 'like', "%{$search}%")
                        ->orWhere('visa_number', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('sponsor_number', 'like', "%{$search}%")
                        ->orWhere('passport_number', 'like', "%{$search}%")
                        ->orWhere('musaned_contract_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('visa_number'), fn($query) => $query->where('visa_number', 'like', '%' . $request->string('visa_number') . '%'))
            ->when($request->filled('id_number'), fn($query) => $query->where('id_number', 'like', '%' . $request->string('id_number') . '%'))
            ->when($request->filled('employee_id'), fn($query) => $query->where('employee_id', $request->integer('employee_id')))
            ->when($request->filled('client_id'), fn($query) => $query->where('client_id', $request->integer('client_id')))
            ->when($request->filled('saudi_office_id'), fn($query) => $query->where('saudi_office_id', $request->integer('saudi_office_id')))
            ->when($request->filled('external_office_id'), fn($query) => $query->where('external_office_id', $request->integer('external_office_id')))
            ->when($request->filled('from_date'), fn($query) => $query->whereDate('created_at', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn($query) => $query->whereDate('created_at', '<=', $request->date('to_date')))
            ->orderBy(
                in_array($request->input('sort_by'), ['id', 'visa_holder_name', 'visa_number', 'id_number', 'musaned_contract_number', 'status', 'total_price', 'musaned_paid', 'created_at', 'contract_date'], true)
                    ? $request->input('sort_by')
                    : 'id',
                $request->input('sort_dir') === 'asc' ? 'asc' : 'desc'
            )
            ->paginate((int) $request->integer('per_page', 15))
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        if (!$request->client_id && $request->new_client_name && $request->new_client_phone) {
            $client = Client::create([
                'name' => $request->new_client_name,
                'phone' => $request->new_client_phone,
                'client_type' => $request->new_client_type ?? 'individual',
            ]);
            $request->merge(['client_id' => $client->id]);
        }

        $data = $request->validated();
        unset($data['attachment_files'], $data['attachment_titles']);
        $order = Order::create($data);
        $this->storeOrderAttachments($order, $request);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $request->validated();
        unset($data['attachment_files'], $data['attachment_titles']);
        $order->update($data);
        $this->storeOrderAttachments($order, $request);

        return new OrderResource($order);
    }

    public function destroy(Order $order)
    {
        foreach ($order->attachments as $attachment) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    private function storeOrderAttachments(Order $order, Request $request): void
    {
        $files = $request->file('attachment_files', []);
        $titles = $request->input('attachment_titles', []);

        foreach ($files as $index => $file) {
            if (!$file) {
                continue;
            }
            $title = $titles[$index] ?? ('Attachment ' . ($index + 1));
            $path = $file->store('attachments/orders/' . $order->id, 'public');
            $order->attachments()->create([
                'title' => $title,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}