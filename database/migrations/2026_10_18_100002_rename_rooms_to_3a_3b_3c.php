<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $names = [
        'Classroom 1' => 'Room 3A',
        'Classroom 2' => 'Room 3B',
        'Classroom 3' => 'Room 3C',
    ];

    public function up(): void
    {
        foreach ($this->names as $old => $new) {
            DB::table('rooms')->where('name', $old)->update(['name' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->names as $old => $new) {
            DB::table('rooms')->where('name', $new)->update(['name' => $old]);
        }
    }
};
