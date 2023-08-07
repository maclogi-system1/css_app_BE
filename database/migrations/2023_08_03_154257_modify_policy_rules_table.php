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
        Schema::table('policy_rules', function (Blueprint $table) {
            $table->text('condition_value_1')->nullable()->change();
            $table->text('condition_value_2')->nullable()->change();
            $table->text('condition_value_3')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_rules', function (Blueprint $table) {
            $table->text('condition_value_1')->nullable(false)->change();
            $table->text('condition_value_2')->nullable(false)->change();
            $table->text('condition_value_3')->nullable(false)->change();
        });
    }
};
