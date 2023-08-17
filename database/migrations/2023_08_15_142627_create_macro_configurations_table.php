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
        Schema::create('macro_configurations', function (Blueprint $table) {
            $table->id();
            $table->json("conditions")->comment("All condition for this macro, store as json format");
            $table->json("time_conditions")->comment("Time to run this macro");
            $table->integer("macro_type")->comment("Type of macro");
            $table->bigInteger("created_by");
            $table->bigInteger("updated_by")->nullable();
            $table->bigInteger("deleted_by")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macro_configurations');
    }
};
