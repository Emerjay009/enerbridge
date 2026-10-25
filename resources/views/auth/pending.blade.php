<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Waiting for approval</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="auth page-pending">
<div class="box">
  <div class="brand" data-tilt><img src="{{ asset('img/enerbridge-logo.png') }}" alt="EnerBridge" width="300" height="178"></div>
  <div class="icon">&#9203;</div>
  <h1>Thank you for registering</h1>
  <p>Your Teacher / Professor account is waiting for administrator approval. We check the employee ID and department you entered.</p>
  <span class="time">Approval is usually completed within 1 day</span>
  <p>You can log in once an administrator has approved your account.</p>
  <a class="btn" href="/login">Go to log in</a>
</div>
<script src="{{ asset('js/enerbridge.js') }}" defer></script>
</body>
</html>
