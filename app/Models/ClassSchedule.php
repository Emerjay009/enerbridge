<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    protected $fillable = ['room_id', 'user_id', 'day_of_week', 'start_time', 'end_time', 'subject', 'notes'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
