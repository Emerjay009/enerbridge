<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->boolean('occupied')->default(false);
            $table->boolean('door_open')->default(false);
            $table->boolean('light_on')->default(false);
            $table->decimal('voltage', 6, 2)->default(0);      // volts
            $table->decimal('current_ma', 8, 2)->default(0);   // milliamps
            $table->decimal('power_mw', 10, 2)->default(0);    // milliwatts
            $table->timestamps();
            $table->index(['room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
