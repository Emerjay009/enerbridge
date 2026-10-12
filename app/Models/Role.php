<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['slug', 'label', 'description', 'privileges', 'limitations'];

    protected $casts = [
        'privileges' => 'array',
        'limitations' => 'array',
    ];
}
