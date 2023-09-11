<?php

use App\Constants\MacroConstant;
use App\Models\MacroConfiguration;
use App\Models\MacroTemplate;
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
        Schema::create('macro_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MacroConfiguration::class);
            $table->smallInteger('type');
            $table->json('payload');
            $table->smallInteger('status')->default(MacroTemplate::REGISTRABLE_STATUS);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('macro_templates');
    }
};
