<?php

namespace App\Console\Commands;

use App\Services\PendingSync;
use Illuminate\Console\Command;

class SyncPending extends Command
{
    protected $signature = 'app:sync-pending';
    protected $description = 'Send offline check-ins and meal records to the registration API and Supabase';

    public function handle(PendingSync $sync): int
    {
        $sent = $sync->run();
        $this->info("Sent {$sent['checkins']} check-in(s) and {$sent['meals']} meal record(s).");

        return self::SUCCESS;
    }
}