<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\OrderTransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['order.client', 'employee', 'client']);

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('order_id') && $request->order_id) {
            $orderId = $request->order_id;
            $query->where(function ($q) use ($orderId) {
                $q->where('order_id', $orderId)
                    ->orWhere('order_ids', 'like', '%' . $orderId . '%')
                    ->orWhere('order_ids', 'like', '%"' . $orderId . '"%');
            });
        }

        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('employee_id') && $request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority_level') && $request->priority_level) {
            $query->where('priority_level', $request->priority_level);
        }

        if ($request->has('bank_name') && $request->bank_name) {
            $query->where('bank_name', 'like', '%' . $request->bank_name . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('id', 'like', "%{$search}%")
                            ->orWhere('visa_number', 'like', "%{$search}%")
                            ->orWhere('visa_holder_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order.client', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('visa_holder_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('client', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('transfer_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('transfer_date', '<=', $request->to_date);
        }

        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSortFields = ['id', 'amount', 'transfer_date', 'created_at', 'type', 'status', 'priority_level'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'id';
        }

        $query->orderBy($sortField, $sortDirection);

        $transactions = $query->paginate((int) $request->integer('per_page', 15))->withQueryString();

        return OrderTransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();
        if ($request->has('order_ids') && is_array($request->order_ids) && count($request->order_ids) > 0) {
            $data['order_id'] = $request->order_ids[0];
        }
        $transaction = Transaction::create($data);

        return (new OrderTransactionResource($transaction->load(['order.client', 'employee', 'client'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Transaction $transaction)
    {
        return new OrderTransactionResource($transaction->load(['order.client', 'employee', 'client']));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $data = $request->validated();
        if ($request->has('order_ids') && is_array($request->order_ids) && count($request->order_ids) > 0) {
            $data['order_id'] = $request->order_ids[0];
        }
        $transaction->update($data);

        return new OrderTransactionResource($transaction->load(['order.client', 'employee', 'client']));
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response()->json([
            'message' => 'تم حذف الحوالة بنجاح.',
        ]);
    }

    public function summary(Request $request)
    {
        $query = Transaction::query();

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('transfer_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('transfer_date', '<=', $request->to_date);
        }

        $totalReceipts = (float) $query->clone()->where('type', 'receipt')->sum('amount');
        $totalPayments = (float) $query->clone()->where('type', 'payment')->sum('amount');
        $netProfit = $totalReceipts - $totalPayments;

        return response()->json([
            'success' => true,
            'data' => [
                'total_receipts' => $totalReceipts,
                'total_payments' => $totalPayments,
                'net_profit' => $netProfit,
            ],
        ]);
    }
}