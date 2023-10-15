<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'shop_setting_rankings';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->smallInteger('is_competitive')->default(0);
        });

        DB::table($this->table)->whereNotNull('store_competitive_id')->update(['is_competitive' => 1]);
        DB::table($this->table)->whereNull('store_competitive_id')->update(['is_competitive' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn(['is_competitive']);
        });
    }
};
