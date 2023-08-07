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
        Schema::create('policy_simulation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Policy::class);
            $table->string('manager')->nullable();
            $table->string('title')->nullable();
            $table->string('job_title')->nullable();
            $table->dateTime('execution_time')->nullable();
            $table->dateTime('undo_time')->nullable();
            $table->dateTime('creation_date')->nullable();
            $table->string('sale_effect')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_simulation_histories');
    }
};
