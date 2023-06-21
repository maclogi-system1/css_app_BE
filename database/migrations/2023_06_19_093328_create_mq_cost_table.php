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
        Schema::create('mq_cost', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('coupon_points_cost')->nullable();
            $table->decimal('coupon_points_cost_rate', 12, 6)->nullable();
            $table->integer('ad_cost')->nullable();
            $table->integer('ad_cpc_cost')->nullable();
            $table->integer('ad_season_cost')->nullable();
            $table->integer('ad_event_cost')->nullable();
            $table->integer('ad_tda_cost')->nullable();
            $table->decimal('ad_cost_rate', 12, 6)->nullable();
            $table->integer('cost_price')->nullable();
            $table->decimal('cost_price_rate', 12, 6)->nullable();
            $table->integer('postage')->nullable();
            $table->decimal('postage_rate', 12, 6)->nullable();
            $table->integer('commision')->nullable();
            $table->decimal('commision_rate', 12, 6)->nullable();
            $table->integer('variable_cost_sum')->nullable();
            $table->integer('gross_profit')->nullable();
            $table->decimal('gross_profit_rate', 12, 6)->nullable();
            $table->integer('management_agency_fee')->nullable();
            $table->integer('reserve1')->nullable();
            $table->integer('reserve2')->nullable();
            $table->decimal('management_agency_fee_rate', 12, 6)->nullable();
            $table->integer('cost_sum')->nullable();
            $table->integer('profit')->nullable();
            $table->integer('sum_profit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_cost');
    }
};
