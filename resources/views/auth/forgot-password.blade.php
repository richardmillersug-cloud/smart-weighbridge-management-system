<x-layouts.guest title="Forgot password">
    <div class="card p-8">
        <h2 class="font-display text-lg font-semibold tracking-widest text-white uppercase">Reset Password</h2>
        <p class="mt-1 mb-6 text-sm text-steel-400">
            Enter your email address and we will send you a password reset link.
        </p>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-700/50 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="label">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input">
                @error('email') <p class="input-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full">Email Reset Link</button>
        </form>

        <p class="mt-5 text-center">
            <a href="{{ route('login') }}" class="text-sm text-brand-400 hover:text-brand-300">&larr; Back to sign in</a>
        </p>
    </div>
</x-layouts.guest>
