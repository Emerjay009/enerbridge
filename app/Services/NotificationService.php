<?php

namespace App\Services;

use App\Models\Role;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /** Email addresses of all approved administrators. */
    public function adminEmails(): array
    {
        $adminId = Role::where('slug', 'administrator')->value('id');

        return User::where('role_id', $adminId)
            ->where('approval_status', 'approved')
            ->pluck('email')
            ->all();
    }

    /** Sends an email. Returns null on success, or an error message. */
    public function email(string $subject, string $body, ?array $to = null): ?string
    {
        $to ??= $this->adminEmails();

        if (empty($to)) {
            return 'No administrator email addresses found.';
        }

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            return null;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    /** Telegram chat ids of administrators who connected Telegram, plus the optional shared chat in .env. */
    public function telegramRecipients(): array
    {
        $adminId = Role::where('slug', 'administrator')->value('id');

        $ids = User::where('role_id', $adminId)
            ->where('approval_status', 'approved')
            ->whereNotNull('telegram_chat_id')
            ->pluck('telegram_chat_id')
            ->all();

        if ($shared = config('services.telegram.chat_id')) {
            $ids[] = $shared;
        }

        return array_values(array_unique(array_map('strval', $ids)));
    }

    /** Sends a Telegram message to every recipient. Returns null on success, or the error messages. */
    public function telegram(string $text, ?array $chatIds = null): ?string
    {
        $token = config('services.telegram.token');

        if (! $token) {
            return 'Telegram is not configured (bot token is missing in .env).';
        }

        $chatIds ??= $this->telegramRecipients();

        if (empty($chatIds)) {
            return 'No Telegram recipients: nobody has connected Telegram yet and TELEGRAM_CHAT_ID is empty.';
        }

        $errors = [];

        foreach ($chatIds as $chatId) {
            try {
                $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]);

                if (! $response->successful()) {
                    $errors[] = "chat {$chatId}: ".$response->body();
                }
            } catch (\Throwable $e) {
                $errors[] = "chat {$chatId}: ".$e->getMessage();
            }
        }

        return $errors ? implode(' | ', $errors) : null;
    }

    /** Sends to administrators by email and Telegram. Failures are logged, never thrown. */
    public function notifyAdmins(string $subject, string $body): array
    {
        $result = [
            'email' => $this->email($subject, $body),
            'telegram' => $this->telegram($subject."\n\n".$body),
        ];

        foreach ($result as $channel => $error) {
            if ($error !== null) {
                SystemLog::create([
                    'level' => 'warning',
                    'source' => 'notifications',
                    'message' => "Could not send {$channel} notification: {$error}",
                    'context' => ['subject' => $subject],
                ]);
            }
        }

        return $result;
    }
}
