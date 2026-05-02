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
        $query = Transaction::with(['order.client']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority_level')) {
            $query->where('priority_level', $request->priority_level);
        }

        if ($request->has('bank_name')) {
            $query->where('bank_name', 'like', '%' . $request->bank_name . '%');
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('id', 'like', "%{$search}%")
                            ->orWhere('visa_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order.client', function ($cq) use ($search) {
                        $cq->where('visa_holder_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('from_date')) {
            $query->whereDate('transfer_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('transfer_date', '<=', $request->to_date);
        }

        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $transactions = $query->paginate((int) $request->integer('per_page', 15))->withQueryString();

        return OrderTransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create($request->validated());

        return (new OrderTransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Transaction $transaction)
    {
        return new OrderTransactionResource($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $transaction->update($request->validated());

        return new OrderTransactionResource($transaction);
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

        if ($request->has('from_date')) {
            $query->whereDate('transfer_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
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
