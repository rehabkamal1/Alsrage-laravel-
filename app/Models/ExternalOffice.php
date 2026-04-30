<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

class ExternalOffice extends Model
{
    protected $fillable = [
        'name',
        'country',
        'contacts',
        'notes',
        'whatsapp_link',
    ];
    protected function casts(): array
    {
        return [
            'contacts' => 'array',
        ];
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
