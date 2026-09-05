<x-layouts.setup title="Station setup">
    @php
        $phpOk = $checks['php']['ok'];
        $extOk = $checks['extensions']['ok'];
        $mysqlOk = $checks['mysql']['ok'];
        $ready = $phpOk && $extOk;
    @endphp

    <div class="card mb-6 p-6">
        <h2 class="card-title mb-1">Before you continue</h2>
        <p class="mb-4 text-sm text-steel-400">This PC needs <strong class="text-steel-200">PHP 8.4+</strong> on PATH and <strong class="text-steel-200">MySQL 8</strong> running. The app creates the database and finishes setup for you.</p>

        <ul class="space-y-3 text-sm">
            <li class="flex items-start justify-between gap-4">
                <span class="text-steel-300">PHP {{ $checks['php']['version'] }}</span>
                <span class="badge {{ $phpOk ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/30' : 'bg-red-500/10 text-red-300 ring-red-500/30' }}">
                    {{ $phpOk ? 'Ready' : 'Install PHP 8.4+' }}
                </span>
            </li>
            <li class="flex items-start justify-between gap-4">
                <span class="text-steel-300">
                    PHP extensions
                    @if (! $extOk)
                        <span class="mt-1 block text-xs text-red-300">Missing: {{ implode(', ', $checks['extensions']['missing']) }}</span>
                    @endif
                </span>
                <span class="badge {{ $extOk ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/30' : 'bg-red-500/10 text-red-300 ring-red-500/30' }}">
                    {{ $extOk ? 'Ready' : 'Enable in php.ini' }}
                </span>
            </li>
            <li class="flex items-start justify-between gap-4">
                <span class="text-steel-300">MySQL at {{ $checks['mysql']['host'] }}:{{ $checks['mysql']['port'] }}</span>
                <span class="badge {{ $mysqlOk ? 'bg-emerald-500/10 text-emerald-300 ring-emerald-500/30' : 'bg-amber-500/10 text-amber-300 ring-amber-500/30' }}">
                    {{ $mysqlOk ? 'Running' : 'Start MySQL, then continue' }}
                </span>
            </li>
        </ul>
    </div>

    @if (! $ready)
        <div class="mb-6 rounded-lg border border-red-700/50 bg-red-950/50 px-4 py-3 text-sm text-red-200">
            Install PHP 8.4+ (add it to PATH) and enable the missing extensions. Restart this app when that is done.
        </div>
    @endif

    <form method="POST" action="{{ route('setup.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="card p-6">
            <h2 class="card-title mb-1">Local MySQL</h2>
            <p class="mb-4 text-sm text-steel-400">Use the root password you set when installing MySQL on this PC. The app will create the database if it does not exist.</p>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="db_host">Host</label>
                    <input id="db_host" name="db_host" type="text" value="{{ old('db_host', '127.0.0.1') }}" required class="input font-mono" @disabled(! $ready)>
                    @error('db_host') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="db_port">Port</label>
                    <input id="db_port" name="db_port" type="number" value="{{ old('db_port', 3306) }}" required class="input font-mono" @disabled(! $ready)>
                    @error('db_port') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="db_database">Database name</label>
                    <input id="db_database" name="db_database" type="text" value="{{ old('db_database', 'smart_weighbridge') }}" required class="input font-mono" @disabled(! $ready)>
                    @error('db_database') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="db_username">Username</label>
                    <input id="db_username" name="db_username" type="text" value="{{ old('db_username', 'root') }}" required class="input font-mono" @disabled(! $ready)>
                    @error('db_username') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="label" for="db_password">MySQL password</label>
                    <input id="db_password" name="db_password" type="password" required class="input" autocomplete="new-password" @disabled(! $ready)>
                    @error('db_password') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="card-title mb-1">Weighbridge scale</h2>
            <p class="mb-4 text-sm text-steel-400">COM port from Device Manager → Ports (COM &amp; LPT). You can change this later under Stations.</p>
            <div>
                <label class="label" for="com_port">COM port</label>
                <input id="com_port" name="com_port" type="text" value="{{ old('com_port', 'COM1') }}" required class="input font-mono uppercase" @disabled(! $ready)>
                @error('com_port') <p class="input-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card p-6">
            <h2 class="card-title mb-1">Cloud backup (DigitalOcean)</h2>
            <p class="mb-4 text-sm text-steel-400">Optional. Turn this on if your supplier gave you cloud database details. You can finish this later under Administration → Cloud Sync.</p>

            <label class="mb-5 flex items-center gap-2 text-sm text-steel-200">
                <input id="cloud_sync_enabled" name="cloud_sync_enabled" type="checkbox" value="1"
                       class="rounded border-steel-600 bg-carbon-900 text-brand-500 focus:ring-brand-500"
                       @checked(old('cloud_sync_enabled'))
                       @disabled(! $ready)
                       onchange="document.getElementById('cloud-fields').classList.toggle('hidden', !this.checked)">
                Enable cloud sync now
            </label>

            <div id="cloud-fields" class="grid grid-cols-1 gap-5 md:grid-cols-2 {{ old('cloud_sync_enabled') ? '' : 'hidden' }}">
                <div class="md:col-span-2">
                    <label class="label" for="db_cloud_host">Cloud host</label>
                    <input id="db_cloud_host" name="db_cloud_host" type="text" value="{{ old('db_cloud_host') }}" class="input font-mono" placeholder="your-cluster.db.ondigitalocean.com" @disabled(! $ready)>
                    @error('db_cloud_host') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="db_cloud_port">Cloud port</label>
                    <input id="db_cloud_port" name="db_cloud_port" type="number" value="{{ old('db_cloud_port', 25060) }}" class="input font-mono" @disabled(! $ready)>
                    @error('db_cloud_port') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="db_cloud_database">Cloud database</label>
                    <input id="db_cloud_database" name="db_cloud_database" type="text" value="{{ old('db_cloud_database', 'smart_weighbridge') }}" class="input font-mono" @disabled(! $ready)>
                    @error('db_cloud_database') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="db_cloud_username">Cloud username</label>
                    <input id="db_cloud_username" name="db_cloud_username" type="text" value="{{ old('db_cloud_username') }}" class="input font-mono" placeholder="doadmin" @disabled(! $ready)>
                    @error('db_cloud_username') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="db_cloud_password">Cloud password</label>
                    <input id="db_cloud_password" name="db_cloud_password" type="password" class="input" autocomplete="new-password" @disabled(! $ready)>
                    @error('db_cloud_password') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="label" for="cloud_ssl_ca">CA certificate (optional upload)</label>
                    <input id="cloud_ssl_ca" name="cloud_ssl_ca" type="file" accept=".crt,.pem" class="input" @disabled(! $ready)>
                    <p class="mt-2 text-xs text-steel-500">Or paste a full path if the file is already on this PC.</p>
                    <input id="db_cloud_ssl_ca" name="db_cloud_ssl_ca" type="text" value="{{ old('db_cloud_ssl_ca') }}" class="input mt-2 font-mono" placeholder="C:\Program Files\SmartWeighbridge\storage\certs\ca-certificate.crt" @disabled(! $ready)>
                    @error('cloud_ssl_ca') <p class="input-error">{{ $message }}</p> @enderror
                    @error('db_cloud_ssl_ca') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full" @disabled(! $ready)>Create database and finish setup</button>
    </form>
</x-layouts.setup>
