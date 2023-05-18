<?php

use App\Models\Chatwork;
use App\Models\Notification;
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
        Schema::create('chatwork_notification', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Chatwork::class);
            $table->foreignIdFor(Notification::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatwork_notification');
    }
};
