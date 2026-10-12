<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    // Edit the text here, then run: php artisan db:seed --class=RoleSeeder
    public function run(): void
    {
        Role::updateOrCreate(['slug' => 'administrator'], [
            'label' => 'Administrator',
            'description' => 'Full access to the EnerBridge system.',
            'privileges' => [
                'View the live status of all three classrooms (occupancy, door, lighting)',
                'View voltage, current, power and energy readings of every room',
                'View the power consumption history chart',
                'View and resolve alerts for loads running outside the schedule',
                'Edit the authorized schedule of each room',
                'Manage user accounts and assign roles',
                'Receive email alerts',
            ],
            'limitations' => [
                'Cannot switch the lights manually; control is automatic, based on occupancy and schedule',
                'Cannot edit or delete stored sensor readings; the record is read-only',
            ],
        ]);

        Role::updateOrCreate(['slug' => 'teacher'], [
            'label' => 'Teacher / Professor',
            'description' => 'Monitors the classrooms. This is the account type for faculty.',
            'privileges' => [
                'View the live status of all three classrooms (occupancy, door, lighting)',
                'View voltage, current, power and energy readings of every room',
                'View the power consumption history chart',
                'View the authorized schedule of each room (read-only)',
            ],
            'limitations' => [
                'Cannot edit room schedules',
                'Cannot view or resolve system alerts',
                'Cannot manage user accounts or assign roles',
                'Cannot switch the lights manually; control is automatic',
                'Cannot edit or delete stored sensor readings',
            ],
        ]);
    }
}
