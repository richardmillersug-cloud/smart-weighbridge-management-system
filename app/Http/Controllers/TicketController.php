<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\WeighbridgeTicket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', WeighbridgeTicket::class);

        $tickets = WeighbridgeTicket::query()
            ->with(['customer', 'vehicle', 'driver', 'product', 'creator', 'invoice'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(fn ($q) => $q
                    ->where('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$search}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function show(WeighbridgeTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['customer', 'vehicle', 'driver', 'product', 'creator', 'completer', 'invoice.payments']);

        return view('tickets.show', compact('ticket'));
    }

    public function print(WeighbridgeTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['customer', 'vehicle', 'driver', 'product', 'creator', 'completer']);

        return view('tickets.print', compact('ticket'));
    }

    public function cancel(Request $request, WeighbridgeTicket $ticket, TicketService $tickets): RedirectResponse
    {
        $this->authorize('cancel', $ticket);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $tickets->cancel($ticket, $validated['reason']);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} cancelled.");
    }
}
