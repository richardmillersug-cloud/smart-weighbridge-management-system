<x-layouts.print :title="'Ticket '.$ticket->ticket_number">
    <div class="wb-ticket">
        <div class="wb-header">
            <h1 class="wb-company">{{ setting('company_name', config('app.name')) }}</h1>
            @if (setting('company_address'))
                <p class="wb-location">{{ setting('company_address') }}</p>
            @endif
            @if (setting('company_phone') || setting('company_email'))
                <p class="wb-contacts">
                    {{ setting('company_phone') }}
                    @if (setting('company_phone') && setting('company_email')) · @endif
                    {{ setting('company_email') }}
                </p>
            @endif
        </div>

        <div class="wb-meta">
            <span>Time: {{ now()->format('n/j/Y g:i:s A') }}</span>
            <span>No: {{ $ticket->ticket_number }}</span>
        </div>

        <table class="wb-grid">
            <colgroup>
                <col class="wb-label-column">
                <col class="wb-value-column">
                <col class="wb-label-column">
                <col class="wb-value-column">
            </colgroup>
            <tr>
                <td class="wb-label">Truck No:</td>
                <td class="wb-value">{{ $ticket->vehicle?->plate_number ?: '—' }}</td>
                <td class="wb-label">Gross:</td>
                <td class="wb-value">{{ $ticket->gross_weight !== null ? number_format((float) $ticket->gross_weight, 0) : '—' }} kg</td>
            </tr>
            <tr>
                <td class="wb-label">Goods Name:</td>
                <td class="wb-value">{{ $ticket->product?->name ?: '—' }}</td>
                <td class="wb-label">Tare:</td>
                <td class="wb-value">{{ $ticket->tare_weight !== null ? number_format((float) $ticket->tare_weight, 0) : '—' }} kg</td>
            </tr>
            <tr>
                <td class="wb-label">Supplier:</td>
                <td class="wb-value">{{ $ticket->supplier ?: '—' }}</td>
                <td class="wb-label">Net:</td>
                <td class="wb-value wb-net">{{ $ticket->net_weight !== null ? number_format((float) $ticket->net_weight, 0) : '—' }} kg</td>
            </tr>
            <tr>
                <td class="wb-label">Customer:</td>
                <td class="wb-value">{{ $ticket->customer?->name ?: '—' }}</td>
                <td class="wb-label">Gross Time:</td>
                <td class="wb-value wb-time">{{ $ticket->gross_captured_at?->format('d-m-Y H:i:s') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="wb-label">Carrier:</td>
                <td class="wb-value">{{ $ticket->carrier ?: '—' }}</td>
                <td class="wb-label">Tare Time:</td>
                <td class="wb-value wb-time">{{ $ticket->tare_captured_at?->format('d-m-Y H:i:s') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="wb-label">Driver:</td>
                <td class="wb-value">{{ $ticket->driver?->name ?: '—' }}</td>
                <td class="wb-label">Remarks:</td>
                <td class="wb-value">{{ $ticket->remarks ?: '—' }}</td>
            </tr>
            <tr>
                <td class="wb-label">&nbsp;</td>
                <td class="wb-value">&nbsp;</td>
                <td class="wb-label">Operator:</td>
                <td class="wb-value">{{ $ticket->creator?->name ?: '—' }}</td>
            </tr>
        </table>

        <div class="wb-signs">
            <div class="wb-signature">
                <div class="wb-signature-line"></div>
                <div class="wb-signature-label">Operator Signature</div>
            </div>
            <div class="wb-signature">
                <div class="wb-signature-line"></div>
                <div class="wb-signature-label">Driver's Sign</div>
            </div>
        </div>
    </div>

    <style>
        .wb-ticket {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            max-width: 780px;
            margin: 0 auto;
        }
        .wb-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .wb-company {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
        }
        .wb-location,
        .wb-contacts {
            margin: 2px 0 0;
            font-size: 12px;
            text-transform: uppercase;
        }
        .wb-meta {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            margin: 12px 0 8px;
        }
        .wb-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .wb-label-column { width: 18%; }
        .wb-value-column { width: 32%; }
        .wb-grid td {
            border: 1px solid #222;
            padding: 6px 8px;
            vertical-align: middle;
            min-height: 28px;
        }
        .wb-label {
            font-weight: 700;
            font-size: 12px;
            background: #fafafa;
        }
        .wb-value {
            font-size: 13px;
            font-weight: 600;
        }
        .wb-time {
            font-size: 11px;
            white-space: nowrap;
        }
        .wb-net {
            font-size: 14px;
            font-weight: 700;
        }
        .wb-signs {
            display: flex;
            justify-content: space-between;
            gap: 60px;
            margin-top: 55px;
        }
        .wb-signature {
            flex: 1;
            text-align: center;
        }
        .wb-signature-line {
            border-bottom: 1.5px solid #222;
            height: 1px;
        }
        .wb-signature-label {
            padding-top: 6px;
            font-size: 12px;
            font-weight: 700;
        }
        @media print {
            .wb-label { background: transparent !important; }
        }
    </style>
</x-layouts.print>
