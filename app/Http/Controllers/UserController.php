<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($q) => $q->role($request->string('role')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'status' => $validated['status'],
        ]);

        $user->syncRoles([$validated['role']]);

        $this->audit->log('CREATE', 'users', $user->id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $validated['role'],
            'status' => $user->status,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', "User {$user->name} created.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $old = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->implode(', '),
            'status' => $user->status,
        ];

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        if ($request->user()->can('users.assign-roles')) {
            $user->syncRoles([$validated['role']]);
        }

        $this->audit->log('UPDATE', 'users', $user->id, $old, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $validated['role'],
            'status' => $user->status,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', "User {$user->name} updated.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('disable', $user);

        $newStatus = $user->isActive() ? User::STATUS_DISABLED : User::STATUS_ACTIVE;

        $this->audit->log(
            $newStatus === User::STATUS_DISABLED ? 'DISABLE' : 'ENABLE',
            'users',
            $user->id,
            ['status' => $user->status],
            ['status' => $newStatus],
        );

        $user->forceFill(['status' => $newStatus])->save();

        return redirect()
            ->route('users.index')
            ->with('success', "User {$user->name} ".($newStatus === User::STATUS_ACTIVE ? 'enabled' : 'disabled').'.');
    }
}
