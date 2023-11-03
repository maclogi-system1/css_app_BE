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
        Schema::create('macroables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MacroConfiguration::class);
            $table->morphs('macroable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macroables');
    }
};
