<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | My profile</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="app page-profile">
<div class="wrap">
  <div class="top">
    <div class="title"><a class="brand-mark" href="/" title="EnerBridge home"><img src="{{ asset('img/enerbridge-mark.png') }}" alt="EnerBridge" width="73" height="48"></a><h1>My profile</h1></div>
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
