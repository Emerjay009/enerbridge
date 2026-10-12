<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles');
            $table->string('employee_id', 30)->nullable()->unique()->after('role_id');
            $table->string('department', 100)->nullable()->after('employee_id');
        });

        $adminId = DB::table('roles')->where('slug', 'administrator')->value('id');
        $teacherId = DB::table('roles')->where('slug', 'teacher')->value('id');

        // If the earlier text "role" column exists, carry its values over, then remove it.
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'admin')->update(['role_id' => $adminId]);
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        // Everyone else becomes a teacher/professor by default.
        DB::table('users')->whereNull('role_id')->update(['role_id' => $teacherId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['employee_id', 'department']);
        });
    }
};
