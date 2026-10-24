<?php

namespace App\Hardware;

/** Decoding of INA219 register values (voltage and current). */
class Ina219
{
    /**
     * @param  int  $shuntHi  high byte of the shunt voltage register (0x01)
     * @param  int  $busHi    high byte of the bus voltage register (0x02)
     * @return array{0: float, 1: float}|null  [volts, milliamps], or null on math overflow
     */
    public static function decode(int $shuntHi, int $shuntLo, int $busHi, int $busLo, float $shuntOhms): ?array
    {
        $shunt = ($shuntHi << 8) | $shuntLo;

        if ($shunt & 0x8000) {
            $shunt -= 0x10000;                       // negative current
        }

        $shuntMillivolts = $shunt * 0.01;            // 10 microvolts per step
        $milliamps = $shuntMillivolts / $shuntOhms;  // mV / ohm = mA

        $bus = ($busHi << 8) | $busLo;

        if ($bus & 0x1) {
            return null;                             // overflow flag: current above the shunt range
        }

        $volts = ($bus >> 3) * 0.004;                // 4 mV per step

        return [$volts, $milliamps];
    }

    /** Decodes the text printed by: i2ctransfer -y 1 w1@0x40 0x01 r2 w1@0x40 0x02 r2 */
    public static function decodeOutput(string $output, float $shuntOhms): ?array
    {
        if (! preg_match_all('/0x([0-9a-fA-F]{2})/', $output, $matches) || count($matches[1]) < 4) {
            return null;
        }

        $bytes = array_map('hexdec', $matches[1]);

        return self::decode($bytes[0], $bytes[1], $bytes[2], $bytes[3], $shuntOhms);
    }
}
