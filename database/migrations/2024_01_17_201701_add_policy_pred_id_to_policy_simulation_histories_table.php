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
        Schema::table('policy_simulation_histories', function (Blueprint $table) {
            $table->uuid('policy_pred_id')->after('items_pred_2m')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_simulation_histories', function (Blueprint $table) {
            $table->dropColumn('policy_pred_id');
        });
    }
};
