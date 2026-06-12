@extends('frontend.layouts.__prof_app')
@section('title', 'Dashboard')

@section('css')
<style>
    .bar { transition: height 0.4s ease; }
    .bar:hover { filter: brightness(1.1); }
    .pulse-dot { width:8px;height:8px;border-radius:50%;background:#10b981;animation:pulseDot 1.5s infinite; }
    @keyframes pulseDot { 0%,100%{opacity:1;transform:scale(1);}50%{opacity:.4;transform:scale(.8);} }
    .lead-card { transition: transform .15s ease, box-shadow .15s ease; }
    .lead-card:hover { transform: translateY(-1px); box-shadow: 0 8px 24px -4px rgba(0,0,0,.08); }
    .period-btn.active { background:#0d9488!important; color:#fff!important; border-color:#0d9488!important; }
    .channel-btn.active { background:#1e293b!important; color:#fff!important; border-color:#1e293b!important; }
    .scroll-hide { -ms-overflow-style:none; scrollbar-width:none; }
    .scroll-hide::-webkit-scrollbar { display:none; }
</style>
@endsection

@section('content')
<div class="pb-16 max-w-2xl mx-auto px-4 py-6 lg:px-6 lg:py-8">

{{-- ── 1. HEADER ── --}}
<div class="flex items-center justify-between mb-7">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Welcome back, {{ $user->name }}!</h1>
        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
            <span class="pulse-dot"></span>
            Your page is live
        </p>
    </div>
    <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}" target="_blank"
       class="text-xs font-bold text-teal-700 border border-teal-200 bg-teal-50 hover:bg-teal-100 px-3 py-2 rounded-xl transition flex items-center gap-1.5">
        View My Page <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
    </a>
</div>

{{-- ── 2. LEAD LIST ── --}}
<div class="mb-2 flex items-center justify-between gap-3 flex-wrap">
    <h2 class="font-bold text-base text-slate-800 flex items-center gap-2">
        <i class="fa-solid fa-inbox text-teal-700"></i> Your Leads
        <span class="text-xs font-semibold bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full">{{ $leads->count() }}</span>
    </h2>
    <div class="flex items-center gap-2">
        <div class="relative">
            <input id="searchInput" type="text" placeholder="Search..."
                   oninput="searchLeads()"
                   class="pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs w-36 focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
        </div>
        <button onclick="exportCSV()"
                class="bg-white border border-slate-200 hover:bg-slate-50 px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition">
            <i class="fa-solid fa-download text-teal-700 text-xs"></i> CSV
        </button>
    </div>
</div>

{{-- Channel filter --}}
<div class="flex gap-2 mb-5 overflow-x-auto scroll-hide pb-1">
    <button onclick="filterChannel(this,'all')"   class="channel-btn active px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap border border-slate-200 bg-white text-slate-700">All ({{ $leads->count() }})</button>
    <button onclick="filterChannel(this,'form')"  class="channel-btn px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">📋 Form ({{ $stats['form'] }})</button>
    <button onclick="filterChannel(this,'phone')" class="channel-btn px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">📞 Phone ({{ $stats['phone'] }})</button>
    @if($stats['whatsapp'] > 0)
    <button onclick="filterChannel(this,'whatsapp')" class="channel-btn px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">💬 WhatsApp ({{ $stats['whatsapp'] }})</button>
    @endif
    @if($stats['email'] > 0)
    <button onclick="filterChannel(this,'email')" class="channel-btn px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">📧 Email ({{ $stats['email'] }})</button>
    @endif
</div>

{{-- Lead Cards --}}
<div class="space-y-3 mb-8" id="leadsList">

    @forelse($leads as $lead)
    @php
        $source = $lead->source ?? 'form';
        $channelIcon  = match($source) { 'phone'=>'📞', 'whatsapp'=>'💬', 'email'=>'📧', default=>'📋' };
        $channelLabel = match($source) { 'phone'=>'Phone Call', 'whatsapp'=>'WhatsApp', 'email'=>'Email', default=>'Form' };
        $channelColor = match($source) {
            'phone'    => 'bg-amber-50 text-amber-700 border-amber-200',
            'whatsapp' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'email'    => 'bg-blue-50 text-blue-700 border-blue-200',
            default    => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    @endphp
    <div class="lead-card bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
         data-source="{{ $source }}"
         data-search="{{ strtolower($lead->name . ' ' . $lead->phone . ' ' . $lead->email . ' ' . $lead->service) }}">

        {{-- Card header --}}
        <div class="flex items-center justify-between px-4 pt-4 pb-2">
            <div class="flex items-center gap-2">
                <a href="{{ route('seller.lead.detail', $lead->id) }}"
                   class="text-xs font-black text-teal-700 hover:text-teal-800 hover:underline tracking-wide">
                    #ZL-{{ $lead->id }}
                </a>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $channelColor }}">
                    {{ $channelIcon }} {{ $channelLabel }}
                </span>
            </div>
            <span class="text-[11px] text-slate-400">{{ $lead->created_at->format('M d · g:i A') }}</span>
        </div>

        {{-- Card body --}}
        <div class="px-4 pb-4">
            @if($source === 'phone' || $source === 'whatsapp')
                {{-- Phone/WhatsApp — minimal data --}}
                <p class="font-bold text-slate-800 text-sm">{{ $lead->phone }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Caller ID only — no name available</p>
            @else
                {{-- Form/Email — full data --}}
                @if($lead->name && $lead->name !== 'Phone Lead')
                <p class="font-bold text-slate-900 text-sm mb-1">{{ $lead->name }}</p>
                @endif
                <div class="flex flex-col gap-0.5">
                    @if($lead->phone)
                    <a href="tel:{{ $lead->phone }}" class="text-xs text-teal-700 font-semibold hover:underline">
                        📞 {{ $lead->phone }}
                    </a>
                    @endif
                    @if($lead->email)
                    <a href="mailto:{{ $lead->email }}" class="text-xs text-blue-600 font-semibold hover:underline">
                        ✉️ {{ $lead->email }}
                    </a>
                    @endif
                    @if($lead->service && $lead->service !== 'General Inquiry' && $lead->service !== 'Phone Call')
                    <p class="text-xs text-slate-600 mt-0.5">🔧 {{ $lead->service }}</p>
                    @endif
                    @if($lead->message)
                    <p class="text-xs text-slate-500 mt-1 bg-slate-50 rounded-xl px-3 py-2 leading-relaxed">
                        "{{ Str::limit($lead->message, 100) }}"
                    </p>
                    @endif
                </div>
            @endif

            <div class="flex justify-end mt-3">
                <a href="{{ route('seller.lead.detail', $lead->id) }}"
                   class="text-xs font-bold text-teal-700 hover:text-teal-800 flex items-center gap-1 transition">
                    View Full Details <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>

    @empty
    <div class="bg-white rounded-2xl border-2 border-dashed border-slate-200 p-10 text-center">
        <div class="w-14 h-14 bg-teal-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-inbox text-teal-400 text-xl"></i>
        </div>
        <p class="font-bold text-slate-700 mb-1">No leads yet</p>
        <p class="text-xs text-slate-400 mb-4 max-w-xs mx-auto">When buyers contact you they appear here instantly</p>
        <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}" target="_blank"
           class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white font-bold px-5 py-2.5 rounded-2xl text-xs transition">
            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> View Your Public Page
        </a>
    </div>
    @endforelse

</div>

{{-- ── 3. TIME FILTER ── --}}
<div class="mb-4">
    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Filter by period</p>
    <div class="flex gap-2 overflow-x-auto scroll-hide pb-1">
        @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $val=>$label)
        <a href="{{ request()->fullUrlWithQuery(['period' => $val]) }}"
           class="period-btn {{ $period === $val ? 'active' : '' }} px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

{{-- ── 4. STAT BOXES ── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
        <p class="text-3xl font-black text-teal-700">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500 mt-1 font-medium">Total Leads</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
        <p class="text-3xl font-black text-slate-800">{{ $stats['today'] }}</p>
        <p class="text-xs text-slate-500 mt-1 font-medium">Today</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
        <p class="text-3xl font-black text-amber-500">{{ $stats['form'] }}</p>
        <p class="text-xs text-slate-500 mt-1 font-medium">📋 Form</p>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
        <p class="text-3xl font-black text-emerald-600">{{ $stats['phone'] }}</p>
        <p class="text-xs text-slate-500 mt-1 font-medium">📞 Phone</p>
    </div>
</div>

{{-- ── 5. ANALYTICS ── --}}
@php $maxCount = max(array_merge($weekCounts, [1])); @endphp
<h3 class="font-bold text-base mb-4 flex items-center gap-2 text-slate-800">
    <i class="fa-solid fa-chart-simple text-teal-700"></i> Analytics
</h3>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- Weekly Bar Chart --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <p class="font-semibold text-slate-700 text-sm mb-0.5">Leads This Week</p>
        <p class="text-xs text-slate-400 mb-4">Daily incoming leads</p>
        <div class="flex items-end gap-1.5 h-24">
            @foreach($weekCounts as $count)
            @php $pct = $maxCount > 0 ? round($count / $maxCount * 100) : 5; $pct = max($pct, 5); @endphp
            <div class="flex-1 flex flex-col items-center gap-1">
                <span class="text-[9px] text-slate-400 font-semibold">{{ $count ?: '' }}</span>
                <div class="bar w-full {{ $count === $maxCount && $count > 0 ? 'bg-teal-600' : 'bg-teal-200' }} rounded-t-md" style="height:{{ $pct }}%"></div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-between text-[10px] text-slate-400 mt-2 font-medium">
            @foreach($weekDays as $d)<span>{{ $d }}</span>@endforeach
        </div>
    </div>

    {{-- By Channel --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <p class="font-semibold text-slate-700 text-sm mb-0.5">By Channel</p>
        <p class="text-xs text-slate-400 mb-4">How buyers are reaching you</p>
        <div class="space-y-3">
            @foreach([
                ['📋','Form',     $allLeads->where('source','form')->count(),    'bg-slate-600'],
                ['📞','Phone',    $allLeads->where('source','phone')->count(),   'bg-amber-500'],
                ['💬','WhatsApp', $allLeads->where('source','whatsapp')->count(),'bg-emerald-500'],
                ['📧','Email',    $allLeads->where('source','email')->count(),   'bg-blue-500'],
            ] as [$icon, $label, $count, $color])
            @php $pct = $allLeads->count() ? round($count / $allLeads->count() * 100) : 0; @endphp
            <div class="flex items-center gap-3">
                <span class="text-sm w-5 shrink-0">{{ $icon }}</span>
                <span class="text-xs text-slate-600 w-16 shrink-0">{{ $label }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-2">
                    <div class="{{ $color }} h-2 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                </div>
                <span class="text-xs font-bold text-slate-700 w-6 text-right shrink-0">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

</div>
@endsection

@section('scripts')
<script>
let activeChannel = 'all';

function filterChannel(btn, channel) {
    activeChannel = channel;
    document.querySelectorAll('.channel-btn').forEach(b => {
        b.classList.remove('active');
        b.classList.add('bg-white','text-slate-600');
    });
    btn.classList.add('active');
    applyFilters();
}

function applyFilters() {
    const q = (document.getElementById('searchInput')?.value || '').toLowerCase();
    document.querySelectorAll('#leadsList .lead-card').forEach(card => {
        const channelOk = activeChannel === 'all' || card.dataset.source === activeChannel;
        const searchOk  = !q || (card.dataset.search || '').includes(q);
        card.style.display = (channelOk && searchOk) ? 'block' : 'none';
    });
}

function searchLeads() { applyFilters(); }

function exportCSV() {
    const rows = [['Lead ID','Channel','Date','Name','Phone','Email','Service','Message']];
    @foreach($leads as $lead)
    rows.push([
        '#ZL-{{ $lead->id }}',
        @json(ucfirst($lead->source ?? 'form')),
        @json($lead->created_at->format('M d Y g:i A')),
        @json($lead->name ?? ''),
        @json($lead->phone ?? ''),
        @json($lead->email ?? ''),
        @json($lead->service ?? ''),
        @json($lead->message ?? ''),
    ]);
    @endforeach
    const csv = rows.map(r => r.map(c => `"${(c||'').replace(/"/g,'""')}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'Zonely_Leads_{{ now()->format("Y_m_d") }}.csv';
    a.click();
}
</script>
@endsection
