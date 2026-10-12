<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'enerbridge:test-notify {--to= : Email address for the test (default: all administrators)}';

    protected $description = 'Send a test email and a test Telegram message';

    public function handle(NotificationService $notify): int
    {
        $stamp = now()->format('Y-m-d H:i:s');
        $to = $this->option('to') ? [$this->option('to')] : null;

        $this->info('Sending test email...');
        $emailError = $notify->email('EnerBridge test email', "This is a test email from EnerBridge.\nSent at {$stamp}.", $to);
        $this->line($emailError ? "  FAILED: {$emailError}" : '  Email sent.');

        $this->info('Sending test Telegram message...');
        $telegramError = $notify->telegram("EnerBridge test message.\nSent at {$stamp}.");
        $this->line($telegramError ? "  FAILED: {$telegramError}" : '  Telegram message sent.');

        return ($emailError || $telegramError) ? self::FAILURE : self::SUCCESS;
    }
}
