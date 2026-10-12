<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomSchedule;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $rooms = [
            'Classroom 1' => 'First classroom of the three-room prototype. A simulated classroom with a teacher\'s desk, student desks and chairs, and ceiling lighting as the controlled load.',
            'Classroom 2' => 'Second classroom of the three-room prototype. A simulated classroom with a teacher\'s desk, student desks and chairs, and ceiling lighting as the controlled load.',
            'Classroom 3' => 'Third classroom of the three-room prototype. A simulated classroom with a teacher\'s desk, student desks and chairs, and ceiling lighting as the controlled load.',
        ];

        foreach ($rooms as $name => $description) {
            $room = Room::updateOrCreate(
                ['name' => $name],
                ['description' => $description, 'width_in' => 12, 'depth_in' => 6]
            );

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
