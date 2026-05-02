<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->string('visa_holder_name')->nullable();
            $table->foreignId('saudi_office_id')->nullable()->constrained('saudi_offices')->onDelete('set null');
            $table->foreignId('external_office_id')->nullable()->constrained('external_offices')->onDelete('set null');
            $table->string('visa_number')->nullable();
            $table->string('id_number')->nullable();
            $table->string('sponsor_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('musaned_contract_number')->nullable()->unique();
            $table->string('authentication_contract_number')->nullable();
            $table->string('external_agent_number')->nullable();
            $table->date('contract_date')->nullable();
            $table->date('passport_date')->nullable();
            $table->string('nationality')->nullable();
            $table->string('arrival_destination')->nullable();
            $table->string('profession')->nullable();
            $table->decimal('total_price', 12, 2)->nullable()->default(0);
            $table->decimal('musaned_paid', 12, 2)->nullable()->default(0);
            $table->decimal('price_difference', 12, 2)->nullable()->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->nullable()->default('pending');
            $table->string('visa_image')->nullable();
            $table->string('contract_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};