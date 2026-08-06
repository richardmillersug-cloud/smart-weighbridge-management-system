<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeightInvoice extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'ticket_id',
        'customer_id',
        'net_weight',
        'actual_weight',
        'rate',
        'amount',
        'status',
        'cancel_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'net_weight' => 'decimal:2',
            'actual_weight' => 'decimal:2',
            'rate' => 'decimal:4',
            'amount' => 'decimal:2',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(WeighbridgeTicket::class, 'ticket_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    protected function amountPaid(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->payments->sum('amount'));
    }

    protected function outstanding(): Attribute
    {
        return Attribute::get(fn (): float => max(0, (float) $this->amount - $this->amount_paid));
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::Pending);
    }
}
