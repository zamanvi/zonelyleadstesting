@extends('frontend.layouts.__prof_app')
@section('title', 'Lead #ZL-' . $lead->id)

@section('content')
<div class="max-w-xl mx-auto px-4 py-6 pb-16">

    {{-- Back --}}
    <a href="{{ auth()->user()?->type === 'admin' ? route('admin.leads') : route('seller.dashboard') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-teal-700 mb-6 transition">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        {{ auth()->user()?->type === 'admin' ? 'Back to Leads' : 'Back to Dashboard' }}
    </a>

    @php
        $source       = $lead->source ?? 'form';
        $channelIcon  = match($source) { 'phone'=>'📞', 'whatsapp'=>'💬', 'email'=>'📧', 'booking'=>'📅', default=>'📋' };
        $channelLabel = match($source) { 'phone'=>'Phone Call', 'whatsapp'=>'WhatsApp', 'email'=>'Email', 'booking'=>'Booking', default=>'Form' };
        $channelColor = match($source) {
            'phone'    => 'bg-amber-50 text-amber-700 border-amber-200',
            'whatsapp' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'email'    => 'bg-blue-50 text-blue-700 border-blue-200',
            'booking'  => 'bg-sky-50 text-sky-700 border-sky-200',
            default    => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    @endphp

    {{-- Lead header --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-wide">#ZL-{{ $lead->id }}</h1>
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border {{ $channelColor }}">
                        {{ $channelIcon }} {{ $channelLabel }}
                    </span>
                    <span class="text-xs text-slate-400">
                        {{ $lead->created_at->format('M d, Y · g:i A') }}
                    </span>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs text-slate-400">Received</p>
                <p class="text-sm font-bold text-slate-700">{{ $lead->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    {{-- Buyer Information --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
        <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Buyer Information</h2>
        <div class="space-y-3">

            @if($lead->name && $lead->name !== 'Phone Lead')
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-user text-slate-500 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Name</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $lead->name }}</p>
                </div>
            </div>
            @endif

            @if($lead->phone)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-teal-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-phone text-teal-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Phone</p>
                    <a href="tel:{{ $lead->phone }}" class="text-sm font-bold text-teal-700 hover:underline mt-0.5 block">
                        {{ $lead->phone }}
                    </a>
                </div>
            </div>
            @endif

            @if($lead->email)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-envelope text-blue-500 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Email</p>
                    <a href="mailto:{{ $lead->email }}" class="text-sm font-bold text-blue-600 hover:underline mt-0.5 block">
                        {{ $lead->email }}
                    </a>
                </div>
            </div>
            @endif

            @if($lead->service && !in_array($lead->service, ['Phone Call','General Inquiry']))
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-briefcase text-amber-500 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Service Requested</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $lead->service }}</p>
                </div>
            </div>
            @endif

            @if($source === 'booking' && $lead->message)
            @php
                preg_match('/Booking:\s*(\d{4}-\d{2}-\d{2})\s*@\s*(\d{2}:\d{2})/', $lead->message, $bm);
                $bookingDate = isset($bm[1]) ? \Carbon\Carbon::parse($bm[1])->format('l, M j, Y') : null;
                $bookingTime = $bm[2] ?? null;
            @endphp
            @if($bookingDate)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-sky-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calendar-check text-sky-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Appointment</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $bookingDate }}</p>
                    @if($bookingTime)
                    <p class="text-xs text-sky-700 font-semibold mt-0.5">🕐 {{ \Carbon\Carbon::parse($bookingTime)->format('g:i A') }}</p>
                    @endif
                </div>
            </div>
            @endif
            @endif

            @if($lead->message && $lead->message !== 'Inbound call via Zonely tracking number.' && $source !== 'booking')
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-100 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-message text-slate-500 text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Message</p>
                    <p class="text-sm text-slate-700 mt-1.5 bg-slate-50 rounded-xl px-4 py-3 leading-relaxed border border-slate-100">
                        "{{ $lead->message }}"
                    </p>
                </div>
            </div>
            @endif

            @if($lead->location || $lead->zip_code)
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-location-dot text-emerald-500 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Location</p>
                    <p class="text-sm font-bold text-slate-800 mt-0.5">
                        {{ implode(', ', array_filter([$lead->location, $lead->zip_code])) }}
                    </p>
                </div>
            </div>
            @endif

            {{-- Phone call with no extra info --}}
            @if($source === 'phone' && (!$lead->email) && (!$lead->message || $lead->message === 'Inbound call via Zonely tracking number.'))
            <div class="text-center py-3">
                <p class="text-xs text-slate-400">Caller ID only — no additional information available</p>
            </div>
            @endif

        </div>
    </div>

    {{-- Footer --}}
    <p class="text-center text-xs text-slate-400 mt-6">
        Lead delivered by Zonely
        @if(auth()->user()?->type === 'seller')
        · <a href="{{ route('seller.billing') }}" class="text-teal-600 font-semibold hover:underline">View billing →</a>
        @endif
    </p>

</div>
@endsection
