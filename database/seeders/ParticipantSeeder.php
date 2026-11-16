<?php

namespace Database\Seeders;

use App\Models\Participant;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        // 30 pending participants
        Participant::factory()->count(30)->create();

        // 20 already checked in, each with 3 days of random meal/accommodation data
        Participant::factory()->checkedIn()->count(20)->create()->each(function ($participant) {
            foreach ([1, 2, 3] as $day) {
                $participant->requirements()->create([
                    'day' => $day,
                    'lunch' => fake()->boolean(70),
                    'dinner' => fake()->boolean(60),
                    'accommodation' => fake()->boolean(40),
                ]);
            }
        });
    }
}
