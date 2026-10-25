<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Create account</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="auth page-register">
<div class="box">
  <h1 class="brand" data-tilt><img src="{{ asset('img/enerbridge-logo.png') }}" alt="EnerBridge" width="300" height="178"></h1>
  <div class="sub">Create an account to use the classroom energy dashboard</div>

  @if ($errors->any())
    <div class="err">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="/register">
    @csrf

    <label>I am registering as</label>
    <div class="seg">
      <input type="radio" id="type_teacher" name="account_type" value="teacher" {{ old('account_type', 'teacher') === 'teacher' ? 'checked' : '' }}>
      <label for="type_teacher">Teacher / Professor</label>
      <input type="radio" id="type_admin" name="account_type" value="administrator" {{ old('account_type') === 'administrator' ? 'checked' : '' }}>
      <label for="type_admin">Administrator</label>
    </div>

    <label for="first_name">First name</label>
    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus autocomplete="given-name">

    <label for="middle_name">Middle name <span class="muted">(optional)</span></label>
    <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" autocomplete="additional-name">

    <label for="last_name">Last name</label>
    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">

    <label for="employee_id">Employee ID</label>
    <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id') }}" required>

    <label for="phone_number">Mobile number (Philippines)</label>
    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required inputmode="tel" placeholder="0917 123 4567" autocomplete="tel">
    <div class="hint">Format: 09XX XXX XXXX or +63 9XX XXX XXXX</div>

    <div id="dept-field">
      <label for="department">Department / College</label>
      <input type="text" id="department" name="department" value="{{ old('department') }}">
    </div>

    <div id="code-field" class="hidden">
      <label for="admin_code">Administrator access code</label>
      <input type="password" id="admin_code" name="admin_code" autocomplete="off">
      <div class="hint">Ask the system owner for this code.</div>
    </div>

    <label for="password">Password (at least 8 characters)</label>
    <input type="password" id="password" name="password" required autocomplete="new-password">

    <label for="password_confirmation">Confirm password</label>
    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">

    <button type="submit">Sign up</button>
  </form>

  <div class="foot">Already have an account? <a href="/login">Log in</a></div>
</div>

<script>
  function updateFields() {
    const admin = document.getElementById('type_admin').checked;
    document.getElementById('dept-field').classList.toggle('hidden', admin);
    document.getElementById('code-field').classList.toggle('hidden', !admin);
  }
  document.getElementById('type_teacher').addEventListener('change', updateFields);
  document.getElementById('type_admin').addEventListener('change', updateFields);
  updateFields();
</script>

@include('auth._popup', ['popupTitle' => 'Sign-up problem'])
<script src="{{ asset('js/enerbridge.js') }}" defer></script>
</body>
</html>
