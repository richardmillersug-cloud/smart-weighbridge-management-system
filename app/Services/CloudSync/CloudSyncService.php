<?php

namespace App\Services\CloudSync;

use App\Models\CloudSyncLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Throwable;

class CloudSyncService
{
    private static bool $syncing = false;

    public static function isSyncing(): bool
    {
        return static::$syncing;
    }

    public static function cloudConfigured(): bool
    {
        $connection = config('database.connections.'.config('cloud_sync.connection', 'mysql_cloud'));

        if (($connection['driver'] ?? null) === 'sqlite') {
            return filled($connection['database'] ?? null);
        }

        return filled($connection['host'] ?? null)
            && filled($connection['database'] ?? null);
    }

    public static function cloudReachable(): bool
    {
        if (! static::cloudConfigured()) {
            return false;
        }

        try {
            DB::connection(config('cloud_sync.connection'))->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array{
     *     enabled: bool,
     *     configured: bool,
     *     reachable: bool,
     *     host: ?string,
     *     port: ?string,
     *     database: ?string,
     *     ssl_configured: bool,
     *     counts: array{success: int, pending: int, failed: int},
     *     recent: \Illuminate\Support\Collection<int, CloudSyncLog>
     * }
     */
    public function status(): array
    {
        return [
            'enabled' => (bool) config('cloud_sync.enabled'),
            'configured' => static::cloudConfigured(),
            'reachable' => static::cloudReachable(),
            'host' => config('database.connections.mysql_cloud.host') ?: null,
            'port' => (string) config('database.connections.mysql_cloud.port'),
            'database' => config('database.connections.mysql_cloud.database') ?: null,
            'ssl_configured' => filled(env('DB_CLOUD_SSL_CA')),
            'counts' => [
                'success' => CloudSyncLog::query()->where('status', 'success')->count(),
                'pending' => CloudSyncLog::query()->where('status', 'pending')->count(),
                'failed' => CloudSyncLog::query()->where('status', 'failed')->count(),
            ],
            'recent' => CloudSyncLog::query()->latest('updated_at')->limit(25)->get(),
        ];
    }

    public function sync(string $modelClass, int|string $modelId, string $action = 'upsert'): void
    {
        if (! config('cloud_sync.enabled') || ! static::cloudConfigured()) {
            return;
        }

        $log = CloudSyncLog::query()->firstOrCreate(
            [
                'model_type' => $modelClass,
                'model_id' => (int) $modelId,
                'action' => $action,
            ],
            ['status' => 'pending'],
        );

        static::$syncing = true;

        try {
            if ($action === 'delete') {
                $this->deleteFromCloud($modelClass, $modelId);
            } else {
                $model = $this->findLocalModel($modelClass, $modelId);

                if ($model === null) {
                    $this->deleteFromCloud($modelClass, $modelId);

                    $this->markSuccess($log);

                    return;
                }

                $this->syncDependencies($model);
                $this->upsertToCloud($model);
            }

            $this->markSuccess($log);
        } catch (Throwable $exception) {
            $this->markFailure($log, $exception);

            throw $exception;
        } finally {
            static::$syncing = false;
        }
    }

    public function syncAll(): array
    {
        $summary = ['synced' => 0, 'failed' => 0];

        foreach (config('cloud_sync.full_sync_order') as $modelClass) {
            /** @var Model $modelClass */
            $query = $modelClass::query();

            if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
                $query->withTrashed();
            }

            $query->orderBy('id')->chunkById(100, function ($models) use (&$summary): void {
                foreach ($models as $model) {
                    try {
                        $this->sync($model::class, $model->getKey(), 'upsert');
                        $summary['synced']++;
                    } catch (Throwable) {
                        $summary['failed']++;
                    }
                }
            });
        }

        return $summary;
    }

    public function retryFailed(): int
    {
        $retried = 0;

        CloudSyncLog::query()
            ->where('status', 'failed')
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->each(function (CloudSyncLog $log) use (&$retried): void {
                try {
                    $this->sync($log->model_type, $log->model_id, $log->action);
                    $retried++;
                } catch (Throwable) {
                    // Failure already logged on the sync log row.
                }
            });

        return $retried;
    }

    private function findLocalModel(string $modelClass, int|string $modelId): ?Model
    {
        /** @var Model $modelClass */
        $query = $modelClass::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        return $query->find($modelId);
    }

    private function syncDependencies(Model $model): void
    {
        $foreignKeys = config('cloud_sync.foreign_keys.'.$model::class, []);

        foreach ($foreignKeys as $column => $dependencyClass) {
            $dependencyId = $model->getAttribute($column);

            if ($dependencyId === null) {
                continue;
            }

            $dependency = $this->findLocalModel($dependencyClass, $dependencyId);

            if ($dependency !== null) {
                $this->syncDependencies($dependency);
                $this->upsertToCloud($dependency);
            }
        }
    }

    private function upsertToCloud(Model $model): void
    {
        $connection = DB::connection(config('cloud_sync.connection'));
        $attributes = $this->prepareAttributes($model);

        $connection->table($model->getTable())->updateOrInsert(
            ['id' => $model->getKey()],
            $attributes,
        );
    }

    private function deleteFromCloud(string $modelClass, int|string $modelId): void
    {
        /** @var Model $modelClass */
        $instance = new $modelClass;

        DB::connection(config('cloud_sync.connection'))
            ->table($instance->getTable())
            ->where('id', $modelId)
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareAttributes(Model $model): array
    {
        $attributes = $model->getAttributes();

        foreach ($model->getCasts() as $key => $cast) {
            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            $value = $attributes[$key];

            if ($value instanceof \BackedEnum) {
                $attributes[$key] = $value->value;
            } elseif (is_array($value) || is_object($value)) {
                $attributes[$key] = json_encode($value);
            }
        }

        return $attributes;
    }

    private function markSuccess(CloudSyncLog $log): void
    {
        $log->update([
            'status' => 'success',
            'attempts' => $log->attempts + 1,
            'error_message' => null,
            'synced_at' => now(),
        ]);
    }

    private function markFailure(CloudSyncLog $log, Throwable $exception): void
    {
        $log->update([
            'status' => 'failed',
            'attempts' => $log->attempts + 1,
            'error_message' => mb_substr($exception->getMessage(), 0, 65000),
        ]);
    }
}
