<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_office_id')->nullable()->constrained('external_offices');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->boolean('is_authenticated')->default(false);
            $table->date('authentication_date')->nullable();
            $table->date('certification_date')->nullable();
            $table->string('authentication_number')->nullable();
            $table->string('authorization_number')->nullable();
            $table->string('sponsor_number')->nullable();
            $table->date('last_action_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('priority_level')->nullable();
            $table->string('passport_status')->nullable();
            $table->string('transfer_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking');
    }
};