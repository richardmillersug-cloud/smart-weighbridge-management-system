@props(['status'])

@php
    if ($status instanceof \App\Enums\TicketStatus || $status instanceof \App\Enums\InvoiceStatus) {
        $classes = $status->badgeClasses();
        $label = $status->label();
    } else {
        $label = ucfirst((string) $status);
        $classes = match ((string) $status) {
            'active' => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30',
            'inactive', 'disabled' => 'bg-red-500/10 text-red-400 ring-red-500/30',
            default => 'bg-steel-600/20 text-steel-300 ring-steel-500/40',
        };
    }
@endphp

<span {{ $attributes->merge(['class' => "badge {$classes}"]) }}>{{ $label }}</span>
