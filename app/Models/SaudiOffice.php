<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class SaudiOffice extends Model
{
    protected $fillable = [
        'name',
        'is_supplier',
        'destination',
        'city',
        'responsible_employee',
        'mobile',
        'phone',
        'address',
        'notes',
        'total_authorization',
        'musaned_price',
        'whatsapp_link',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
