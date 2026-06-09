@extends('frontend.layouts._app')
@section('title', 'My Dashboard')
@section('hideHeader')@endsection

@section('content')
<div class="min-h-screen bg-slate-50 pb-28">

    {{-- ── HERO HEADER ── --}}
    <div class="bg-gradient-to-br from-teal-800 via-teal-700 to-teal-600 pt-10 pb-10 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-teal-300 text-xs font-semibold uppercase tracking-widest mb-1">Welcome back</p>
                    <h1 class="text-2xl font-black text-white">{{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
                    <p class="text-teal-200 text-sm mt-0.5">Find and contact local experts near you</p>
                </div>
                <a href="{{ route('buyer.profile') }}"
                   class="w-14 h-14 rounded-2xl bg-white/20 border-2 border-white/30 text-white flex items-center justify-center font-black text-lg shrink-0 hover:bg-white/30 transition">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </a>
            </div>

            {{-- SEARCH --}}
            <div class="mt-5 bg-white rounded-2xl flex items-center gap-3 px-4 py-3 shadow-lg">
                <i class="fa-solid fa-magnifying-glass text-teal-400 shrink-0"></i>
                <input type="text" placeholder="Search for a service or professional..."
                       class="flex-1 text-sm focus:outline-none text-slate-700 placeholder-slate-400 min-w-0"
                       onkeydown="if(event.key==='Enter') window.location='{{ route('frontend.service.search') }}?q='+this.value">
                <a href="{{ route('frontend.service.all') }}" class="text-xs font-bold text-teal-700 whitespace-nowrap shrink-0 bg-teal-50 px-3 py-1.5 rounded-xl hover:bg-teal-100 transition">Browse all</a>
            </div>

            {{-- STATS --}}
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="bg-white/15 backdrop-blur rounded-2xl p-3 text-center border border-white/20">
                    <p class="text-2xl font-black text-white">{{ $stats['bookings'] }}</p>
                    <p class="text-[10px] text-teal-200 font-semibold mt-0.5">Total</p>
                </div>
                <div class="bg-white/15 backdrop-blur rounded-2xl p-3 text-center border border-white/20">
                    <p class="text-2xl font-black text-amber-300">{{ $stats['active'] }}</p>
                    <p class="text-[10px] text-teal-200 font-semibold mt-0.5">Active</p>
                </div>
                <div class="bg-white/15 backdrop-blur rounded-2xl p-3 text-center border border-white/20">
                    <p class="text-2xl font-black text-emerald-300">{{ $stats['resolved'] }}</p>
                    <p class="text-[10px] text-teal-200 font-semibold mt-0.5">Resolved</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 -mt-2 py-5 space-y-4">

        {{-- ── PENDING REVIEWS (high priority — shown first) ── --}}
        @if($pendingReviews->count())
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-3xl border border-amber-200 shadow-sm overflow-hidden">
            <div class="px-5 pt-5 pb-3 flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-star text-white text-sm"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Leave a Review</h2>
                    <p class="text-xs text-slate-500">Share your experience</p>
                </div>
                <span class="ml-auto text-xs bg-amber-400 text-white font-bold w-6 h-6 rounded-full flex items-center justify-center shrink-0">{{ $pendingReviews->count() }}</span>
            </div>
            @foreach($pendingReviews as $review)
            @php $sellerName = $review->lead?->seller?->name ?? $review->seller?->name ?? 'Professional'; @endphp
            <div class="flex items-center gap-3 px-5 py-3 border-t border-amber-100">
                <div class="w-10 h-10 bg-amber-200 rounded-xl flex items-center justify-center shrink-0 font-black text-amber-700 text-sm">
                    {{ strtoupper(substr($sellerName, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-slate-900 truncate">{{ $sellerName }}</p>
                    <p class="text-xs text-slate-500">{{ $review->lead?->service ?? 'Service' }}</p>
                </div>
                <a href="{{ route('buyer.review', $review->seller_id) }}"
                   class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-4 py-2 rounded-xl text-xs transition shrink-0 shadow-sm">
                    ⭐ Review
                </a>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── ACTIVE INQUIRIES ── --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse inline-block"></span>
                    <h2 class="font-bold text-slate-900">Active Inquiries</h2>
                </div>
                <span class="text-xs bg-teal-50 text-teal-700 font-bold px-3 py-1 rounded-full border border-teal-100">{{ $activeLeads->count() }}</span>
            </div>

            @forelse($activeLeads as $lead)
            @php
                $statusColor = match($lead->status) {
                    'new'     => 'bg-teal-100 text-teal-700 border border-teal-200',
                    'pending' => 'bg-amber-100 text-amber-700 border border-amber-200',
                    default   => 'bg-slate-100 text-slate-600 border border-slate-200',
                };
            @endphp
            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition">
                <img src="{{ asset($lead->seller?->profile_photo ?? '') }}" alt="{{ $lead->seller?->name }}"
                     class="w-12 h-12 rounded-2xl object-cover border border-slate-100 shrink-0 shadow-sm"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($lead->seller?->name ?? 'S') }}&background=0d9488&color=fff&size=80'">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-slate-900 truncate">{{ $lead->seller?->name ?? 'Professional' }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $lead->service ?? 'General Inquiry' }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $lead->created_at?->diffForHumans() }}</p>
                </div>
                <div class="text-right shrink-0 space-y-1.5">
                    <span class="block text-[10px] font-bold px-2.5 py-1 rounded-full {{ $statusColor }}">
                        {{ ucfirst($lead->status) }}
                    </span>
                    @if($lead->seller?->slug)
                    <a href="{{ route('frontend.service.show', $lead->seller->slug) }}"
                       class="block text-[10px] font-bold text-teal-700 hover:underline">
                        View Profile →
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-12 text-center px-6">
                <div class="w-16 h-16 bg-teal-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-magnifying-glass text-teal-400 text-2xl"></i>
                </div>
                <p class="font-bold text-slate-700 mb-1">No active inquiries</p>
                <p class="text-xs text-slate-400 mb-4">Find a professional and send your first inquiry</p>
                <a href="{{ route('frontend.service.all') }}"
                   class="inline-flex items-center gap-2 bg-teal-700 text-white font-bold px-6 py-2.5 rounded-2xl text-sm hover:bg-teal-800 transition shadow-sm">
                    <i class="fa-solid fa-search text-xs"></i> Find a Professional
                </a>
            </div>
            @endforelse
        </div>

        {{-- ── PAST INQUIRIES ── --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-400 text-sm"></i>
                    <h2 class="font-bold text-slate-900">Past Inquiries</h2>
                </div>
                <span class="text-xs bg-slate-100 text-slate-500 font-bold px-3 py-1 rounded-full">{{ $resolvedLeads->count() }}</span>
            </div>

            @forelse($resolvedLeads as $lead)
            @php
                $statusColor = match($lead->status) {
                    'won'    => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                    'lost'   => 'bg-red-100 text-red-600 border border-red-200',
                    'closed' => 'bg-slate-100 text-slate-500 border border-slate-200',
                    default  => 'bg-slate-100 text-slate-500 border border-slate-200',
                };
            @endphp
            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-50 last:border-0">
                <img src="{{ asset($lead->seller?->profile_photo ?? '') }}" alt="{{ $lead->seller?->name }}"
                     class="w-12 h-12 rounded-2xl object-cover border border-slate-100 shrink-0 shadow-sm grayscale opacity-70"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($lead->seller?->name ?? 'S') }}&background=94a3b8&color=fff&size=80'">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-slate-700 truncate">{{ $lead->seller?->name ?? 'Professional' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $lead->service ?? 'General Inquiry' }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $lead->created_at?->format('M d, Y') }}</p>
                </div>
                <div class="text-right shrink-0 space-y-1.5">
                    <span class="block text-[10px] font-bold px-2.5 py-1 rounded-full {{ $statusColor }}">
                        {{ ucfirst($lead->status) }}
                    </span>
                    @if($lead->fee)
                    <p class="text-[10px] text-slate-400">${{ number_format($lead->fee, 0) }} fee</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-10 text-center px-6">
                <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-200 mb-3 block"></i>
                <p class="text-sm text-slate-400 font-semibold">No past inquiries yet</p>
            </div>
            @endforelse
        </div>

        {{-- ── AFFILIATE BANNER ── --}}
        <a href="{{ route('buyer.affiliate') }}"
           class="bg-gradient-to-r from-teal-700 via-teal-600 to-emerald-600 rounded-3xl p-5 flex items-center justify-between text-white hover:opacity-95 transition shadow-md block overflow-hidden relative">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute -right-2 bottom-0 w-16 h-16 bg-white/5 rounded-full"></div>
            <div class="flex items-center gap-4 relative">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center shrink-0 border border-white/30">
                    <i class="fa-solid fa-share-nodes text-white text-base"></i>
                </div>
                <div>
                    <p class="font-black text-base">Earn {{ $commRate }} per referral</p>
                    <p class="text-xs text-teal-100 mt-0.5">Refer a business → they join → you earn</p>
                </div>
            </div>
            <span class="text-xs font-bold bg-white text-teal-700 px-3 py-2 rounded-xl whitespace-nowrap shrink-0 relative shadow-sm">Refer Now →</span>
        </a>

        {{-- ── QUICK LINKS ── --}}
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('frontend.service.all') }}"
               class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 hover:border-teal-200 hover:shadow-md transition group">
                <div class="w-11 h-11 bg-teal-50 rounded-2xl flex items-center justify-center shrink-0 group-hover:bg-teal-100 transition">
                    <i class="fa-solid fa-magnifying-glass text-teal-700 text-sm"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-sm text-slate-900">Find Experts</p>
                    <p class="text-xs text-slate-400">Browse services</p>
                </div>
            </a>
            <a href="{{ route('buyer.profile') }}"
               class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 hover:border-teal-200 hover:shadow-md transition group">
                <div class="w-11 h-11 bg-teal-50 rounded-2xl flex items-center justify-center shrink-0 group-hover:bg-teal-100 transition">
                    <i class="fa-solid fa-user text-teal-700 text-sm"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-sm text-slate-900">My Profile</p>
                    <p class="text-xs text-slate-400">Edit your info</p>
                </div>
            </a>
            <a href="{{ route('buyer.notifications') }}"
               class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 hover:border-teal-200 hover:shadow-md transition group">
                <div class="w-11 h-11 bg-teal-50 rounded-2xl flex items-center justify-center shrink-0 group-hover:bg-teal-100 transition">
                    <i class="fa-solid fa-bell text-teal-700 text-sm"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-sm text-slate-900">Notifications</p>
                    <p class="text-xs text-slate-400">Updates & alerts</p>
                </div>
            </a>
            <a href="{{ route('frontend.help') }}"
               class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 hover:border-teal-200 hover:shadow-md transition group">
                <div class="w-11 h-11 bg-teal-50 rounded-2xl flex items-center justify-center shrink-0 group-hover:bg-teal-100 transition">
                    <i class="fa-solid fa-circle-question text-teal-700 text-sm"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-sm text-slate-900">Help & FAQ</p>
                    <p class="text-xs text-slate-400">Get support</p>
                </div>
            </a>
        </div>

    </div>
</div>

{{-- ── STICKY BOTTOM NAV ── --}}
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 shadow-xl z-50 safe-area-pb">
    <div class="max-w-lg mx-auto flex items-center justify-around px-2 py-2">
        <a href="{{ route('buyer.dashboard') }}"
           class="flex flex-col items-center gap-0.5 px-4 py-2 rounded-2xl bg-teal-50 text-teal-700">
            <i class="fa-solid fa-house text-base"></i>
            <span class="text-[10px] font-bold">Home</span>
        </a>
        <a href="{{ route('frontend.service.all') }}"
           class="flex flex-col items-center gap-0.5 px-4 py-2 rounded-2xl text-slate-400 hover:text-teal-700 hover:bg-teal-50 transition">
            <i class="fa-solid fa-magnifying-glass text-base"></i>
            <span class="text-[10px] font-semibold">Explore</span>
        </a>
        <a href="{{ route('buyer.affiliate') }}"
           class="flex flex-col items-center gap-0.5 px-4 py-2 rounded-2xl text-slate-400 hover:text-teal-700 hover:bg-teal-50 transition">
            <i class="fa-solid fa-share-nodes text-base"></i>
            <span class="text-[10px] font-semibold">Refer</span>
        </a>
        <a href="{{ route('buyer.notifications') }}"
           class="flex flex-col items-center gap-0.5 px-4 py-2 rounded-2xl text-slate-400 hover:text-teal-700 hover:bg-teal-50 transition relative">
            <i class="fa-solid fa-bell text-base"></i>
            <span class="text-[10px] font-semibold">Alerts</span>
        </a>
        <a href="{{ route('buyer.profile') }}"
           class="flex flex-col items-center gap-0.5 px-4 py-2 rounded-2xl text-slate-400 hover:text-teal-700 hover:bg-teal-50 transition">
            <i class="fa-solid fa-user text-base"></i>
            <span class="text-[10px] font-semibold">Profile</span>
        </a>
    </div>
</nav>

@endsection
