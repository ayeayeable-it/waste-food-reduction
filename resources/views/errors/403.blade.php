@extends('layouts.app')

@section('title', '403 Forbidden — RescueBite')

@section('content')
    <div class="max-w-lg mx-auto text-center rounded-2xl bg-white p-10 border border-slate-200 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-wider text-red-600">Error 403</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-800">Access Denied</h1>
        <p class="mt-3 text-sm text-slate-600">
            {{ $exception->getMessage() ?: 'You do not have permission to view this page.' }}
        </p>
        <div class="mt-6">
            <a href="{{ route('home') }}" class="inline-block rounded-lg bg-brand-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">
                Return to Home
            </a>
        </div>
    </div>
@endsection
