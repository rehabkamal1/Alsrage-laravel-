<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_leads', function (Blueprint $table) {
            $table->id();
            $table->morphs('source');
            $table->string('name');
            $table->string('phone');
            $table->string('type'); // saudi_office, external_office, service_office
            $table->string('status')->default('new');
            $table->string('priority_level')->default('medium');
            $table->text('notes')->nullable();
            $table->date('contact_date')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->string('assigned_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_leads');
    }
};