<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="app page-dashboard">
<div class="top">
  <div>
    <h1>EnerBridge</h1>
    <div class="sub">Classroom energy monitoring and occupancy-based load control (prototype)</div>
  </div>
  <div class="who">
    <a href="/rooms" class="profile-link">Classrooms</a>
    <a href="/schedules" class="profile-link">Schedules</a>
    @if ($isAdmin)
      <a href="/admin/approvals" class="admin-link">
        Approvals
        @if ($pendingCount > 0)
          <span class="approval-pill {{ $hasOverdue ? 'overdue' : '' }}">
            {{ $pendingCount }}
          </span>
        @endif
      </a>
    @endif
    <div class="account-actions">
      <a href="/profile" class="profile-link">{{ collect([auth()->user()->first_name, auth()->user()->middle_name, auth()->user()->last_name])->filter()->implode(' ') }}</a>
      <form method="POST" action="/logout" style="display:inline">@csrf<button type="submit">Log out</button></form>
    </div>
  </div>
</div>
<div class="grid" id="rooms"></div>
<div class="chart"><canvas id="powerChart" height="110"></canvas></div>

<script>
if (window.Chart) {
  Chart.defaults.font.family = "ui-monospace, 'JetBrains Mono', Menlo, Consolas, monospace";
  Chart.defaults.font.size = 11;
  Chart.defaults.color = '#9bb5a4';
  Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';
}
const colors = ['#ffde00', '#35d07a', '#ffffff'];
let chart;

function badge(on, yes, no) {
  return `<span class="badge ${on ? 'on' : 'off'}">${on ? yes : no}</span>`;
}

function render(rooms) {
  document.getElementById('rooms').innerHTML = rooms.map(r => `
    <div class="card">
      <h2><a href="/rooms/${r.id}" style="color:inherit;text-decoration:none">${r.name}</a></h2>
      <div class="row"><span>Occupancy</span>${badge(r.occupied, 'Occupied', 'Vacant')}</div>
      <div class="row"><span>Door</span>${badge(r.door_open, 'Open', 'Closed')}</div>
      <div class="row"><span>Electric load</span>${badge(r.load_detected, 'Detected', 'Stable')}</div>
      <div class="row"><span>Voltage</span><b>${r.voltage.toFixed(2)} V</b></div>
      <div class="row"><span>Current</span><b>${r.current_ma.toFixed(1)} mA</b></div>
      <div class="row"><span>Power</span><b>${r.power_mw.toFixed(1)} mW</b></div>
      <small>Updated ${r.updated}</small>
      <div><a href="/rooms/${r.id}" class="details-link">View classroom details &rarr;</a></div>
    </div>`).join('');

  const labels = (rooms[0]?.history || []).map(h => h.t);
  const datasets = rooms.map((r, i) => ({
    label: r.name,
    data: r.history.map(h => h.p),
    borderColor: colors[i % colors.length],
    borderWidth: 2,
    pointRadius: 0,
    pointHoverRadius: 4,
    tension: 0.3,
  }));

  if (!chart) {
    chart = new Chart(document.getElementById('powerChart'), {
      type: 'line',
      data: { labels, datasets },
      options: {
        animation: false,
        plugins: { title: { display: true, text: 'Power consumption (mW)', color: '#ffffff' },
                   legend: { labels: { color: '#ffffff' } } },
        scales: { x: { ticks: { color: '#9bb5a4' } }, y: { ticks: { color: '#9bb5a4' } } },
      },
    });
  } else {
    chart.data.labels = labels;
    chart.data.datasets = datasets;
    chart.update();
  }
}

async function load() {
  try {
    const res = await fetch('/data');
    render(await res.json());
  } catch (e) { console.error(e); }
}
load();
setInterval(load, 3000);
</script>
</body>
</html>
