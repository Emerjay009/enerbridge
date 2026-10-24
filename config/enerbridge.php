<?php

/*
|--------------------------------------------------------------------------
| EnerBridge settings
|--------------------------------------------------------------------------
| >>> The GPIO pins of every sensor and relay are assigned in 'rooms' below. <<<
| Use BCM / "GPIO" numbers (GPIO17 = 17), not the physical pin numbers.
| After changing this file run: php artisan config:clear
*/

return [

    // Minutes before and after each class during which a room's load is still "authorized".
    'schedule_buffer_minutes' => (int) env('ENERBRIDGE_SCHEDULE_BUFFER', 10),

    // Hours shown on the weekly timetable of the Schedules page.
    'timetable' => ['start_hour' => 6, 'end_hour' => 21],

    'sensors' => [
        // hardware = real sensors and relays on the Raspberry Pi, simulate = fake sensors for testing
        'mode' => env('ENERBRIDGE_SENSOR_MODE', 'hardware'),

        'poll_seconds' => (float) env('ENERBRIDGE_POLL_SECONDS', 1),    // how often the sensors are read
        'hold_seconds' => (int) env('ENERBRIDGE_HOLD_SECONDS', 60),     // light stays on this long after the last movement
        'record_seconds' => (int) env('ENERBRIDGE_RECORD_SECONDS', 5),  // a reading is saved at least this often

        'relay_active_low' => true,        // 4-channel relay boards switch ON when the input is LOW
        'ir_active_low' => true,           // most IR modules pull OUT to LOW when they see something
        'ir_detects_door_closed' => true,  // true: the IR sensor sees the door when it is CLOSED
        'i2c_bus' => 1,                    // Raspberry Pi 4: bus 1
        'shunt_ohms' => 0.1,               // INA219 shunt resistor

        // Room code => pins. The room must exist in the database as "Room <code>" (Room 3A, Room 3B, Room 3C).
        'rooms' => [
            '3A' => ['pir' => 17, 'ir' => 27, 'relay' => 4, 'ina219' => 0x40],   // relay IN1
            '3B' => ['pir' => 23, 'ir' => 24, 'relay' => 5, 'ina219' => 0x41],   // relay IN2
            '3C' => ['pir' => 13, 'ir' => 19, 'relay' => 6, 'ina219' => 0x44],   // relay IN3 (IN4 = GPIO7 is spare)
        ],
    ],
];
