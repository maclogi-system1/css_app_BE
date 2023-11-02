<?php

use App\Models\InferenceRealData\SuggestPolicies;
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
            $table->integer('value')->after('items_pred_2m')->default(0);
            $table->smallInteger('service')
                ->after('items_pred_2m')
                ->default(SuggestPolicies::FIXED_PRICE_DISCOUNT_SERVICE);
            $table->smallInteger('class')
                ->after('items_pred_2m')
                ->default(SuggestPolicies::COUPON_CLASS);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_simulation_histories', function (Blueprint $table) {
            $table->dropColumn(['class', 'service', 'value']);
        });
    }
};
