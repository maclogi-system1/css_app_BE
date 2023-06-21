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
        Schema::create('mq_user_trends', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('new_sales_amnt')->nullable();
            $table->integer('new_sales_num')->nullable();
            $table->integer('new_price_per_user')->nullable();
            $table->integer('re_sales_amnt')->nullable();
            $table->integer('re_sales_num')->nullable();
            $table->integer('re_price_per_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_user_trends');
    }
};
