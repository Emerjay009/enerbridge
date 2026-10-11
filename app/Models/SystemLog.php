<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['level', 'source', 'message', 'context'];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];
}
