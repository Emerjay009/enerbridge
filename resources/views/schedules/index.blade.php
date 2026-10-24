<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Class schedules</title>
<style>
  * { box-sizing:border-box; }
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; padding:24px; }
  .wrap { max-width:1280px; margin:0 auto; }
  a { color:#38bdf8; text-decoration:none; } a:hover { text-decoration:underline; }
  .top { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:6px; }
  h1 { margin:0; font-size:28px; } h2 { margin:0 0 12px; font-size:18px; }
  .sub { color:#94a3b8; font-size:14px; margin-bottom:16px; max-width:760px; line-height:1.5; }
  .flash { background:#14532d; color:#bbf7d0; padding:10px 14px; border-radius:10px; margin-bottom:16px; font-size:14px; }
  .layout { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:16px; align-items:start; }
  @media (max-width: 1000px) { .layout { grid-template-columns:1fr; } }
  .card { background:#1e293b; border-radius:12px; padding:18px; margin-bottom:16px; }
  .tabs { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
  .tabs a { padding:9px 18px; border-radius:999px; background:#1e293b; color:#e2e8f0; font-size:15px; font-weight:bold; }
  .tabs a:hover { text-decoration:none; background:#334155; }
  .tabs a.active { background:#0284c7; }

  /* weekly timetable */
  .ttwrap { overflow-x:auto; }
  .tt { min-width:680px; }
  .tt-head, .tt-body { display:grid; grid-template-columns:52px repeat(7, minmax(0,1fr)); }
  .dayhead { text-align:center; font-size:13px; font-weight:bold; padding:8px 0; color:#94a3b8; }
  .dayhead.today { color:#2dd4bf; }
  .hours .hour { height:var(--row); font-size:11px; color:#94a3b8; text-align:right; padding-right:8px; transform:translateY(-7px); }
  .daycol { position:relative; height:var(--h); border-left:1px solid #334155;
            background-image:repeating-linear-gradient(to bottom, transparent 0, transparent calc(var(--row) - 1px), #26344a calc(var(--row) - 1px), #26344a var(--row)); }
  .daycol.today { background-color:rgba(45,212,191,0.05); }
  .now { position:absolute; left:0; right:0; height:2px; background:#f87171; z-index:3; }
  .blk { position:absolute; left:3px; right:3px; border-radius:8px; padding:4px 6px; overflow:hidden; font-size:12px; line-height:1.25; color:#fff; z-index:2; display:block; }
  .blk b { display:block; font-size:12px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .blk span { display:block; font-size:11px; opacity:.9; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .blk.mine { background:#0d9488; border:1px solid #2dd4bf; }
  .blk.other { background:#334155; border:1px solid #475569; color:#e2e8f0; }
  a.blk:hover { text-decoration:none; filter:brightness(1.12); }
  .legend { display:flex; gap:16px; font-size:13px; color:#94a3b8; margin-top:12px; flex-wrap:wrap; }
  .legend i { display:inline-block; width:12px; height:12px; border-radius:3px; margin-right:6px; vertical-align:-1px; }

  /* class list */
  table { width:100%; border-collapse:collapse; font-size:14px; }
  th { text-align:left; color:#94a3b8; font-weight:normal; padding:8px 6px; border-bottom:1px solid #334155; }
  td { padding:10px 6px; border-bottom:1px solid #334155; vertical-align:middle; }
  tr:last-child td { border-bottom:0; }
  .room-badge { background:#0e2b2a; color:#2dd4bf; border:1px solid #14595a; border-radius:999px; padding:2px 10px; font-size:12px; font-weight:bold; white-space:nowrap; }
  .muted { color:#94a3b8; font-size:13px; }
  .empty { text-align:center; padding:22px 10px; color:#94a3b8; font-size:14px; line-height:1.6; }
  .actions { display:flex; gap:8px; align-items:center; }
  .linkbtn { background:none; border:0; color:#f87171; cursor:pointer; font-size:14px; padding:0; }
  .linkbtn:hover { text-decoration:underline; }

  /* form */
  label.l { display:block; font-size:14px; margin:14px 0 6px; font-weight:bold; }
  input[type=text], input[type=time], select { width:100%; padding:10px; border-radius:8px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; font-size:15px; }
  input:focus, select:focus { outline:2px solid #0284c7; border-color:#0284c7; }
  .row2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .days { display:flex; flex-wrap:wrap; gap:6px; }
  .day input { display:none; }
  .day span { display:inline-block; padding:8px 12px; border-radius:999px; border:1px solid #334155; cursor:pointer; font-size:14px; user-select:none; }
  .day input:checked + span { background:#0d9488; border-color:#2dd4bf; font-weight:bold; }
  .presets { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
  .presets button { background:#0f172a; color:#38bdf8; border:1px solid #334155; border-radius:8px; padding:5px 10px; font-size:12px; cursor:pointer; }
  .presets button:hover { background:#1e3a52; }
  .btn { width:100%; margin-top:18px; padding:12px; border:0; border-radius:10px; background:#0284c7; color:#fff; font-size:16px; font-weight:bold; cursor:pointer; }
  .btn:hover { background:#0369a1; }
  .tip { font-size:13px; color:#94a3b8; line-height:1.55; }
  .tip b { color:#e2e8f0; }
</style>
</head>
<body>
@php
  $fmt = fn ($t) => \App\Services\ScheduleService::formatTime($t);
  $firstName = fn ($u) => trim(($u->first_name ?? '').' '.($u->last_name ?? ''));
@endphp
<div class="wrap">
  <div class="top">
    <h1>Class schedules</h1>
    <div><a href="/">&larr; Dashboard</a> &nbsp;&middot;&nbsp; <a href="/rooms">Rooms</a></div>
  </div>
  <div class="sub">
    Add your classes so each room knows when it is in use. A room's lights are watched from {{ $buffer }} minutes before a class
    until {{ $buffer }} minutes after it. If a light stays on when no class is scheduled, the administrators get an alert.
  </div>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif

  <div class="layout">
    <div>
      <div class="tabs">
        @foreach ($rooms as $r)
          <a href="/schedules?room={{ $r->id }}" class="{{ $r->id === $selected->id ? 'active' : '' }}">{{ $r->name }}</a>
        @endforeach
      </div>

      <div class="card">
        <h2>{{ $selected->name }}: weekly timetable</h2>
        <div class="ttwrap">
          <div class="tt" style="--row: {{ $rowPx }}px; --h: {{ $totalHeight }}px;">
            <div class="tt-head">
              <div></div>
              @foreach ($columns as $col)
                <div class="dayhead {{ $col['is_today'] ? 'today' : '' }}">{{ $col['label'] }}</div>
              @endforeach
            </div>
            <div class="tt-body">
              <div class="hours">
                @foreach ($hours as $h)
                  <div class="hour">{{ $h }}</div>
                @endforeach
              </div>
              @foreach ($columns as $col)
                <div class="daycol {{ $col['is_today'] ? 'today' : '' }}">
                  @if ($col['is_today'] && $nowTop !== null)
                    <div class="now" style="top: {{ $nowTop }}px" title="Now"></div>
                  @endif
                  @foreach ($col['blocks'] as $b)
                    <a @if ($b['mine'] || $isAdmin) href="/schedules/{{ $b['id'] }}/edit" @endif
                       class="blk {{ $b['mine'] ? 'mine' : 'other' }}"
                       style="top: {{ $b['top'] }}px; height: {{ $b['height'] }}px"
                       title="{{ $b['subject'] }}, {{ $b['time'] }}{{ $b['teacher'] ? ', '.$b['teacher'] : '' }}{{ $b['notes'] ? ' ('.$b['notes'].')' : '' }}">
                      <b>{{ $b['subject'] }}</b>
                      <span>{{ $b['time'] }}</span>
                      @if ($b['teacher'])<span>{{ $b['mine'] ? 'You' : $b['teacher'] }}</span>@endif
                    </a>
                  @endforeach
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="legend">
          <span><i style="background:#0d9488"></i>Your classes (click to edit)</span>
          <span><i style="background:#334155"></i>Other teachers</span>
          <span><i style="background:#f87171"></i>Now</span>
        </div>
      </div>

      <div class="card">
        <h2>{{ $isAdmin ? 'All classes' : 'My classes' }}</h2>
        @if ($myClasses->isEmpty())
          <div class="empty">
            @if ($isAdmin) No classes have been added yet. @else You have not added any classes yet.<br>Use the form to add your first one. @endif
          </div>
        @else
          <div style="overflow-x:auto">
          <table>
            <thead>
              <tr>
                <th>Day</th><th>Time</th><th>Room</th><th>Subject</th>
                @if ($isAdmin)<th>Teacher</th>@endif
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($myClasses as $c)
                <tr>
                  <td>{{ $days[$c->day_of_week] }}</td>
                  <td>{{ $fmt($c->start_time) }} to {{ $fmt($c->end_time) }}</td>
                  <td><span class="room-badge">{{ $c->room->name }}</span></td>
                  <td>{{ $c->subject }}@if ($c->notes)<div class="muted">{{ $c->notes }}</div>@endif</td>
                  @if ($isAdmin)<td>{{ $firstName($c->teacher) }}</td>@endif
                  <td>
                    <div class="actions">
                      <a href="/schedules/{{ $c->id }}/edit">Edit</a>
                      <form method="POST" action="/schedules/{{ $c->id }}" onsubmit="return confirm('Remove this class from the schedule?')">
                        @csrf @method('DELETE')
                        <button class="linkbtn" type="submit">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
          </div>
        @endif
      </div>
    </div>

    <div>
      <div class="card">
        <h2>Add a class</h2>
        <form method="POST" action="/schedules">
          @csrf

          <label class="l" for="room_id">Room</label>
          <select id="room_id" name="room_id">
            @foreach ($rooms as $r)
              <option value="{{ $r->id }}" {{ (int) old('room_id', $selected->id) === $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
            @endforeach
          </select>

          <label class="l">Days</label>
          <div class="days">
            @foreach ($days as $num => $name)
              <label class="day"><input type="checkbox" name="days[]" value="{{ $num }}" {{ in_array($num, array_map('intval', old('days', []))) ? 'checked' : '' }}><span>{{ substr($name, 0, 3) }}</span></label>
            @endforeach
          </div>
          <div class="presets">
            <button type="button" data-days="1,2,3,4,5">Mon to Fri</button>
            <button type="button" data-days="1,3,5">Mon Wed Fri</button>
            <button type="button" data-days="2,4">Tue Thu</button>
            <button type="button" data-days="">Clear</button>
          </div>

          <div class="row2">
            <div>
              <label class="l" for="start_time">Starts</label>
              <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" required>
            </div>
            <div>
              <label class="l" for="end_time">Ends</label>
              <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" required>
            </div>
          </div>

          <label class="l" for="subject">Subject or course</label>
          <input type="text" id="subject" name="subject" value="{{ old('subject') }}" maxlength="120" placeholder="For example: Physics 101" required>

          <label class="l" for="notes">Section or notes <span class="muted">(optional)</span></label>
          <input type="text" id="notes" name="notes" value="{{ old('notes') }}" maxlength="255" placeholder="For example: BSIT 3-1">

          @if ($isAdmin)
            <label class="l" for="user_id">Teacher</label>
            <select id="user_id" name="user_id">
              <option value="">Me</option>
              @foreach ($teachers as $t)
                <option value="{{ $t->id }}" {{ (int) old('user_id') === $t->id ? 'selected' : '' }}>{{ $firstName($t) }}</option>
              @endforeach
            </select>
          @endif

          <button class="btn" type="submit">Add to schedule</button>
        </form>
      </div>

      <div class="card tip">
        <b>Good to know</b><br>
        Pick several days at once to repeat a class every week.<br>
        Two classes cannot use the same room at the same time, and one teacher cannot be in two rooms at once.
        If there is a clash, you will see exactly which class it clashes with.
      </div>
    </div>
  </div>
</div>

<script>
  // Day presets
  document.querySelectorAll('.presets button').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var wanted = btn.dataset.days ? btn.dataset.days.split(',') : [];
      document.querySelectorAll('input[name="days[]"]').forEach(function (box) {
        box.checked = wanted.indexOf(box.value) !== -1;
      });
    });
  });

  // When a start time is picked, suggest an end time one hour later
  var start = document.getElementById('start_time');
  var end = document.getElementById('end_time');
  start.addEventListener('change', function () {
    if (!start.value) return;
    var parts = start.value.split(':');
    var minutes = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    var current = end.value ? parseInt(end.value.split(':')[0], 10) * 60 + parseInt(end.value.split(':')[1], 10) : 0;
    if (!end.value || current <= minutes) {
      var next = Math.min(minutes + 60, 23 * 60 + 59);
      end.value = String(Math.floor(next / 60)).padStart(2, '0') + ':' + String(next % 60).padStart(2, '0');
    }
  });
</script>
@include('auth._popup', ['popupTitle' => 'Please check the schedule'])
</body>
</html>
