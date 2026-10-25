<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Services\AlertService;

class DashboardController extends Controller
{
    public function index()
    {
        $isAdmin = Role::find(auth()->user()->role_id)?->slug === 'administrator';

        $pendingCount = 0;
        $hasOverdue = false;

        if ($isAdmin) {
            $pending = User::where('approval_status', 'pending');
            $pendingCount = (clone $pending)->count();
            $hasOverdue = (clone $pending)
                ->where('created_at', '<', now()->subHours(ApprovalController::TARGET_HOURS))
                ->exists();
        }

        return view('dashboard', compact('isAdmin', 'pendingCount', 'hasOverdue'));
    }

    public function data()
    {
        $rooms = Room::with('latestReading')->get()->map(function ($room) {
            $l = $room->latestReading;
            $history = $room->readings()->latest()->take(30)->get()->reverse()->values();

            return [
                'id' => $room->id,
                'name' => $room->name,
                'occupied' => $l?->occupied ?? false,
                'door_open' => $l?->door_open ?? false,
                'light_on' => $l?->light_on ?? false,
                'load_detected' => $l ? ($l->light_on || $l->power_mw >= AlertService::MIN_POWER_MW) : false,
                'voltage' => $l?->voltage ?? 0,
                'current_ma' => $l?->current_ma ?? 0,
                'power_mw' => $l?->power_mw ?? 0,
                'updated' => $l?->created_at?->diffForHumans() ?? 'no data yet',
                'history' => $history->map(fn ($h) => [
                    't' => $h->created_at->format('H:i:s'),
                    'p' => $h->power_mw,
                ]),
            ];
        });

        return response()->json($rooms);
    }
}
