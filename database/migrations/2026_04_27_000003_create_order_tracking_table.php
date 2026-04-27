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
        Schema::create('order_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // التوثيق
            $table->boolean('is_authenticated')->default(false); // هل تم التوثيق؟
            $table->date('authentication_date')->nullable(); // تاريخ التصديق
            $table->string('authentication_number')->nullable(); // رقم التوثيق

            // الإرسال للمكتب الخارجي
            $table->boolean('sent_to_external')->default(false); // تم الإرسال للمكتب الخارجي؟
            $table->enum('external_status', ['pending', 'accepted', 'rejected'])->default('pending'); // حالة المكتب الخارجي

            // ترشيح الجواز
            $table->enum('passport_filtered', ['pending', 'accepted', 'rejected'])->default('pending'); // الجواز مرشح أو غير مرشح

            // التوريد
            $table->boolean('is_delivered')->default(false); // تم التوريد؟

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tracking');
    }
};
