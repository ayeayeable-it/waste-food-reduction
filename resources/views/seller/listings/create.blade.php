@extends('layouts.app')

@section('title', 'Create Food Listing — RescueBite')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('seller.listings.index') }}" class="text-sm text-brand-teal hover:underline">&larr; Back to My Listings</a>
            <h1 class="mt-2 text-2xl font-bold text-brand-dark">Publish Surplus Food Listing</h1>
            <p class="text-sm text-slate-500">Provide accurate pricing, expiry labels, storage instructions, and allergen disclosures.</p>
        </div>

        <form method="POST" action="{{ route('seller.listings.store') }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 sm:p-8 border border-slate-200 shadow-sm space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="store_id" class="block text-sm font-medium text-slate-700">Store</label>
                    <select id="store_id" name="store_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-slate-700">Food Category</label>
                    <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="food_name" class="block text-sm font-medium text-slate-700">Food Name</label>
                <input id="food_name" name="food_name" type="text" value="{{ old('food_name') }}" required
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
                <textarea id="description" name="description" rows="3" required
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-slate-700">Food Photo (JPG, PNG, WEBP — Max 2MB)</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-green-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-dark hover:file:bg-green-100">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="original_price" class="block text-sm font-medium text-slate-700">Original Price ($)</label>
                    <input id="original_price" name="original_price" type="number" step="0.01" min="0.02" value="{{ old('original_price') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="discounted_price" class="block text-sm font-medium text-slate-700">Discounted Price ($)</label>
                    <input id="discounted_price" name="discounted_price" type="number" step="0.01" min="0.01" value="{{ old('discounted_price') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="available_quantity" class="block text-sm font-medium text-slate-700">Available Quantity</label>
                    <input id="available_quantity" name="available_quantity" type="number" min="1" value="{{ old('available_quantity', 1) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="expiry_label" class="block text-sm font-medium text-slate-700">Expiry / Date Label</label>
                    <input id="expiry_label" name="expiry_label" type="text" value="{{ old('expiry_label') }}" placeholder="e.g., Best Before: Today 22:00" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="collection_deadline" class="block text-sm font-medium text-slate-700">Collection Deadline</label>
                    <input id="collection_deadline" name="collection_deadline" type="datetime-local" value="{{ old('collection_deadline', now()->addHours(4)->format('Y-m-d\TH:i')) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label for="storage_instructions" class="block text-sm font-medium text-slate-700">Storage Instructions</label>
                <textarea id="storage_instructions" name="storage_instructions" rows="2" required
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('storage_instructions') }}</textarea>
            </div>

            <div>
                <label for="allergen_info" class="block text-sm font-medium text-slate-700">Allergen Information</label>
                <textarea id="allergen_info" name="allergen_info" rows="2" required placeholder="e.g., Contains wheat (gluten), milk, eggs. Or: None declared."
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('allergen_info') }}</textarea>
            </div>

            <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                <strong>Seller Responsibility:</strong> You are solely responsible for the accuracy of product details, allergen disclosures, expiry labels, and physical stock quantities.
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('seller.listings.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-brand-primary px-6 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                    Publish Listing
                </button>
            </div>
        </form>
    </div>
@endsection
