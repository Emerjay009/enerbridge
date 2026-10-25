<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Edit class</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="app page-schedule-edit">
<div class="wrap">
  <div class="title"><a class="brand-mark" href="/" title="EnerBridge home"><img src="{{ asset('img/enerbridge-mark.png') }}" alt="EnerBridge" width="73" height="48"></a><h1>Edit class</h1></div>
  <div class="sub"><a href="/schedules?room={{ $schedule->room_id }}">&larr; Back to schedules</a></div>

  <div class="card">
    <form method="POST" action="/schedules/{{ $schedule->id }}">
      @csrf @method('PUT')

      <label class="l" for="room_id">Room</label>
      <select id="room_id" name="room_id">
        @foreach ($rooms as $r)
          <option value="{{ $r->id }}" {{ (int) old('room_id', $schedule->room_id) === $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
        @endforeach
      </select>

      <label class="l" for="day_of_week">Day</label>
      <select id="day_of_week" name="day_of_week">
        @foreach ($days as $num => $name)
          <option value="{{ $num }}" {{ (int) old('day_of_week', $schedule->day_of_week) === $num ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </select>

      <div class="row2">
        <div>
          <label class="l" for="start_time">Starts</label>
          <input type="time" id="start_time" name="start_time" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required>
        </div>
        <div>
          <label class="l" for="end_time">Ends</label>
          <input type="time" id="end_time" name="end_time" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required>
        </div>
      </div>

      <label class="l" for="subject">Subject or course</label>
      <input type="text" id="subject" name="subject" value="{{ old('subject', $schedule->subject) }}" maxlength="120" required>

      <label class="l" for="notes">Section or notes <span class="muted">(optional)</span></label>
      <input type="text" id="notes" name="notes" value="{{ old('notes', $schedule->notes) }}" maxlength="255">

      @if ($isAdmin)
        <label class="l" for="user_id">Teacher</label>
        <select id="user_id" name="user_id">
          @foreach ($teachers as $t)
            <option value="{{ $t->id }}" {{ (int) old('user_id', $schedule->user_id) === $t->id ? 'selected' : '' }}>{{ trim($t->first_name.' '.$t->last_name) }}</option>
          @endforeach
        </select>
      @endif

      <div class="bar">
        <button class="btn" type="submit">Save changes</button>
        <a href="/schedules?room={{ $schedule->room_id }}">Cancel</a>
      </div>
    </form>

    <form method="POST" action="/schedules/{{ $schedule->id }}" onsubmit="return confirm('Remove this class from the schedule?')" style="margin-top:16px">
      @csrf @method('DELETE')
      <button class="linkbtn" type="submit">Delete this class</button>
    </form>
  </div>
</div>
@include('auth._popup', ['popupTitle' => 'Please check the schedule'])
</body>
</html>
