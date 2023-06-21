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
        Schema::create('mq_ad_sales_amnt', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('sales_amnt_via_ad')->nullable();
            $table->integer('sales_amnt_seasonal')->nullable();
            $table->integer('sales_amnt_event')->nullable();
            $table->integer('tda_access_num')->nullable();
            $table->integer('tda_v_sales_amnt')->nullable();
            $table->decimal('tda_v_roas', 12, 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_ad_sales_amnt');
    }
};
