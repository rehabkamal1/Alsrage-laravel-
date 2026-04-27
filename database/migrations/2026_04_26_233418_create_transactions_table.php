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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['receipt', 'payment']);
            $table->decimal('amount', 10, 2);
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('payment_method', ['cash', 'visa'])->nullable();
            $table->string('bank_name')->nullable();
            $table->date('transfer_date')->nullable();
            $table->string('sender_name')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};