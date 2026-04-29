<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'client_name', 'client_phone', 'id_and_visa', 'support_contract_number', 'remaining_balance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('client_type', ['individual', 'office'])->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('id_and_visa')->nullable();
            $table->string('support_contract_number')->nullable();
            $table->decimal('remaining_balance', 12, 2)->default(0);
        });
    }
};
