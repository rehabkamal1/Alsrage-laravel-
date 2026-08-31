<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Employee extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'username',
        'password',
        'position',
        'saudi_office_id',
        'permissions'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'permissions' => 'array'
    ];

    protected $appends = ['role'];

    /**
     * Get the dynamic role attribute for frontend
     */
    public function getRoleAttribute()
    {
        return 'employee';
    }

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
    public function saudiOffice()
    {
        return $this->belongsTo(SaudiOffice::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}

