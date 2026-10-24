<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Classrooms</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="app page-rooms">
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
        <div class="row"><span>Electric load</span><span class="badge {{ $s['load_detected'] ? 'on' : 'off' }}">{{ $s['load_detected'] ? 'Detected' : 'Stable' }}</span></div>
        <div class="row"><span>Power now</span><b>{{ number_format($s['power_mw'], 1) }} mW</b></div>
        <div class="row"><span>Energy today</span><b>{{ number_format($c['metrics']['energy_wh'], 2) }} Wh</b></div>
        <a class="btn" href="/rooms/{{ $c['room']->id }}">View details &rarr;</a>
      </div>
    @endforeach
  </div>
</div>
</body>
</html>
