<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name'];

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

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
