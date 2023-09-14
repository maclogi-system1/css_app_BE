<?php

use App\Models\MqSheet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mq_accounting', function (Blueprint $table) {
            $table->foreignIdFor(MqSheet::class)->nullable();
            $table->dropPrimary('primary');

            $table->unsignedBigInteger('id')->first()->nullable();
            $table->unique(['store_id', 'month', 'year', 'mq_sheet_id']);
        });

        $increment = 1;

        foreach (DB::table('mq_accounting')->get() as $mqAccounting) {
            $sheet = DB::table('mq_sheets')->where('store_id', $mqAccounting->store_id)->first();

            DB::table('mq_accounting')->where([
                'store_id' => $mqAccounting->store_id,
                'month' => $mqAccounting->month,
                'year' => $mqAccounting->year,
            ])->update(['id' => $increment, 'mq_sheet_id' => $sheet?->id]);
            $increment++;
        }
        DB::statement("ALTER TABLE mq_accounting MODIFY COLUMN `id` BIGINT AUTO_INCREMENT PRIMARY KEY;");
        DB::statement("ALTER TABLE mq_accounting AUTO_INCREMENT={$increment};");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mq_accounting', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->change();
            $table->dropPrimary('primary');
            $table->dropColumn(['mq_sheet_id', 'id']);
            $table->dropUnique('mq_accounting_store_id_month_year_mq_sheet_id_unique');
            $table->primary(['store_id', 'year', 'month']);
        });
    }
};
