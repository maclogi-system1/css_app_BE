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
        Schema::table('policy_simulation_histories', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->nullable()->after('manager');
        });

        $managers = DB::table('policy_simulation_histories')
            ->where('manager', '!=', '')
            ->whereNotNull('manager')
            ->get()
            ->pluck('manager');

        foreach ($managers as $manager) {
            $user = User::where('name', $manager)->first();

            if (is_null($user)) {
                continue;
            }

            DB::table('policy_simulation_histories')
                ->where('manager', $manager)
                ->update(['user_id' => $user->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_simulation_histories', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
