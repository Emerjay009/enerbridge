<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Existing accounts stay approved; new teacher sign-ups are set to "pending" by the code.
            $table->string('approval_status', 20)->default('approved')->after('department'); // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->string('rejection_reason')->nullable()->after('reviewed_at');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['approval_status']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['approval_status', 'reviewed_at', 'rejection_reason']);
        });
    }
};
