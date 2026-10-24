<?php

namespace App\Console\Commands;

use App\Hardware\PiHardware;
use App\Hardware\RoomHardware;
use App\Hardware\SimulatedHardware;
use App\Models\Room;
use App\Services\ReadingRecorder;
use App\Services\SensorRunner;
use Illuminate\Console\Command;

class RunSensors extends Command
{
    protected $signature = 'enerbridge:sensors
                            {--simulate : Use fake sensors (no hardware needed)}
                            {--test : Print live sensor values until you press Ctrl+C}
                            {--blink : Switch each relay on for 2 seconds, one by one}
                            {--duration= : Stop after this many seconds}';

    protected $description = 'Read the sensors, switch the relays and save the readings';

    public function handle(): int
    {
        $config = config('enerbridge.sensors');
        $rooms = $this->loadRooms($config['rooms']);

        if (empty($rooms)) {
            $this->error('No rooms found. Run: php artisan migrate && php artisan db:seed');

            return self::FAILURE;
        }

        if ($problems = $this->pinProblems($rooms)) {
            $this->error('Fix these problems in config/enerbridge.php first:');

            foreach ($problems as $problem) {
                $this->line("  - {$problem}");
            }

            return self::FAILURE;
        }

        $simulate = $this->option('simulate') || $config['mode'] === 'simulate';
        $hardware = $simulate ? new SimulatedHardware($rooms) : new PiHardware($rooms, $config);

        $this->printPins($rooms, $simulate);
        $this->saveHardwareInfo($rooms);

        $stop = false;

        if (function_exists('pcntl_signal')) {
            $this->trap([SIGINT, SIGTERM], function () use (&$stop) {
                $stop = true;
            });
        }

        try {
            $hardware->setup();

            if ($this->option('blink')) {
                return $this->blink($hardware, $rooms);
            }

            if ($this->option('test')) {
                return $this->liveTest($hardware, $rooms, $stop);
            }

            return $this->runLoop($hardware, $rooms, $config, $stop);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            $hardware->close();   // every relay OFF
            $this->line('Stopped. All relays switched off.');
        }
    }

    private function runLoop(RoomHardware $hardware, array $rooms, array $config, bool &$stop): int
    {
        $runner = new SensorRunner(
            $hardware,
            app(ReadingRecorder::class),
            $rooms,
            (int) $config['hold_seconds'],
            (int) $config['record_seconds'],
        );

        $duration = $this->option('duration') !== null ? (float) $this->option('duration') : null;
        $started = microtime(true);

        $this->info('Running. Readings are saved to the database. Press Ctrl+C to stop.');

        while (! $stop) {
            $now = microtime(true);

            try {
                $runner->tick($now);
            } catch (\Throwable $e) {
                $this->error('Sensor error: '.$e->getMessage());
            }

            if ($duration !== null && ($now - $started) >= $duration) {
                break;
            }

            usleep((int) ($config['poll_seconds'] * 1000000));
        }

        return self::SUCCESS;
    }

    private function liveTest(RoomHardware $hardware, array $rooms, bool &$stop): int
    {
        $this->info('Live values. Wave a hand in front of a sensor, open a door. Press Ctrl+C to stop.');

        while (! $stop) {
            $hardware->refresh();

            foreach ($rooms as $room) {
                $power = $hardware->readPower($room);

                $this->line(sprintf(
                    'Room %s   PIR motion: %-3s   Door: %-6s   %s',
                    $room['code'],
                    $hardware->motion($room) ? 'YES' : 'no',
                    $hardware->doorOpen($room) ? 'OPEN' : 'closed',
                    $power ? sprintf('%6.2f V  %7.1f mA', $power[0], $power[1]) : 'INA219 not answering'
                ));
            }

            $this->newLine();
            sleep(1);
        }

        return self::SUCCESS;
    }

    private function blink(RoomHardware $hardware, array $rooms): int
    {
        foreach ($rooms as $room) {
            $this->line("Room {$room['code']}: relay IN on GPIO{$room['relay']} ON for 2 seconds...");
            $hardware->setRelay($room, true);
            sleep(2);
            $hardware->setRelay($room, false);
        }

        $this->info('Done. Each relay should have clicked and its light should have come on.');

        return self::SUCCESS;
    }

    /** Joins the pin settings with the rooms found in the database. */
    private function loadRooms(array $map): array
    {
        $rooms = [];

        foreach ($map as $code => $pins) {
            $room = Room::where('name', 'Room '.$code)->first();

            if (! $room) {
                $this->warn("Room {$code} is in config/enerbridge.php but not in the database (expected the name \"Room {$code}\"). Skipping it.");

                continue;
            }

            $rooms[] = [
                'id' => $room->id,
                'name' => $room->name,
                'code' => (string) $code,
                'pir' => $pins['pir'],
                'ir' => $pins['ir'],
                'relay' => $pins['relay'],
                'ina219' => $pins['ina219'],
            ];
        }

        return $rooms;
    }

    private function pinProblems(array $rooms): array
    {
        $problems = [];
        $usedPins = [];
        $usedAddresses = [];

        foreach ($rooms as $room) {
            foreach (['pir', 'ir', 'relay'] as $field) {
                $pin = $room[$field];

                if (! is_int($pin) || $pin < 4 || $pin > 27) {
                    $problems[] = "Room {$room['code']}: {$field} GPIO ".json_encode($pin).' is not usable (use 4 to 27; GPIO2 and 3 are the I2C pins).';

                    continue;
                }

                if (isset($usedPins[$pin])) {
                    $problems[] = "Room {$room['code']}: {$field} GPIO{$pin} is already used by {$usedPins[$pin]}.";
                } else {
                    $usedPins[$pin] = "room {$room['code']} {$field}";
                }
            }

            $address = $room['ina219'];

            if (! is_int($address) || $address < 0x40 || $address > 0x4F) {
                $problems[] = "Room {$room['code']}: INA219 address must be between 0x40 and 0x4F.";
            } elseif (isset($usedAddresses[$address])) {
                $problems[] = "Room {$room['code']}: INA219 address ".sprintf('0x%02x', $address)." is already used by room {$usedAddresses[$address]}.";
            } else {
                $usedAddresses[$address] = $room['code'];
            }
        }

        return $problems;
    }

    private function printPins(array $rooms, bool $simulate): void
    {
        $this->info($simulate ? 'Mode: SIMULATION (fake sensors)' : 'Mode: real hardware');

        $this->table(
            ['Room', 'HC-SR501', 'IR sensor', 'Relay input', 'INA219'],
            array_map(fn ($r) => [
                $r['name'], 'GPIO'.$r['pir'], 'GPIO'.$r['ir'], 'GPIO'.$r['relay'], sprintf('0x%02x', $r['ina219']),
            ], $rooms)
        );
    }

    /** So each classroom page shows the pins that are really in use. */
    private function saveHardwareInfo(array $rooms): void
    {
        foreach ($rooms as $room) {
            Room::whereKey($room['id'])->update([
                'pir_gpio' => $room['pir'],
                'ir_gpio' => $room['ir'],
                'light_gpio' => $room['relay'],
                'ina219_address' => sprintf('0x%02x', $room['ina219']),
                'hardware_synced_at' => now(),
            ]);
        }
    }
}
