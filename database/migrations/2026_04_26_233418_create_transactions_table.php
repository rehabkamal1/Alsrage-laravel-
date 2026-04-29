<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['receipt', 'payment']);
            $table->decimal('amount', 12, 2);
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('payment_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->date('transfer_date')->nullable();
            $table->string('transfer_number')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority_level')->default('medium');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};