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
            $table->string('first_name', 100)->default('')->after('id');
            $table->string('middle_name', 100)->nullable()->after('first_name');
            $table->string('last_name', 100)->default('')->after('middle_name');
        });

        // Split the existing full names: first word = first name, last word = last name,
        // anything in between = middle name.
        DB::table('users')->get(['id', 'name'])->each(function ($user) {
            $parts = preg_split('/\s+/', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY);
            $count = count($parts);

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? '',
                'middle_name' => $count > 2 ? implode(' ', array_slice($parts, 1, $count - 2)) : null,
                'last_name' => $count > 1 ? $parts[$count - 1] : '',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->default('')->after('id');
        });

        DB::table('users')->get(['id', 'first_name', 'middle_name', 'last_name'])->each(function ($user) {
            $full = trim(implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name])));
            DB::table('users')->where('id', $user->id)->update(['name' => $full]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};
