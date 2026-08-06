<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'address',
        'status',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(WeighbridgeTicket::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(WeightInvoice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Generate the next sequential customer code, e.g. CUS-0001.
     */
    public static function nextCode(): string
    {
        $lastId = (int) static::withTrashed()->max('id');

        return sprintf('CUS-%04d', $lastId + 1);
    }
}
