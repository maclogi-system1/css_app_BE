<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_setting_mq_accounting', function (Blueprint $table) {
            $table->id();
            $table->string('store_id');
            $table->datetime('date')->nullable();
            $table->integer('estimated_management_agency_expenses')->nullable();
            $table->decimal('estimated_cost_rate', 12, 6)->nullable();
            $table->integer('estimated_shipping_fee')->nullable();
            $table->decimal('estimated_commission_rate', 12, 6)->nullable();
            $table->integer('estimated_csv_usage_fee')->nullable();
            $table->integer('estimated_store_opening_fee')->nullable();

            $table->integer('actual_management_agency_expenses')->nullable();
            $table->decimal('actual_cost_rate', 12, 6)->nullable();
            $table->integer('actual_shipping_fee')->nullable();
            $table->decimal('actual_commission_rate', 12, 6)->nullable();
            $table->integer('actual_csv_usage_fee')->nullable();
            $table->integer('actual_store_opening_fee')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_setting_mq_accounting');
    }
};
