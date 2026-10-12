<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomHardwareController extends Controller
{
    // The Pi sensor service calls this once at startup to report its GPIO pins and INA219 addresses.
    public function sync(Request $request)
    {
        $key = config('services.enerbridge_key');
        abort_if(! $key || ! hash_equals($key, (string) $request->header('X-API-Key')), 401);

        $data = $request->validate([
            'rooms' => 'required|array|min:1',
            'rooms.*.room_id' => 'required|integer|exists:rooms,id',
            'rooms.*.pir_gpio' => 'nullable|integer|between:0,40',
            'rooms.*.ir_gpio' => 'nullable|integer|between:0,40',
            'rooms.*.light_gpio' => 'nullable|integer|between:0,40',
            'rooms.*.ina219_address' => 'nullable|string|max:6',
        ]);

        foreach ($data['rooms'] as $r) {
            Room::whereKey($r['room_id'])->update([
                'pir_gpio' => $r['pir_gpio'] ?? null,
                'ir_gpio' => $r['ir_gpio'] ?? null,
                'light_gpio' => $r['light_gpio'] ?? null,
                'ina219_address' => $r['ina219_address'] ?? null,
                'hardware_synced_at' => now(),
            ]);
        }

        return response()->json(['ok' => true, 'updated' => count($data['rooms'])]);
    }
}
