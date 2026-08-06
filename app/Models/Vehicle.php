<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'plate_number',
        'owner_name',
        'capacity',
        'preset_tare',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
            'preset_tare' => 'decimal:2',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(WeighbridgeTicket::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
