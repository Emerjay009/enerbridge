<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();      // administrator, teacher
            $table->string('label', 60);
            $table->string('description')->nullable();
            $table->json('privileges')->nullable();
            $table->json('limitations')->nullable();
            $table->timestamps();
        });

        // Basic rows so the users migration can link to them.
        // RoleSeeder fills in the full descriptions, privileges and limitations.
        DB::table('roles')->insert([
            ['slug' => 'administrator', 'label' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'teacher', 'label' => 'Teacher / Professor', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
