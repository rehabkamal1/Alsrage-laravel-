<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'title',
        'file_path',
        'file_name',
        'mime_type',
        'size'
    ];

    public function attachable()
    {
        return $this->morphTo();
    }
}