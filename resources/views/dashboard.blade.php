<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; padding:24px; }
  h1 { margin:0 0 4px; } .sub { color:#94a3b8; margin-bottom:20px; }
  .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; }
  .card { background:#1e293b; border-radius:12px; padding:16px; }
  .card h2 { margin:0 0 10px; font-size:18px; }
  .row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #334155; }
  .badge { padding:2px 10px; border-radius:999px; font-size:13px; font-weight:bold; }
  .on { background:#16a34a; } .off { background:#475569; }
  .chart { background:#1e293b; border-radius:12px; padding:16px; margin-top:16px; }
  small { color:#94a3b8; }
</style>
</head>
<body>
<h1>EnerBridge</h1>
<div class="sub">Classroom energy monitoring and occupancy-based load control (prototype)</div>
<div class="grid" id="rooms"></div>
<div class="chart"><canvas id="powerChart" height="110"></canvas></div>

<script>
const colors = ['#38bdf8', '#facc15', '#f472b6'];
let chart;

function badge(on, yes, no) {
  return `<span class="badge ${on ? 'on' : 'off'}">${on ? yes : no}</span>`;
}

function render(rooms) {
  document.getElementById('rooms').innerHTML = rooms.map(r => `
    <div class="card">
      <h2>${r.name}</h2>
      <div class="row"><span>Occupancy</span>${badge(r.occupied, 'Occupied', 'Vacant')}</div>
      <div class="row"><span>Door</span>${badge(r.door_open, 'Open', 'Closed')}</div>
      <div class="row"><span>Lighting</span>${badge(r.light_on, 'ON', 'OFF')}</div>
      <div class="row"><span>Fan</span>${badge(r.fan_on, 'ON', 'OFF')}</div>
      <div class="row"><span>Voltage</span><b>${r.voltage.toFixed(2)} V</b></div>
      <div class="row"><span>Current</span><b>${r.current_ma.toFixed(1)} mA</b></div>
      <div class="row"><span>Power</span><b>${r.power_mw.toFixed(1)} mW</b></div>
      <small>Updated ${r.updated}</small>
    </div>`).join('');

  const labels = (rooms[0]?.history || []).map(h => h.t);
  const datasets = rooms.map((r, i) => ({
    label: r.name,
    data: r.history.map(h => h.p),
    borderColor: colors[i % colors.length],
    tension: 0.3,
  }));

  if (!chart) {
    chart = new Chart(document.getElementById('powerChart'), {
      type: 'line',
      data: { labels, datasets },
      options: {
        animation: false,
        plugins: { title: { display: true, text: 'Power consumption (mW)', color: '#e2e8f0' },
                   legend: { labels: { color: '#e2e8f0' } } },
        scales: { x: { ticks: { color: '#94a3b8' } }, y: { ticks: { color: '#94a3b8' } } },
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
