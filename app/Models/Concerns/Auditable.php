<?php

namespace App\Models\Concerns;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Automatically records CREATE / UPDATE / DELETE entries in the audit log
 * for any model using this trait.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            app(AuditService::class)->log(
                action: 'CREATE',
                module: static::auditModule(),
                recordId: $model->getKey(),
                newValue: $model->attributesToArray(),
            );
        });

        static::updated(function (Model $model): void {
            $changes = collect($model->getChanges())
                ->except(['updated_at', 'remember_token'])
                ->all();

            if ($changes === []) {
                return;
            }

            $original = array_intersect_key($model->getOriginal(), $changes);

            app(AuditService::class)->log(
                action: 'UPDATE',
                module: static::auditModule(),
                recordId: $model->getKey(),
                oldValue: $original,
                newValue: $changes,
            );
        });

        static::deleted(function (Model $model): void {
            app(AuditService::class)->log(
                action: 'DELETE',
                module: static::auditModule(),
                recordId: $model->getKey(),
                oldValue: $model->attributesToArray(),
            );
        });
    }

    public static function auditModule(): string
    {
        return Str::snake(Str::pluralStudly(class_basename(static::class)));
    }
}
