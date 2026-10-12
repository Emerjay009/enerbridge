<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->unique()->after('email');          // +639XXXXXXXXX
            $table->string('telegram_chat_id', 30)->nullable()->unique()->after('phone_number');
            $table->string('telegram_link_token', 40)->nullable()->unique()->after('telegram_chat_id');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_link_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'telegram_chat_id', 'telegram_link_token', 'telegram_linked_at']);
        });
    }
};
