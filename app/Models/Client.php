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
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
