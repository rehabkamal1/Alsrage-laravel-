<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'country',
    'contacts',
])]
class ExternalOffice extends Model
{
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
