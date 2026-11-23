@extends('layouts.app')

@section('title', 'Notifications — RescueBite')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-brand-dark">Notifications</h1>
                <p class="text-sm text-slate-500">You have {{ $unreadCount }} unread notification(s).</p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-lg bg-brand-teal px-4 py-2 text-xs font-semibold text-white hover:bg-brand-dark transition">
                        Mark All as Read
                    </button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center border border-slate-200 shadow-sm">
                <p class="text-slate-600">You have no notifications yet.</p>
            </div>
        @else
            <div class="rounded-2xl bg-white border border-slate-200 shadow-sm divide-y divide-slate-100">
                @foreach ($notifications as $notif)
                    <div class="p-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 {{ $notif->isRead() ? 'bg-white' : 'bg-green-50/50' }}">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-600">
                                    {{ str_replace('_', ' ', $notif->type) }}
                                </span>
                                @unless ($notif->isRead())
                                    <span class="h-2 w-2 rounded-full bg-brand-primary"></span>
                                @endunless
                                <span class="text-xs text-slate-400">{{ $notif->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <h2 class="text-sm font-bold text-slate-800">{{ $notif->title }}</h2>
                            <p class="text-sm text-slate-600">{{ $notif->message }}</p>
                        </div>

                        @unless ($notif->isRead())
                            <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold text-brand-primary hover:underline whitespace-nowrap">
                                    Mark read
                                </button>
                            </form>
                        @endunless
                    </div>
                @endforeach
            </div>

            <div>{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
