<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->string('workflow_status', 30)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropIndex(['workflow_status']);
            $table->dropColumn('workflow_status');
        });
    }
};
