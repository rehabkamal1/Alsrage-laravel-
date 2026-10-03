<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $table = 'order_tracking';

    public const WORKFLOW_STATUS_REVIEWED = 'reviewed';

    public const WORKFLOW_STATUS_CERTIFIED = 'certified';

    public const WORKFLOW_STATUSES = [
        self::WORKFLOW_STATUS_REVIEWED,
        self::WORKFLOW_STATUS_CERTIFIED,
    ];

    protected $fillable = [
        'order_id',
        'workflow_status',
        'external_office_id',
        'saudi_office_id',
        'is_authenticated',
        'authentication_date',
        'certification_date',
        'authentication_number',
        'authorization_number',
        'sponsor_number',
        'delegate_phone',
        'last_action_date',
        'notes',
        'priority_level',
        'passport_status',
        'transfer_status',
        'authentication_status',
        'authorization_status',
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

    public function saudiOffice()
    {
        return $this->belongsTo(SaudiOffice::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}