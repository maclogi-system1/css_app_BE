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
        Schema::table('mq_accounting', function (Blueprint $table) {
            $table->integer('store_opening_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mq_accounting', function (Blueprint $table) {
            $table->dropColumn('store_opening_fee');
        });
    }
};
