<?php

use App\Models\MqSheet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mq_sheets', function (Blueprint $table) {
            $table->boolean('is_default')->after('name')->default(0);
        });
        $groupMqSheets = DB::table('mq_sheets')->get()->groupBy('store_id');

        foreach ($groupMqSheets as $mqSheets) {
            DB::table('mq_sheets')->where('id', $mqSheets->first()->id)->update([
                'name' => MqSheet::DEFAULT_NAME,
                'is_default' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mq_sheets', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
