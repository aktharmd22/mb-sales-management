<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class FlagDormantClients extends Command
{
    protected $signature = 'clients:flag-dormant';

    protected $description = 'Flag clients with no visit in the dormancy window as dormant (and revive any that are active again).';

    public function handle(): int
    {
        $cutoff = now()->subDays(Client::DORMANT_DAYS);

        // Go dormant: had a visit but it was long ago, or never visited and created long ago.
        $dormant = Client::query()
            ->where('status', 'active')
            ->where(function ($q) use ($cutoff) {
                $q->where(fn ($w) => $w->whereNotNull('last_visit_at')->where('last_visit_at', '<', $cutoff))
                  ->orWhere(fn ($w) => $w->whereNull('last_visit_at')->where('created_at', '<', $cutoff));
            })
            ->update(['status' => 'dormant']);

        // Revive: recently visited but still marked dormant.
        $revived = Client::query()
            ->where('status', 'dormant')
            ->whereNotNull('last_visit_at')
            ->where('last_visit_at', '>=', $cutoff)
            ->update(['status' => 'active']);

        $this->info("Dormancy sweep complete — {$dormant} flagged dormant, {$revived} revived.");

        return self::SUCCESS;
    }
}
