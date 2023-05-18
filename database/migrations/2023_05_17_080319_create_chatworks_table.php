<?php

use App\Models\User;
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
        Schema::create('chatworks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('account_id', 8);
            $table->string('role', 125)->default('member')->comment('admin, member, readonly');
            $table->string('name', 150);
            $table->string('chatwork_id', 50);
            $table->string('organization_id', 8);
            $table->string('organization_name', 150)->nullable();
            $table->string('url')->nullable();
            $table->string('department', 125)->nullable();
            $table->string('avatar_image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatworks');
    }
};
