<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Create account</title>
<style>
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; box-sizing:border-box; }
  .box { background:#1e293b; border-radius:12px; padding:28px; width:100%; max-width:420px; }
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
  .hint { font-size:12px; color:#94a3b8; margin-top:4px; }
  .hidden { display:none; }
</style>
</head>
<body>
<div class="box">
  <h1>EnerBridge</h1>
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

    <label for="middle_name">Middle name <span style="color:#94a3b8">(optional)</span></label>
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
</body>
</html>
