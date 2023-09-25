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
        Schema::create('shop_setting_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('store_id');
            $table->string('store_competitive_id')->nullable();
            $table->string('merchandise_control_number')->nullable();
            $table->integer('directory_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_setting_rankings');
    }
};
