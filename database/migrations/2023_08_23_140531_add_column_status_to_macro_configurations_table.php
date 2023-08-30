<?php

use App\Constants\MacroConstant;
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
        Schema::table('macro_configurations', function (Blueprint $table) {
            $table->tinyInteger('status')->default(MacroConstant::MACRO_STATUS_NOT_READY)->after('macro_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('macro_configurations', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
