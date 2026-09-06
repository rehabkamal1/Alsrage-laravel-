<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->foreignId('saudi_office_id')->nullable()->after('external_office_id')->constrained('saudi_offices');
        });
    }

    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropForeign(['saudi_office_id']);
            $table->dropColumn('saudi_office_id');
        });
    }
};