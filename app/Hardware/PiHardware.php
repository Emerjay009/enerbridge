<?php

namespace App\Hardware;

use Illuminate\Support\Facades\Process;

/**
 * Real hardware on the Raspberry Pi, driven with two standard tools:
 *   pinctrl      (GPIO: PIR, IR sensor and relays)  - comes with Raspberry Pi OS
 *   i2ctransfer  (INA219 voltage/current)           - package i2c-tools
 * pinctrl keeps an output pin at its level after it exits, so the relays hold their state.
 */
class PiHardware implements RoomHardware
{
    /** @var array<int, bool>  pin => true when the pin reads HIGH */
    private array $levels = [];

    private array $warned = [];

    public function __construct(private array $rooms, private array $options)
    {
    }

    public function setup(): void
    {
        foreach ($this->rooms as $room) {
            $this->run(['pinctrl', 'set', (string) $room['pir'], 'ip', 'pd']);
            $this->run(['pinctrl', 'set', (string) $room['ir'], 'ip', $this->options['ir_active_low'] ? 'pu' : 'pd']);

            // Relay OFF first, so a light never flashes on while starting
            $this->drive($room, false);

            // INA219 config: 32 V range, +/-320 mV, 12 bit, continuous (the chip's default, written to be sure)
            try {
                $this->run(['i2ctransfer', '-y', (string) $this->options['i2c_bus'], sprintf('w3@0x%02x', $room['ina219']), '0x00', '0x39', '0x9f']);
            } catch (\Throwable $e) {
                $this->warnOnce("ina-setup-{$room['code']}", "Room {$room['code']}: INA219 did not answer at ".sprintf('0x%02x', $room['ina219']).' ('.$e->getMessage().')');
            }
        }
    }

    public function refresh(): void
    {
        $pins = [];

        foreach ($this->rooms as $room) {
            $pins[] = $room['pir'];
            $pins[] = $room['ir'];
        }

        $pins = array_values(array_unique($pins));

        // One call reads all input pins
        $levels = self::parsePinctrl($this->run(['pinctrl', 'get', implode(',', $pins)]));

        // Any pin missing from that answer is read on its own
        foreach ($pins as $pin) {
            if (! array_key_exists($pin, $levels)) {
                $levels += self::parsePinctrl($this->run(['pinctrl', 'get', (string) $pin]));
            }
        }

        $this->levels = $levels;
    }

    public function motion(array $room): bool
    {
        return $this->levels[$room['pir']] ?? false;           // the HC-SR501 output is HIGH while it sees movement
    }

    public function doorOpen(array $room): bool
    {
        $high = $this->levels[$room['ir']] ?? true;
        $detects = $this->options['ir_active_low'] ? ! $high : $high;   // IR modules usually pull OUT LOW on detection

        return $this->options['ir_detects_door_closed'] ? ! $detects : $detects;
    }

    public function readPower(array $room): ?array
    {
        $address = sprintf('0x%02x', $room['ina219']);

        try {
            $output = $this->run([
                'i2ctransfer', '-y', (string) $this->options['i2c_bus'],
                "w1@{$address}", '0x01', 'r2',
                "w1@{$address}", '0x02', 'r2',
            ]);
        } catch (\Throwable $e) {
            $this->warnOnce("ina-read-{$room['code']}", "Room {$room['code']}: cannot read the INA219 ({$e->getMessage()})");

            return null;
        }

        return Ina219::decodeOutput($output, (float) $this->options['shunt_ohms']);
    }

    public function setRelay(array $room, bool $on): void
    {
        $this->drive($room, $on);
    }

    public function close(): void
    {
        foreach ($this->rooms as $room) {
            try {
                $this->drive($room, false);
            } catch (\Throwable $e) {
                // keep switching the other relays off
            }
        }
    }

    /** Parses lines like: " 17: ip    pd | lo // GPIO17 = input"  =>  [17 => false] */
    public static function parsePinctrl(string $output): array
    {
        $levels = [];

        if (preg_match_all('/^\s*(\d+):.*?\|\s*(hi|lo)\b/m', $output, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $levels[(int) $match[1]] = $match[2] === 'hi';
            }
        }

        return $levels;
    }

    private function drive(array $room, bool $on): void
    {
        // Active-low relay boards: LOW = ON, HIGH = OFF
        $high = $this->options['relay_active_low'] ? ! $on : $on;

        $this->run(['pinctrl', 'set', (string) $room['relay'], 'op', $high ? 'dh' : 'dl']);
    }

    private function run(array $command): string
    {
        $result = Process::timeout(5)->run($command);

        if (! $result->successful()) {
            throw new \RuntimeException($command[0].' failed: '.trim($result->errorOutput() ?: $result->output()));
        }

        return $result->output();
    }

    private function warnOnce(string $key, string $message): void
    {
        if (! isset($this->warned[$key])) {
            $this->warned[$key] = true;
            fwrite(STDERR, $message.PHP_EOL);
        }
    }
}
