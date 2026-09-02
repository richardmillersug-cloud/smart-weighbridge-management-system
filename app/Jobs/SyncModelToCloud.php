<?php

namespace App\Jobs;

use App\Services\CloudSync\CloudSyncService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncModelToCloud implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public string $modelClass,
        public int|string $modelId,
        public string $action = 'upsert',
    ) {}

    public function uniqueId(): string
    {
        return $this->modelClass.':'.$this->modelId.':'.$this->action;
    }

    public function handle(CloudSyncService $cloudSync): void
    {
        $cloudSync->sync($this->modelClass, $this->modelId, $this->action);
    }
}
