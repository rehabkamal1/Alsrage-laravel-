<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'orders_status_index');
            $table->index('created_at', 'orders_created_at_index');
            $table->index('employee_id', 'orders_employee_id_index');
            $table->index('client_id', 'orders_client_id_index');
            $table->index('saudi_office_id', 'orders_saudi_office_id_index');
            $table->index('external_office_id', 'orders_external_office_id_index');
            $table->index('visa_number', 'orders_visa_number_index');
            $table->index('service_type', 'orders_service_type_index');
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_created_at_index');
            $table->dropIndex('orders_employee_id_index');
            $table->dropIndex('orders_client_id_index');
            $table->dropIndex('orders_saudi_office_id_index');
            $table->dropIndex('orders_external_office_id_index');
            $table->dropIndex('orders_visa_number_index');
            $table->dropIndex('orders_service_type_index');
            $table->dropIndex('orders_status_created_at_index');
        });
    }
};
