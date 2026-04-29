<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'label',
        'color',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function getByGroup($group)
    {
        return self::where('group', $group)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public static function getFormattedForSelect($group)
    {
        return self::getByGroup($group)->map(function ($item) {
            return [
                'value' => $item->key,
                'label' => $item->label,
                'color' => $item->color
            ];
        });
    }
}
