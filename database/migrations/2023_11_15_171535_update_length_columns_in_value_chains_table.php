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
        Schema::table('value_chains', function (Blueprint $table) {
            $table->string('top_page', 1000)->change();
            $table->string('category_page', 1000)->change();
            $table->string('header', 1000)->change();
            $table->string('product_page', 1000)->change();
            $table->string('product_thumbnail', 1000)->change();
            $table->string('featured_products', 1000)->change();
            $table->string('implementation_of_measures', 1000)->change();
            $table->string('rpp_ad_operation', 1000)->change();
            $table->string('gift_available', 1000)->change();
            $table->string('review_measures', 1000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('value_chains', function (Blueprint $table) {
            $table->string('top_page', 255)->change();
            $table->string('category_page', 255)->change();
            $table->string('header', 255)->change();
            $table->string('product_page', 255)->change();
            $table->string('product_thumbnail', 255)->change();
            $table->string('featured_products', 255)->change();
            $table->string('implementation_of_measures', 255)->change();
            $table->string('rpp_ad_operation', 255)->change();
            $table->string('gift_available', 255)->change();
            $table->string('review_measures', 255)->change();
        });
    }
};
