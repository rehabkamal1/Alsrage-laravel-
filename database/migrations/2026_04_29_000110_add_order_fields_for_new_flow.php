<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('id_number')->nullable()->after('visa_number');
            $table->string('sponsor_number')->nullable()->after('id_number');
            $table->string('passport_number')->nullable()->after('sponsor_number');
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['id_number', 'sponsor_number', 'passport_number', 'notes']);
        });
    }
};
