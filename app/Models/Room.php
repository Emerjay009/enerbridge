<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name', 'description', 'width_in', 'depth_in',
        'pir_gpio', 'ir_gpio', 'light_gpio', 'ina219_address', 'hardware_synced_at',
    ];

    protected $casts = [
        'width_in' => 'float',
        'depth_in' => 'float',
        'hardware_synced_at' => 'datetime',
    ];

    /** "Room 3A" => "3A" */
    public function getCodeAttribute(): string
    {
        return trim(preg_replace('/^Room\s+/i', '', $this->name));
    }

    public function readings()
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReading()
    {
        return $this->hasOne(SensorReading::class)->latestOfMany();
    }

    public function schedules()
    {
        return $this->hasMany(RoomSchedule::class);
    }

    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
