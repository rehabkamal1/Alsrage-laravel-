<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id',
        'employee_id',
        'visa_holder_name',
        'visa_holder_phone',
        'saudi_office_id',
        'external_office_id',
        'nationality',
        'arrival_destination',
        'profession',
        'visa_number',
        'service_type',
        'id_number',
        'sponsor_number',
        'passport_number',
        'birth_date',
        'musaned_contract_number',
        'authentication_contract_number',
        'external_agent_number',
        'contract_date',
        'passport_date',
        'total_price',
        'musaned_paid',
        'price_difference',
        'is_paid_by_office',
        'visa_image',
        'contract_image',
        'status',
        'notes',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'passport_date' => 'date',
        'birth_date' => 'date',
        'total_price' => 'decimal:2',
        'musaned_paid' => 'decimal:2',
        'price_difference' => 'decimal:2',
        'is_paid_by_office' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function saudiOffice()
    {
        return $this->belongsTo(SaudiOffice::class, 'saudi_office_id');
    }

    public function supplier()
    {
        return $this->belongsTo(SaudiOffice::class, 'supplier_id');
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

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function checkAwaitingMusanedTransferTimeouts(): void
    {
        $cutoff = \Carbon\Carbon::now()->subHours(24);

        self::where(function ($q) {
            $q->where('status', 'تم انتظار حوالة مساند')
              ->orWhere('status', 'بانتظار حوالة مساند')
              ->orWhere('status', 'awaiting_musaned_transfer');
        })
        ->where('updated_at', '<', $cutoff)
        ->update([
            'status' => 'لم يتم السداد',
        ]);
    }

    protected static function booting()
    {
        parent::booting();

        static::saving(function ($order) {
            if ($order->is_paid_by_office) {
                $order->musaned_paid = 0;
                $order->price_difference = $order->total_price ?? 0;
            } else {
                $order->price_difference = ($order->total_price ?? 0) - ($order->musaned_paid ?? 0);
            }
        });
    }
}
