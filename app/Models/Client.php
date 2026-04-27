<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'category',
        'office_name',
        'phone',
        'additional_phone',
        'address',
        'visa_holder_name',
        'passport_number',
        'national_id',
        'passport_image',
        'visa_image',
        'id_image',
    ];

    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}