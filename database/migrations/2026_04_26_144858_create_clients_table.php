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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->string('category')->nullable();
            $table->string('office_name')->nullable();
            $table->string('phone')->unique();
            $table->string('additional_phone')->nullable();
            $table->text('address')->nullable();

            $table->string('passport_number')->nullable()->unique()->after('visa_holder_name');
            $table->string('national_id')->nullable()->unique()->after('passport_number');
            $table->string('passport_image')->nullable()->after('national_id');
            $table->string('visa_image')->nullable()->after('passport_image');
            $table->string('id_image')->nullable()->after('visa_image');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
