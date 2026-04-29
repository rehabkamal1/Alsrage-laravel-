<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingLead extends Model
{
    protected $fillable = [
        'source_id',
        'source_type',
        'name',
        'phone',
        'type',
        'status',
        'priority_level',
        'notes',
        'contact_date',
        'next_followup_date',
        'assigned_to'
    ];

    protected $casts = [
        'contact_date' => 'date',
        'next_followup_date' => 'date',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function getSourceNameAttribute()
    {
        if ($this->source_type === 'App\\Models\\SaudiOffice') {
            return $this->source?->name;
        }
        if ($this->source_type === 'App\\Models\\ExternalOffice') {
            return $this->source?->name;
        }
        if ($this->source_type === 'App\\Models\\Client') {
            return $this->source?->office_name ?? $this->source?->name;
        }
        return null;
    }

    public function getSourcePhoneAttribute()
    {
        if ($this->source_type === 'App\\Models\\SaudiOffice') {
            return $this->source?->mobile ?? $this->source?->phone;
        }
        if ($this->source_type === 'App\\Models\\ExternalOffice') {
            if ($this->source?->contacts && is_array($this->source->contacts) && count($this->source->contacts) > 0) {
                return $this->source->contacts[0]['phone'] ?? null;
            }
            return $this->source?->phone;
        }
        if ($this->source_type === 'App\\Models\\Client') {
            return $this->source?->phone;
        }
        return null;
    }
}