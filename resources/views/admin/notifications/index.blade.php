@extends('layouts.app')

@section('title', 'Notification Management — RescueBite Admin')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-dark">Notification Management</h1>
            <p class="text-sm text-slate-500">Send platform announcements or collection reminders and audit sent alerts.</p>
        </div>

        <form method="POST" action="{{ route('admin.notifications.store') }}" class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm space-y-4">
            @csrf
            <h2 class="text-base font-bold text-slate-800">Send Broadcast Notification</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Target Audience</label>
                    <select name="target_role" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="all">All Active Users</option>
                        <option value="buyer">Buyers Only</option>
                        <option value="seller">Sellers Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Notification Type</label>
                    <select name="type" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="system_announcement">System Announcement</option>
                        <option value="collection_reminder">Collection Reminder</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Title</label>
                    <input type="text" name="title" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Message</label>
                <textarea name="message" rows="2" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            </div>
            <button type="submit" class="rounded-lg bg-brand-primary px-5 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                Send Notification
            </button>
        </form>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Recipient</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Title &amp; Message</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($notifications as $notif)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $notif->user->name }}</td>
                            <td class="px-4 py-3 uppercase text-slate-500">{{ str_replace('_', ' ', $notif->type) }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $notif->title }}</p>
                                <p class="text-slate-600">{{ $notif->message }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $notif->isRead() ? 'Read' : 'Unread' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $notif->created_at->format('M d, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $notifications->links() }}</div>
    </div>
@endsection
