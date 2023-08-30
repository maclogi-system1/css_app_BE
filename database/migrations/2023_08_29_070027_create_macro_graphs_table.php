<?php

use App\Models\MacroConfiguration;
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
        Schema::create('macro_graphs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MacroConfiguration::class);
            $table->string('title')->nullable();
            $table->string('axis_x');
            $table->string('axis_y');
            $table->string('graph_type');
            $table->string('position_display');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macro_graphs');
    }
};
