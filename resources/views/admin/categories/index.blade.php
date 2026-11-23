@extends('layouts.app')

@section('title', 'Food Categories — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Food Category Management</h1>
            <p class="text-sm text-slate-500">Create, update, or remove surplus food categories.</p>
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}" class="rounded-2xl bg-white p-5 border border-slate-200 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-600">Category Name</label>
                <input type="text" name="name" required placeholder="e.g., Ready Meals" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-600">Description</label>
                <input type="text" name="description" placeholder="Short description..." class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                + Add Category
            </button>
        </form>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name &amp; Description</th>
                        <th class="px-4 py-3">Listings</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($categories as $category)
                        <tr>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex flex-col sm:flex-row gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}" required class="rounded-lg border border-slate-300 px-2.5 py-1 text-sm font-semibold">
                                    <input type="text" name="description" value="{{ $category->description }}" class="flex-1 rounded-lg border border-slate-300 px-2.5 py-1 text-xs text-slate-600">
                                    <button type="submit" class="rounded-lg bg-brand-teal px-3 py-1 text-xs font-semibold text-white">Save</button>
                                </form>
                            </td>
                            <td class="px-4 py-3">{{ $category->listings_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
