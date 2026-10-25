<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | {{ $room->name }}</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="app page-room">
@php
  $fmtMin = function ($m) {
      $m = (float) $m;
      return $m >= 60 ? floor($m / 60).' h '.round(fmod($m, 60)).' min' : round($m).' min';
  };
  $gpio = fn ($n) => $n === null ? 'Not reported yet' : 'GPIO'.$n;
  $size = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
@endphp
<div class="wrap">
  <div class="top">
    <div class="title"><a class="brand-mark" href="/" title="EnerBridge home"><img src="{{ asset('img/enerbridge-mark.png') }}" alt="EnerBridge" width="73" height="48"></a><h1>{{ $room->name }}</h1></div>
    <div><a href="/">&larr; Dashboard</a></div>
  </div>
  <div class="sub">{{ $room->description }}<span class="live" title="Readings refresh automatically every 3 seconds"><i></i>Live</span></div>

  <div class="tabs">
    @foreach ($rooms as $r)
      <a href="/rooms/{{ $r->id }}" class="{{ $r->id === $room->id ? 'active' : '' }}">{{ $r->name }}</a>
    @endforeach
  </div>

  <div id="alert-banner" class="banner" style="{{ $openAlert ? '' : 'display:none' }}">
    Active alert: the load in this room is ON outside the authorized schedule.
  </div>

  <div class="grid g2">
    <div class="card">
      <h2>Live status</h2>
      <div class="row"><span>Sensors</span>
        <span id="st-online" class="badge {{ $status['online'] ? 'on' : ($status['has_data'] ? 'bad' : 'off') }}">{{ $status['online'] ? 'Online' : ($status['has_data'] ? 'Offline' : 'No data yet') }}</span></div>
      <div class="row"><span>Occupancy</span>
        <span id="st-occupied" class="badge {{ $status['occupied'] ? 'on' : 'off' }}">{{ $status['occupied'] ? 'Occupied' : 'Vacant' }}</span></div>
      <div class="row"><span>Door</span>
        <span id="st-door" class="badge {{ $status['door_open'] ? 'warn' : 'off' }}">{{ $status['door_open'] ? 'Open' : 'Closed' }}</span></div>
      <div class="row"><span>Electric load</span>
        <span id="st-load" class="badge {{ $status['load_detected'] ? 'on' : 'off' }}">{{ $status['load_detected'] ? 'Detected' : 'Stable' }}</span></div>
      <div class="row"><span>Authorized hours now</span>
        <span id="st-schedule" class="badge {{ $inSchedule ? 'on' : 'off' }}">{{ $inSchedule ? 'Yes' : 'No' }}</span></div>
      <div class="row"><span>Voltage</span><b id="v-voltage">{{ number_format($status['voltage'], 2) }} V</b></div>
      <div class="row"><span>Current</span><b id="v-current">{{ number_format($status['current_ma'], 1) }} mA</b></div>
      <div class="row"><span>Power</span><b id="v-power">{{ number_format($status['power_mw'], 1) }} mW</b></div>
      <div class="muted" style="margin-top:8px">Last reading <span id="v-updated">{{ $status['updated'] }}</span></div>
    </div>

    <div class="card">
      <h2>Classroom details</h2>
      <div class="row"><span>Size</span><b>{{ $size($room->width_in) }} &times; {{ $size($room->depth_in) }} inches</b></div>
      <div class="row"><span>Floor area</span><b>{{ number_format($room->width_in * $room->depth_in, 0) }} square inches</b></div>
      <div class="row"><span>Walls</span><b>0.5 inch thick</b></div>
      <div class="row"><span>Furniture</span><b>Teacher's desk, student desks and chairs</b></div>
      <div class="row"><span>Electrical load</span><b>Light and fan, as one load</b></div>
      <div class="row"><span>Control rule</span><b>Switched on while occupied</b></div>
      <div class="muted" style="margin-top:8px">The load switches off after the room has been vacant for a while, because the motion sensor only sees movement.</div>
    </div>
  </div>

  <div class="grid g5">
    <div class="metric"><div class="l">Energy today</div><div class="v" id="m-energy">{{ number_format($metrics['energy_wh'], 2) }} Wh</div></div>
    <div class="metric"><div class="l">Peak power today</div><div class="v" id="m-peak">{{ number_format($metrics['peak_mw'], 1) }} mW</div></div>
    <div class="metric"><div class="l">Load detected today</div><div class="v" id="m-load">{{ $fmtMin($metrics['load_min']) }}</div></div>
    <div class="metric"><div class="l">Occupied today</div><div class="v" id="m-occupied">{{ $fmtMin($metrics['occupied_min']) }}</div></div>
    <div class="metric"><div class="l">Readings today</div><div class="v" id="m-readings">{{ number_format($metrics['readings']) }}</div></div>
  </div>

  <div class="grid g2">
    <div class="card"><h2>Power (last 60 readings)</h2><canvas id="powerChart" height="150"></canvas></div>
    <div class="card"><h2>Energy per day (last 7 days, Wh)</h2><canvas id="dailyChart" height="150"></canvas></div>
  </div>

  <div class="grid g2">
    <div class="card">
      <h2>Authorized schedule</h2>
      <table>
        @foreach ($schedule as $s)
          <tr class="{{ $s['is_today'] ? 'today' : '' }}">
            <td>{{ $s['day'] }}{{ $s['is_today'] ? ' (today)' : '' }}</td>
            <td class="day-hours {{ $s['has_class'] ? 'has-class' : '' }}">{{ $s['hours'] }}</td>
          </tr>
        @endforeach
      </table>
      <div class="muted" style="margin-top:8px">A load that is on outside these hours raises an alert by email and Telegram.</div>
    </div>

    <div class="card">
      <h2>Sensors and wiring</h2>
      <div class="row"><span>HC-SR501 PIR (motion)</span><b>{{ $gpio($room->pir_gpio) }}</b></div>
      <div class="row"><span>IR proximity (door)</span><b>{{ $gpio($room->ir_gpio) }}</b></div>
      <div class="row"><span>Relay input</span><b>{{ $gpio($room->light_gpio) }}</b></div>
      <div class="row"><span>INA219 (voltage, current)</span><b>{{ $room->ina219_address ?? 'Not reported yet' }}</b></div>
      <div class="row"><span>Controller</span><b>Raspberry Pi 4</b></div>
      <div class="muted" style="margin-top:8px">Pins last reported by the sensor service: {{ $room->hardware_synced_at?->diffForHumans() ?? 'never' }}</div>
    </div>
  </div>

  <div class="grid g2">
    <div class="card">
      <h2>Recent activity</h2>
      @forelse ($events as $e)
        <ul class="list" style="margin:0">
          <li><span class="t">{{ $e['time']->format('M j, g:i:s A') }}</span>
              <span><span class="dot {{ $e['on'] ? 'on' : 'off' }}"></span>{{ $e['label'] }}</span></li>
        </ul>
      @empty
        <div class="empty">No activity recorded yet.</div>
      @endforelse
    </div>

    <div class="card">
      <h2>Recent alerts</h2>
      @forelse ($alerts as $a)
        <ul class="list" style="margin:0">
          <li>
            <span class="t">{{ $a->created_at->format('M j, g:i A') }}</span>
            <span>
              <span class="badge {{ $a->resolved_at ? 'on' : 'bad' }}">{{ $a->resolved_at ? 'Resolved' : 'Active' }}</span>
              <div style="margin-top:4px">{{ \Illuminate\Support\Str::of($a->message)->before("\n") }}</div>
              <div class="muted">
                {{ $a->emailed_at ? 'Notification sent' : 'No notification sent' }}
                @if ($a->resolved_at) &middot; resolved {{ $a->resolved_at->diffForHumans() }} @endif
              </div>
            </span>
          </li>
        </ul>
      @empty
        <div class="empty">No alerts for this room.</div>
      @endforelse
    </div>
  </div>
</div>

<script>
const roomId = {{ $room->id }};
const fmtMin = m => m >= 60 ? Math.floor(m / 60) + ' h ' + Math.round(m % 60) + ' min' : Math.round(m) + ' min';

function setBadge(id, cls, text) {
  const el = document.getElementById(id);
  const changed = el.textContent !== text;
  el.className = 'badge ' + cls;
  el.textContent = text;
  if (changed && !REDUCE) { void el.offsetWidth; el.classList.add('pop'); }
}

// numbers flash gold briefly when a live value actually changes
function setVal(id, text) {
  const el = document.getElementById(id);
  if (el.textContent === text) return;
  el.textContent = text;
  if (REDUCE) return;
  el.classList.remove('pulse-update');
  void el.offsetWidth;
  el.classList.add('pulse-update');
}

const REDUCE = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
if (window.Chart) {
  Chart.defaults.font.family = "ui-monospace, 'JetBrains Mono', Menlo, Consolas, monospace";
  Chart.defaults.font.size = 11;
  Chart.defaults.color = '#9bb5a4';
  Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';
}

const powerChart = new Chart(document.getElementById('powerChart'), {
  type: 'line',
  data: {
    labels: @json($history->pluck('t')),
    datasets: [{ label: 'Power (mW)', data: @json($history->pluck('p')), borderColor: '#35d07a', backgroundColor: 'rgba(0, 145, 61, 0.18)', fill: true, borderWidth: 2, tension: 0.3, pointRadius: 0 }],
  },
  options: {
    animation: REDUCE ? false : { duration: 800, easing: 'easeOutQuart' },
    plugins: { legend: { labels: { color: '#ffffff' } } },
    scales: { x: { ticks: { color: '#9bb5a4', maxTicksLimit: 6 } }, y: { ticks: { color: '#9bb5a4' }, beginAtZero: true } },
  },
});

new Chart(document.getElementById('dailyChart'), {
  type: 'bar',
  data: {
    labels: @json(collect($daily)->pluck('label')),
    datasets: [{ label: 'Energy (Wh)', data: @json(collect($daily)->pluck('wh')), backgroundColor: '#ffde00', borderRadius: 3 }],
  },
  options: {
    animation: REDUCE ? false : { duration: 800, easing: 'easeOutQuart' },
    plugins: { legend: { labels: { color: '#ffffff' } } },
    scales: { x: { ticks: { color: '#9bb5a4' } }, y: { ticks: { color: '#9bb5a4' }, beginAtZero: true } },
  },
});

async function refresh() {
  try {
    const res = await fetch('/rooms/' + roomId + '/data');
    if (!res.ok) return;
    const d = await res.json();
    const s = d.status, m = d.metrics;

    setBadge('st-online', s.online ? 'on' : (s.has_data ? 'bad' : 'off'), s.online ? 'Online' : (s.has_data ? 'Offline' : 'No data yet'));
    setBadge('st-occupied', s.occupied ? 'on' : 'off', s.occupied ? 'Occupied' : 'Vacant');
    setBadge('st-door', s.door_open ? 'warn' : 'off', s.door_open ? 'Open' : 'Closed');
    setBadge('st-load', s.load_detected ? 'on' : 'off', s.load_detected ? 'Detected' : 'Stable');
    setBadge('st-schedule', d.in_schedule ? 'on' : 'off', d.in_schedule ? 'Yes' : 'No');

    setVal('v-voltage', s.voltage.toFixed(2) + ' V');
    setVal('v-current', s.current_ma.toFixed(1) + ' mA');
    setVal('v-power', s.power_mw.toFixed(1) + ' mW');
    document.getElementById('v-updated').textContent = s.updated;

    setVal('m-energy', m.energy_wh.toFixed(2) + ' Wh');
    setVal('m-peak', m.peak_mw.toFixed(1) + ' mW');
    setVal('m-load', fmtMin(m.load_min));
    setVal('m-occupied', fmtMin(m.occupied_min));
    setVal('m-readings', m.readings.toLocaleString());

    document.getElementById('alert-banner').style.display = d.open_alert ? '' : 'none';

    powerChart.data.labels = d.history.map(h => h.t);
    powerChart.data.datasets[0].data = d.history.map(h => h.p);
    powerChart.update('none');
  } catch (e) { console.error(e); }
}
setInterval(refresh, 3000);
</script>
</body>
</html>
