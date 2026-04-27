<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'order_id',
    'is_authenticated',
    'authentication_date',
    'authentication_number',
    'sent_to_external',
    'external_status',
    'passport_filtered',
    'is_delivered',
])]
class OrderTracking extends Model
{
    protected $table = 'order_tracking';

    protected $casts = [
        'is_authenticated' => 'boolean',
        'authentication_date' => 'date',
        'sent_to_external' => 'boolean',
        'is_delivered' => 'boolean',
    ];

    /**
     * العلاقات
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
