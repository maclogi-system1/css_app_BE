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
        Schema::create('policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('store_id');
            $table->bigInteger('job_group_id')->nullable();
            $table->string('name', 100);
            $table->unsignedTinyInteger('category')->comment('1: 中期施策, 2: 長期施策, 3: AIレコメンド施策');
            $table->unsignedTinyInteger('kpi')->comment('1: 売上向上, 2: アクセス向上, 3: 転換率向上, 4: 客単価向上, 5: その他');
            $table->unsignedTinyInteger('template')->comment('1: クーポン, 2: ポイント, 3: タイムセール');
            $table->unsignedTinyInteger('status')->comment('1: 提案前, 2: 施策確定, 3: 対応中, 4: 遅延, 5: 完了, 6: 対応不要');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->text('description')->nullable();
            $table->decimal('point_rate', 12, 6)->nullable()->comment('template = ポイント');
            $table->dateTime('point_application_period')->nullable()->comment('template = ポイント');
            $table->decimal('flat_rate_discount', 12, 6)->nullable()->comment('template = タイムセール');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
