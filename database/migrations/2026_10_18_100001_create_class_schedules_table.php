<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // the teacher who teaches the class
            $table->unsignedTinyInteger('day_of_week');                       // 0 = Sunday ... 6 = Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->string('subject', 120);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->index(['room_id', 'day_of_week']);
            $table->index(['user_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
