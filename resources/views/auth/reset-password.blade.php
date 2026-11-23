@extends('layouts.app')

@section('title', 'Set New Password — RescueBite')

@section('content')
    <div class="max-w-md mx-auto rounded-2xl bg-white p-8 border border-slate-200 shadow-sm">
        <h1 class="text-2xl font-bold text-brand-dark">Choose a new password</h1>

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
            </div>

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

            <button type="submit" class="w-full rounded-lg bg-brand-primary py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                Reset Password
            </button>
        </form>
    </div>
@endsection
