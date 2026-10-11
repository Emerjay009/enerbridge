<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomSchedule;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Classroom 1', 'Classroom 2', 'Classroom 3'] as $name) {
            $room = Room::firstOrCreate(['name' => $name]);

            // Authorized hours: Monday to Friday, 7:00 AM to 5:00 PM (change as needed)
            foreach ([1, 2, 3, 4, 5] as $day) {
                RoomSchedule::firstOrCreate(
                    ['room_id' => $room->id, 'day_of_week' => $day],
                    ['start_time' => '07:00:00', 'end_time' => '17:00:00']
                );
            }
        }
    }
}
