<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | My profile</title>
<link rel="icon" type="image/png" href="/favicon.ico">
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
</body>
</html>
