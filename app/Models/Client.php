<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'category',
    'office_name',
    'phone',
    'additional_phone',
    'address',
])]
class Client extends Model
{
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
