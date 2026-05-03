<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'client_type')) {
                $table->string('client_type')->default('individual')->after('id');
            }
            if (!Schema::hasColumn('clients', 'office_name')) {
                $table->string('office_name')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'office_name']);
        });
    }
};