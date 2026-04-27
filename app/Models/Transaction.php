<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type',
    'amount',
    'order_id',
    'client_id',
    'notes',
    'payment_method',
    'bank_name',
    'transfer_date',
    'sender_name',
    'status',
])]
class Transaction extends Model
{
    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    /**
     * العلاقات
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
