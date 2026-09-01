<?php

namespace App\Console\Commands;

use App\Models\CloudSyncLog;
use App\Services\CloudSync\CloudSyncService;
use Illuminate\Console\Command;

class CloudSyncFullCommand extends Command
{
    protected $signature = 'cloud:sync-full';

    protected $description = 'Push all local business records to the cloud database';

    public function handle(CloudSyncService $cloudSync): int
    {
        if (! config('cloud_sync.enabled')) {
            $this->error('Cloud sync is disabled. Set CLOUD_SYNC_ENABLED=true in .env');

            return self::FAILURE;
        }

        if (! CloudSyncService::cloudReachable()) {
            $this->error('Cloud database is not reachable. Check DB_CLOUD_* settings and trusted sources.');

            return self::FAILURE;
        }

        $this->info('Starting full cloud sync...');

        $summary = $cloudSync->syncAll();

        $this->info("Synced {$summary['synced']} records. Failed {$summary['failed']}.");

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
