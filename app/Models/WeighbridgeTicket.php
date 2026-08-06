<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\WeighingMode;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeighbridgeTicket extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'station_id',
        'customer_id',
        'vehicle_id',
        'driver_id',
        'product_id',
        'weighing_mode',
        'supplier',
        'carrier',
        'origin',
        'destination',
        'goods_type',
        'gross_weight',
        'gross_captured_at',
        'tare_weight',
        'tare_captured_at',
        'net_weight',
        'deduction_percentage',
        'deduction_weight',
        'actual_weight',
        'unit_price',
        'total_amount',
        'weight_one',
        'weight_two',
        'simple_capture_count',
        'status',
        'remarks',
        'cancel_reason',
        'created_by',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'weighing_mode' => WeighingMode::class,
            'gross_weight' => 'decimal:2',
            'tare_weight' => 'decimal:2',
            'net_weight' => 'decimal:2',
            'deduction_percentage' => 'decimal:4',
            'deduction_weight' => 'decimal:2',
            'actual_weight' => 'decimal:2',
            'unit_price' => 'decimal:4',
            'total_amount' => 'decimal:2',
            'weight_one' => 'decimal:2',
            'weight_two' => 'decimal:2',
            'simple_capture_count' => 'integer',
            'gross_captured_at' => 'datetime',
            'tare_captured_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(WeighbridgeStation::class, 'station_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(WeightInvoice::class, 'ticket_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', TicketStatus::openStatuses());
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }
}
