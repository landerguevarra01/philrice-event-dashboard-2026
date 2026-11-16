<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('synced_at')->nullable();
            $table->string('sync_error')->nullable();
        });

        Schema::table('day_requirements', function (Blueprint $table) {
            $table->timestamp('synced_at')->nullable();
        });

        // Check-ins made before this change are already in the registration system
        DB::table('attendances')->update(['synced_at' => DB::raw('checked_in_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
