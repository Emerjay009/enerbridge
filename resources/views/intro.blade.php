<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="EnerBridge classroom energy monitoring system.">
<title>EnerBridge</title>
<link rel="icon" type="image/png" href="/favicon.ico">
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="page-intro">
<main class="intro-splash">
  <div class="intro-branding" aria-label="EnerBridge: Smart energy. Stronger tomorrow.">
    <img class="intro-logo" src="{{ asset('img/enerbridge-mark.png') }}" alt="EnerBridge logo" width="260" height="172">
    <h1>EnerBridge</h1>
    <p>Smart energy. Stronger tomorrow.</p>
  </div>
</main>
<script>
  window.setTimeout(() => window.location.replace(@json(route('login'))), 1000);
</script>
</body>
</html>
