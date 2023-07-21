<?php

use App\Models\Policy;
use App\Models\PolicyAttachment;
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
        Schema::create('policy_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Policy::class)->nullable();
            $table->string('attachment_key', 16);
            $table->string('name', 64);
            $table->string('path');
            $table->string('type', 20)->default(PolicyAttachment::IMAGE_TYPE);
            $table->string('disk', 16)->default('public');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_attachments');
    }
};
