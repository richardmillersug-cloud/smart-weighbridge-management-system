<x-layouts.app title="New User">
    <x-page-header title="New User" subtitle="Create a system account and assign a role" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6 px-6 py-6">
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required class="input">
                    @error('name') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="input">
                    @error('email') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="phone">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="input">
                    @error('phone') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="role">Role</label>
                    <select id="role" name="role" required class="input">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
                    @error('password') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
                </div>

                <div>
                    <label class="label" for="status">Status</label>
                    <select id="status" name="status" class="input">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="disabled" @selected(old('status') === 'disabled')>Disabled</option>
                    </select>
                    @error('status') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Create User</button>
                <a href="{{ route('users.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
