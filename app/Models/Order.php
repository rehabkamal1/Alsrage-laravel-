<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'client_id',
    'saudi_office_id',
    'external_office_id',
    'employee_id',
    'visa_number',
    'musaned_contract_number',
    'authentication_contract_number',
    'external_agent_number',
    'contract_date',
    'passport_date',
    'total_price',
    'musaned_paid',
    'price_difference',
    'visa_image',
    'contract_image',
    'status',
])]
class Order extends Model
{
    protected $casts = [
        'contract_date' => 'date',
        'passport_date' => 'date',
        'total_price' => 'decimal:2',
        'musaned_paid' => 'decimal:2',
        'price_difference' => 'decimal:2',
    ];

    /**
     * العلاقات
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function saudiOffice()
    {
        return $this->belongsTo(SaudiOffice::class);
    }

    public function externalOffice()
    {
        return $this->belongsTo(ExternalOffice::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function tracking()
    {
        return $this->hasOne(OrderTracking::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * حساب الفرق تلقائياً قبل الحفظ
     */
    protected static function booting()
    {
        parent::booting();

        static::saving(function ($order) {
            $order->price_difference = $order->total_price - $order->musaned_paid;
        });
    }
}
