<?php

namespace App\Services\CloudSync;

use App\Jobs\SyncModelToCloud;
use App\Models\CloudSyncLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CloudSyncDispatcher
{
    public static function queue(Model $model, string $action = 'upsert'): void
    {
        if (! config('cloud_sync.enabled') || ! CloudSyncService::cloudConfigured()) {
            return;
        }

        if (! in_array($model::class, config('cloud_sync.models'), true)) {
            return;
        }

        if (CloudSyncService::isSyncing()) {
            return;
        }

        SyncModelToCloud::dispatch(
            modelClass: $model::class,
            modelId: $model->getKey(),
            action: $action,
        )->onQueue(config('cloud_sync.queue'));

        CloudSyncLog::query()->updateOrCreate(
            [
                'model_type' => $model::class,
                'model_id' => (int) $model->getKey(),
                'action' => $action,
            ],
            [
                'status' => 'pending',
                'error_message' => null,
            ],
        );
    }

    public static function registerModelHooks(): void
    {
        foreach (config('cloud_sync.models') as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $modelClass::saved(function (Model $model): void {
                static::queue($model, 'upsert');
            });

            $modelClass::deleted(function (Model $model): void {
                $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);

                if ($usesSoftDeletes && ! $model->isForceDeleting()) {
                    static::queue($model, 'upsert');

                    return;
                }

                static::queue($model, 'delete');
            });
        }
    }
}
