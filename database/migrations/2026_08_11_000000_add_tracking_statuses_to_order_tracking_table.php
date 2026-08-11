<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->string('authentication_status')->nullable()->after('transfer_status');
            $table->string('authorization_status')->nullable()->after('authentication_status');
            $table->string('delegate_phone')->nullable()->after('sponsor_number');
        });
    }

    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropColumn(['authentication_status', 'authorization_status', 'delegate_phone']);
        });
    }
};
