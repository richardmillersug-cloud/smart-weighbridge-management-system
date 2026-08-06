<x-layouts.app title="System Settings">
    <x-page-header title="System Settings" subtitle="Company, weighing, documents, printing and security" />
    <x-flash />

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card p-6">
            <h3 class="card-title mb-4">Company</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="label" for="company_name">Name</label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name', $settings['company_name'] ?? '') }}" required class="input">
                </div>
                <div class="md:col-span-2">
                    <label class="label" for="company_address">Address</label>
                    <input id="company_address" name="company_address" type="text" value="{{ old('company_address', $settings['company_address'] ?? '') }}" class="input">
                </div>
                <div>
                    <label class="label" for="company_phone">Phone</label>
                    <input id="company_phone" name="company_phone" type="text" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" class="input">
                </div>
                <div>
                    <label class="label" for="company_email">Email</label>
                    <input id="company_email" name="company_email" type="email" value="{{ old('company_email', $settings['company_email'] ?? '') }}" class="input">
                </div>
                <div>
                    <label class="label" for="currency">Currency</label>
                    <input id="currency" name="currency" type="text" value="{{ old('currency', $settings['currency'] ?? 'USD') }}" required class="input uppercase">
                </div>
                <div>
                    <label class="label" for="company_logo">Logo path / URL</label>
                    <input id="company_logo" name="company_logo" type="text" value="{{ old('company_logo', $settings['company_logo'] ?? '') }}" class="input" placeholder="/storage/logo.png">
                </div>
            </div>
            <p class="mt-3 text-xs text-steel-400">Weighbridge hardware is managed under <a href="{{ route('stations.index') }}" class="text-blue-400 hover:underline">Stations</a> (COM port, baud, connection test).</p>
        </div>

        <div class="card p-6">
            <h3 class="card-title mb-4">Ticket Settings</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div>
                    <label class="label" for="ticket_prefix">Ticket prefix</label>
                    <input id="ticket_prefix" name="ticket_prefix" type="text" value="{{ old('ticket_prefix', $settings['ticket_prefix'] ?? 'WB') }}" required class="input font-mono uppercase">
                </div>
                <div>
                    <label class="label" for="invoice_prefix">Invoice prefix</label>
                    <input id="invoice_prefix" name="invoice_prefix" type="text" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}" required class="input font-mono uppercase">
                </div>
                <div>
                    <label class="label" for="receipt_prefix">Receipt prefix</label>
                    <input id="receipt_prefix" name="receipt_prefix" type="text" value="{{ old('receipt_prefix', $settings['receipt_prefix'] ?? 'RCP') }}" required class="input font-mono uppercase">
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="card-title mb-4">Weighing</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="weight_unit">Weight units</label>
                    <input id="weight_unit" name="weight_unit" type="text" value="{{ old('weight_unit', $settings['weight_unit'] ?? 'kg') }}" class="input">
                </div>
                <div>
                    <label class="label" for="deduction_enabled">Deduction enabled</label>
                    <select id="deduction_enabled" name="deduction_enabled" class="input">
                        <option value="1" @selected(old('deduction_enabled', $settings['deduction_enabled'] ?? '1') === '1')>Yes</option>
                        <option value="0" @selected(old('deduction_enabled', $settings['deduction_enabled'] ?? '1') === '0')>No</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="default_deduction_percent">Default deduction %</label>
                    <input id="default_deduction_percent" name="default_deduction_percent" type="number" step="0.01" min="0" max="100" value="{{ old('default_deduction_percent', $settings['default_deduction_percent'] ?? '0') }}" class="input">
                </div>
                <div>
                    <label class="label" for="allow_manual_weight">Allow manual weight</label>
                    <select id="allow_manual_weight" name="allow_manual_weight" class="input">
                        <option value="0" @selected(old('allow_manual_weight', $settings['allow_manual_weight'] ?? '0') === '0')>No</option>
                        <option value="1" @selected(old('allow_manual_weight', $settings['allow_manual_weight'] ?? '0') === '1')>Yes</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="stable_weight_timeout">Stable weight timeout (sec)</label>
                    <input id="stable_weight_timeout" name="stable_weight_timeout" type="number" min="1" max="60" value="{{ old('stable_weight_timeout', $settings['stable_weight_timeout'] ?? '5') }}" class="input">
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="card-title mb-4">Printing</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="ticket_template">Ticket template</label>
                    <input id="ticket_template" name="ticket_template" type="text" value="{{ old('ticket_template', $settings['ticket_template'] ?? 'default') }}" class="input">
                </div>
                <div>
                    <label class="label" for="invoice_template">Invoice template</label>
                    <input id="invoice_template" name="invoice_template" type="text" value="{{ old('invoice_template', $settings['invoice_template'] ?? 'default') }}" class="input">
                </div>
                <div>
                    <label class="label" for="receipt_template">Receipt template</label>
                    <input id="receipt_template" name="receipt_template" type="text" value="{{ old('receipt_template', $settings['receipt_template'] ?? 'default') }}" class="input">
                </div>
                <div>
                    <label class="label" for="default_printer">Printer selection</label>
                    <input id="default_printer" name="default_printer" type="text" value="{{ old('default_printer', $settings['default_printer'] ?? '') }}" class="input" placeholder="System default">
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="card-title mb-4">Security</h3>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="session_timeout_minutes">Session timeout (minutes)</label>
                    <input id="session_timeout_minutes" name="session_timeout_minutes" type="number" min="15" max="1440" value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? '120') }}" class="input">
                </div>
                <div>
                    <label class="label" for="password_min_length">Password min length</label>
                    <input id="password_min_length" name="password_min_length" type="number" min="6" max="64" value="{{ old('password_min_length', $settings['password_min_length'] ?? '8') }}" class="input">
                </div>
            </div>
            <p class="mt-3 text-xs text-steel-400">Role permissions are managed under Users &amp; Roles.</p>
        </div>

        <button type="submit" class="btn-primary">Save Settings</button>
    </form>
</x-layouts.app>
