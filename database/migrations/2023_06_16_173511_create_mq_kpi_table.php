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
        Schema::create('mq_kpi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('sales_amnt')->nullable();
            $table->integer('sales_num')->nullable();
            $table->integer('access_num')->nullable();
            $table->decimal('conversion_rate', 12, 6)->nullable();
            $table->integer('sales_amnt_per_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_kpi');
    }
};
