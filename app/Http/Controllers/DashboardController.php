<?php

namespace App\Http\Controllers;

use App\Models\Room;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
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
                'fan_on' => $l?->fan_on ?? false,
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
