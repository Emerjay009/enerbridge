<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Classrooms</title>
<style>
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; padding:24px; }
  .wrap { max-width:1100px; margin:0 auto; }
  a { color:#38bdf8; text-decoration:none; } a:hover { text-decoration:underline; }
  .top { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:6px; }
  h1 { margin:0; font-size:28px; } .sub { color:#94a3b8; font-size:14px; margin-bottom:18px; }
  .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:16px; }
  .card { background:#1e293b; border-radius:12px; padding:18px; }
  .card h2 { margin:0 0 4px; font-size:20px; } .card h2 a { color:#e2e8f0; }
  .desc { color:#94a3b8; font-size:13px; margin-bottom:10px; min-height:54px; }
  .row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #334155; font-size:15px; }
  .row span:first-child { color:#94a3b8; }
  .badge { padding:2px 10px; border-radius:999px; font-size:13px; font-weight:bold; }
  .on { background:#16a34a; } .off { background:#475569; } .bad { background:#dc2626; } .warn { background:#d97706; }
  .btn { display:block; text-align:center; margin-top:14px; padding:10px; border-radius:8px; background:#0284c7; color:#fff; font-weight:bold; }
  .btn:hover { background:#0369a1; text-decoration:none; }
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h1>Classrooms</h1>
    <div><a href="/">&larr; Dashboard</a></div>
  </div>
  <div class="sub">Three simulated classrooms, each 12 &times; 6 inches. Open a classroom for its full details.</div>

  <div class="grid">
    @foreach ($cards as $c)
      @php($s = $c['status'])
      <div class="card">
        <h2><a href="/rooms/{{ $c['room']->id }}">{{ $c['room']->name }}</a></h2>
        <div class="desc">{{ $c['room']->description }}</div>
        @if ($c['openAlert'])
          <div class="row"><span>Alert</span><span class="badge bad">Active</span></div>
        @endif
        <div class="row"><span>Sensors</span>
          <span class="badge {{ $s['online'] ? 'on' : ($s['has_data'] ? 'bad' : 'off') }}">{{ $s['online'] ? 'Online' : ($s['has_data'] ? 'Offline' : 'No data yet') }}</span></div>
        <div class="row"><span>Occupancy</span><span class="badge {{ $s['occupied'] ? 'on' : 'off' }}">{{ $s['occupied'] ? 'Occupied' : 'Vacant' }}</span></div>
        <div class="row"><span>Lighting</span><span class="badge {{ $s['light_on'] ? 'on' : 'off' }}">{{ $s['light_on'] ? 'ON' : 'OFF' }}</span></div>
        <div class="row"><span>Power now</span><b>{{ number_format($s['power_mw'], 1) }} mW</b></div>
        <div class="row"><span>Energy today</span><b>{{ number_format($c['metrics']['energy_wh'], 2) }} Wh</b></div>
        <a class="btn" href="/rooms/{{ $c['room']->id }}">View details &rarr;</a>
      </div>
    @endforeach
  </div>
</div>
</body>
</html>
