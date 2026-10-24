<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Class schedules</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="app page-schedules">
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
    Add your classes so each room knows when it is in use. A room's electrical load is watched from {{ $buffer }} minutes before a class
    until {{ $buffer }} minutes after it. If an electric load is detected when no class is scheduled, the administrators get an alert.
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
          <span><i class="sw-mine"></i>Your classes (click to edit)</span>
          <span><i class="sw-other"></i>Other teachers</span>
          <span><i class="sw-now"></i>Now</span>
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
