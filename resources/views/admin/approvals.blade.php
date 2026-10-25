<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnerBridge | Account approvals</title>
<link rel="stylesheet" href="{{ asset('css/enerbridge.css') }}?v={{ @filemtime(public_path('css/enerbridge.css')) }}">
</head>
<body class="app page-approvals">
<div class="wrap">
  <div class="top">
    <div class="title"><a class="brand-mark" href="/" title="EnerBridge home"><img src="{{ asset('img/enerbridge-mark.png') }}" alt="EnerBridge" width="73" height="48"></a><h1>Account approvals</h1></div>
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
