<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="/favicon.ico">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Log in</title>
<style>
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; box-sizing:border-box; }
  .box { background:#1e293b; border-radius:12px; padding:28px; width:100%; max-width:400px; }
  h1 { margin:0 0 4px; font-size:24px; } .sub { color:#94a3b8; margin-bottom:18px; font-size:14px; }
  label { display:block; font-size:14px; margin:12px 0 4px; }
  input[type=text], input[type=email], input[type=password] { width:100%; padding:10px; border-radius:8px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; box-sizing:border-box; font-size:15px; }
  button { width:100%; margin-top:20px; padding:11px; border:0; border-radius:8px; background:#0284c7; color:#fff; font-size:15px; font-weight:bold; cursor:pointer; }
  button:hover { background:#0369a1; }
  .err { background:#7f1d1d; color:#fecaca; padding:8px 12px; border-radius:8px; margin-bottom:12px; font-size:14px; }
  .err div { margin:2px 0; }
  .foot { margin-top:16px; text-align:center; font-size:14px; color:#94a3b8; } a { color:#38bdf8; }
  .seg { display:flex; gap:8px; margin-top:4px; }
  .seg input { display:none; }
  .seg label { flex:1; text-align:center; padding:10px 6px; border:1px solid #334155; border-radius:8px; cursor:pointer; margin:0; font-size:14px; }
  .seg input:checked + label { background:#0284c7; border-color:#0284c7; font-weight:bold; }
  .check { display:flex; align-items:center; gap:8px; margin-top:12px; font-size:14px; } .check input { width:auto; }
</style>
</head>
<body>
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
</div>

@include('auth._popup', ['popupTitle' => 'Login failed'])
</body>
</html>
