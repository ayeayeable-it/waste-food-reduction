@extends('layouts.app')

@section('title', 'My Stores — RescueBite')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">My Stores</h1>
                <p class="text-sm text-slate-500">Manage your convenience store locations and pickup instructions.</p>
            </div>
            <a href="{{ route('seller.stores.create') }}" class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                + Register New Store
            </a>
        </div>

        @if ($stores->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center border border-slate-200 shadow-sm">
                <p class="text-slate-600">You have not registered any stores yet.</p>
                <a href="{{ route('seller.stores.create') }}" class="mt-4 inline-block rounded-lg bg-brand-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark">
                    Register Your First Store
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($stores as $store)
                    <div class="rounded-xl bg-white p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="text-lg font-bold text-slate-800">{{ $store->name }}</h2>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $store->is_active ? 'bg-green-100 text-brand-dark' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $store->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ $store->address }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                Coordinates: <code>{{ $store->latitude }}, {{ $store->longitude }}</code>
                            </p>
                            @if ($store->phone)
                                <p class="mt-1 text-xs text-slate-500">Phone: {{ $store->phone }}</p>
                            @endif
                            @if ($store->collection_instructions)
                                <p class="mt-3 rounded-lg bg-slate-50 p-3 text-xs text-slate-600 border border-slate-100">
                                    <strong>Pickup:</strong> {{ $store->collection_instructions }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                            <span class="text-xs text-slate-500">{{ $store->listings_count }} listing(s)</span>
                            <a href="{{ route('seller.stores.edit', $store) }}" class="font-semibold text-brand-teal hover:underline">
                                Edit Store
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $stores->links() }}</div>
        @endif
    </div>
@endsection
