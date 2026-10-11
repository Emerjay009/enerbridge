<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['room_id', 'type', 'message', 'power_mw', 'emailed_at', 'resolved_at'];

    protected $casts = [
        'emailed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
