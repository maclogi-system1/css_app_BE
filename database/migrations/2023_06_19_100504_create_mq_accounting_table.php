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
        Schema::create('mq_accounting', function (Blueprint $table) {
            $table->uuid('store_id');
            $table->unsignedInteger('year');
            $table->unsignedSmallInteger('month');

            $table->foreignUuid('mq_kpi_id');
            $table->foreignUuid('mq_access_num_id');
            $table->foreignUuid('mq_ad_sales_amnt_id');
            $table->foreignUuid('mq_user_trends_id');
            $table->foreignUuid('mq_cost_id');

            $table->integer('ltv_2y_amnt')->nullable();
            $table->integer('lim_cpa')->nullable();
            $table->integer('cpo_via_ad')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_accounting');
    }
};
