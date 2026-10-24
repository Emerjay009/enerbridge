<?php

namespace App\Hardware;

/**
 * Everything the sensor loop needs from the outside world.
 * Each $room is an array with the keys: id, name, code, pir, ir, relay, ina219.
 */
interface RoomHardware
{
    /** Prepare the pins (inputs with pull resistors, relays driven OFF). */
    public function setup(): void;

    /** Read all inputs once. Called at the start of every pass. */
    public function refresh(): void;

    public function motion(array $room): bool;

    public function doorOpen(array $room): bool;

    /** @return array{0: float, 1: float}|null  [volts, milliamps], or null if it cannot be read */
    public function readPower(array $room): ?array;

    public function setRelay(array $room, bool $on): void;

    /** Switch every relay OFF. */
    public function close(): void;
}
