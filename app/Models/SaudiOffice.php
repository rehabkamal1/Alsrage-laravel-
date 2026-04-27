<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class SaudiOffice extends Model
{
    protected $fillable = [
        'name',
        'destination',
        'responsible_employee',
        'mobile',
        'phone',
        'address',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
