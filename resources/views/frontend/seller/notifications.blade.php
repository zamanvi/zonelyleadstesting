@extends('frontend.layouts.__prof_app')
@section('title', 'Notifications')
@section('content')
<div class="pb-10">
    <div class="max-w-2xl mx-auto py-6">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
                <p class="text-sm text-slate-500 mt-0.5">Stay updated on leads, bookings and payments</p>
            </div>
            @if(isset($notifications) && $notifications->count())
            <button onclick="markAllRead()" class="text-sm font-bold text-teal-700 hover:underline shrink-0">Mark all read</button>
            @endif
        </div>

        <div class="flex gap-2 mb-5 overflow-x-auto scroll-hide pb-1">
            <button onclick="filterNotifs(this,'all')" class="notif-tab active-ntab shrink-0 px-3 sm:px-4 py-2 rounded-xl text-xs font-bold bg-teal-700 text-white whitespace-nowrap">All</button>
            <button onclick="filterNotifs(this,'lead')" class="notif-tab shrink-0 px-3 sm:px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 whitespace-nowrap transition">New Leads</button>
            <button onclick="filterNotifs(this,'booking')" class="notif-tab shrink-0 px-3 sm:px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 whitespace-nowrap transition">Bookings</button>
            <button onclick="filterNotifs(this,'review')" class="notif-tab shrink-0 px-3 sm:px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 whitespace-nowrap transition">Reviews</button>
            <button onclick="filterNotifs(this,'payment')" class="notif-tab shrink-0 px-3 sm:px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 whitespace-nowrap transition">Payments</button>
        </div>

        <div class="space-y-2" id="notifList">
            @forelse($notifications ?? [] as $notif)
            <div class="notif-item bg-white rounded-2xl border {{ $notif->read_at ? 'border-slate-100' : 'border-teal-100' }} shadow-sm p-4 flex items-start gap-4"
                 data-type="{{ $notif->data['type'] ?? 'system' }}">
                @php
                    $iconMap = ['lead'=>'fa-user-plus text-emerald-600','booking'=>'fa-calendar text-teal-700','review'=>'fa-star text-amber-500','payment'=>'fa-credit-card text-purple-600'];
                    $bgMap   = ['lead'=>'bg-emerald-100','booking'=>'bg-teal-100','review'=>'bg-amber-100','payment'=>'bg-purple-100'];
                    $t = $notif->data['type'] ?? 'system';
                @endphp
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $bgMap[$t] ?? 'bg-slate-100' }}">
                    <i class="fa-solid {{ $iconMap[$t] ?? 'fa-bell text-slate-500' }} text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900">{{ $notif->data['title'] ?? 'Notification' }}</p>
                    <p class="text-sm text-slate-500 mt-0.5 leading-relaxed">{{ $notif->data['message'] ?? '' }}</p>
                    <p class="text-xs text-slate-400 mt-1.5">{{ $notif->created_at?->diffForHumans() }}</p>
                </div>
                @if(!$notif->read_at)
                <div class="w-2 h-2 bg-teal-700 rounded-full shrink-0 mt-1.5"></div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-teal-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-bell text-teal-400 text-2xl"></i>
                </div>
                <p class="font-bold text-slate-700 mb-1">No notifications yet</p>
                <p class="text-sm text-slate-400">You'll be notified here when you receive new leads, bookings, reviews, or payments.</p>
            </div>
            @endforelse
        </div>

    </div>
</div>

<script>
function filterNotifs(btn, type) {
    document.querySelectorAll('.notif-tab').forEach(b => {
        b.className = 'notif-tab shrink-0 px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 whitespace-nowrap transition';
    });
    btn.className = 'notif-tab active-ntab shrink-0 px-4 py-2 rounded-xl text-xs font-bold bg-teal-700 text-white whitespace-nowrap';
    document.querySelectorAll('.notif-item').forEach(el => {
        el.style.display = (type === 'all' || el.dataset.type === type) ? '' : 'none';
    });
}
function markAllRead() {
    document.querySelectorAll('.w-2.h-2.bg-teal-700.rounded-full').forEach(d => d.remove());
    fetch('/seller/notifications/read-all', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
    });
}
</script>
@endsection
