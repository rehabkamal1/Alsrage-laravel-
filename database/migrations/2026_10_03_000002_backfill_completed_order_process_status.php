<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereNull('order_status')
            ->whereIn('status', ['completed', 'مكتمل'])
            ->update(['order_status' => 'completed']);
    }

    public function down(): void
    {
        // Preserve process statuses; the column migration removes the field if rolled back.
    }
};
