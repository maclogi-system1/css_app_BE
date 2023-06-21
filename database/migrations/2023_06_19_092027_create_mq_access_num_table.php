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
        Schema::create('mq_access_num', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('access_flow_sum')->nullable();
            $table->integer('search_flow_num')->nullable();
            $table->integer('ranking_flow_num')->nullable();
            $table->integer('instagram_flow_num')->nullable();
            $table->integer('google_flow_num')->nullable();
            $table->integer('cpc_num')->nullable();
            $table->integer('display_num')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_access_num');
    }
};
