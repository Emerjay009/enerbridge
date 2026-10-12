<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use App\Services\AlertService;
use Illuminate\Http\Request;

class ReadingController extends Controller
{
    public function store(Request $request)
    {
        // Only the Raspberry Pi (which knows the key) may post readings.
        $key = config('services.enerbridge_key');
        abort_if(! $key || ! hash_equals($key, (string) $request->header('X-API-Key')), 401);

        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'occupied' => 'required|boolean',
            'door_open' => 'required|boolean',
            'light_on' => 'required|boolean',
            'voltage' => 'required|numeric',
            'current_ma' => 'required|numeric',
        ]);

        $data['power_mw'] = $data['voltage'] * $data['current_ma']; // V x mA = mW

        $reading = SensorReading::create($data);

        // Raise an alert (email + Telegram) if a load is on outside the room's schedule
        app(AlertService::class)->check($reading);

        return response()->json(['ok' => true, 'id' => $reading->id], 201);
    }
}
