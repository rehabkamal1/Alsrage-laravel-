<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'client_id',
    'saudi_office_id',
    'external_office_id',
    'details',
    'status',
])]
class Order extends Model
{
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
}
