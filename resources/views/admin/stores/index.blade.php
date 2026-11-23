@extends('layouts.app')

@section('title', 'Manage Stores — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Store Management</h1>
            <p class="text-sm text-slate-500">Monitor participating convenience stores and mini-markets.</p>
        </div>

        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search store name or address..." class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm bg-white w-72">
            <button type="submit" class="rounded-lg bg-brand-teal px-4 py-1.5 text-sm font-semibold text-white">Search</button>
        </form>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Store</th>
                        <th class="px-4 py-3">Seller</th>
                        <th class="px-4 py-3">Coordinates</th>
                        <th class="px-4 py-3">Listings</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($stores as $store)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $store->name }}</p>
                                <p class="text-xs text-slate-500">{{ $store->address }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $store->seller->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $store->latitude }}, {{ $store->longitude }}</td>
                            <td class="px-4 py-3">{{ $store->listings_count }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $store->is_active ? 'bg-green-100 text-brand-dark' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $store->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.stores.toggle-status', $store) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-semibold {{ $store->is_active ? 'text-red-600' : 'text-brand-primary' }} hover:underline">
                                        {{ $store->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $stores->links() }}</div>
    </div>
@endsection
