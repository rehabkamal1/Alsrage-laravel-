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
        if ($this->source_type === 'saudi_office') {
            $office = SaudiOffice::find($this->source_id);
            return $office?->name;
        }
        if ($this->source_type === 'external_office') {
            $office = ExternalOffice::find($this->source_id);
            return $office?->name;
        }
        if ($this->source_type === 'client') {
            $client = Client::find($this->source_id);
            return $client?->office_name ?? $client?->name;
        }
        return null;
    }

    public function getSourcePhoneAttribute()
    {
        if ($this->source_type === 'saudi_office') {
            $office = SaudiOffice::find($this->source_id);
            return $office?->mobile ?? $office?->phone;
        }
        if ($this->source_type === 'external_office') {
            $office = ExternalOffice::find($this->source_id);
            if ($office?->contacts && is_array($office->contacts) && count($office->contacts) > 0) {
                return $office->contacts[0]['phone'] ?? null;
            }
            return $office?->phone;
        }
        if ($this->source_type === 'client') {
            $client = Client::find($this->source_id);
            return $client?->phone;
        }
        return null;
    }

    public function saudiOffice()
    {
        return $this->belongsTo(SaudiOffice::class, 'source_id')->where('source_type', 'saudi_office');
    }

    public function externalOffice()
    {
        return $this->belongsTo(ExternalOffice::class, 'source_id')->where('source_type', 'external_office');
    }

    public function serviceOffice()
    {
        return $this->belongsTo(Client::class, 'source_id')->where('source_type', 'client');
    }
}