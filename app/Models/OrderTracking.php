<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $table = 'order_tracking';

    protected $fillable = [
        'order_id',
        'external_office_id',
        'is_authenticated',
        'authentication_date',
        'certification_date',
        'authentication_number',
        'authorization_number',
        'sponsor_number',
        'last_action_date',
        'notes',
        'priority_level',
        'passport_status',
        'transfer_status',
    ];

    protected $casts = [
        'is_authenticated' => 'boolean',
        'authentication_date' => 'date',
        'certification_date' => 'date',
        'last_action_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function externalOffice()
    {
        return $this->belongsTo(ExternalOffice::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}