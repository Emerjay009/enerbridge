<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | My profile</title>
<style>
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; padding:24px; }
  .wrap { max-width:760px; margin:0 auto; }
  .top { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
  h1 { margin:0; font-size:26px; }
  a { color:#38bdf8; text-decoration:none; } a:hover { text-decoration:underline; }
  .card { background:#1e293b; border-radius:12px; padding:20px; margin-bottom:16px; }
  .who { display:flex; align-items:center; gap:16px; }
  .avatar { width:64px; height:64px; border-radius:50%; background:#0284c7; display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:bold; flex-shrink:0; }
  .name { font-size:20px; font-weight:bold; } .muted { color:#94a3b8; font-size:14px; }
  .badge { display:inline-block; padding:3px 12px; border-radius:999px; font-size:13px; font-weight:bold; margin-top:6px; }
  .role-administrator { background:#7c3aed; } .role-teacher { background:#0d9488; }
  h2 { margin:0 0 10px; font-size:18px; }
  ul { list-style:none; margin:0; padding:0; }
  li { display:flex; gap:10px; padding:8px 0; border-bottom:1px solid #334155; font-size:15px; }
  li:last-child { border-bottom:0; }
  .yes { color:#4ade80; font-weight:bold; } .no { color:#f87171; font-weight:bold; }
  .info { display:grid; grid-template-columns:140px 1fr; gap:6px 12px; margin-top:16px; font-size:15px; }
  .info div:nth-child(odd) { color:#94a3b8; }
  button { background:#334155; color:#e2e8f0; border:0; border-radius:8px; padding:8px 14px; cursor:pointer; font-size:14px; }
  button:hover { background:#475569; }
  .note { font-size:13px; color:#94a3b8; }
  .field { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
  input[type=text], input[type=email] { padding:9px; border-radius:8px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; font-size:15px; flex:1; min-width:180px; }
  .edit-profile .field { flex-direction:column; align-items:stretch; }
  .edit-profile .field button { align-self:flex-start; }
  .btn { display:inline-block; background:#0284c7; color:#fff; border-radius:8px; padding:9px 16px; font-size:14px; font-weight:bold; text-decoration:none; border:0; cursor:pointer; }
  .btn:hover { background:#0369a1; text-decoration:none; }
  .flash { background:#14532d; color:#bbf7d0; padding:10px 14px; border-radius:8px; margin-bottom:16px; font-size:14px; }
  .hint { font-size:13px; color:#94a3b8; margin-top:6px; }
  .ok { color:#4ade80; font-weight:bold; }
  .edit-profile summary { display:inline-block; list-style:none; background:#0284c7; color:#fff; border-radius:8px; padding:9px 16px; font-size:14px; font-weight:bold; cursor:pointer; }
  .edit-profile summary::-webkit-details-marker { display:none; }
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h1>My profile</h1>
    <div>
      <a href="/">&larr; Back to dashboard</a>
      <form method="POST" action="/logout" style="display:inline; margin-left:12px">@csrf<button type="submit">Log out</button></form>
    </div>
  </div>

  <div class="card">
    <div class="who">
      <div class="avatar">{{ strtoupper(mb_substr($user->first_name, 0, 1)) }}</div>
      <div>
        <div class="name">{{ collect([$user->first_name, $user->middle_name, $user->last_name])->filter()->implode(' ') }}</div>
        <div class="muted">{{ $user->email }}</div>
        <span class="badge role-{{ $role->slug }}">{{ $role->label }}</span>
      </div>
    </div>
    <div class="info">
      <div>Account type</div><div>{{ $role->label }}@if ($role->description): {{ $role->description }}@endif</div>
      <div>Employee ID</div><div>{{ $user->employee_id ?? 'Not set' }}</div>
      @if ($user->department)
        <div>Department</div><div>{{ $user->department }}</div>
      @endif
      <div>Member since</div><div>{{ $user->created_at?->format('F j, Y') }}</div>
    </div>
  </div>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif

  <div class="card">
    <h2>Mobile number</h2>
    @if ($user->phone_number)
      <details class="edit-profile" @if ($errors->any()) open @endif>
        <summary>Change number</summary>
        <form method="POST" action="/profile/phone">
          @csrf
          <div class="field">
            <input type="text" name="phone_number" value="{{ old('phone_number', \App\Support\PhoneNumber::display($user->phone_number)) }}" placeholder="0917 123 4567" inputmode="tel">
            <button class="btn" type="submit">Save</button>
          </div>
        </form>
      </details>
    @else
      <form method="POST" action="/profile/phone">
        @csrf
        <div class="field">
          <input type="text" name="phone_number" value="{{ old('phone_number', \App\Support\PhoneNumber::display($user->phone_number)) }}" placeholder="0917 123 4567" inputmode="tel">
          <button class="btn" type="submit">Save</button>
        </div>
      </form>
    @endif
    <div class="hint">No one can see your phone number.</div>
  </div>

  <div class="card">
    <h2>Telegram alerts</h2>
    @if ($user->telegram_chat_id)
      <p><span class="ok">&#10003; Connected</span> on {{ \Illuminate\Support\Carbon::parse($user->telegram_linked_at)->format('F j, Y g:i A') }}.</p>
      <div class="hint">Administrators receive EnerBridge alerts in this Telegram chat.</div>
      <form method="POST" action="/profile/telegram/unlink" style="margin-top:10px">@csrf<button type="submit">Disconnect Telegram</button></form>
    @elseif ($user->telegram_link_token && $botUsername)
      <p>1. Open Telegram and press <b>Start</b>:</p>
      <a class="btn" href="https://t.me/{{ $botUsername }}?start={{ $user->telegram_link_token }}" target="_blank" rel="noopener">Open Telegram</a>
      <p style="margin-top:16px">2. Come back here and check the connection:</p>
      <form method="POST" action="/profile/telegram/check">@csrf<button class="btn" type="submit">I pressed Start, check connection</button></form>
    @else
      <p>Connect your Telegram so the EnerBridge bot can message you.</p>
      <form method="POST" action="/profile/telegram/link">@csrf<button class="btn" type="submit">Connect Telegram</button></form>
      <div class="hint">The bot can only message people who press Start on it, so a phone number alone is not enough.</div>
    @endif
  </div>

  <div class="card">
    <h2>What you can do</h2>
    <ul>
      @foreach (($role->privileges ?? []) as $item)
        <li><span class="yes">&#10003;</span><span>{{ $item }}</span></li>
      @endforeach
    </ul>
  </div>

  <div class="card">
    <h2>What you cannot do</h2>
    <ul>
      @foreach (($role->limitations ?? []) as $item)
        <li><span class="no">&#10007;</span><span>{{ $item }}</span></li>
      @endforeach
    </ul>
  </div>

  <div class="note">Need a different account type? Ask an administrator to change your account.</div>
</div>
@include('auth._popup', ['popupTitle' => 'Could not save'])
</body>
</html>
