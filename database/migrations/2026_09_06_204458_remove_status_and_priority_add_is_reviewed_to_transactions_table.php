<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('priority_level');
            $table->boolean('is_reviewed')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('transfer_number');
            $table->string('priority_level')->default('medium')->after('status');
            $table->dropColumn('is_reviewed');
        });
    }
};