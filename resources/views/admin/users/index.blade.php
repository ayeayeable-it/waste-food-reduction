@extends('layouts.app')

@section('title', 'Manage Users — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">User Management</h1>
            <p class="text-sm text-slate-500">Manage buyer, seller, and administrator accounts.</p>
        </div>

        <form method="GET" class="rounded-xl bg-white p-4 border border-slate-200 flex flex-wrap gap-3 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            <select name="role" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All Roles</option>
                @foreach (['buyer', 'seller', 'admin'] as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:underline">Reset</a>
        </form>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                            <td class="px-4 py-3 capitalize">{{ $user->role }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-brand-dark' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Active' : 'Deactivated' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-semibold {{ $user->is_active ? 'text-red-600' : 'text-brand-primary' }} hover:underline">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Current User</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $users->links() }}</div>
    </div>
@endsection
