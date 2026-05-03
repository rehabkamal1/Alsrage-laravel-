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
        Schema::create('saudi_offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_supplier')->default(false);
            $table->string('destination')->nullable();
            $table->string('city')->nullable();
            $table->string('responsible_employee')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('total_authorization', 10, 2)->nullable();
            $table->decimal('musaned_price', 10, 2)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saudi_offices');
    }
};
