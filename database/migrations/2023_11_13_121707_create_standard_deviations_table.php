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
        Schema::create('standard_deviations', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();

            // 商品 | Merchandise
            $table->decimal('number_of_categories', 3)->nullable();
            $table->decimal('number_of_items', 3)->nullable();
            $table->decimal('product_utilization_rate', 3)->nullable();

            // 構築・制作 | Construction/Production
            $table->decimal('product_page_conversion_rate', 3)->nullable();
            $table->decimal('access_number', 3)->nullable();

            // イベント・セール | Event sale
            $table->decimal('event_sales_ratio', 3)->nullable();
            $table->decimal('sales_ratio_day_endings_0_5', 3)->nullable();
            $table->decimal('coupon_effect', 3)->nullable();

            // 広告 | Advertisement
            $table->decimal('rpp_ad', 3)->nullable();
            $table->decimal('coupon_advance', 3)->nullable();
            $table->decimal('rgroup_ad', 3)->nullable();
            $table->decimal('tda_ad', 3)->nullable();
            $table->decimal('sns_ad', 3)->nullable();
            $table->decimal('google_access', 3)->nullable();
            $table->decimal('instagram_access', 3)->nullable();

            // 物流 | Logistics
            $table->decimal('shipping_fee', 3)->nullable();
            $table->decimal('shipping_ratio', 3)->nullable();
            $table->decimal('bundling_ratio', 3)->nullable();

            // CRM
            $table->decimal('email_newsletter', 3)->nullable();
            $table->decimal('re_sales_num_rate', 3)->nullable(); // Repeat rate
            $table->decimal('line_official', 3)->nullable(); // LINE official
            $table->decimal('ltv', 3)->nullable(); // LTV

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_deviations');
    }
};
