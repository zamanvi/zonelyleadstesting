@extends('frontend.layouts._app')
@section('title', 'Notifications')
@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-16 px-4">
    <div class="max-w-2xl mx-auto py-6">

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('buyer.dashboard') ?? '#' }}" class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <h1 class="text-xl font-bold text-slate-900">Notifications</h1>
            </div>
            @if(isset($notifications) && $notifications->count())
            <button onclick="markAllRead()" class="text-xs font-bold text-teal-700 hover:underline">
                Mark all as read
            </button>
            @endif
        </div>

        {{-- Filter Tabs --}}
        <div class="flex gap-2 mb-5">
            <button onclick="filterNotifs(this,'all')" class="notif-tab active-ntab shrink-0 px-4 py-2 rounded-xl text-xs font-bold bg-teal-700 text-white">All</button>
            <button onclick="filterNotifs(this,'booking')" class="notif-tab shrink-0 px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Bookings</button>
            <button onclick="filterNotifs(this,'review')" class="notif-tab shrink-0 px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Reviews</button>
            <button onclick="filterNotifs(this,'system')" class="notif-tab shrink-0 px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Updates</button>
        </div>

        <div class="space-y-2" id="notifList">
            @forelse($notifications ?? [] as $notif)
            <div class="notif-item bg-white rounded-2xl border {{ $notif->read_at ? 'border-slate-100' : 'border-teal-100 bg-teal-50/30' }} shadow-sm p-4 flex items-start gap-4"
                 data-type="{{ $notif->data['type'] ?? 'system' }}">
                @php $nt = $notif->data['type'] ?? 'system'; @endphp
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                    {{ $nt === 'booking' ? 'bg-teal-100' : ($nt === 'review' ? 'bg-amber-100' : 'bg-slate-100') }}">
                    <i class="text-sm
                        {{ $nt === 'booking' ? 'fa-solid fa-calendar text-teal-700' : ($nt === 'review' ? 'fa-solid fa-star text-amber-500' : 'fa-solid fa-bell text-slate-500') }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900">{{ $notif->data['title'] ?? 'Notification' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $notif->data['message'] ?? '' }}</p>
                    <p class="text-[10px] text-slate-400 mt-1.5">{{ $notif->created_at?->diffForHumans() }}</p>
                </div>
                @if(!$notif->read_at)
                <div class="w-2 h-2 bg-teal-700 rounded-full shrink-0 mt-1.5"></div>
                @endif
            </div>
            @empty
            {{-- Demo notifications --}}
            @foreach([
                ['type'=>'booking','title'=>'Booking Confirmed','msg'=>'James Law Office confirmed your appointment on Apr 25 at 10:00 AM.','time'=>'2 hours ago','unread'=>true],
                ['type'=>'booking','title'=>'Booking Reminder','msg'=>'You have an appointment tomorrow with TK Tax Services at 2:00 PM. Don\'t forget!','time'=>'1 day ago','unread'=>true],
                ['type'=>'review','title'=>'Leave a Review','msg'=>'Your session with AllPro Plumbing is complete. Share your experience to help others.','time'=>'3 days ago','unread'=>false],
                ['type'=>'system','title'=>'Welcome to Zonely!','msg'=>'Your account is set up and ready. Find and book top professionals near you.','time'=>'1 week ago','unread'=>false],
            ] as $n)
            <div class="notif-item bg-white rounded-2xl border {{ $n['unread'] ? 'border-teal-100' : 'border-slate-100' }} shadow-sm p-4 flex items-start gap-4"
                 data-type="{{ $n['type'] }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                    {{ $n['type']==='booking' ? 'bg-teal-100' : ($n['type']==='review' ? 'bg-amber-100' : 'bg-slate-100') }}">
                    <i class="text-sm {{ $n['type']==='booking' ? 'fa-solid fa-calendar text-teal-700' : ($n['type']==='review' ? 'fa-solid fa-star text-amber-500' : 'fa-solid fa-bell text-slate-500') }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900">{{ $n['title'] }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $n['msg'] }}</p>
                    <p class="text-[10px] text-slate-400 mt-1.5">{{ $n['time'] }}</p>
                </div>
                @if($n['unread'])
                <div class="w-2 h-2 bg-teal-700 rounded-full shrink-0 mt-1.5"></div>
                @endif
            </div>
            @endforeach
            @endforelse
        </div>

    </div>
</div>

<script>
function filterNotifs(btn, type) {
    document.querySelectorAll('.notif-tab').forEach(b => {
        b.className = 'notif-tab shrink-0 px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition';
    });
    btn.className = 'notif-tab active-ntab shrink-0 px-4 py-2 rounded-xl text-xs font-bold bg-teal-700 text-white';
    document.querySelectorAll('.notif-item').forEach(el => {
        el.style.display = (type === 'all' || el.dataset.type === type) ? '' : 'none';
    });
}
function markAllRead() {
    document.querySelectorAll('.w-2.h-2.bg-teal-700').forEach(dot => dot.remove());
    fetch('/buyer/notifications/read-all', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }
    });
}
</script>
@endsection
