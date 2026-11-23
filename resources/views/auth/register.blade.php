@extends('layouts.app')

@section('title', 'Create Account — RescueBite')

@section('content')
    <div class="max-w-lg mx-auto rounded-2xl bg-white p-8 border border-slate-200 shadow-sm">
        <h1 class="text-2xl font-bold text-brand-dark">Create your RescueBite account</h1>
        <p class="mt-1 text-sm text-slate-500">Join as a buyer to rescue discounted food or as a seller to list store surplus.</p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Account Role</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 rounded-lg border border-slate-300 p-3 cursor-pointer hover:border-brand-primary">
                        <input type="radio" name="role" value="buyer" {{ old('role', 'buyer') === 'buyer' ? 'checked' : '' }} class="text-brand-primary focus:ring-brand-primary">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Buyer</p>
                            <p class="text-xs text-slate-500">Reserve surplus food</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 rounded-lg border border-slate-300 p-3 cursor-pointer hover:border-brand-primary">
                        <input type="radio" name="role" value="seller" {{ old('role') === 'seller' ? 'checked' : '' }} class="text-brand-primary focus:ring-brand-primary">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Seller</p>
                            <p class="text-xs text-slate-500">List store surplus</p>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Full Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number (Optional)</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>
            </div>
            <p class="text-xs text-slate-500">Minimum 8 characters, including letters and numbers.</p>

            <button type="submit" class="w-full rounded-lg bg-brand-primary py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                Create Account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Already registered?
            <a href="{{ route('login') }}" class="font-semibold text-brand-primary hover:underline">Sign in</a>
        </p>
    </div>
@endsection
