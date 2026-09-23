<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('group', 'arrival_destination')
            ->where('key', 'cairo')
            ->where('label', 'القاهرة')
            ->where('nationality_key', 'مصر')
            ->delete();
    }

    public function down(): void
    {
        // Arrival destinations are managed manually by administrators.
    }
};
