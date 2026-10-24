<?php

namespace App\Services;

use App\Models\ClassSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    // Day numbers follow PHP/Carbon: 0 = Sunday ... 6 = Saturday. Listed Monday first for display.
    public const DAYS = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
        5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday',
    ];

    public function bufferMinutes(): int
    {
        return (int) config('enerbridge.schedule_buffer_minutes', 10);
    }

    /** "07:30:00" or "07:30" => 450 */
    public static function toMinutes(string $time): int
    {
        $parts = explode(':', $time);

        return ((int) $parts[0]) * 60 + (int) ($parts[1] ?? 0);
    }

    /** "13:05:00" => "1:05 PM" */
    public static function formatTime(string $time): string
    {
        return Carbon::createFromTimeString($time)->format('g:i A');
    }

    /** True when a class is scheduled in this room now, counting the buffer before and after it. */
    public function isAuthorized(int $roomId, Carbon $at): bool
    {
        $buffer = $this->bufferMinutes();
        $now = $at->hour * 60 + $at->minute;

        return ClassSchedule::where('room_id', $roomId)
            ->where('day_of_week', $at->dayOfWeek)
            ->get(['start_time', 'end_time'])
            ->contains(fn ($c) => $now >= self::toMinutes($c->start_time) - $buffer
                && $now <= self::toMinutes($c->end_time) + $buffer);
    }

    /** Classes in the same room whose time overlaps the range (touching ends do not overlap). */
    public function overlapping(int $roomId, int $day, string $start, string $end, ?int $ignoreId = null): Collection
    {
        return ClassSchedule::with(['teacher', 'room'])
            ->where('room_id', $roomId)
            ->where('day_of_week', $day)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();
    }

    /** Classes of the same teacher (in any room) that overlap the range. */
    public function teacherOverlapping(int $userId, int $day, string $start, string $end, ?int $ignoreId = null): Collection
    {
        return ClassSchedule::with(['teacher', 'room'])
            ->where('user_id', $userId)
            ->where('day_of_week', $day)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();
    }
}
