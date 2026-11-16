<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'participant_code' => 'EVT-' . fake()->unique()->numerify('#####'),
            'name' => fake()->name(),
            'organization' => fake()->company(),
            'checked_in_at' => null,
        ];
    }

    // Helper state: a participant who is already checked in
    public function checkedIn(): static
    {
        return $this->state(fn () => [
            'checked_in_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }
}