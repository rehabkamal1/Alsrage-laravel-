<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'position',
        'office_name'
    ];

    /**
     * العلاقات
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
