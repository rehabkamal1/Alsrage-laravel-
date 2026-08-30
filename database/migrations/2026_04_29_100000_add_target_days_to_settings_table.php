<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'target_days')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->integer('target_days')->default(60)->after('sort_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'target_days')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('target_days');
            });
        }
    }
};
