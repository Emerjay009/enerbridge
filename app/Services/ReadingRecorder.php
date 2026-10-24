<?php

namespace App\Services;

use App\Models\SensorReading;

class ReadingRecorder
{
    /** Saves one reading and checks it against the room's class schedule. */
    public function record(int $roomId, bool $occupied, bool $doorOpen, bool $lightOn, float $voltage, float $currentMa): SensorReading
    {
        $reading = SensorReading::create([
            'room_id' => $roomId,
            'occupied' => $occupied,
            'door_open' => $doorOpen,
            'light_on' => $lightOn,
            'voltage' => round($voltage, 2),
            'current_ma' => round($currentMa, 1),
            'power_mw' => round($voltage * $currentMa, 2),   // V x mA = mW
        ]);

        app(AlertService::class)->check($reading);

        return $reading;
    }
}
