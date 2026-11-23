@extends('layouts.app')

@section('title', 'Forgot Password — RescueBite')

@section('content')
    <div class="max-w-md mx-auto rounded-2xl bg-white p-8 border border-slate-200 shadow-sm">
        <h1 class="text-2xl font-bold text-brand-dark">Reset your password</h1>
        <p class="mt-1 text-sm text-slate-500">
            Enter your account email and we will generate a password reset link.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
            </div>

            <button type="submit" class="w-full rounded-lg bg-brand-primary py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                Send Password Reset Link
            </button>
        </form>

        <p class="mt-4 text-xs text-slate-500">
            Note: In local development (<code>MAIL_MAILER=log</code>), the reset link is written to <code>storage/logs/laravel.log</code>.
        </p>
    </div>
@endsection
