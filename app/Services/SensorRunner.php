<?php

namespace App\Services;

use App\Hardware\RoomHardware;

/**
 * One pass of the control loop: read sensors, switch each room's relay, save readings.
 *
 * Rule: movement means the room is occupied and the light turns ON. When there has been no
 * movement for $holdSeconds the room is vacant and the light turns OFF
 * (a PIR only sees movement, so the hold time bridges quiet moments).
 */
class SensorRunner
{
    private array $states = [];

    /** @param  array<int, array>  $rooms  each with: id, name, code, pir, ir, relay, ina219 */
    public function __construct(
        private RoomHardware $hardware,
        private ReadingRecorder $recorder,
        private array $rooms,
        private int $holdSeconds,
        private int $recordSeconds,
    ) {
    }

    /** @param  float  $now  seconds, from microtime(true) */
    public function tick(float $now): void
    {
        $this->hardware->refresh();

        foreach ($this->rooms as $room) {
            $id = $room['id'];

            if (! isset($this->states[$id])) {
                $this->states[$id] = ['last_motion' => null, 'light' => false, 'last_sent' => -INF, 'last_key' => null];
            }

            $st = $this->states[$id];

            if ($this->hardware->motion($room)) {
                $st['last_motion'] = $now;
            }

            $occupied = $st['last_motion'] !== null && ($now - $st['last_motion']) < $this->holdSeconds;

            if ($occupied !== $st['light']) {
                $this->hardware->setRelay($room, $occupied);
                $st['light'] = $occupied;
                fwrite(STDOUT, date('H:i:s')." Room {$room['code']}: ".($occupied ? 'occupied, light ON' : 'vacant, light OFF').PHP_EOL);
            }

            $doorOpen = $this->hardware->doorOpen($room);
            $power = $this->hardware->readPower($room);
            [$volts, $milliamps] = $power ?? [0.0, 0.0];

            $key = [$occupied, $doorOpen, $st['light']];

            if ($key !== $st['last_key'] || ($now - $st['last_sent']) >= $this->recordSeconds) {
                $this->recorder->record($id, $occupied, $doorOpen, $st['light'], $volts, $milliamps);
                $st['last_sent'] = $now;
                $st['last_key'] = $key;
            }

            $this->states[$id] = $st;
        }
    }
}
