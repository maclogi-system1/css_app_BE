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
        Schema::table('policy_rules', function (Blueprint $table) {
            $table->smallInteger('class')->nullable()->change();
            $table->smallInteger('service')->nullable()->change();
            $table->integer('value')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_rules', function (Blueprint $table) {
            $table->string('class')->change();
            $table->string('service')->change();
            $table->string('value')->change();
        });
    }
};
