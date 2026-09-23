<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('passport_number');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('nationality_key')->nullable()->after('target_days');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('birth_date');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('nationality_key');
        });
    }
};
