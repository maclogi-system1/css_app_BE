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
            $table->text('condition_value_3')->nullable()->after('value')->comment('適用条件値3');
            $table->tinyInteger('condition_3')->nullable()->after('value')->comment('適用条件3');
            $table->text('condition_value_2')->nullable()->after('value')->comment('適用条件値2');
            $table->tinyInteger('condition_2')->nullable()->after('value')->comment('適用条件2');
            $table->text('condition_value_1')->nullable()->after('value')->comment('適用条件値1');
            $table->tinyInteger('condition_1')->nullable()->after('value')->comment('適用条件1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_simulation_histories', function (Blueprint $table) {
            $table->dropColumn([
                'condition_1',
                'condition_value_1',
                'condition_2',
                'condition_value_2',
                'condition_3',
                'condition_value_3',
            ]);
        });
    }
};
