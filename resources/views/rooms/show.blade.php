<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | {{ $room->name }}</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; padding:24px; }
  .wrap { max-width:1100px; margin:0 auto; }
  a { color:#38bdf8; text-decoration:none; } a:hover { text-decoration:underline; }
  .top { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:8px; }
  h1 { margin:0; font-size:28px; }
  .sub { color:#94a3b8; font-size:14px; margin-bottom:14px; }
  .tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
  .tabs a { padding:8px 16px; border-radius:999px; background:#1e293b; color:#e2e8f0; font-size:14px; }
  .tabs a:hover { text-decoration:none; background:#334155; }
  .tabs a.active { background:#0284c7; font-weight:bold; }
  .banner { background:#7f1d1d; color:#fecaca; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-weight:bold; }
  .grid { display:grid; gap:16px; margin-bottom:16px; }
  .g2 { grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); }
  .g5 { grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); }
  .card { background:#1e293b; border-radius:12px; padding:18px; }
  .card h2 { margin:0 0 12px; font-size:18px; }
  .row { display:flex; justify-content:space-between; gap:12px; padding:7px 0; border-bottom:1px solid #334155; font-size:15px; }
  .row:last-child { border-bottom:0; }
  .row span:first-child { color:#94a3b8; }
  .badge { padding:2px 10px; border-radius:999px; font-size:13px; font-weight:bold; white-space:nowrap; }
  .on { background:#16a34a; } .off { background:#475569; } .warn { background:#d97706; } .bad { background:#dc2626; }
  .metric { background:#1e293b; border-radius:12px; padding:16px; }
  .metric .v { font-size:26px; font-weight:bold; margin-top:4px; }
  .metric .l { color:#94a3b8; font-size:13px; }
  .muted { color:#94a3b8; font-size:13px; }
  table { width:100%; border-collapse:collapse; font-size:15px; }
  td { padding:7px 4px; border-bottom:1px solid #334155; }
  tr:last-child td { border-bottom:0; }
  tr.today td { background:#0f2a43; font-weight:bold; }
  ul.list { list-style:none; margin:0; padding:0; }
  ul.list li { display:flex; gap:12px; padding:7px 0; border-bottom:1px solid #334155; font-size:14px; }
  ul.list li:last-child { border-bottom:0; }
  ul.list .t { color:#94a3b8; min-width:150px; }
  .dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:8px; }
  .empty { color:#94a3b8; padding:8px 0; font-size:14px; }
</style>
</head>
<body>
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
    <h1>{{ $room->name }}</h1>
    <div><a href="/">&larr; Dashboard</a></div>
  </div>
  <div class="sub">{{ $room->description }}</div>

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
      <div class="row"><span>Lighting</span>
        <span id="st-light" class="badge {{ $status['light_on'] ? 'on' : 'off' }}">{{ $status['light_on'] ? 'ON' : 'OFF' }}</span></div>
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
      <div class="row"><span>Controlled load</span><b>Ceiling lighting</b></div>
      <div class="row"><span>Control rule</span><b>Light on when occupied</b></div>
      <div class="muted" style="margin-top:8px">The light turns off after the room has been vacant for a while, because the motion sensor only sees movement.</div>
    </div>
  </div>

  <div class="grid g5">
    <div class="metric"><div class="l">Energy today</div><div class="v" id="m-energy">{{ number_format($metrics['energy_wh'], 2) }} Wh</div></div>
    <div class="metric"><div class="l">Peak power today</div><div class="v" id="m-peak">{{ number_format($metrics['peak_mw'], 1) }} mW</div></div>
    <div class="metric"><div class="l">Light on today</div><div class="v" id="m-light">{{ $fmtMin($metrics['light_min']) }}</div></div>
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
            <td style="text-align:right; color:{{ $s['has_class'] ? '#e2e8f0' : '#94a3b8' }}">{{ $s['hours'] }}</td>
          </tr>
        @endforeach
      </table>
      <div class="muted" style="margin-top:8px">A load that is on outside these hours raises an alert by email and Telegram.</div>
    </div>

    <div class="card">
      <h2>Sensors and wiring</h2>
      <div class="row"><span>HC-SR501 PIR (motion)</span><b>{{ $gpio($room->pir_gpio) }}</b></div>
      <div class="row"><span>IR proximity (door)</span><b>{{ $gpio($room->ir_gpio) }}</b></div>
      <div class="row"><span>Lighting switch pin</span><b>{{ $gpio($room->light_gpio) }}</b></div>
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
              <span><span class="dot" style="background:{{ $e['on'] ? '#4ade80' : '#94a3b8' }}"></span>{{ $e['label'] }}</span></li>
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
  el.className = 'badge ' + cls;
  el.textContent = text;
}

const powerChart = new Chart(document.getElementById('powerChart'), {
  type: 'line',
  data: {
    labels: @json($history->pluck('t')),
    datasets: [{ label: 'Power (mW)', data: @json($history->pluck('p')), borderColor: '#38bdf8', tension: 0.3, pointRadius: 0 }],
  },
  options: {
    animation: false,
    plugins: { legend: { labels: { color: '#e2e8f0' } } },
    scales: { x: { ticks: { color: '#94a3b8', maxTicksLimit: 6 } }, y: { ticks: { color: '#94a3b8' }, beginAtZero: true } },
  },
});

new Chart(document.getElementById('dailyChart'), {
  type: 'bar',
  data: {
    labels: @json(collect($daily)->pluck('label')),
    datasets: [{ label: 'Energy (Wh)', data: @json(collect($daily)->pluck('wh')), backgroundColor: '#facc15' }],
  },
  options: {
    animation: false,
    plugins: { legend: { labels: { color: '#e2e8f0' } } },
    scales: { x: { ticks: { color: '#94a3b8' } }, y: { ticks: { color: '#94a3b8' }, beginAtZero: true } },
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
    setBadge('st-light', s.light_on ? 'on' : 'off', s.light_on ? 'ON' : 'OFF');
    setBadge('st-schedule', d.in_schedule ? 'on' : 'off', d.in_schedule ? 'Yes' : 'No');

    document.getElementById('v-voltage').textContent = s.voltage.toFixed(2) + ' V';
    document.getElementById('v-current').textContent = s.current_ma.toFixed(1) + ' mA';
    document.getElementById('v-power').textContent = s.power_mw.toFixed(1) + ' mW';
    document.getElementById('v-updated').textContent = s.updated;

    document.getElementById('m-energy').textContent = m.energy_wh.toFixed(2) + ' Wh';
    document.getElementById('m-peak').textContent = m.peak_mw.toFixed(1) + ' mW';
    document.getElementById('m-light').textContent = fmtMin(m.light_min);
    document.getElementById('m-occupied').textContent = fmtMin(m.occupied_min);
    document.getElementById('m-readings').textContent = m.readings.toLocaleString();

    document.getElementById('alert-banner').style.display = d.open_alert ? '' : 'none';

    powerChart.data.labels = d.history.map(h => h.t);
    powerChart.data.datasets[0].data = d.history.map(h => h.p);
    powerChart.update();
  } catch (e) { console.error(e); }
}
setInterval(refresh, 3000);
</script>
</body>
</html>
