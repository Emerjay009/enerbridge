<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\RoomSchedule;
use App\Models\SensorReading;

class AlertService
{
    // A load counts as "on" if the light is on or the room draws at least this much power.
    public const MIN_POWER_MW = 50;

    public function check(SensorReading $reading): void
    {
        $room = $reading->room;
        $at = $reading->created_at ?? now();

        $withinSchedule = $this->withinSchedule($reading->room_id, $at);
        $loadOn = $reading->light_on || $reading->power_mw >= self::MIN_POWER_MW;

        $open = Alert::where('room_id', $reading->room_id)
            ->where('type', 'off_schedule_load')
            ->whereNull('resolved_at')
            ->first();

        if (! $withinSchedule && $loadOn && ! $open) {
            $message = sprintf(
                "%s: the load is ON outside the authorized schedule.\nTime: %s\nVoltage: %.2f V, current: %.1f mA, power: %.1f mW",
                $room->name,
                $at->format('D, M j, Y g:i A'),
                $reading->voltage,
                $reading->current_ma,
                $reading->power_mw
            );

            $alert = Alert::create([
                'room_id' => $reading->room_id,
                'type' => 'off_schedule_load',
                'message' => $message,
                'power_mw' => $reading->power_mw,
            ]);

            // Send after the HTTP response, so the sensor script is not kept waiting.
            defer(function () use ($alert, $room, $message) {
                $result = app(NotificationService::class)
                    ->notifyAdmins("EnerBridge alert: {$room->name}", $message);

                if ($result['email'] === null) {
                    $alert->update(['emailed_at' => now()]);
                }
            });
        } elseif (($withinSchedule || ! $loadOn) && $open) {
            // The problem is over (load switched off, or the room's hours began): close the alert.
            $open->update(['resolved_at' => now()]);
        }
    }

    private function withinSchedule(int $roomId, $at): bool
    {
        $time = $at->format('H:i:s');

        return RoomSchedule::where('room_id', $roomId)
            ->where('day_of_week', $at->dayOfWeek)   // 0 = Sunday ... 6 = Saturday
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->exists();
    }
}
