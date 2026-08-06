<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeighbridgeStation extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'station_name',
        'indicator_model',
        'communication_type',
        'com_port',
        'baud_rate',
        'data_bits',
        'parity',
        'stop_bits',
        'flow_control',
        'status',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'baud_rate' => 'integer',
            'data_bits' => 'integer',
            'stop_bits' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(WeighbridgeTicket::class, 'station_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public static function defaultStation(): ?self
    {
        return static::query()->active()->where('is_default', true)->first()
            ?? static::query()->active()->orderBy('id')->first();
    }
}
