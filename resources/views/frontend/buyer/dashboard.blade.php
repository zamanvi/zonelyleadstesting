@extends('frontend.layouts._app')
@section('title', 'My Dashboard')

@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-24 px-4">
<div class="max-w-3xl mx-auto py-6">

    {{-- ── HEADER ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Hello, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <p class="text-sm text-slate-500 mt-0.5">Find and contact local experts near you</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs font-bold text-slate-400 hover:text-red-500 transition px-3 py-2 rounded-xl border border-slate-200 hover:border-red-200 bg-white">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
                </button>
            </form>
            <a href="{{ route('buyer.profile') }}"
               class="w-10 h-10 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold text-sm shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </a>
        </div>
    </div>

    {{-- ── SEARCH ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3 px-4 py-3.5 mb-6">
        <i class="fa-solid fa-magnifying-glass text-slate-300 shrink-0"></i>
        <input type="text" placeholder="Search for a service or professional..."
               class="flex-1 text-sm focus:outline-none text-slate-700 placeholder-slate-400 min-w-0"
               onkeydown="if(event.key==='Enter') window.location='{{ route('frontend.service.search') }}?q='+this.value">
        <a href="{{ route('frontend.service.all') }}" class="text-xs font-bold text-teal-700 whitespace-nowrap shrink-0">Browse all →</a>
    </div>

    {{-- ── STATS ── --}}
    <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-xl sm:text-2xl font-black text-teal-700">{{ $stats['bookings'] }}</p>
            <p class="text-[10px] sm:text-xs text-slate-500 font-semibold mt-0.5">Total Inquiries</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-xl sm:text-2xl font-black text-amber-500">{{ $stats['active'] }}</p>
            <p class="text-[10px] sm:text-xs text-slate-500 font-semibold mt-0.5">Active</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $stats['resolved'] }}</p>
            <p class="text-[10px] sm:text-xs text-slate-500 font-semibold mt-0.5">Resolved</p>
        </div>
    </div>

    {{-- ── ACTIVE INQUIRIES ── --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm mb-5">
        <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-900 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse inline-block"></span>
                Active Inquiries
            </h2>
            <span class="text-xs bg-amber-100 text-amber-700 font-bold px-2.5 py-1 rounded-full">{{ $activeLeads->count() }}</span>
        </div>

        @forelse($activeLeads as $lead)
        @php
            $statusColor = match($lead->status) {
                'new'     => 'bg-teal-100 text-teal-800',
                'pending' => 'bg-amber-100 text-amber-700',
                default   => 'bg-slate-100 text-slate-600',
            };
        @endphp
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-50 last:border-0">
            <img src="{{ asset($lead->seller?->profile_photo ?? '') }}" alt="{{ $lead->seller?->name }}"
                 class="w-11 h-11 rounded-xl object-cover border border-slate-100 shrink-0"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($lead->seller?->name ?? 'S') }}&background=2563eb&color=fff&size=80'">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm text-slate-900 truncate">{{ $lead->seller?->name ?? 'Professional' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ $lead->service ?? 'General Inquiry' }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $lead->created_at?->diffForHumans() }}</p>
            </div>
            <div class="text-right shrink-0 space-y-1">
                <span class="block text-[10px] font-bold px-2.5 py-1 rounded-full {{ $statusColor }}">
                    {{ ucfirst($lead->status) }}
                </span>
                @if($lead->seller?->slug)
                <a href="{{ route('frontend.service.show', $lead->seller->slug) }}"
                   class="block text-[10px] font-bold text-teal-700 hover:underline">
                    View seller →
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="p-10 text-center">
            <i class="fa-solid fa-bolt text-4xl text-slate-200 mb-3"></i>
            <p class="text-sm font-semibold text-slate-400">No active inquiries</p>
            <a href="{{ route('frontend.service.all') }}"
               class="inline-block mt-3 bg-teal-700 text-white font-bold px-5 py-2.5 rounded-2xl text-sm hover:bg-teal-800 transition">
                Find a Professional
            </a>
        </div>
        @endforelse
    </div>

    {{-- ── PAST / RESOLVED INQUIRIES ── --}}
    @if($resolvedLeads->count())
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm mb-5">
        <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-900">Past Inquiries</h2>
            <span class="text-xs bg-slate-100 text-slate-500 font-bold px-2.5 py-1 rounded-full">{{ $resolvedLeads->count() }}</span>
        </div>

        @foreach($resolvedLeads as $lead)
        @php
            $statusColor = match($lead->status) {
                'won'    => 'bg-emerald-100 text-emerald-700',
                'lost'   => 'bg-red-100 text-red-600',
                'closed' => 'bg-slate-100 text-slate-500',
                default  => 'bg-slate-100 text-slate-500',
            };
        @endphp
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-50 last:border-0">
            <img src="{{ asset($lead->seller?->profile_photo ?? '') }}" alt="{{ $lead->seller?->name }}"
                 class="w-11 h-11 rounded-xl object-cover border border-slate-100 shrink-0"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($lead->seller?->name ?? 'S') }}&background=64748b&color=fff&size=80'">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm text-slate-900 truncate">{{ $lead->seller?->name ?? 'Professional' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ $lead->service ?? 'General Inquiry' }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $lead->created_at?->format('M d, Y') }}</p>
            </div>
            <div class="text-right shrink-0 space-y-1">
                <span class="block text-[10px] font-bold px-2.5 py-1 rounded-full {{ $statusColor }}">
                    {{ ucfirst($lead->status) }}
                </span>
                @if($lead->fee)
                <p class="text-[10px] text-slate-400">${{ number_format($lead->fee, 0) }} fee</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── PENDING REVIEWS ── --}}
    @if($pendingReviews->count())
    <div class="bg-amber-50 rounded-3xl border border-amber-200 shadow-sm mb-5">
        <div class="px-5 pt-5 pb-4 border-b border-amber-200">
            <h2 class="font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-star text-amber-500 text-sm"></i> Leave a Review
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Share your experience with these professionals</p>
        </div>
        @foreach($pendingReviews as $booking)
        <div class="flex items-center gap-4 px-5 py-4 border-b border-amber-100 last:border-0">
            <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center shrink-0 font-bold text-amber-600 text-sm">
                {{ strtoupper(substr($booking->seller->name ?? 'PR', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm text-slate-900 truncate">{{ $booking->seller->name ?? 'Professional' }}</p>
                <p class="text-xs text-slate-500">{{ $booking->service ?? 'Service' }}</p>
            </div>
            <a href="{{ route('buyer.review', $booking->id) }}"
               class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl text-xs transition shrink-0">
                Review
            </a>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── AFFILIATE BANNER ── --}}
    <a href="{{ route('buyer.affiliate') }}"
       class="mb-3 bg-gradient-to-r from-teal-700 to-teal-800 rounded-2xl p-4 flex items-center justify-between text-white hover:from-teal-800 hover:to-teal-800 transition shadow-sm block">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-share-nodes text-white text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-sm">Earn $10 per referral</p>
                <p class="text-xs text-teal-200">Refer a business → they join → you earn</p>
            </div>
        </div>
        <span class="text-xs font-bold bg-white/20 px-3 py-1.5 rounded-xl whitespace-nowrap shrink-0">Refer Now →</span>
    </a>

    {{-- ── QUICK LINKS ── --}}
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('frontend.service.all') }}"
           class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 flex items-center gap-3 hover:border-teal-200 transition">
            <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-magnifying-glass text-teal-700 text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-900">Find Experts</p>
                <p class="text-xs text-slate-500 hidden sm:block">Browse all services</p>
            </div>
        </a>
        <a href="{{ route('buyer.profile') }}"
           class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 flex items-center gap-3 hover:border-teal-200 transition">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-user text-purple-600 text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-900">My Profile</p>
                <p class="text-xs text-slate-500 hidden sm:block">Edit your info</p>
            </div>
        </a>
        <a href="{{ route('buyer.notifications') }}"
           class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 flex items-center gap-3 hover:border-teal-200 transition">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-bell text-emerald-600 text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-900">Notifications</p>
                <p class="text-xs text-slate-500 hidden sm:block">Updates & alerts</p>
            </div>
        </a>
        <a href="{{ route('frontend.help') }}"
           class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 flex items-center gap-3 hover:border-teal-200 transition">
            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-question text-slate-500 text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-sm text-slate-900">Help & FAQ</p>
                <p class="text-xs text-slate-500 hidden sm:block">Get support</p>
            </div>
        </a>
    </div>

</div>
</div>
@endsection
