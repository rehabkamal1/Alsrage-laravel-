<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_offices', function (Blueprint $table) {
            $table->string('license_number')->nullable()->unique();
        });

        Schema::table('saudi_offices', function (Blueprint $table) {
            $table->string('license_number')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('external_offices', function (Blueprint $table) {
            $table->dropUnique('external_offices_license_number_unique');
            $table->dropColumn('license_number');
        });

        Schema::table('saudi_offices', function (Blueprint $table) {
            $table->dropUnique('saudi_offices_license_number_unique');
            $table->dropColumn('license_number');
        });
    }
};
