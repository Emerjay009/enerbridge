<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    private const ROW_PX = 44;   // height of one hour on the timetable

    public function __construct(private ScheduleService $schedules)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $this->isAdmin($request);

        $rooms = Room::orderBy('id')->get();
        $selected = $rooms->firstWhere('id', (int) $request->query('room')) ?? $rooms->first();

        $startHour = (int) config('enerbridge.timetable.start_hour', 6);
        $endHour = (int) config('enerbridge.timetable.end_hour', 21);

        // ---- weekly timetable of the selected room
        $slots = ClassSchedule::with('teacher')
            ->where('room_id', $selected->id)
            ->orderBy('start_time')
            ->get();

        $columns = [];

        foreach (ScheduleService::DAYS as $dow => $name) {
            $columns[] = [
                'label' => substr($name, 0, 3),
                'is_today' => now()->dayOfWeek === $dow,
                'blocks' => $slots->where('day_of_week', $dow)
                    ->map(fn ($c) => $this->block($c, $startHour, $endHour, $user->id))
                    ->filter()->values()->all(),
            ];
        }

        $hours = [];
        for ($h = $startHour; $h < $endHour; $h++) {
            $hours[] = date('g A', mktime($h, 0, 0));
        }

        $nowMinutes = now()->hour * 60 + now()->minute;
        $nowTop = ($nowMinutes >= $startHour * 60 && $nowMinutes < $endHour * 60)
            ? (int) round(($nowMinutes - $startHour * 60) / 60 * self::ROW_PX)
            : null;

        // ---- the person's own classes (administrators see everyone's)
        $myClasses = ClassSchedule::with(['room', 'teacher'])
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->get()
            ->sortBy(fn ($c) => [($c->day_of_week + 6) % 7, $c->start_time])
            ->values();

        return view('schedules.index', [
            'rooms' => $rooms,
            'selected' => $selected,
            'columns' => $columns,
            'hours' => $hours,
            'rowPx' => self::ROW_PX,
            'totalHeight' => ($endHour - $startHour) * self::ROW_PX,
            'nowTop' => $nowTop,
            'myClasses' => $myClasses,
            'isAdmin' => $isAdmin,
            'teachers' => $isAdmin ? $this->teachers() : collect(),
            'days' => ScheduleService::DAYS,
            'buffer' => $this->schedules->bufferMinutes(),
        ]);
    }

    public function store(Request $request)
    {
        $isAdmin = $this->isAdmin($request);

        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'subject' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('approval_status', 'approved')],
        ], $this->messages());

        $teacherId = ($isAdmin && ! empty($data['user_id'])) ? (int) $data['user_id'] : $request->user()->id;
        $start = $data['start_time'].':00';
        $end = $data['end_time'].':00';
        $roomId = (int) $data['room_id'];
        $days = array_values(array_unique(array_map('intval', $data['days'])));

        $problems = [];

        foreach ($days as $day) {
            $problems = array_merge($problems, $this->conflicts($request, $roomId, $teacherId, $day, $start, $end, null));
        }

        if ($problems) {
            return back()->withErrors(['schedule' => $problems])->withInput();
        }

        DB::transaction(function () use ($days, $roomId, $teacherId, $start, $end, $data) {
            foreach ($days as $day) {
                ClassSchedule::create([
                    'room_id' => $roomId,
                    'user_id' => $teacherId,
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'subject' => trim($data['subject']),
                    'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
                ]);
            }
        });

        $room = Room::find($roomId);
        $count = count($days);

        return redirect('/schedules?room='.$roomId)
            ->with('status', "Added {$count} ".($count === 1 ? 'class' : 'classes')." to {$room->name}.");
    }

    public function edit(Request $request, ClassSchedule $schedule)
    {
        $this->authorizeChange($request, $schedule);
        $isAdmin = $this->isAdmin($request);

        return view('schedules.edit', [
            'schedule' => $schedule,
            'rooms' => Room::orderBy('id')->get(),
            'days' => ScheduleService::DAYS,
            'isAdmin' => $isAdmin,
            'teachers' => $isAdmin ? $this->teachers() : collect(),
        ]);
    }

    public function update(Request $request, ClassSchedule $schedule)
    {
        $this->authorizeChange($request, $schedule);
        $isAdmin = $this->isAdmin($request);

        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'subject' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('approval_status', 'approved')],
        ], $this->messages());

        $teacherId = ($isAdmin && ! empty($data['user_id'])) ? (int) $data['user_id'] : (int) $schedule->user_id;
        $start = $data['start_time'].':00';
        $end = $data['end_time'].':00';

        $problems = $this->conflicts($request, (int) $data['room_id'], $teacherId, (int) $data['day_of_week'], $start, $end, $schedule->id);

        if ($problems) {
            return back()->withErrors(['schedule' => $problems])->withInput();
        }

        $schedule->update([
            'room_id' => (int) $data['room_id'],
            'user_id' => $teacherId,
            'day_of_week' => (int) $data['day_of_week'],
            'start_time' => $start,
            'end_time' => $end,
            'subject' => trim($data['subject']),
            'notes' => ! empty($data['notes']) ? trim($data['notes']) : null,
        ]);

        return redirect('/schedules?room='.$schedule->room_id)->with('status', 'Class updated.');
    }

    public function destroy(Request $request, ClassSchedule $schedule)
    {
        $this->authorizeChange($request, $schedule);

        $roomId = $schedule->room_id;
        $schedule->delete();

        return redirect('/schedules?room='.$roomId)->with('status', 'Class removed from the schedule.');
    }

    // ---------------------------------------------------------------------

    private function isAdmin(Request $request): bool
    {
        return Role::find($request->user()->role_id)?->slug === 'administrator';
    }

    private function authorizeChange(Request $request, ClassSchedule $schedule): void
    {
        abort_unless(
            $this->isAdmin($request) || (int) $schedule->user_id === (int) $request->user()->id,
            403,
            'You can only change your own classes.'
        );
    }

    private function teachers()
    {
        return User::where('approval_status', 'approved')->orderBy('first_name')->orderBy('last_name')->get();
    }

    /** One class as a block on the timetable: position and size in pixels. */
    private function block(ClassSchedule $c, int $startHour, int $endHour, int $userId): ?array
    {
        $from = $startHour * 60;
        $to = $endHour * 60;

        $start = max(ScheduleService::toMinutes($c->start_time), $from);
        $end = min(ScheduleService::toMinutes($c->end_time), $to);

        if ($end <= $start) {
            return null;
        }

        return [
            'id' => $c->id,
            'top' => (int) round(($start - $from) / 60 * self::ROW_PX),
            'height' => max(22, (int) round(($end - $start) / 60 * self::ROW_PX) - 2),
            'subject' => $c->subject,
            'time' => ScheduleService::formatTime($c->start_time).' to '.ScheduleService::formatTime($c->end_time),
            'teacher' => $c->teacher?->last_name ?? '',
            'notes' => $c->notes,
            'mine' => (int) $c->user_id === $userId,
        ];
    }

    /** Friendly sentences for every clash with an existing class. */
    private function conflicts(Request $request, int $roomId, int $teacherId, int $day, string $start, string $end, ?int $ignoreId): array
    {
        $messages = [];
        $dayName = ScheduleService::DAYS[$day];

        foreach ($this->schedules->overlapping($roomId, $day, $start, $end, $ignoreId) as $c) {
            $messages[] = sprintf(
                '%s is already booked on %s from %s (%s%s).',
                $c->room->name,
                $dayName,
                $this->range($c),
                $c->subject,
                $c->teacher ? ', '.$c->teacher->last_name : ''
            );
        }

        foreach ($this->schedules->teacherOverlapping($teacherId, $day, $start, $end, $ignoreId) as $c) {
            if ((int) $c->room_id === $roomId) {
                continue;   // already reported above as a room clash
            }

            $who = $teacherId === (int) $request->user()->id
                ? 'You are'
                : trim(($c->teacher->first_name ?? '').' '.($c->teacher->last_name ?? '')).' is';

            $messages[] = sprintf('%s already teaching in %s on %s from %s (%s).', $who, $c->room->name, $dayName, $this->range($c), $c->subject);
        }

        return $messages;
    }

    private function range(ClassSchedule $c): string
    {
        return ScheduleService::formatTime($c->start_time).' to '.ScheduleService::formatTime($c->end_time);
    }

    private function messages(): array
    {
        return [
            'room_id.required' => 'Choose a room.',
            'days.required' => 'Pick at least one day.',
            'days.min' => 'Pick at least one day.',
            'day_of_week.required' => 'Choose a day.',
            'start_time.required' => 'Enter the start time.',
            'start_time.date_format' => 'Enter the start time like 07:30.',
            'end_time.required' => 'Enter the end time.',
            'end_time.date_format' => 'Enter the end time like 09:00.',
            'end_time.after' => 'The end time must be later than the start time.',
            'subject.required' => 'Enter the subject or course name.',
            'subject.max' => 'The subject is too long (120 characters at most).',
            'notes.max' => 'The notes are too long (255 characters at most).',
        ];
    }
}
