<x-layouts.guest title="Reset password">
    <div class="card p-8">
        <h2 class="font-display text-lg font-semibold tracking-widest text-white uppercase">Choose New Password</h2>
        <p class="mt-1 mb-6 text-sm text-steel-400">Set a strong password for your account.</p>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="label">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="input">
                @error('email') <p class="input-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="label">New password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
                @error('password') <p class="input-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
            </div>

            <button type="submit" class="btn-primary w-full">Reset Password</button>
        </form>
    </div>
</x-layouts.guest>
