<?php

use App\Models\Policy;
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
        Schema::create('policy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Policy::class);
            $table->string('class')->comment('施策種別');
            $table->string('service')->comment('施策サービス');
            $table->string('value')->comment('施策値');
            $table->tinyInteger('condition_1')->comment('適用条件1');
            $table->text('condition_value_1')->comment('適用条件値1');
            $table->tinyInteger('condition_2')->comment('適用条件2');
            $table->text('condition_value_2')->comment('適用条件値2');
            $table->tinyInteger('condition_3')->comment('適用条件3');
            $table->text('condition_value_3')->comment('適用条件値3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_rules');
    }
};
