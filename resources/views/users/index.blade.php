<x-layouts.app title="Users">
    <x-page-header title="Users &amp; Roles" subtitle="System accounts and access control">
        <x-slot:actions>
            @can('users.create')
                <a href="{{ route('users.create') }}" class="btn-primary">+ New User</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-steel-700/60 px-5 py-4">
            <div class="w-full sm:w-64">
                <label class="label" for="search">Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" class="input" placeholder="Name or email">
            </div>
            <div class="w-56">
                <label class="label" for="role">Role</label>
                <select id="role" name="role" class="input">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="font-semibold text-slate-100">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>
                                <span class="badge bg-brand-500/10 text-brand-400 ring-brand-500/30">{{ $user->getRoleNames()->first() ?? '—' }}</span>
                            </td>
                            <td><x-status-badge :status="$user->status" /></td>
                            <td class="text-xs text-steel-400">{{ $user->last_login_at?->format('d M Y H:i') ?? 'Never' }}</td>
                            <td class="text-right">
                                @can('users.edit')
                                    <a href="{{ route('users.edit', $user) }}" class="text-xs font-semibold text-brand-400 uppercase hover:text-brand-300">Edit</a>
                                @endcan
                                @can('disable', $user)
                                    <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline"
                                          onsubmit="return confirm('{{ $user->isActive() ? 'Disable' : 'Enable' }} this user?')">
                                        @csrf
                                        <button type="submit" class="ml-3 text-xs font-semibold uppercase {{ $user->isActive() ? 'text-red-400 hover:text-red-300' : 'text-emerald-400 hover:text-emerald-300' }}">
                                            {{ $user->isActive() ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state message="No users found." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $users->links() }}</div>
    </div>
</x-layouts.app>
