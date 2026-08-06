<x-layouts.app title="Reports">
    <x-page-header title="Reports" subtitle="Operational, financial and audit reporting" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['route' => 'reports.daily', 'title' => 'Daily Weighments', 'desc' => 'All weighings for a chosen day with gross, tare, net and actual totals.'],
            ['route' => 'reports.product', 'title' => 'Product Summary', 'desc' => 'Tickets and weight totals grouped by goods.'],
            ['route' => 'reports.customer', 'title' => 'Customer Summary', 'desc' => 'Tickets and weight totals grouped by customer.'],
            ['route' => 'reports.vehicle', 'title' => 'Vehicle Summary', 'desc' => 'Tickets and weight totals grouped by truck.'],
            ['route' => 'reports.operator', 'title' => 'Operator Performance', 'desc' => 'Tickets, invoices and collections per bridge handler.'],
            ['route' => 'reports.collections', 'title' => 'Daily Collections', 'desc' => 'Payments and cash sessions for a chosen day.'],
            ['route' => 'reports.outstanding', 'title' => 'Outstanding Invoices', 'desc' => 'Pending invoices awaiting payment.'],
            ['route' => 'reports.paid', 'title' => 'Paid Invoices', 'desc' => 'Settled invoices over a period.'],
            ['route' => 'reports.revenue-product', 'title' => 'Revenue by Product', 'desc' => 'Invoice revenue grouped by goods.'],
            ['route' => 'reports.invoice', 'title' => 'Invoice Report', 'desc' => 'All invoices issued over a period.'],
            ['route' => 'reports.payment', 'title' => 'Payment Report', 'desc' => 'Collections grouped by payment method.'],
            ['route' => 'reports.cancelled', 'title' => 'Cancelled Tickets', 'desc' => 'Cancelled weighbridge tickets and reasons.'],
            ['route' => 'reports.audit', 'title' => 'User Activities', 'desc' => 'Full audit trail filterable by user and module.'],
        ] as $report)
            <a href="{{ route($report['route']) }}" class="card group p-6 transition-colors hover:border-blue-500/50">
                <h3 class="font-display text-base font-semibold tracking-wide text-white uppercase group-hover:text-blue-300">{{ $report['title'] }}</h3>
                <p class="mt-2 text-sm text-steel-400">{{ $report['desc'] }}</p>
                <p class="mt-4 text-xs font-semibold text-blue-400 uppercase">Open report &rarr;</p>
            </a>
        @endforeach
    </div>
</x-layouts.app>
