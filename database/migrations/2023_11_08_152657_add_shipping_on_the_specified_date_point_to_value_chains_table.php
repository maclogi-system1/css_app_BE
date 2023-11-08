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
            $table->decimal('shipping_on_the_specified_date_point', 3)
                ->default(1.00)
                ->after('delivery_preparation_period_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('value_chains', function (Blueprint $table) {
            $table->dropColumn('shipping_on_the_specified_date_point');
        });
    }
};
