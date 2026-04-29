<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->string('passport_number')->nullable()->after('sponsor_number');
        });
    }

    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropColumn('passport_number');
        });
    }
};
