<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="/favicon.ico">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Log in</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="auth page-login">
<div class="box">
  <h1>EnerBridge</h1>
  <div class="sub">Log in to the classroom energy dashboard</div>

  @if ($errors->any())
    <div class="err">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="/login">
    @csrf

    <label>Log in as</label>
    <div class="seg">
      <input type="radio" id="as_teacher" name="login_as" value="teacher" {{ old('login_as', 'teacher') === 'teacher' ? 'checked' : '' }}>
      <label for="as_teacher">Teacher / Professor</label>
      <input type="radio" id="as_admin" name="login_as" value="administrator" {{ old('login_as') === 'administrator' ? 'checked' : '' }}>
      <label for="as_admin">Administrator</label>
    </div>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">

    <div class="check"><input type="checkbox" id="remember" name="remember" value="1"><label for="remember" style="margin:0">Remember me</label></div>

    <button type="submit">Log in</button>
  </form>

  <div class="foot">No account yet? <a href="/register">Sign up</a></div>
  <div class="note">This is a Capstone project of Mr. Emerjay A. Jumamil &amp; Mr. Prince Calindatas of Bachelor of Science in Information Technology. <br> All Rights Reserved, 2026.</div>
</div>

@include('auth._popup', ['popupTitle' => 'Login failed'])
</body>
</html>
