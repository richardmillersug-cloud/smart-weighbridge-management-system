<?php

namespace App\Console\Commands;

use App\Models\CloudSyncLog;
use App\Services\CloudSync\CloudSyncService;
use Illuminate\Console\Command;

class CloudSyncStatusCommand extends Command
{
    protected $signature = 'cloud:sync-status';

    protected $description = 'Show cloud sync connection and recent sync status';

    public function handle(): int
    {
        $enabled = config('cloud_sync.enabled');
        $configured = CloudSyncService::cloudConfigured();
        $reachable = CloudSyncService::cloudReachable();

        $this->table(['Setting', 'Value'], [
            ['Enabled', $enabled ? 'yes' : 'no'],
            ['Configured', $configured ? 'yes' : 'no'],
            ['Reachable', $reachable ? 'yes' : 'no'],
            ['Cloud host', config('database.connections.mysql_cloud.host') ?: '(empty)'],
            ['Cloud database', config('database.connections.mysql_cloud.database') ?: '(empty)'],
        ]);

        $pending = CloudSyncLog::query()->where('status', 'pending')->count();
        $failed = CloudSyncLog::query()->where('status', 'failed')->count();
        $success = CloudSyncLog::query()->where('status', 'success')->count();

        $this->newLine();
        $this->info("Sync log: {$success} success, {$pending} pending, {$failed} failed.");

        CloudSyncLog::query()
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->each(function (CloudSyncLog $log): void {
                $this->line(sprintf(
                    ' - %s #%d (%s): %s',
                    class_basename($log->model_type),
                    $log->model_id,
                    $log->action,
                    $log->status,
                ));
            });

        return self::SUCCESS;
    }
}
