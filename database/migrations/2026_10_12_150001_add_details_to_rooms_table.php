<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->decimal('width_in', 5, 1)->default(12)->after('description');
            $table->decimal('depth_in', 5, 1)->default(6)->after('width_in');
            // Filled in automatically by the Pi sensor service (config.py)
            $table->unsignedTinyInteger('pir_gpio')->nullable()->after('depth_in');
            $table->unsignedTinyInteger('ir_gpio')->nullable()->after('pir_gpio');
            $table->unsignedTinyInteger('light_gpio')->nullable()->after('ir_gpio');
            $table->string('ina219_address', 6)->nullable()->after('light_gpio');
            $table->timestamp('hardware_synced_at')->nullable()->after('ina219_address');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'width_in', 'depth_in',
                'pir_gpio', 'ir_gpio', 'light_gpio', 'ina219_address', 'hardware_synced_at',
            ]);
        });
    }
};
