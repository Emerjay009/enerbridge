<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $rooms = [
            'Room 3A' => 'First room of the three-room prototype. A simulated classroom with a teacher\'s desk, student desks and chairs, and ceiling lighting as the controlled load.',
            'Room 3B' => 'Second room of the three-room prototype. A simulated classroom with a teacher\'s desk, student desks and chairs, and ceiling lighting as the controlled load.',
            'Room 3C' => 'Third room of the three-room prototype. A simulated classroom with a teacher\'s desk, student desks and chairs, and ceiling lighting as the controlled load.',
        ];

        foreach ($rooms as $name => $description) {
            Room::updateOrCreate(
                ['name' => $name],
                ['description' => $description, 'width_in' => 12, 'depth_in' => 6]
            );
        }

        // Authorized hours now come from the classes that teachers add on the Schedules page.
    }
}
