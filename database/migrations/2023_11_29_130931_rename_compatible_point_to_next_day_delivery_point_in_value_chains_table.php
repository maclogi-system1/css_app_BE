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
            $table->renameColumn('compatible_point', 'next_day_delivery_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('value_chains', function (Blueprint $table) {
            $table->renameColumn('next_day_delivery_point', 'compatible_point');
        });
    }
};
