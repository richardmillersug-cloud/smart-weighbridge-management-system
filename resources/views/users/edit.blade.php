<x-layouts.app title="Edit User">
    <x-page-header :title="'Edit — '.$user->name" :subtitle="$user->email" />

    <div class="card max-w-3xl">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6 px-6 py-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="label" for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input">
                    @error('name') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input">
                    @error('email') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="phone">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="input">
                    @error('phone') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="role">Role</label>
                    <select id="role" name="role" required class="input" @disabled(! auth()->user()->can('users.assign-roles'))>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role', $user->getRoleNames()->first()) === $role->name)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="password">New password <span class="normal-case">(leave blank to keep current)</span></label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="input">
                    @error('password') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="input">
                </div>

                <div>
                    <label class="label" for="status">Status</label>
                    <select id="status" name="status" class="input">
                        <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                        <option value="disabled" @selected(old('status', $user->status) === 'disabled')>Disabled</option>
                    </select>
                    @error('status') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-steel-700/60 pt-5">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('users.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.app>
