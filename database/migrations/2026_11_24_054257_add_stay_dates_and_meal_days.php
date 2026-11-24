<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('stay_from', 10)->nullable();   // 'YYYY-MM-DD'
            $table->string('stay_to', 10)->nullable();
        });

        Schema::create('meal_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->string('service_date', 10);            // 'YYYY-MM-DD'
            $table->boolean('lunch')->default(false);
            $table->boolean('dinner')->default(false);
            $table->boolean('accommodation')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['participant_id', 'service_date']);
        });

        // Keep what staff already ticked: the old Day 1, 2, 3 become the real event dates
        $start = Carbon::parse(config('event.start_date'))->startOfDay();

        $ticked = DB::table('day_requirements')
            ->where(fn ($q) => $q->where('lunch', true)->orWhere('dinner', true)->orWhere('accommodation', true))
            ->get();

        foreach ($ticked as $row) {
            DB::table('meal_days')->insert([
                'participant_id' => $row->participant_id,
                'service_date' => $start->copy()->addDays($row->day - 1)->toDateString(),
                'lunch' => $row->lunch,
                'dinner' => $row->dinner,
                'accommodation' => $row->accommodation,
                'synced_at' => null,   // sent to Supabase again in the new format
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Their range is the first to the last date they have ticks on
        foreach ($ticked->groupBy('participant_id') as $participantId => $rows) {
            $dates = $rows->map(fn ($r) => $start->copy()->addDays($r->day - 1)->toDateString());

            DB::table('participants')->where('id', $participantId)->update([
                'stay_from' => $dates->min(),
                'stay_to' => $dates->max(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_days');

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['stay_from', 'stay_to']);
        });
    }
};
