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

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->latest()->paginate((int) $request->integer('per_page', 15))->withQueryString();

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
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $totalReceipts = $query->clone()->where('type', 'receipt')->sum('amount');
        $totalPayments = $query->clone()->where('type', 'payment')->sum('amount');
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