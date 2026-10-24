<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\ScheduleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    // A room counts as "online" if its sensor service sent a reading this recently.
    public const ONLINE_SECONDS = 30;

    // When integrating energy, a gap longer than this between readings is not counted
    // (for example while the sensor service was stopped).
    public const MAX_GAP_SECONDS = 300;

    public function index()
    {
        $cards = Room::orderBy('id')->get()->map(fn ($room) => [
            'room' => $room,
            'status' => $this->status($room),
            'metrics' => $this->todayMetrics($room),
            'openAlert' => $room->alerts()->whereNull('resolved_at')->exists(),
        ]);

        return view('rooms.index', compact('cards'));
    }

    public function show(Room $room)
    {
        return view('rooms.show', [
            'room' => $room,
            'rooms' => Room::orderBy('id')->get(['id', 'name']),
            'status' => $this->status($room),
            'metrics' => $this->todayMetrics($room),
            'history' => $this->history($room),
            'daily' => $this->dailyEnergy($room),
            'events' => $this->events($room),
            'schedule' => $this->scheduleRows($room),
            'inSchedule' => $this->withinScheduleNow($room),
            'alerts' => $room->alerts()->latest()->take(10)->get(),
            'openAlert' => $room->alerts()->whereNull('resolved_at')->exists(),
        ]);
    }

    // Refreshed every few seconds by the page
    public function data(Room $room)
    {
        return response()->json([
            'status' => $this->status($room),
            'metrics' => $this->todayMetrics($room),
            'history' => $this->history($room),
            'in_schedule' => $this->withinScheduleNow($room),
            'open_alert' => $room->alerts()->whereNull('resolved_at')->exists(),
        ]);
    }

    private function startOfToday(): string
    {
        return now()->startOfDay()->format('Y-m-d H:i:s');
    }

    private function status(Room $room): array
    {
        $l = $room->readings()->latest('id')->first();
        $online = $l && $l->created_at->gt(now()->subSeconds(self::ONLINE_SECONDS));

        return [
            'has_data' => (bool) $l,
            'online' => (bool) $online,
            'occupied' => $l?->occupied ?? false,
            'door_open' => $l?->door_open ?? false,
            'light_on' => $l?->light_on ?? false,
            'voltage' => (float) ($l?->voltage ?? 0),
            'current_ma' => (float) ($l?->current_ma ?? 0),
            'power_mw' => (float) ($l?->power_mw ?? 0),
            'updated' => $l?->created_at?->diffForHumans() ?? 'no data yet',
        ];
    }

    // Energy, light-on time and occupied time since midnight, from the readings.
    // Each reading is counted until the next one arrives (up to MAX_GAP_SECONDS).
    private function todayMetrics(Room $room): array
    {
        $from = $this->startOfToday();
        $gap = (int) self::MAX_GAP_SECONDS;

        $row = DB::selectOne("
            SELECT
                COALESCE(SUM(power_mw * dt) / 3600 / 1000, 0) AS energy_wh,
                COALESCE(SUM(CASE WHEN light_on = 1 THEN dt ELSE 0 END) / 60, 0) AS light_min,
                COALESCE(SUM(CASE WHEN occupied = 1 THEN dt ELSE 0 END) / 60, 0) AS occupied_min
            FROM (
                SELECT power_mw, light_on, occupied,
                       LEAST(TIMESTAMPDIFF(SECOND, created_at, LEAD(created_at) OVER (ORDER BY created_at)), {$gap}) AS dt
                FROM sensor_readings
                WHERE room_id = ? AND created_at >= ?
            ) t
            WHERE dt IS NOT NULL
        ", [$room->id, $from]);

        $today = $room->readings()->where('created_at', '>=', $from);

        return [
            'energy_wh' => round((float) $row->energy_wh, 2),
            'light_min' => round((float) $row->light_min, 1),
            'occupied_min' => round((float) $row->occupied_min, 1),
            'peak_mw' => round((float) (clone $today)->max('power_mw'), 1),
            'readings' => (clone $today)->count(),
        ];
    }

    private function history(Room $room)
    {
        return $room->readings()->latest('id')->take(60)->get()->reverse()->values()
            ->map(fn ($h) => ['t' => $h->created_at->format('H:i:s'), 'p' => $h->power_mw]);
    }

    private function dailyEnergy(Room $room, int $days = 7): array
    {
        $from = now()->subDays($days - 1)->startOfDay()->format('Y-m-d H:i:s');
        $gap = (int) self::MAX_GAP_SECONDS;

        $rows = DB::select("
            SELECT DATE(created_at) AS d, SUM(power_mw * dt) / 3600 / 1000 AS wh
            FROM (
                SELECT created_at, power_mw,
                       LEAST(TIMESTAMPDIFF(SECOND, created_at, LEAD(created_at) OVER (ORDER BY created_at)), {$gap}) AS dt
                FROM sensor_readings
                WHERE room_id = ? AND created_at >= ?
            ) t
            WHERE dt IS NOT NULL
            GROUP BY DATE(created_at)
        ", [$room->id, $from]);

        $byDay = collect($rows)->pluck('wh', 'd');
        $out = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $out[] = [
                'label' => $day->format('D M j'),
                'wh' => round((float) ($byDay[$day->format('Y-m-d')] ?? 0), 2),
            ];
        }

        return $out;
    }

    // The last changes of occupancy, door and light, found by comparing neighbouring readings.
    private function events(Room $room): array
    {
        $rows = $room->readings()->latest('id')->take(400)->get();
        $events = [];

        for ($i = 0; $i < $rows->count() - 1; $i++) {
            $new = $rows[$i];
            $old = $rows[$i + 1];

            foreach ([
                ['occupied', 'Room became occupied', 'Room became vacant'],
                ['door_open', 'Door opened', 'Door closed'],
                ['light_on', 'Light turned ON', 'Light turned OFF'],
            ] as [$field, $onText, $offText]) {
                if ($new->$field !== $old->$field) {
                    $events[] = [
                        'time' => $new->created_at,
                        'label' => $new->$field ? $onText : $offText,
                        'on' => (bool) $new->$field,
                    ];
                }
            }

            if (count($events) >= 15) {
                break;
            }
        }

        return array_slice($events, 0, 15);
    }

    private function scheduleRows(Room $room): array
    {
        $slots = $room->classSchedules()->orderBy('start_time')->get()->groupBy('day_of_week');
        $rows = [];

        foreach (ScheduleService::DAYS as $dow => $name) {
            $day = $slots->get($dow, collect());

            $rows[] = [
                'day' => $name,
                'is_today' => now()->dayOfWeek === $dow,
                'hours' => $day->isEmpty()
                    ? 'No classes'
                    : $day->map(fn ($c) => ScheduleService::formatTime($c->start_time).' to '.ScheduleService::formatTime($c->end_time))->implode(', '),
                'has_class' => $day->isNotEmpty(),
            ];
        }

        return $rows;
    }

    private function withinScheduleNow(Room $room): bool
    {
        return app(ScheduleService::class)->isAuthorized($room->id, now());
    }
}
