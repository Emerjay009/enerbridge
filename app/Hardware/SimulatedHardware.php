<?php

namespace App\Hardware;

/** Fake sensors, so the whole system can be tested without any wiring. */
class SimulatedHardware implements RoomHardware
{
    private const LIGHT_VOLTS = 12.0;
    private const LIGHT_MA = 380.0;

    private array $state = [];

    public function __construct(private array $rooms)
    {
        $now = microtime(true);

        foreach ($rooms as $room) {
            $this->state[$room['id']] = [
                'present' => mt_rand(0, 1) === 1,
                'switch_at' => $now + mt_rand(10, 40),
                'door_until' => 0.0,
                'light' => false,
            ];
        }
    }

    public function setup(): void
    {
    }

    public function refresh(): void
    {
        $now = microtime(true);

        foreach ($this->state as $id => $st) {
            if ($now >= $st['switch_at']) {
                $st['present'] = ! $st['present'];
                $st['door_until'] = $now + 3.0;                 // the door opens briefly when someone comes or goes
                $st['switch_at'] = $now + ($st['present'] ? mt_rand(20, 60) : mt_rand(15, 45));
                $this->state[$id] = $st;
            }
        }
    }

    public function motion(array $room): bool
    {
        return $this->state[$room['id']]['present'] && mt_rand(0, 99) < 50;
    }

    public function doorOpen(array $room): bool
    {
        return microtime(true) < $this->state[$room['id']]['door_until'];
    }

    public function readPower(array $room): ?array
    {
        $jitter = mt_rand(-50, 50) / 1000;

        if ($this->state[$room['id']]['light']) {
            return [self::LIGHT_VOLTS + $jitter, self::LIGHT_MA + mt_rand(-80, 80) / 10];
        }

        return [self::LIGHT_VOLTS + $jitter, mt_rand(0, 15) / 10];
    }

    public function setRelay(array $room, bool $on): void
    {
        $this->state[$room['id']]['light'] = $on;
    }

    public function close(): void
    {
        foreach ($this->state as $id => $st) {
            $this->state[$id]['light'] = false;
        }
    }
}
