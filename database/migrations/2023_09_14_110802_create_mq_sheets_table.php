<?php

use App\Models\MqSheet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mq_sheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('store_id');
            $table->string('name', 100);
            $table->timestamps();

            $table->unique(['store_id', 'name']);
        });

        $storeIds = DB::table('mq_accounting')->get()->pluck('store_id')->unique();
        $mqSheets = [];

        foreach ($storeIds as $storeId) {
            $mqSheets[] = [
                'id' => (string) Str::orderedUuid(),
                'store_id' => $storeId,
                'name' => 'Sheet 1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        MqSheet::insert($mqSheets);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_sheets');
    }
};
