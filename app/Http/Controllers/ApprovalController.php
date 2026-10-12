<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    // Approval requests should be handled within this many hours; older ones show as "Overdue".
    public const TARGET_HOURS = 24;

    private function teacherRoleId(): ?int
    {
        return Role::where('slug', 'teacher')->value('id');
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, ['pending', 'approved', 'rejected'], true), 404);

        $teacherId = $this->teacherRoleId();

        $users = User::where('role_id', $teacherId)
            ->where('approval_status', $status)
            ->orderBy('created_at')
            ->get();

        $counts = [];
        foreach (['pending', 'approved', 'rejected'] as $s) {
            $counts[$s] = User::where('role_id', $teacherId)->where('approval_status', $s)->count();
        }

        return view('admin.approvals', [
            'users' => $users,
            'status' => $status,
            'counts' => $counts,
            'targetHours' => self::TARGET_HOURS,
        ]);
    }

    public function approve(Request $request, User $user)
    {
        abort_unless($user->role_id === $this->teacherRoleId(), 404);

        $user->forceFill([
            'approval_status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ])->save();

        return back()->with('status', "{$user->first_name} {$user->last_name} was approved.");
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($user->role_id === $this->teacherRoleId(), 404);

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $user->forceFill([
            'approval_status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'rejection_reason' => $data['reason'] ?? null,
        ])->save();

        return back()->with('status', "{$user->first_name} {$user->last_name} was rejected or had access removed.");
    }
}
