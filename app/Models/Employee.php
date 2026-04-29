<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'username',
        'password',
        'position',
        'office_name',
        'permissions'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'permissions' => 'array'
    ];

    /**
     * Hash the password when setting it
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * العلاقات
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
