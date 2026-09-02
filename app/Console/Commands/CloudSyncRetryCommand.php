<?php

namespace App\Console\Commands;

use App\Models\CloudSyncLog;
use App\Services\CloudSync\CloudSyncService;
use Illuminate\Console\Command;

class CloudSyncRetryCommand extends Command
{
    protected $signature = 'cloud:sync-retry';

    protected $description = 'Retry failed cloud sync jobs';

    public function handle(CloudSyncService $cloudSync): int
    {
        if (! config('cloud_sync.enabled')) {
            $this->error('Cloud sync is disabled. Set CLOUD_SYNC_ENABLED=true in .env');

            return self::FAILURE;
        }

        if (! CloudSyncService::cloudReachable()) {
            $this->error('Cloud database is not reachable.');

            return self::FAILURE;
        }

        $retried = $cloudSync->retryFailed();

        $this->info("Retried {$retried} failed sync record(s).");

        return self::SUCCESS;
    }
}
