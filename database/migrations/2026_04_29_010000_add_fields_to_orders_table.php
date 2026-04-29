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
            $table->enum('client_type', ['individual', 'office'])->after('client_id');
            $table->string('client_name')->after('client_type');
            $table->string('client_phone')->unique()->after('client_name');
            $table->string('id_and_visa')->nullable()->after('client_phone'); // combines id and visa numbers
            $table->string('support_contract_number')->nullable()->after('id_and_visa');
            $table->decimal('remaining_balance', 12, 2)->default(0)->after('price_difference');
            // drop obsolete columns
            $table->dropColumn(['authentication_contract_number', 'external_agent_number', 'contract_date', 'passport_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'client_type',
                'client_name',
                'client_phone',
                'id_and_visa',
                'support_contract_number',
                'remaining_balance',
            ]);
            // re-add dropped columns (basic types, may need adjustment)
            $table->string('authentication_contract_number')->nullable();
            $table->string('external_agent_number')->nullable();
            $table->date('contract_date')->nullable();
            $table->date('passport_date')->nullable();
        });
    }
};
