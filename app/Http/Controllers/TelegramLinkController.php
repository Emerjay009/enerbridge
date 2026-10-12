<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TelegramLinkController extends Controller
{
    /** Step 1: create a one-time code; the profile page shows a link that carries it. */
    public function generate(Request $request)
    {
        if (! config('services.telegram.token') || ! config('services.telegram.username')) {
            return back()->withErrors(['telegram' => 'Telegram is not set up yet. Ask the system owner to add the bot token and bot username to the .env file.']);
        }

        $request->user()->forceFill(['telegram_link_token' => Str::random(24)])->save();

        return back()->with('status', 'Now open the link below and press Start in Telegram.');
    }

    /** Step 2: after the person pressed Start, find their /start message and save their chat id. */
    public function check(Request $request)
    {
        $token = config('services.telegram.token');

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates", ['timeout' => 0]);
        } catch (\Throwable $e) {
            return back()->withErrors(['telegram' => 'Could not reach Telegram: '.$e->getMessage()]);
        }

        if (! $response->successful()) {
            return back()->withErrors(['telegram' => 'Telegram error: '.$response->body()]);
        }

        $updates = $response->json('result', []);
        $maxId = null;
        $linkedMe = false;

        foreach ($updates as $update) {
            $maxId = max($maxId ?? 0, $update['update_id']);

            $message = $update['message'] ?? null;
            $text = $message['text'] ?? '';
            $chatId = $message['chat']['id'] ?? null;
            $isPrivate = ($message['chat']['type'] ?? '') === 'private';

            if (! $chatId || ! $isPrivate || ! preg_match('/^\/start\s+(\S+)/', $text, $match)) {
                continue;
            }

            $owner = User::where('telegram_link_token', $match[1])->first();

            if (! $owner) {
                continue;
            }

            // One Telegram chat belongs to one account
            User::where('telegram_chat_id', (string) $chatId)->where('id', '!=', $owner->id)
                ->update(['telegram_chat_id' => null, 'telegram_linked_at' => null]);

            $owner->forceFill([
                'telegram_chat_id' => (string) $chatId,
                'telegram_link_token' => null,
                'telegram_linked_at' => now(),
            ])->save();

            Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => 'EnerBridge: your Telegram is now connected to your account.',
            ]);

            if ($owner->is($request->user())) {
                $linkedMe = true;
            }
        }

        // Mark the messages as read so they are not returned again
        if ($maxId !== null) {
            Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getUpdates", ['offset' => $maxId + 1, 'timeout' => 0]);
        }

        if ($linkedMe) {
            return back()->with('status', 'Telegram connected. Test messages and alerts will now reach you.');
        }

        return back()->withErrors(['telegram' => 'We did not find your Start message yet. Open the link, press Start in Telegram, then check again.']);
    }

    public function unlink(Request $request)
    {
        $request->user()->forceFill([
            'telegram_chat_id' => null,
            'telegram_link_token' => null,
            'telegram_linked_at' => null,
        ])->save();

        return back()->with('status', 'Telegram was disconnected from your account.');
    }
}
