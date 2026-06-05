@extends('frontend.layouts.__prof_app')
@section('title', 'Lead Dashboard')

@section('css')
<style>
    .bar { transition: height 0.4s ease; }
    .bar:hover { filter: brightness(1.1); }
    .pulse-dot { width:8px; height:8px; border-radius:50%; background:#10b981; animation:pulseDot 1.5s infinite; }
    @keyframes pulseDot { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.4;transform:scale(.8);} }
    .pill { display:inline-flex; align-items:center; gap:5px; padding:3px 11px; border-radius:999px; font-size:11px; font-weight:700; }
    .scroll-hide { -ms-overflow-style:none; scrollbar-width:none; }
    .scroll-hide::-webkit-scrollbar { display:none; }
    .won-active     { background:#059669!important; color:#fff!important; }
    .pending-active { background:#d97706!important; color:#fff!important; }
    .lost-active    { background:#dc2626!important; color:#fff!important; }
    audio { accent-color:#0D9488; }
    .lead-card { transition:transform .2s ease,box-shadow .2s ease; }
    .lead-card:hover { transform:translateY(-1px); box-shadow:0 8px 20px -4px rgba(0,0,0,.08); }
</style>
@endsection

@section('content')
@php
    $winRate   = $stats['total'] ? round($stats['won'] / $stats['total'] * 100) : 0;
    $circ      = 99.9;
    $wonArc    = $stats['total'] ? round($stats['won']    / $stats['total'] * $circ, 1) : 0;
    $pendArc   = $stats['total'] ? round($stats['pending']/ $stats['total'] * $circ, 1) : 0;
    $lostArc   = max(0, $circ - $wonArc - $pendArc);
    $wonOffset = 0;
    $pendOffset= -$wonArc;
    $lostOffset= -($wonArc + $pendArc);

    $wonLeads     = $leads->where('status','won');
    $pendLeads    = $leads->where('status','pending');
    $lostLeads    = $leads->where('status','lost');
    $totalBill    = $wonLeads->sum('fee');
    $pendBill     = $pendLeads->sum('fee');
    $avgFee       = $stats['total'] ? round(($totalBill + $pendBill) / $stats['total']) : 0;
    $currentMonth = now()->format('F Y');

    // Weekly lead counts (last 7 days Mon→Sun)
    $weekDays  = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $weekCounts= [];
    for ($i = 6; $i >= 0; $i--) {
        $d = now()->subDays($i);
        $weekCounts[] = $leads->filter(fn($l) => $l->created_at?->isSameDay($d))->count();
    }
    $maxCount = max(array_merge($weekCounts,[1]));

    $hasLeads = $leads->count() > 0;
    $unpaidCount = $leads->whereNull('paid_at')->count();
@endphp

<div id="toast" class="fixed bottom-24 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-sm font-semibold px-5 py-2.5 rounded-full opacity-0 pointer-events-none transition-all duration-300 z-50 whitespace-nowrap"></div>

<div class="pb-10">
<div class="max-w-3xl mx-auto px-4 py-6 lg:px-6 lg:py-8">

    {{-- ── HEADER ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 mb-0.5">
                <h1 class="text-xl font-bold text-slate-900">Lead Dashboard</h1>
                <span class="text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full font-bold">Twilio</span>
            </div>
            <p class="text-xs text-slate-500 flex items-center gap-1.5">
                <span class="pulse-dot"></span> Live
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('seller.schedule') }}" class="text-xs font-bold text-slate-500 border border-slate-200 bg-white px-3 py-2 rounded-xl hover:border-teal-300 hover:text-teal-700 transition">
                <i class="fa-solid fa-calendar-days mr-1"></i> Schedule
            </a>
            <div class="w-9 h-9 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold text-sm shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
        </div>
    </div>


    {{-- ── NEW LEAD ALERT ── --}}
    @if(session('new_lead'))
    <div id="newLeadBanner" class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3.5 mb-5 flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shrink-0">
            <i class="fa-solid fa-bell text-white text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-slate-800 text-sm">New lead just arrived!</p>
            <p class="text-emerald-700 text-sm truncate">{{ session('new_lead') }}</p>
        </div>
        <button onclick="document.getElementById('newLeadBanner').style.display='none'"
                class="shrink-0 text-xs bg-white border border-emerald-200 text-slate-600 px-3 py-1.5 rounded-xl font-semibold hover:bg-slate-50 transition">
            Dismiss
        </button>
    </div>
    @endif

    {{-- ── WELCOME ── --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Welcome back, {{ $user->name }}!</h2>
        <p class="text-sm text-slate-500 mt-0.5">
            <span class="font-semibold text-teal-700">{{ $stats['total'] }} leads this month</span>
            &nbsp;&bull;&nbsp;
            @if($user->status)
                {{ $user->title ?? 'Your page' }} is <span class="text-emerald-600 font-bold">live</span>
            @else
                Your page is <span class="text-amber-600 font-bold">pending admin review</span>
            @endif
            @if($unpaidCount > 0)
            &nbsp;&bull;&nbsp; <a href="{{ route('seller.billing') }}" class="text-red-500 font-semibold">{{ $unpaidCount }} unpaid →</a>
            @else
            &nbsp;&bull;&nbsp; Twilio connected
            @endif
        </p>
    </div>

    {{-- ── STATS ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-7">
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-black text-teal-700">{{ $stats['total'] }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Total Leads</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-black text-emerald-600">{{ $stats['won'] }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Won</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-black text-amber-500">{{ $stats['pending'] }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Pending</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
            <p class="text-3xl font-black text-slate-800">{{ $winRate }}%</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Win Rate</p>
        </div>
    </div>

    {{-- ── MY PAGE CARD ── --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-7">
        <div class="bg-teal-700 text-white px-5 py-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center font-black text-teal-700 text-base shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold leading-tight">{{ $user->title ?? $user->name }}</p>
                <p class="text-teal-200 text-sm mt-0.5 truncate">zonelyleads.com/{{ $user->slug }}</p>
            </div>
            <div class="flex flex-col items-end gap-1.5 shrink-0">
                @if($user->status)
                    <span class="text-xs bg-white/20 px-3 py-1 rounded-lg font-bold">LIVE</span>
                @else
                    <span class="text-xs bg-amber-400 text-amber-900 px-3 py-1 rounded-lg font-bold">Pending Review</span>
                @endif
                <span class="text-xs bg-emerald-500 px-3 py-1 rounded-lg font-bold flex items-center gap-1">
                    <span class="pulse-dot" style="width:6px;height:6px;background:white;"></span>Twilio
                </span>
            </div>
        </div>
        <div class="px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-5">
                <div>
                    <span class="text-2xl font-black text-emerald-600">{{ $stats['total'] }}</span>
                    <span class="text-sm text-slate-400 ml-1">leads</span>
                </div>
                <div class="w-px h-6 bg-slate-100"></div>
                <div>
                    <span class="text-2xl font-black text-slate-800">${{ number_format($totalBill + $pendBill) }}</span>
                    <span class="text-sm text-slate-400 ml-1">earned</span>
                </div>
            </div>
            <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}" target="_blank"
               class="text-sm font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1.5 transition">
                View Page <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            </a>
        </div>
    </div>

    {{-- ── ANALYTICS ── --}}
    <h3 class="font-bold text-base mb-4 flex items-center gap-2">
        <i class="fa-solid fa-chart-simple text-teal-700"></i> Analytics
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">

        {{-- Weekly Chart --}}
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm">
            <p class="font-semibold text-slate-700 text-sm">Leads This Week</p>
            <p class="text-xs text-slate-400 mt-0.5 mb-4">Daily call volume via Twilio</p>
            <div class="flex items-end gap-1.5 h-24">
                @foreach($weekCounts as $count)
                @php $pct = $maxCount > 0 ? round($count / $maxCount * 100) : 5; $pct = max($pct, 5); @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    <span class="text-[9px] text-slate-400 font-semibold">{{ $count }}</span>
                    <div class="bar w-full {{ $count === $maxCount && $count > 0 ? 'bg-emerald-500' : 'bg-teal-300' }} rounded-t-md" style="height:{{ $pct }}%"></div>
                </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 mt-2 font-medium">
                @foreach($weekDays as $d)<span>{{ $d }}</span>@endforeach
            </div>
        </div>

        {{-- Lead Sources --}}
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm">
            <p class="font-semibold text-slate-700 text-sm">Lead Sources</p>
            <p class="text-xs text-slate-400 mt-0.5 mb-4">Where your leads come from</p>
            <div class="space-y-3.5">
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-slate-600">Google Search</span>
                        <span class="font-bold text-slate-800">58%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-teal-600 to-teal-400" style="width:58%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-slate-600">Facebook Ads</span>
                        <span class="font-bold text-slate-800">25%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-pink-400" style="width:25%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-slate-600">Direct / Referral</span>
                        <span class="font-bold text-slate-800">17%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-400" style="width:17%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conversion Funnel --}}
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm">
            <p class="font-semibold text-slate-700 text-sm">Conversion Funnel</p>
            <p class="text-xs text-slate-400 mt-0.5 mb-4">Lead → client pipeline</p>
            @php
                $contacted = $stats['total'] > 0 ? $stats['total'] - $lostLeads->count() : 0;
                $contactedPct = $stats['total'] ? round($contacted / $stats['total'] * 100) : 0;
                $wonPctFunnel  = $stats['total'] ? round($stats['won'] / $stats['total'] * 100) : 0;
            @endphp
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 w-20 text-right shrink-0">{{ $stats['total'] }} calls</span>
                    <div class="flex-1 h-8 bg-teal-700 rounded-xl flex items-center px-3">
                        <span class="text-white text-xs font-bold">Leads Received</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 w-20 text-right shrink-0">{{ $contacted }} replied</span>
                    <div class="h-8 bg-teal-400 rounded-xl flex items-center px-3" style="flex:0 0 {{ max($contactedPct, 30) }}%">
                        <span class="text-white text-xs font-bold">Contacted</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 w-20 text-right shrink-0">{{ $stats['won'] }} closed</span>
                    <div class="h-8 bg-emerald-500 rounded-xl flex items-center px-3" style="flex:0 0 {{ max($wonPctFunnel, 20) }}%">
                        <span class="text-white text-xs font-bold">Won</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Win Rate Donut --}}
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="relative w-24 h-24 shrink-0">
                <svg viewBox="0 0 36 36" class="w-24 h-24 -rotate-90">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f1f5f9" stroke-width="3.5"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#0D9488" stroke-width="3.5"
                        stroke-dasharray="{{ $wonArc }} {{ $circ - $wonArc }}" stroke-dashoffset="{{ $wonOffset }}" stroke-linecap="round"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f59e0b" stroke-width="3.5"
                        stroke-dasharray="{{ $pendArc }} {{ $circ - $pendArc }}" stroke-dashoffset="{{ $pendOffset }}" stroke-linecap="round"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#ef4444" stroke-width="3.5"
                        stroke-dasharray="{{ $lostArc }} {{ $circ - $lostArc }}" stroke-dashoffset="{{ $lostOffset }}" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-lg font-black text-slate-800 leading-none">{{ $winRate }}%</span>
                    <span class="text-[9px] text-slate-400 font-medium mt-0.5">Win Rate</span>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-teal-600 shrink-0"></div>
                    <span class="text-xs text-slate-600">Won — {{ $wonArc > 0 ? round($wonArc/$circ*100) : 0 }}%</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0"></div>
                    <span class="text-xs text-slate-600">Pending — {{ $pendArc > 0 ? round($pendArc/$circ*100) : 0 }}%</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-red-400 shrink-0"></div>
                    <span class="text-xs text-slate-600">Lost — {{ $lostArc > 0 ? round($lostArc/$circ*100) : 0 }}%</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── LEAD MANAGEMENT ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <h3 class="font-bold text-base flex items-center gap-2">
            <i class="fa-solid fa-list-check text-teal-700"></i>
            Lead Management
            <span class="text-[10px] bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full font-bold">Twilio Synced</span>
        </h3>
        <div class="flex items-center gap-2">
            <div class="relative">
                <input id="searchInput" type="text" placeholder="Search leads..."
                       oninput="searchLeads()"
                       class="pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm w-full sm:w-44 focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-sm pointer-events-none"></i>
            </div>
            <button onclick="exportLeads()"
                    class="bg-white border border-slate-200 hover:bg-slate-50 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-download text-teal-700"></i> CSV
            </button>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2 mb-5 overflow-x-auto scroll-hide pb-1">
        <button onclick="filterLeads(this,'all')"
                class="filter-btn active px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap bg-teal-700 text-white">
            All ({{ $stats['total'] }})
        </button>
        <button onclick="filterLeads(this,'pending')"
                class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
            Pending ({{ $stats['pending'] }})
        </button>
        <button onclick="filterLeads(this,'won')"
                class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
            Won ({{ $stats['won'] }})
        </button>
        <button onclick="filterLeads(this,'lost')"
                class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
            Lost ({{ $leads->where('status','lost')->count() }})
        </button>
    </div>

    {{-- Lead Cards --}}
    <div class="space-y-4 mb-8" id="leadsList">

        @forelse($leads as $lead)
        @php
            $lStatus = $lead->status ?? 'pending';
            $lPaid   = !is_null($lead->paid_at);
            $iconBg  = $lStatus==='won' ? 'bg-emerald-100' : ($lStatus==='lost' ? 'bg-red-100' : 'bg-amber-100');
            $iconClr = $lStatus==='won' ? 'text-emerald-600' : ($lStatus==='lost' ? 'text-red-400' : 'text-amber-600');
            $feeBg   = $lStatus==='won' ? 'bg-emerald-100 text-emerald-700' : ($lStatus==='lost' ? 'bg-slate-100 text-slate-400' : 'bg-amber-100 text-amber-700');
            $borderClr = $lStatus==='pending' ? 'border-amber-100' : 'border-slate-100';
            $wonBtn  = $lStatus==='won'     ? 'won-active'     : 'bg-slate-100 text-slate-500 hover:bg-emerald-100 hover:text-emerald-700 transition';
            $pendBtn = $lStatus==='pending' ? 'pending-active' : 'bg-slate-100 text-slate-500 hover:bg-amber-100 hover:text-amber-700 transition';
            $lostBtn = $lStatus==='lost'    ? 'lost-active'    : 'bg-slate-100 text-slate-500 hover:bg-red-100 hover:text-red-600 transition';
        @endphp
        <div class="lead-card bg-white rounded-3xl p-5 border {{ $borderClr }} shadow-sm {{ $lStatus==='lost' ? 'opacity-60' : '' }}"
             data-status="{{ $lStatus }}" data-id="{{ $lead->id }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-11 h-11 {{ $iconBg }} rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-phone {{ $iconClr }}"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900">{{ $lead->phone ?? 'Unknown' }}</p>
                        <p class="text-sm text-slate-500">{{ $lead->service ?? 'General Inquiry' }}</p>
                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                            <span class="text-[11px] text-slate-400">{{ $lead->created_at?->format('M d · g:i A') }}</span>
                            @if($lPaid)
                            <span class="text-[11px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-semibold">Paid</span>
                            @else
                            <span class="text-[11px] bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full font-semibold">Unpaid</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2 shrink-0">
                    @if($lead->fee)
                    <span class="{{ $feeBg }} font-bold px-3 py-1 rounded-xl text-sm">${{ number_format($lead->fee) }}</span>
                    @endif
                    <span class="pill {{ $lStatus==='won' ? 'bg-emerald-50 text-emerald-700' : ($lStatus==='lost' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">
                        <i class="fa-solid {{ $lStatus==='won' ? 'fa-circle-check' : ($lStatus==='lost' ? 'fa-circle-xmark' : 'fa-clock') }} text-xs"></i>
                        {{ ucfirst($lStatus) }}
                    </span>
                </div>
            </div>

            @if($lead->message || $lead->notes)
            <p class="mt-3 text-xs text-slate-500 bg-slate-50 rounded-xl px-4 py-2">
                "{{ Str::limit($lead->message ?? $lead->notes, 100) }}"
            </p>
            @endif

            <input type="text" placeholder="Add a note..." value="{{ $lead->notes ?? '' }}"
                   onblur="saveNote(this, {{ $lead->id }})"
                   class="mt-3 w-full text-sm bg-slate-50 border border-slate-100 rounded-xl px-4 py-2.5 focus:border-teal-300 focus:bg-white transition">

            <div class="grid grid-cols-4 gap-2 mt-3">
                <button onclick="setStatus(this,'won')"     class="action-btn {{ $wonBtn }}  py-2 rounded-xl text-xs font-bold">Won</button>
                <button onclick="setStatus(this,'pending')" class="action-btn {{ $pendBtn }} py-2 rounded-xl text-xs font-bold">Pending</button>
                <button onclick="setStatus(this,'lost')"    class="action-btn {{ $lostBtn }} py-2 rounded-xl text-xs font-bold">Lost</button>
                <a href="tel:{{ $lead->phone }}" class="py-2 rounded-xl text-xs font-bold bg-teal-50 text-teal-700 hover:bg-teal-100 transition text-center">
                    <i class="fa-solid fa-phone mr-1"></i>Call
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl border-2 border-dashed border-slate-200 p-10 text-center">
            <div class="w-16 h-16 bg-teal-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-inbox text-teal-400 text-2xl"></i>
            </div>
            <p class="font-bold text-slate-700 text-base mb-1">No leads yet</p>
            <p class="text-sm text-slate-400 mb-5 max-w-xs mx-auto">When clients call, message, or fill your booking form — leads appear here.</p>
            <a href="{{ route('frontend.service.show', auth()->user()->slug ?? auth()->user()->id) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white font-bold px-5 py-2.5 rounded-2xl text-sm transition">
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> View Your Public Page
            </a>
        </div>
        @endforelse

    </div>

    {{-- ── LEAD CONVERSATIONS ── --}}
    <div class="mb-8" id="chatSection">
        <h3 class="font-bold text-base mb-1 flex items-center gap-2">
            <i class="fa-solid fa-comments text-teal-700"></i> Lead Conversations
            @if($unpaidCount > 0)
            <span class="bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full">{{ $unpaidCount }} unpaid</span>
            @endif
        </h3>
        <p class="text-xs text-slate-400 mb-4">Messages from leads via Twilio / WhatsApp</p>

        <div class="flex gap-2 mb-3 overflow-x-auto scroll-hide pb-1">
            <button onclick="filterChat(this,'recent')" class="chat-tab active-chat-tab px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-teal-700 text-white">Recent</button>
            <button onclick="filterChat(this,'week')"   class="chat-tab px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">This Week</button>
            <button onclick="filterChat(this,'paid')"   class="chat-tab px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                <i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i>Paid
            </button>
            <button onclick="filterChat(this,'unpaid')" class="chat-tab px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-white border border-amber-200 text-amber-600 hover:bg-amber-50 transition">
                <i class="fa-solid fa-clock text-amber-500 mr-1"></i>Unpaid
            </button>
        </div>

        <div id="chatPayNote" class="hidden bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-3 text-xs text-amber-800">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
            <strong>Unpaid leads</strong> = Zonely sent you this lead but you haven't paid the platform fee yet.
            <a href="{{ route('seller.billing') }}" class="font-bold underline ml-1">Pay now →</a>
        </div>

        <div class="space-y-3" id="chatList">

            @forelse($leads->take(6) as $lead)
            @php
                $lPaid   = !is_null($lead->paid_at);
                $filter  = 'recent week ' . ($lPaid ? 'paid' : 'unpaid');
                $initials= strtoupper(substr($lead->phone ?? 'LD', -2));
                $bgAvatar= $lPaid ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600';
            @endphp
            <div class="chat-item bg-white rounded-2xl border {{ $lPaid ? 'border-slate-100' : 'border-amber-200' }} shadow-sm overflow-hidden"
                 data-chat-filter="{{ $filter }}">
                <div class="flex items-center gap-3 px-4 py-3.5 cursor-pointer hover:{{ $lPaid ? 'bg-slate-50' : 'bg-amber-50' }} transition"
                     onclick="toggleChat(this)">
                    <div class="w-10 h-10 {{ $bgAvatar }} rounded-xl flex items-center justify-center shrink-0 font-black text-sm">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-sm text-slate-900 truncate">{{ $lead->phone ?? 'Unknown' }}</p>
                            <span class="text-[10px] text-slate-400 shrink-0">{{ $lead->created_at?->format('M d g:i A') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p class="text-xs text-slate-500 truncate">{{ $lead->service ?? 'Inquiry' }} — "{{ Str::limit($lead->message ?? 'No message', 35) }}"</p>
                            <span class="shrink-0 text-[10px] {{ $lPaid ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} px-2 py-0.5 rounded-full font-bold">
                                {{ $lPaid ? 'Paid' : 'Unpaid' }}
                            </span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down text-slate-300 text-xs shrink-0 chat-chevron transition-transform"></i>
                </div>
                <div class="chat-thread hidden border-t {{ $lPaid ? 'border-slate-100 bg-slate-50' : 'border-amber-100 bg-amber-50' }} px-4 py-4 space-y-3">
                    @if(!$lPaid)
                    <div class="bg-amber-100 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3">
                        <i class="fa-solid fa-lock text-amber-600 mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-xs font-bold text-amber-800">Platform fee unpaid{{ $lead->fee ? ' ($' . number_format($lead->fee) . ')' : '' }}</p>
                            <p class="text-xs text-amber-700 mt-0.5">Pay Zonely to unlock full chat & keep receiving leads.</p>
                            <a href="{{ route('seller.billing') }}"
                               class="mt-2 inline-block bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">
                                Pay Now
                            </a>
                        </div>
                    </div>
                    @endif
                    @if($lead->message)
                    <div class="flex justify-start">
                        <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-4 py-2.5 max-w-[80%]">
                            <p class="text-xs text-slate-500 font-semibold mb-1">Lead · {{ $lead->created_at?->format('g:i A') }}</p>
                            <p class="text-sm text-slate-800">{{ $lead->message }}</p>
                        </div>
                    </div>
                    @endif
                    @if($lPaid)
                    <div class="flex gap-2 pt-1">
                        @if($lead->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', $lead->phone) }}"
                           target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 rounded-xl text-xs transition">
                            <i class="fab fa-whatsapp text-sm"></i> Reply on WhatsApp
                        </a>
                        <a href="tel:{{ $lead->phone }}"
                           class="flex items-center justify-center gap-2 bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold px-4 py-2.5 rounded-xl text-xs transition">
                            <i class="fa-solid fa-phone text-xs"></i> Call
                        </a>
                        @else
                        <p class="text-xs text-slate-400 italic pt-1">No phone number available for this lead.</p>
                        @endif
                    </div>
                    @else
                    <div class="flex gap-2 pt-1 opacity-40 pointer-events-none select-none">
                        <input type="text" placeholder="Pay to unlock reply..." class="flex-1 text-sm bg-white border border-slate-200 rounded-xl px-4 py-2.5">
                        <button class="bg-slate-300 text-white px-4 py-2.5 rounded-xl text-xs font-bold shrink-0">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            {{-- Demo chat items when no real data --}}
            @foreach([
                ['init'=>'TK','phone'=>'+1 (347) 555-1289','service'=>'Tax Preparation','preview'=>'I need help filing my taxes','time'=>'Today 11:32 AM','filter'=>'recent week paid','paid'=>true,'fee'=>null,'msgs'=>[
                    ['side'=>'lead','time'=>'11:32 AM','text'=>'Hi, I need help filing my taxes for this year. Do you handle small business?'],
                    ['side'=>'you','time'=>'11:45 AM','text'=>"Yes! We specialize in small business. I'll send you our package options."],
                    ['side'=>'lead','time'=>'11:50 AM','text'=>'Great, looking forward to it. Can we schedule a call?'],
                ]],
                ['init'=>'IR','phone'=>'+1 (929) 555-7812','service'=>'IRS Audit Assistance','preview'=>'I received an IRS notice','time'=>'Apr 18 9:05 AM','filter'=>'recent week unpaid','paid'=>false,'fee'=>120,'msgs'=>[
                    ['side'=>'lead','time'=>'9:05 AM','text'=>'I received an IRS notice last week. Can you help me?'],
                ]],
                ['init'=>'LC','phone'=>'+1 (914) 555-9900','service'=>'LLC Formation','preview'=>'Need to form an LLC','time'=>'Apr 17 4:48 PM','filter'=>'week unpaid','paid'=>false,'fee'=>200,'msgs'=>[
                    ['side'=>'lead','time'=>'4:48 PM','text'=>'I want to form an LLC and need help with S-Corp election.'],
                ]],
            ] as $chat)
            <div class="chat-item bg-white rounded-2xl border {{ $chat['paid'] ? 'border-slate-100' : 'border-amber-200' }} shadow-sm overflow-hidden"
                 data-chat-filter="{{ $chat['filter'] }}">
                <div class="flex items-center gap-3 px-4 py-3.5 cursor-pointer hover:{{ $chat['paid'] ? 'bg-slate-50' : 'bg-amber-50' }} transition"
                     onclick="toggleChat(this)">
                    <div class="w-10 h-10 {{ $chat['paid'] ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} rounded-xl flex items-center justify-center shrink-0 font-black text-sm">
                        {{ $chat['init'] }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-sm text-slate-900 truncate">{{ $chat['phone'] }}</p>
                            <span class="text-[10px] text-slate-400 shrink-0">{{ $chat['time'] }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <p class="text-xs text-slate-500 truncate">{{ $chat['service'] }} — "{{ $chat['preview'] }}"</p>
                            <span class="shrink-0 text-[10px] {{ $chat['paid'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} px-2 py-0.5 rounded-full font-bold">
                                {{ $chat['paid'] ? 'Paid' : 'Unpaid' }}
                            </span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-down text-slate-300 text-xs shrink-0 chat-chevron transition-transform"></i>
                </div>
                <div class="chat-thread hidden border-t {{ $chat['paid'] ? 'border-slate-100 bg-slate-50' : 'border-amber-100 bg-amber-50' }} px-4 py-4 space-y-3">
                    @if(!$chat['paid'])
                    <div class="bg-amber-100 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3">
                        <i class="fa-solid fa-lock text-amber-600 mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-xs font-bold text-amber-800">Platform fee unpaid{{ $chat['fee'] ? ' ($'.$chat['fee'].')' : '' }}</p>
                            <p class="text-xs text-amber-700 mt-0.5">Pay Zonely to unlock full chat & keep receiving leads.</p>
                            <a href="{{ route('seller.billing') }}"
                               class="mt-2 inline-block bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition">
                                Pay {{ $chat['fee'] ? '$'.$chat['fee'] : '' }} Now
                            </a>
                        </div>
                    </div>
                    @endif
                    @foreach($chat['msgs'] as $msg)
                    <div class="flex {{ $msg['side']==='you' ? 'justify-end' : 'justify-start' }}">
                        <div class="{{ $msg['side']==='you' ? 'bg-teal-700 rounded-2xl rounded-tr-sm' : 'bg-white border border-slate-200 rounded-2xl rounded-tl-sm' }} px-4 py-2.5 max-w-[80%]">
                            <p class="text-xs {{ $msg['side']==='you' ? 'text-teal-200' : 'text-slate-500' }} font-semibold mb-1">{{ $msg['side']==='you' ? 'You' : 'Lead' }} · {{ $msg['time'] }}</p>
                            <p class="text-sm {{ $msg['side']==='you' ? 'text-white' : 'text-slate-800' }}">{{ $msg['text'] }}</p>
                        </div>
                    </div>
                    @endforeach
                    @if($chat['paid'])
                    <div class="flex gap-2 pt-1">
                        <input type="text" placeholder="Reply via WhatsApp / SMS..."
                               class="flex-1 text-sm bg-white border border-slate-200 rounded-xl px-4 py-2.5 focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
                        <button onclick="showToast('Message sent via Twilio!')"
                                class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shrink-0">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                    @else
                    <div class="flex gap-2 pt-1 opacity-40 pointer-events-none select-none">
                        <input type="text" placeholder="Pay to unlock reply..." class="flex-1 text-sm bg-white border border-slate-200 rounded-xl px-4 py-2.5">
                        <button class="bg-slate-300 text-white px-4 py-2.5 rounded-xl text-xs font-bold shrink-0">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            @endforelse

        </div>
    </div>

    {{-- ── CONTACT NUMBERS ── --}}
    <form action="{{ route('seller.settings.update') }}" method="POST"
          class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm mb-6">
        @csrf @method('PUT')
        <input type="hidden" name="name"  value="{{ $user->name }}">
        <input type="hidden" name="email" value="{{ $user->email }}">
        <h3 class="font-bold text-base">My Contact Numbers</h3>
        <p class="text-xs text-slate-400 mt-0.5 mb-5">Twilio forwards all leads to these numbers</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-slate-500 font-semibold mb-2 block">
                    <i class="fa-solid fa-phone text-slate-400 mr-1"></i> Call Number
                </label>
                <input type="tel" name="phone" value="{{ $user->phone }}"
                       class="w-full px-4 py-3.5 border border-slate-200 rounded-2xl text-sm font-semibold focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
            </div>
            <div>
                <label class="text-xs text-slate-500 font-semibold mb-2 block">
                    <i class="fa-brands fa-whatsapp text-emerald-500 mr-1"></i> WhatsApp Number
                </label>
                <input type="tel" name="whatsapp" value="{{ $user->whatsapp }}"
                       class="w-full px-4 py-3.5 border border-slate-200 rounded-2xl text-sm font-semibold focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
            </div>
        </div>
        <button type="submit" class="mt-5 w-full bg-slate-900 hover:bg-slate-800 text-white py-4 rounded-2xl font-bold transition">
            Save Numbers
        </button>
    </form>

    {{-- ── BILLING ── --}}
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm mb-4" id="billingSection">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-base">{{ $currentMonth }} — Billing</h3>
                <p class="text-xs text-orange-600 font-semibold mt-0.5">Twilio Billing</p>
            </div>
            <span class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl font-bold">
                Due {{ now()->endOfMonth()->format('M d') }}
            </span>
        </div>
        <div class="flex items-end justify-between border-b border-slate-100 pb-5 mb-5">
            <div>
                <p class="text-5xl font-black text-slate-900">${{ number_format($totalBill + $pendBill) }}</p>
                <p class="text-sm text-slate-500 mt-1">{{ $stats['total'] }} verified leads · avg ${{ $avgFee }} each</p>
            </div>
            @if(($totalBill + $pendBill) > 0)
            <a href="{{ route('seller.billing') }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-7 py-3.5 rounded-2xl font-bold transition text-sm">
                Pay Now
            </a>
            @endif
        </div>
        <div class="space-y-2.5 text-sm mb-4">
            <div class="flex justify-between">
                <span class="text-slate-600">{{ $wonLeads->count() }} Won leads × ${{ $avgFee ?: 40 }}</span>
                <span class="font-bold text-slate-800">${{ number_format($totalBill) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">{{ $pendLeads->count() }} Pending leads × ${{ $avgFee ?: 40 }}</span>
                <span class="font-semibold text-amber-600">${{ number_format($pendBill) }} <span class="text-slate-400 font-normal">(on close)</span></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">{{ $lostLeads->count() }} Lost leads</span>
                <span class="font-semibold text-slate-400">$0</span>
            </div>
        </div>
        <p class="text-xs text-slate-400">You only pay for real verified calls via Twilio. No hidden fees.</p>
    </div>

</div>
</div>
@endsection

@section('scripts')
<script>
function filterLeads(btn, status) {
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.className = 'filter-btn px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition';
    });
    btn.className = 'filter-btn active px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap bg-teal-700 text-white';
    document.querySelectorAll('#leadsList .lead-card').forEach(card => {
        card.style.display = (status === 'all' || card.dataset.status === status) ? 'block' : 'none';
    });
}

function setStatus(btn, newStatus) {
    const card   = btn.closest('.lead-card');
    const leadId = card.dataset.id;
    card.dataset.status = newStatus;
    const active  = { won:'won-active', pending:'pending-active', lost:'lost-active' };
    const defClass= 'bg-slate-100 text-slate-500 hover:bg-slate-200 transition';
    card.querySelectorAll('.action-btn').forEach(b => {
        const s = b.getAttribute('onclick').match(/'(\w+)'/)[1];
        b.className = `action-btn py-2 rounded-xl text-xs font-bold ${s === newStatus ? active[s] : defClass}`;
    });
    card.classList.toggle('opacity-60', newStatus === 'lost');
    card.style.borderColor = newStatus === 'pending' ? '#fde68a' : '';
    if (leadId) {
        fetch(`/seller/leads/${leadId}/status`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ status: newStatus })
        }).catch(() => {});
    }
    showToast('Lead marked as ' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
}

function saveNote(input, leadId) {
    if (!leadId) return;
    fetch(`/seller/leads/${leadId}/notes`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ notes: input.value })
    }).then(r => r.ok && showToast('Note saved')).catch(() => {});
}

function searchLeads() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.lead-card').forEach(card => {
        card.style.display = card.textContent.toLowerCase().includes(q) ? 'block' : 'none';
    });
}

function exportLeads() {
    const rows = [['Phone','Service','Status','Date','Fee','Note']];
    document.querySelectorAll('#leadsList .lead-card').forEach(card => {
        const cells = card.querySelectorAll('p');
        const note  = card.querySelector('input[type="text"]')?.value || '';
        rows.push([
            cells[0]?.textContent.trim(),
            cells[1]?.textContent.trim(),
            card.dataset.status,
            cells[2]?.textContent.trim(),
            card.querySelector('[class*="font-bold px-3"]')?.textContent.trim() || '',
            note
        ]);
    });
    const csv = rows.map(r => r.map(c => `"${(c||'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'Zonely_Leads_{{ now()->format("Y_m") }}.csv';
    a.click();
    showToast('Leads exported as CSV');
}

function filterChat(btn, filter) {
    document.querySelectorAll('.chat-tab').forEach(b => {
        b.className = 'chat-tab px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition';
    });
    btn.className = 'chat-tab active-chat-tab px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-teal-700 text-white';
    document.getElementById('chatPayNote').classList.toggle('hidden', filter !== 'unpaid');
    document.querySelectorAll('#chatList .chat-item').forEach(item => {
        const filters = (item.dataset.chatFilter || '').split(' ');
        item.style.display = (filter === 'recent' ? filters.includes('recent') : filters.includes(filter)) ? 'block' : 'none';
    });
}

function toggleChat(row) {
    const thread  = row.nextElementSibling;
    const chevron = row.querySelector('.chat-chevron');
    const isOpen  = !thread.classList.contains('hidden');
    document.querySelectorAll('.chat-thread').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.chat-chevron').forEach(c => c.style.transform = '');
    if (!isOpen) {
        thread.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
        setTimeout(() => thread.scrollIntoView({ behavior:'smooth', block:'nearest' }), 50);
    }
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.opacity = '1';
    setTimeout(() => t.style.opacity = '0', 2500);
}
</script>
@endsection
