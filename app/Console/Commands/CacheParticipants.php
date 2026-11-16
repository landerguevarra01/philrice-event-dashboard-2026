<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Services\ParticipantApi;
use Illuminate\Console\Command;

class CacheParticipants extends Command
{
    protected $signature = 'app:cache-participants';
    protected $description = 'Save participants from the registration API on this device for offline search';

    public function handle(ParticipantApi $api): int
    {
        foreach (range('a', 'z') as $letter) {
            foreach ($api->search($letter) as $r) {
                Participant::remember($r);
            }
            $this->line("Letter {$letter} done");
        }

        $this->info('Participants stored on this device: ' . Participant::count());

        return self::SUCCESS;
    }
}