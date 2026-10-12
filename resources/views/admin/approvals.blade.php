<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Account approvals</title>
<style>
  body { font-family: Arial, sans-serif; background:#0f172a; color:#e2e8f0; margin:0; padding:24px; }
  .wrap { max-width:1000px; margin:0 auto; }
  .top { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:6px; }
  h1 { margin:0; font-size:26px; } .sub { color:#94a3b8; margin-bottom:16px; font-size:14px; }
  a { color:#38bdf8; text-decoration:none; } a:hover { text-decoration:underline; }
  .tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
  .tabs a { padding:8px 16px; border-radius:999px; background:#1e293b; color:#e2e8f0; font-size:14px; }
  .tabs a.active { background:#0284c7; font-weight:bold; }
  .flash { background:#14532d; color:#bbf7d0; padding:10px 14px; border-radius:8px; margin-bottom:16px; font-size:14px; }
  .card { background:#1e293b; border-radius:12px; padding:8px 16px; overflow-x:auto; }
  table { width:100%; border-collapse:collapse; font-size:14px; }
  th { text-align:left; color:#94a3b8; font-weight:normal; padding:10px 8px; border-bottom:1px solid #334155; }
  td { padding:10px 8px; border-bottom:1px solid #334155; vertical-align:middle; }
  tr:last-child td { border-bottom:0; }
  .badge { padding:2px 10px; border-radius:999px; font-size:12px; font-weight:bold; white-space:nowrap; }
  .ok { background:#16a34a; } .late { background:#dc2626; } .wait { background:#d97706; }
  .actions { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
  button { border:0; border-radius:8px; padding:6px 12px; cursor:pointer; font-size:13px; color:#fff; }
  .approve { background:#16a34a; } .approve:hover { background:#15803d; }
  .reject { background:#dc2626; } .reject:hover { background:#b91c1c; }
  .logout { background:#334155; color:#e2e8f0; }
  input[type=text] { padding:6px 8px; border-radius:8px; border:1px solid #334155; background:#0f172a; color:#e2e8f0; font-size:13px; width:150px; }
  .empty { padding:24px; text-align:center; color:#94a3b8; }
  .muted { color:#94a3b8; font-size:12px; }
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h1>Account approvals</h1>
    <div><a href="/">&larr; Back to dashboard</a></div>
  </div>
  <div class="sub">Review Teacher / Professor sign-ups. Requests should be handled within {{ $targetHours }} hours; older ones show as Overdue.</div>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif

  <div class="tabs">
    <a href="/admin/approvals?status=pending" class="{{ $status === 'pending' ? 'active' : '' }}">Pending ({{ $counts['pending'] }})</a>
    <a href="/admin/approvals?status=approved" class="{{ $status === 'approved' ? 'active' : '' }}">Approved ({{ $counts['approved'] }})</a>
    <a href="/admin/approvals?status=rejected" class="{{ $status === 'rejected' ? 'active' : '' }}">Rejected ({{ $counts['rejected'] }})</a>
  </div>

  <div class="card">
    @if ($users->isEmpty())
      <div class="empty">No {{ $status }} accounts.</div>
    @else
      <table>
        <thead>
          <tr>
            <th>Name</th><th>Email</th><th>Mobile</th><th>Employee ID</th><th>Department</th><th>Registered</th>
            @if ($status !== 'pending') <th>Reviewed</th> @endif
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($users as $u)
          @php
            $fullName = collect([$u->first_name, $u->middle_name, $u->last_name])->filter()->implode(' ');
            $overdue = $status === 'pending' && $u->created_at->lt(now()->subHours($targetHours));
          @endphp
          <tr>
            <td>{{ $fullName }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ \App\Support\PhoneNumber::display($u->phone_number) }}</td>
            <td>{{ $u->employee_id }}</td>
            <td>{{ $u->department }}</td>
            <td>
              {{ $u->created_at->format('M j, Y g:i A') }}<br>
              @if ($status === 'pending')
                <span class="badge {{ $overdue ? 'late' : 'wait' }}">{{ $overdue ? 'Overdue' : 'Waiting' }} &middot; {{ $u->created_at->diffForHumans(null, true) }}</span>
              @endif
            </td>
            @if ($status !== 'pending')
              <td>
                {{ $u->reviewed_at ? \Illuminate\Support\Carbon::parse($u->reviewed_at)->format('M j, Y g:i A') : '' }}
                @if ($u->rejection_reason)<div class="muted">{{ $u->rejection_reason }}</div>@endif
              </td>
            @endif
            <td>
              <div class="actions">
                @if ($status !== 'approved')
                  <form method="POST" action="/admin/approvals/{{ $u->id }}/approve">@csrf<button class="approve" type="submit">Approve</button></form>
                @endif
                @if ($status !== 'rejected')
                  <form method="POST" action="/admin/approvals/{{ $u->id }}/reject" class="actions">
                    @csrf
                    <input type="text" name="reason" maxlength="255" placeholder="{{ $status === 'approved' ? 'Reason (optional)' : 'Reason (optional)' }}">
                    <button class="reject" type="submit">{{ $status === 'approved' ? 'Remove access' : 'Reject' }}</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
</body>
</html>
