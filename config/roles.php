<?php

// Roles of the EnerBridge dashboard and what each one can and cannot do.
// Edit the text here and the profile page updates automatically.

return [

    'admin' => [
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
    ],

    'staff' => [
        'label' => 'Maintenance Staff',
        'description' => 'Monitors the rooms and handles energy alerts.',
        'privileges' => [
            'View the live status of all three classrooms (occupancy, door, lighting)',
            'View voltage, current, power and energy readings of every room',
            'View the power consumption history chart',
            'View and resolve alerts for loads running outside the schedule',
            'Receive email alerts',
        ],
        'limitations' => [
            'Cannot edit room schedules',
            'Cannot manage user accounts or assign roles',
            'Cannot switch the lights manually; control is automatic',
            'Cannot edit or delete stored sensor readings',
        ],
    ],

    'viewer' => [
        'label' => 'Viewer',
        'description' => 'Read-only access to the dashboard. This is the default role for new sign-ups.',
        'privileges' => [
            'View the live status of all three classrooms (occupancy, door, lighting)',
            'View voltage, current, power and energy readings of every room',
            'View the power consumption history chart',
        ],
        'limitations' => [
            'Cannot view or resolve alerts, and does not receive email alerts',
            'Cannot edit room schedules',
            'Cannot manage user accounts or assign roles',
            'Cannot switch the lights manually; control is automatic',
            'Cannot edit or delete stored sensor readings',
        ],
    ],

];
