<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'room_id', 'occupied', 'door_open', 'light_on',
        'voltage', 'current_ma', 'power_mw',
    ];

    protected $casts = [
        'occupied' => 'boolean',
        'door_open' => 'boolean',
        'light_on' => 'boolean',
        'voltage' => 'float',
        'current_ma' => 'float',
        'power_mw' => 'float',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
