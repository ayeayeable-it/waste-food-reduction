@extends('layouts.app')

@section('title', 'My Profile — RescueBite')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Account Profile</h1>
            <p class="text-sm text-slate-500">Manage your personal details and account security.</p>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Profile Information</h2>
            <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-slate-500">Role: <strong class="uppercase text-brand-dark">{{ $user->role }}</strong></span>
                    <button type="submit" class="rounded-lg bg-brand-primary px-5 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Change Password</h2>
            <form method="POST" action="{{ route('profile.password') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700">Current Password</label>
                    <input id="current_password" name="current_password" type="password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">New Password</label>
                        <input id="password" name="password" type="password" required
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-brand-teal px-5 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
