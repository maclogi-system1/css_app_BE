<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            // New column
            $table->bigInteger('single_job_id')->nullable()->after('job_group_id');
            $table->tinyInteger('immediate_reflection')->default(1)->comment('1. する, 2. しない')->after('category');
            $table->integer('simulation_promotional_expenses')->default(0)->after('end_date');
            $table->decimal('simulation_store_priority', 12, 6)->default(0)->after('simulation_promotional_expenses');
            $table->decimal('simulation_product_priority', 12, 6)->default(0)->after('simulation_store_priority');

            // Modify column
            $table->string('name', 100)->nullable()->change();

            // Rename column
            $table->renameColumn('start_date', 'simulation_start_date');
            $table->renameColumn('end_date', 'simulation_end_date');

            // Drop column
            $table->dropColumn([
                'kpi',
                'template',
                'status',
                'point_rate',
                'point_application_period',
                'flat_rate_discount',
                'description',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn([
                'single_job_id',
                'immediate_reflection',
                'simulation_promotional_expenses',
                'simulation_store_priority',
                'simulation_product_priority',
            ]);

            $table->renameColumn('simulation_start_date', 'start_date');
            $table->renameColumn('simulation_end_date', 'end_date');


            if (!Schema::hasColumn('policies', 'description')) {
                $table->text('description')->nullable()->after('simulation_end_date');
            }

            if (!Schema::hasColumn('policies', 'kpi')) {
                $table->unsignedTinyInteger('kpi')
                    ->comment('1: 売上向上, 2: アクセス向上, 3: 転換率向上, 4: 客単価向上, 5: その他')
                    ->default(1)
                    ->after('category');
            }

            $table->unsignedTinyInteger('template')
                ->comment('1: クーポン, 2: ポイント, 3: タイムセール')
                ->after('kpi');
            $table->unsignedTinyInteger('status')
                ->comment('1: 提案前, 2: 施策確定, 3: 対応中, 4: 遅延, 5: 完了, 6: 対応不要')
                ->after('template');
            $table->decimal('point_rate', 12, 6)->nullable()->comment('template = ポイント')->after('description');
            $table->dateTime('point_application_period')
                ->nullable()
                ->comment('template = ポイント')
                ->after('point_rate');
            $table->decimal('flat_rate_discount', 12, 6)
                ->nullable()
                ->comment('template = タイムセール')
                ->after('point_application_period');
        });
    }
};
