@extends('layouts.app')

@section('title', 'Sign In — RescueBite')

@section('content')
    <div class="max-w-md mx-auto">
        <div class="rounded-2xl bg-white p-8 border border-slate-200 shadow-sm">
            <h1 class="text-2xl font-bold text-brand-dark">Sign in to RescueBite</h1>
            <p class="mt-1 text-sm text-slate-500">Access your buyer, seller, or administrator account.</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-teal hover:underline">
                            Forgot password?
                        </a>
                    </div>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
                    <label for="remember" class="text-sm text-slate-600">Remember me</label>
                </div>

                <button type="submit" class="w-full rounded-lg bg-brand-primary py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                    Sign In
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-brand-primary hover:underline">Register here</a>
            </p>
        </div>

        <div class="mt-4 rounded-xl bg-slate-100 p-4 text-xs text-slate-600 border border-slate-200">
            <p class="font-semibold text-slate-700">Seeded Demo Accounts (Password: <code>Password123!</code>)</p>
            <ul class="mt-1 space-y-0.5">
                <li>Buyer: <code>buyer1@rescuebite.local</code></li>
                <li>Seller: <code>seller1@rescuebite.local</code></li>
                <li>Admin: <code>admin@rescuebite.local</code></li>
            </ul>
        </div>
    </div>
@endsection
