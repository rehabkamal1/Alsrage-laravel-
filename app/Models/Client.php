<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'client_type',
        'employee_id',
        'phone',
        'additional_phone',
        'city',
        'address',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}