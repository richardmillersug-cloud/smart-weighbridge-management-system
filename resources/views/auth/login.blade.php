<x-layouts.guest title="Sign in">
    <div class="card p-8">
        <h2 class="font-display text-lg font-semibold tracking-widest text-white uppercase">Operator Sign In</h2>
        <p class="mt-1 mb-6 text-sm text-steel-400">Enter your credentials to access the control panel.</p>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-700/50 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="label">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username" class="input" placeholder="operator@example.com">
                @error('email') <p class="input-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required
                       autocomplete="current-password" class="input" placeholder="••••••••">
                @error('password') <p class="input-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-steel-300">
                    <input type="checkbox" name="remember" class="rounded border-steel-600 bg-carbon-900 text-brand-500 focus:ring-brand-500">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-brand-400 hover:text-brand-300">Forgot password?</a>
            </div>

            <button type="submit" class="btn-primary w-full">Sign In</button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-steel-500">Authorized personnel only. All activity is logged.</p>
</x-layouts.guest>
