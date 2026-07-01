@php
    // Resolve city/state/country to names if stored as numeric IDs (legacy data)
    $cityName    = $user->city    ? (is_numeric($user->city)    ? (\App\Models\City::find($user->city)?->title    ?? $user->city)    : $user->city)    : null;
    $stateName   = $user->state   ? (is_numeric($user->state)   ? (\App\Models\State::find($user->state)?->title   ?? $user->state)   : $user->state)   : null;
    $countryName = $user->country ? (is_numeric($user->country) ? (\App\Models\Country::find($user->country)?->title ?? $user->country) : $user->country) : null;

    $meta_title       = 'Trusted ' . ($user->category?->title ?? 'Professional') . ' in ' . ($cityName ?? 'Your City') . ($stateName ? ', '.$stateName : '') . ' | ' . $user->name;
    $meta_description = $user->name . ' — verified ' . ($user->category?->title ?? 'professional') . ($cityName ? ' in '.$cityName : '') . '. ' . Str::limit(strip_tags($user->bio ?? $user->about ?? ''), 120);
    // Mother category detection for section labels
    $motherTitle = strtolower($user->category?->parent?->title ?? $user->category?->title ?? '');
    $isHealthcare = str_contains($motherTitle, 'health') || str_contains($motherTitle, 'wellness');
    $isHome       = str_contains($motherTitle, 'home')   || str_contains($motherTitle, 'repair');
    $isBeauty     = str_contains($motherTitle, 'beauty') || str_contains($motherTitle, 'personal care');

    $expSectionTitle  = $isHome ? 'Experience & Trade Memberships'
        : ($isBeauty  ? 'Experience & Professional Orgs'
        : ($isHealthcare ? 'Experience & Associations'
        :                  'Experience & Membership'));

    $eduSectionTitle  = $isHome || $isBeauty ? 'Licenses & Certifications'
        : ($isHealthcare ? 'Education & Medical Credentials'
        :                  'Education & Certification');

    $reviewCount      = $user->reviews->count();
    $avgRating        = $reviewCount ? round($user->reviews->avg('rating'), 1) : null;
    $schedule         = is_array($user->schedule) ? $user->schedule : (json_decode($user->schedule, true) ?? []);
    $workingDays      = $schedule['working_days'] ?? [];
    // Working hours display
    $oh               = $schedule['office_hours'] ?? null;
    $showOH           = ($schedule['show_office_hours'] ?? false) && $oh && !empty($oh['days']);
    $ohIsOpen         = false;
    $ohStatusText     = '';
    $ohStatusColor    = 'bg-slate-100 text-slate-500';
    if ($showOH) {
        try {
            $ohTz    = $oh['timezone'] ?? 'America/New_York';
            $now     = \Carbon\Carbon::now($ohTz);
            $todayK  = strtolower($now->format('D')); // 'mon','tue'...
            $todayD  = $oh['days'][$todayK] ?? null;
            if ($todayD && ($todayD['open'] ?? false)) {
                foreach ($todayD['slots'] ?? [] as $sl) {
                    $f = \Carbon\Carbon::createFromTimeString($sl['from'] ?? '00:00', $ohTz)->setDateFrom($now);
                    $t = \Carbon\Carbon::createFromTimeString($sl['to']   ?? '00:00', $ohTz)->setDateFrom($now);
                    if ($now->between($f, $t)) {
                        $ohIsOpen = true;
                        $minsLeft = (int) $now->diffInMinutes($t);
                        $ohStatusText  = $minsLeft <= 60
                            ? 'Open · Closes in ' . $minsLeft . ' min'
                            : 'Open · Closes ' . $t->format('g:i A');
                        $ohStatusColor = 'bg-emerald-100 text-emerald-700';
                        break;
                    }
                }
                if (!$ohIsOpen) {
                    foreach ($todayD['slots'] ?? [] as $sl) {
                        $f = \Carbon\Carbon::createFromTimeString($sl['from'] ?? '00:00', $ohTz)->setDateFrom($now);
                        if ($f->gt($now)) {
                            $ohStatusText  = 'Closed · Opens today ' . $f->format('g:i A');
                            $ohStatusColor = 'bg-amber-100 text-amber-700';
                            break;
                        }
                    }
                }
            }
            if (!$ohIsOpen && !$ohStatusText) {
                for ($nd = 1; $nd <= 7; $nd++) {
                    $nDay  = $now->copy()->addDays($nd);
                    $nKey  = strtolower($nDay->format('D'));
                    $nData = $oh['days'][$nKey] ?? null;
                    if ($nData && ($nData['open'] ?? false) && !empty($nData['slots'])) {
                        $nf   = \Carbon\Carbon::createFromTimeString($nData['slots'][0]['from'] ?? '09:00', $ohTz)->setDateFrom($nDay);
                        $lbl  = $nd === 1 ? 'Tomorrow' : $nDay->format('l');
                        $ohStatusText  = 'Closed · Opens ' . $lbl . ' ' . $nf->format('g:i A');
                        $ohStatusColor = 'bg-red-100 text-red-600';
                        break;
                    }
                }
                if (!$ohStatusText) {
                    $ohStatusText  = 'Currently Closed';
                    $ohStatusColor = 'bg-red-100 text-red-600';
                }
            }
        } catch (\Exception $e) {
            $showOH = false;
        }
    }
    $allDays          = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $phone            = $user->contacts->where('type','phone')->first();
    $wa               = $user->contacts->where('type','whatsapp')->first();
    $trackingNumber   = $user->twilioNumber?->number;
    $rawPhone         = $phone?->value ?? $user->phone;
    $callNumber       = null; // Phone tracking disabled until Twilio is configured
    $waNumber         = $wa?->value ?? $user->whatsapp;
    $activeServices   = $user->services->where('is_active', true);
    $tags             = array_filter(array_map('trim', explode(',', $user->tags ?? '')));
    $yearsExp         = $user->experience ? (int)$user->experience : null;
@endphp
@extends('frontend.layouts._app')
@section('title', $meta_title)
@php
    $ogSvcs = $activeServices->take(2)->pluck('title')->filter();
    $ogDesc = $user->name . ' — ' . ($user->category?->title ?? 'Professional') . ($cityName ? ' in ' . $cityName : '') . '.';
    if ($ogSvcs->count()) {
        $ogDesc .= ' Services: ' . $ogSvcs->map(fn($s) => '✓ ' . $s)->implode('  ');
    } else {
        $ogDesc .= ' ' . Str::limit(strip_tags($user->about ?? $user->bio ?? 'Verified professional on Zonely. Book a consultation today.'), 100);
    }
    $ogDesc = Str::limit($ogDesc, 200);
@endphp
@section('og_title',       $user->name)
@section('og_description', trim(($user->category?->title ?? '') . ($user->city ? ' · ' . $user->city . ($user->state ? ', ' . $user->state : '') : '') . ($user->experience ? ' · ' . $user->experience . '+ yrs experience' : '')))
@section('og_image',       route('frontend.og.image', $user->slug).'?v='.$user->updated_at->timestamp)
@section('og_extra')
<meta property="og:type" content="profile">
<meta property="profile:first_name" content="{{ $user->name }}">
@endsection

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "{{ addslashes($user->name) }}",
  "description": "{{ addslashes(Str::limit(strip_tags($user->about ?? $user->bio ?? ''), 200)) }}",
  "url": "{{ url()->current() }}",
  "image": "{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo)) : '' }}",
  "@id": "{{ url()->current() }}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{ addslashes($user->work_address ?? '') }}",
    "addressLocality": "{{ $cityName ?? '' }}",
    "addressRegion": "{{ $stateName ?? '' }}",
    "addressCountry": "US"
  }
  @if($callNumber),"telephone": "{{ $callNumber }}"@endif
  @if($avgRating),
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ $avgRating }}",
    "reviewCount": "{{ $reviewCount }}",
    "bestRating": "5",
    "worstRating": "1"
  }
  @endif
}
</script>
@endsection

@section('hideLayoutFooter', true)
@section('hideAccountNav', true)

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
<style>
    .pro-page { font-family: 'Playfair Display', Georgia, serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; }
    .hero-bg { background: linear-gradient(135deg, #134E4A 0%, #0F766E 60%, #0D9488 100%); }
    .sh { position: relative; display: inline-block; }
    .sh::after { content: ''; position: absolute; width: 48px; height: 3px; background: #5EEAD4; bottom: -8px; left: 0; border-radius: 9999px; }
    .sh-center::after { left: 50%; transform: translateX(-50%); }
    .map-container { border-radius: 16px; overflow: hidden; }
    .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out; }
    .accordion-content.open { max-height: 500px; }
    .faq-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
    .faq-content.open { max-height: 200px; }
    .booking-body { max-height: 0; overflow: hidden; transition: max-height 0.45s ease-out, opacity 0.3s ease; opacity: 0; }
    .booking-body.open { max-height: 1200px; opacity: 1; }
    @media (max-width: 640px) { .booking-body.open { max-height: 2000px; } }
    .lift { transition: transform 0.25s ease, box-shadow 0.25s ease; }
    .lift:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.13); }
    .pro-glass { background: rgba(255,255,255,0.12); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.22); }
    .badge-verified { background: linear-gradient(135deg, #059669, #10b981); }
    .service-icon { width: 52px; height: 52px; background: linear-gradient(135deg, #F0FDFA, #CCFBF1); border-radius: 14px; display: flex; align-items: center; justify-content: center; }
    .bg-icon { width: 44px; height: 44px; background: #F0FDFA; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .testimonial-card { background: linear-gradient(135deg, #F0FDFA, #ffffff); border: 1px solid #CCFBF1; }
    .price-num { font-size: 2rem; font-weight: 800; color: #0F766E; line-height: 1; }
    @media (max-width: 767px) { .pro-page { padding-bottom: 80px; } }
    .marquee-track { display: flex; width: max-content; animation: marquee 30s linear infinite; }
    .marquee-track:hover { animation-play-state: paused; }
    @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
</style>
@endsection

@section('content')
<div class="pro-page mt-16 sm:mt-20 bg-gray-50">
<div class="max-w-7xl mx-auto bg-white min-h-screen shadow-2xl overflow-hidden">

    {{-- ── HERO ─────────────────────────────────────────────────────── --}}
    <section class="hero-bg text-white relative">
        <div class="absolute top-4 right-4 flex items-center gap-2">
            <button onclick="openShareModal()" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg whitespace-nowrap flex items-center gap-1.5 transition">
                <i class="fas fa-share-nodes text-xs"></i> Share
            </button>
            <div class="badge-verified text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg whitespace-nowrap flex items-center gap-1.5">
                <i class="fas fa-circle-check text-xs"></i> VERIFIED
            </div>
        </div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10 md:py-14">
            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-14">

                {{-- Profile card — LEFT --}}
                <div class="flex-shrink-0 w-full max-w-xs mx-auto md:mx-0 md:max-w-sm">
                    <div class="pro-glass rounded-3xl p-6 text-center">
                        <div class="relative inline-block">
                            <img src="{{ str_starts_with($user->profile_photo ?? '', 'http') ? $user->profile_photo : asset($user->profile_photo ?? '') }}"
                                 alt="{{ $user->name }}"
                                 class="w-36 h-48 sm:w-48 sm:h-60 object-cover rounded-2xl border-4 border-white/30 shadow-xl mx-auto"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div style="display:none;" class="w-36 h-48 sm:w-48 sm:h-60 bg-teal-600/40 border-4 border-white/30 rounded-2xl mx-auto items-center justify-center text-white font-black text-4xl shadow-xl">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-xl font-bold">{{ $user->name }}</h3>
                            @if($user->business_name)
                            <p class="text-white/80 text-sm font-semibold mt-0.5">{{ $user->business_name }}</p>
                            @endif
                            <p class="text-teal-200 text-base mt-0.5">
                                {{ $user->title ?? $user->designation ?? $user->category?->title }}
                            </p>
                            @if($cityName)
                            <p class="text-teal-300 text-sm mt-0.5">
                                <i class="fas fa-map-marker-alt text-xs mr-1"></i>{{ $cityName }}{{ $stateName ? ', '.$stateName : '' }}
                            </p>
                            @endif
                        </div>
                        @php $statCount = ($yearsExp ? 1 : 0) + ($reviewCount ? 1 : 0) + ($avgRating ? 1 : 0); @endphp
                        @if($statCount)
                        <div class="grid grid-cols-{{ $statCount }} gap-2 mt-4">
                            @if($yearsExp)
                            <div class="bg-white/10 rounded-xl py-2 px-2 text-center">
                                <div class="text-lg font-bold text-yellow-300">{{ $yearsExp }}+</div>
                                <div class="text-xs text-teal-200 leading-tight">Yrs Exp.</div>
                            </div>
                            @endif
                            @if($reviewCount)
                            <div class="bg-white/10 rounded-xl py-2 px-2 text-center">
                                <div class="text-lg font-bold text-yellow-300">{{ $reviewCount }}</div>
                                <div class="text-xs text-teal-200 leading-tight">Reviews</div>
                            </div>
                            @endif
                            @if($avgRating)
                            <div class="bg-white/10 rounded-xl py-2 px-2 text-center">
                                <div class="text-lg font-bold text-yellow-300">{{ $avgRating }}★</div>
                                <div class="text-xs text-teal-200 leading-tight">Rating</div>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if($user->languages->count())
                        <div class="flex flex-wrap justify-center gap-1.5 mt-2">
                            @foreach($user->languages as $lang)
                            <span class="bg-white/10 border border-white/20 text-white text-xs font-medium px-2.5 py-1 rounded-full">{{ $lang->name }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Text + CTAs — RIGHT --}}
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
                        Trusted {{ $user->category?->title ?? 'Professional' }}
                        @if($cityName) in {{ $cityName }}{{ $stateName ? ', '.$stateName : '' }}@endif
                    </h1>
                    @if($user->bio || $user->about)
                    <p class="mt-6 text-teal-100 text-base md:text-lg leading-relaxed">
                        {{ Str::limit($user->bio ?? $user->about, 160) }}
                    </p>
                    @endif
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center md:justify-start flex-wrap">
                        @if($isOverdue ?? false)
                        <div class="flex items-center gap-3 bg-white/10 border border-white/30 px-6 py-4 rounded-2xl text-white text-sm font-semibold">
                            <i class="fas fa-clock text-amber-300"></i>
                            This professional is temporarily unavailable. Please check back soon.
                        </div>
                        @else
                        @if($callNumber)
                        <a href="tel:{{ $callNumber }}"
                           class="flex items-center justify-center gap-3 bg-white text-teal-800 hover:bg-yellow-300 px-8 py-4 rounded-full font-bold text-base shadow-xl transition">
                            <i class="fas fa-phone"></i>
                            @if($trackingNumber) Call Now @else {{ $callNumber }} @endif
                        </a>
                        @endif
                        @if($waNumber)
                        <button onclick="trackWa('{{ route('service.wa.click', $user->slug) }}')"
                           class="flex items-center justify-center gap-3 bg-green-500 hover:bg-green-400 text-white px-8 py-4 rounded-full font-bold text-base shadow-xl transition cursor-pointer">
                            <i class="fab fa-whatsapp text-xl"></i> WhatsApp
                        </button>
                        @endif
                        <a href="#contact"
                           class="flex items-center justify-center gap-3 bg-amber-400 hover:bg-amber-300 text-slate-900 px-8 py-4 rounded-full font-bold text-base transition shadow-xl">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </a>
                        @endif
                    </div>
                    @if($reviewCount && $avgRating)
                    <a href="#testimonials"
                       class="mt-4 inline-flex items-center gap-2.5 justify-center md:justify-start text-white/80 hover:text-white text-sm font-semibold transition group">
                        <span class="flex items-center gap-0.5 text-yellow-300">
                            @for($i=1;$i<=5;$i++)<i class="fas fa-star text-xs"></i>@endfor
                        </span>
                        {{ $avgRating }} · Read client reviews
                        <i class="fas fa-arrow-down text-xs group-hover:translate-y-0.5 transition-transform"></i>
                    </a>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- ── TRUST BAR ────────────────────────────────────────────────── --}}
    @php
        $trustItems = collect();
        foreach($user->certifications as $c) $trustItems->push(['icon'=>'fas fa-certificate text-yellow-400', 'text'=>$c->name.($c->issuer ? ' — '.$c->issuer : '')]);
        foreach($user->memberships as $m) $trustItems->push(['icon'=>'fas fa-id-badge text-teal-400', 'text'=>$m->name]);
        foreach($user->educations as $edu) $trustItems->push(['icon'=>'fas fa-graduation-cap text-emerald-400', 'text'=>$edu->degree.($edu->institution ? ' — '.$edu->institution : '')]);
        if($trustItems->isEmpty()) $trustItems->push(['icon'=>'fas fa-shield-alt text-emerald-400','text'=>'Verified on Zonely']);
    @endphp
    <div class="bg-slate-800 overflow-hidden py-3.5">
        <div class="marquee-track">
            @foreach([0,1] as $_)
            <div class="flex items-center gap-12 text-sm font-semibold text-white px-8" @if($_) aria-hidden="true" @endif>
                @foreach($trustItems as $t)
                <span class="flex items-center gap-2 shrink-0"><i class="{{ $t['icon'] }}"></i> {{ $t['text'] }}</span>
                <span class="text-slate-500 shrink-0 px-4">|</span>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8 py-8 md:py-12 space-y-10 md:space-y-12">

        {{-- ── ROW 1: Bio (left) + Pricing (right) ────────────────────── --}}
        @php
            $ptMap = ['starting_at'=>'Starting at','per_month'=>'Per month','per_hour'=>'Per hour','flat_rate'=>'Flat rate','free'=>'Free','contact'=>'Negotiable'];
            $hasPricing       = $activeServices->count() || count($tags);
            $hasMemberships   = $user->memberships->count();
            $hasEducation     = $user->educations->count();
            $hasExperiences   = $user->experiences->count();
            $hasCertifications= $user->certifications->count();
            $hasBio         = $user->about || $user->bio;
        @endphp
        @php
            $hasGallery      = $user->gallery->count() > 0;
            $bioText         = $user->about ?? $user->bio ?? '';
            $bioLong         = strlen($bioText) > 400;
            $galleryLabel    = $isHome || $isBeauty ? 'Our Work' : ($isHealthcare ? 'Our Clinic' : 'Photo Gallery');
            $galleryPhotosJs = $user->gallery->map(fn($p) => ['src' => $p->image_url, 'caption' => $p->caption ?? ''])->toJson();
        @endphp

        @if($hasBio || $hasPricing)

        {{-- ════════════════════════════════════════════
             WITH GALLERY
             About → full width top
             Pricing + Gallery → side by side, Gallery follows Pricing height
             ════════════════════════════════════════════ --}}
        @if($hasGallery)

            {{-- About — full width, no height competition --}}
            @if($hasBio)
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden mb-6">
                <div class="border-l-4 border-teal-600 p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-quote-left text-teal-300 text-lg"></i>
                        <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">About</span>
                    </div>
                    <p class="text-base text-slate-700 leading-relaxed text-justify" id="bioText">
                        @if($user->title)<strong class="font-bold text-slate-900">{{ $user->title }}</strong><br>@endif
                        <span id="bioShort">{{ $bioLong ? Str::limit($bioText, 400) : $bioText }}</span>
                        @if($bioLong)<span id="bioFull" class="hidden">{{ $bioText }}</span>@endif
                    </p>
                    @if($bioLong)
                    <button onclick="document.getElementById('bioShort').classList.toggle('hidden');document.getElementById('bioFull').classList.toggle('hidden');this.textContent=this.textContent.trim()==='Read more'?'Read less':'Read more';"
                        class="mt-3 text-xs font-bold text-teal-700 hover:underline">Read more</button>
                    @endif
                </div>
            </div>
            @endif

            {{-- Pricing (untouched inside) + Gallery slider — side by side, items-stretch so Gallery follows Pricing height --}}
            <section id="pricing" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

                {{-- LEFT: Pricing — zero changes inside --}}
                @if($activeServices->count())
                <div>
                    <div class="mb-6">
                        <h3 class="font-bold text-2xl sm:text-3xl sh">Services &amp; Pricing</h3>
                        @if($activeServices->where('price', '>', 0)->count())
                        <p class="text-slate-500 mt-4 text-sm font-medium">No hidden fees · Click to see details</p>
                        @endif
                    </div>
                    @php $svcIcons = ['fa-briefcase','fa-file-alt','fa-handshake','fa-chart-line','fa-calculator','fa-gavel','fa-wrench','fa-star','fa-shield-alt','fa-lightbulb']; $svcIdx = 0; @endphp
                    <div class="space-y-4">
                        @foreach($activeServices as $svc)
                        @php
                            $ptLabel  = $ptMap[$svc->pricing_type ?? 'starting_at'] ?? 'Starting at';
                            $features = array_filter(array_map('trim', explode("\n", $svc->features ?? '')));
                            $hasPrice = $svc->price && !in_array($svc->pricing_type, ['free','contact']);
                            $svcIcon  = $svcIcons[$svcIdx % count($svcIcons)]; $svcIdx++;
                        @endphp
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden group hover:shadow-md hover:border-teal-100 transition-all duration-200">
                            <div class="h-1 bg-gradient-to-r from-teal-600 to-indigo-500"></div>
                            <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-teal-50/30 transition-colors">
                                <div class="flex items-center gap-3 sm:gap-6 min-w-0">
                                    <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-teal-100 transition-colors">
                                        <i class="fas {{ $svcIcon }} text-teal-700 text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-base text-slate-900 leading-snug truncate">{{ $svc->title }}</p>
                                        @if($features)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ count($features) }} {{ Str::plural('item', count($features)) }} included</p>
                                        @elseif($svc->description)
                                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ Str::limit($svc->description, 55) }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                                    <div class="text-right">
                                        @if($hasPrice)
                                            <div class="text-2xl font-black text-teal-800 leading-none">${{ number_format($svc->price, 0) }}</div>
                                            <div class="text-xs text-teal-400 font-semibold mt-0.5">{{ $ptLabel }}</div>
                                        @elseif($svc->pricing_type === 'free')
                                            <div class="text-xl font-black text-emerald-600">Free</div>
                                        @else
                                            <div class="text-sm font-bold text-slate-400">Negotiable</div>
                                        @endif
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-teal-100 flex items-center justify-center transition-colors flex-shrink-0">
                                        <i class="fas fa-chevron-down text-slate-400 text-xs accordion-icon transition-transform duration-300"></i>
                                    </div>
                                </div>
                            </button>
                            <div class="accordion-content border-t border-slate-100">
                                @if($features)
                                <div class="px-5 pt-4 pb-3 space-y-2">
                                    @foreach($features as $feature)
                                    <div class="flex items-start gap-2.5">
                                        <span class="mt-0.5 w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-check text-emerald-600" style="font-size:9px"></i>
                                        </span>
                                        <span class="text-sm text-slate-700">{{ $feature }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                @if($svc->description)
                                <p class="px-5 pt-2 pb-3 text-sm text-slate-500 leading-relaxed">{{ $svc->description }}</p>
                                @endif
                                <div class="px-5 pb-4 pt-3 flex items-center gap-3 border-t border-slate-100 bg-slate-50/50">
                                    <a href="#contact" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-slate-900 font-bold px-5 py-2.5 rounded-xl text-sm transition shadow-sm">
                                        <i class="fas fa-paper-plane text-xs"></i> Get a Quote
                                    </a>
                                    @if($callNumber)
                                    <a href="tel:{{ $callNumber }}" class="inline-flex items-center gap-2 text-teal-700 hover:text-teal-800 font-semibold text-sm transition">
                                        <i class="fas fa-phone text-xs"></i> Call Now
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @elseif(count($tags))
                <div>
                    <div class="mb-6"><h3 class="font-bold text-2xl sm:text-3xl sh">Services Offered</h3></div>
                    <div class="flex flex-wrap gap-3">
                        @foreach($tags as $tag)
                        <span class="flex items-center gap-2 bg-teal-50 border border-teal-100 text-teal-800 font-semibold px-5 py-3 rounded-2xl text-sm">
                            <i class="fas fa-circle-check text-teal-400 text-xs"></i> {{ ucfirst($tag) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- RIGHT: Gallery slider — follows Pricing height via items-stretch --}}
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between flex-shrink-0">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-images text-teal-500 text-sm"></i>
                            <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">{{ $galleryLabel }}</span>
                        </div>
                        <span class="text-xs text-slate-400 font-medium" id="galCounter">1 / {{ $user->gallery->count() }}</span>
                    </div>
                    <div class="relative flex-1" style="min-height:180px">
                        <img id="galImg"
                             src="{{ $user->gallery->first()->image_url }}"
                             alt="{{ $user->gallery->first()->caption ?: $galleryLabel }}"
                             loading="lazy"
                             class="w-full h-full object-cover cursor-pointer"
                             onclick="openLightbox(galCurrentIdx)">
                        @if($user->gallery->count() > 1)
                        <button onclick="galPrev()" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/90 hover:bg-white rounded-full shadow flex items-center justify-center transition" aria-label="Previous photo">
                            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
                        </button>
                        <button onclick="galNext()" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white/90 hover:bg-white rounded-full shadow flex items-center justify-center transition" aria-label="Next photo">
                            <i class="fas fa-chevron-right text-slate-600 text-xs"></i>
                        </button>
                        @endif
                    </div>
                    <div class="px-4 py-2 border-t border-slate-50 flex-shrink-0 min-h-[32px]">
                        <p id="galCaption" class="text-xs text-slate-400 truncate">{{ $user->gallery->first()->caption ?? '' }}</p>
                    </div>
                </div>

            </section>

            {{-- Lightbox for gallery --}}
            <div id="galleryLightbox" class="fixed inset-0 z-50 bg-black/90 hidden items-center justify-center p-4" onclick="if(event.target===this)closeLightbox()">
                <button onclick="closeLightbox()" class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
                <button onclick="prevPhoto()" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button onclick="nextPhoto()" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                <div class="max-w-4xl max-h-[85vh] w-full flex flex-col items-center gap-3">
                    <img id="lightboxImg" src="" alt="" class="max-h-[75vh] max-w-full rounded-2xl object-contain shadow-2xl">
                    <p id="lightboxCaption" class="text-white/70 text-sm font-medium text-center"></p>
                    <p id="lightboxCounter" class="text-white/40 text-xs"></p>
                </div>
            </div>

            <script>
            const galPhotos = {!! $galleryPhotosJs !!};
            let galCurrentIdx = 0;
            function galShow(i) {
                galCurrentIdx = i;
                const p = galPhotos[i];
                document.getElementById('galImg').src = p.src;
                document.getElementById('galImg').alt = p.caption || '{{ $galleryLabel }}';
                document.getElementById('galCounter').textContent = (i + 1) + ' / ' + galPhotos.length;
                const cap = document.getElementById('galCaption');
                if (cap) cap.textContent = p.caption || '';
                document.getElementById('lightboxImg').src = p.src;
                document.getElementById('lightboxCaption').textContent = p.caption || '';
                document.getElementById('lightboxCounter').textContent = (i + 1) + ' / ' + galPhotos.length;
            }
            function galPrev() { galShow((galCurrentIdx - 1 + galPhotos.length) % galPhotos.length); }
            function galNext() { galShow((galCurrentIdx + 1) % galPhotos.length); }
            function openLightbox(i) {
                galShow(i);
                document.getElementById('galleryLightbox').classList.remove('hidden');
                document.getElementById('galleryLightbox').classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
            function closeLightbox() {
                document.getElementById('galleryLightbox').classList.add('hidden');
                document.getElementById('galleryLightbox').classList.remove('flex');
                document.body.style.overflow = '';
            }
            function prevPhoto() { galPrev(); }
            function nextPhoto() { galNext(); }
            document.addEventListener('keydown', e => {
                if (!document.getElementById('galleryLightbox').classList.contains('hidden')) {
                    if (e.key === 'ArrowLeft') galPrev();
                    if (e.key === 'ArrowRight') galNext();
                    if (e.key === 'Escape') closeLightbox();
                }
            });
            </script>

        {{-- ════════════════════════════════════════════
             WITHOUT GALLERY
             About + Pricing side by side
             About follows Pricing height (items-stretch)
             Read more only if bio is long, pinned to bottom
             ════════════════════════════════════════════ --}}
        @else

        <section id="pricing" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

            {{-- LEFT: About — stretches to Pricing height, Read more pinned to bottom --}}
            @if($hasBio)
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="border-l-4 border-teal-600 p-6 flex flex-col flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-quote-left text-teal-300 text-lg"></i>
                        <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">About</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-base text-slate-700 leading-relaxed text-justify" id="bioText">
                            @if($user->title)<strong class="font-bold text-slate-900">{{ $user->title }}</strong><br>@endif
                            <span id="bioShort">{{ $bioLong ? Str::limit($bioText, 400) : $bioText }}</span>
                            @if($bioLong)<span id="bioFull" class="hidden">{{ $bioText }}</span>@endif
                        </p>
                    </div>
                    @if($bioLong)
                    <button onclick="document.getElementById('bioShort').classList.toggle('hidden');document.getElementById('bioFull').classList.toggle('hidden');this.textContent=this.textContent.trim()==='Read more'?'Read less':'Read more';"
                        class="mt-4 text-xs font-bold text-teal-700 hover:underline self-start">Read more</button>
                    @endif
                </div>
            </div>
            @endif

            {{-- RIGHT: Pricing — zero changes inside --}}
            @if($activeServices->count())
            <div>
                <div class="mb-6">
                    <h3 class="font-bold text-2xl sm:text-3xl sh">Services &amp; Pricing</h3>
                    @if($activeServices->where('price', '>', 0)->count())
                    <p class="text-slate-500 mt-4 text-sm font-medium">No hidden fees · Click to see details</p>
                    @endif
                </div>
                @php $svcIcons2 = ['fa-briefcase','fa-file-alt','fa-handshake','fa-chart-line','fa-calculator','fa-gavel','fa-wrench','fa-star','fa-shield-alt','fa-lightbulb']; $svcIdx2 = 0; @endphp
                <div class="space-y-4">
                    @foreach($activeServices as $svc)
                    @php
                        $ptLabel  = $ptMap[$svc->pricing_type ?? 'starting_at'] ?? 'Starting at';
                        $features = array_filter(array_map('trim', explode("\n", $svc->features ?? '')));
                        $hasPrice = $svc->price && !in_array($svc->pricing_type, ['free','contact']);
                        $svcIcon  = $svcIcons2[$svcIdx2 % count($svcIcons2)]; $svcIdx2++;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden group hover:shadow-md hover:border-teal-100 transition-all duration-200">
                        <div class="h-1 bg-gradient-to-r from-teal-600 to-indigo-500"></div>
                        <button onclick="toggleAccordion(this)" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-teal-50/30 transition-colors">
                            <div class="flex items-center gap-3 sm:gap-6 min-w-0">
                                <div class="w-11 h-11 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-teal-100 transition-colors">
                                    <i class="fas {{ $svcIcon }} text-teal-700 text-base"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-base text-slate-900 leading-snug truncate">{{ $svc->title }}</p>
                                    @if($features)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ count($features) }} {{ Str::plural('item', count($features)) }} included</p>
                                    @elseif($svc->description)
                                    <p class="text-xs text-slate-400 mt-0.5 truncate">{{ Str::limit($svc->description, 55) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                                <div class="text-right">
                                    @if($hasPrice)
                                        <div class="text-2xl font-black text-teal-800 leading-none">${{ number_format($svc->price, 0) }}</div>
                                        <div class="text-xs text-teal-400 font-semibold mt-0.5">{{ $ptLabel }}</div>
                                    @elseif($svc->pricing_type === 'free')
                                        <div class="text-xl font-black text-emerald-600">Free</div>
                                    @else
                                        <div class="text-sm font-bold text-slate-400">Negotiable</div>
                                    @endif
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-teal-100 flex items-center justify-center transition-colors flex-shrink-0">
                                    <i class="fas fa-chevron-down text-slate-400 text-xs accordion-icon transition-transform duration-300"></i>
                                </div>
                            </div>
                        </button>
                        <div class="accordion-content border-t border-slate-100">
                            @if($features)
                            <div class="px-5 pt-4 pb-3 space-y-2">
                                @foreach($features as $feature)
                                <div class="flex items-start gap-2.5">
                                    <span class="mt-0.5 w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check text-emerald-600" style="font-size:9px"></i>
                                    </span>
                                    <span class="text-sm text-slate-700">{{ $feature }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            @if($svc->description)
                            <p class="px-5 pt-2 pb-3 text-sm text-slate-500 leading-relaxed">{{ $svc->description }}</p>
                            @endif
                            <div class="px-5 pb-4 pt-3 flex items-center gap-3 border-t border-slate-100 bg-slate-50/50">
                                <a href="#contact" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-slate-900 font-bold px-5 py-2.5 rounded-xl text-sm transition shadow-sm">
                                    <i class="fas fa-paper-plane text-xs"></i> Get a Quote
                                </a>
                                @if($callNumber)
                                <a href="tel:{{ $callNumber }}" class="inline-flex items-center gap-2 text-teal-700 hover:text-teal-800 font-semibold text-sm transition">
                                    <i class="fas fa-phone text-xs"></i> Call Now
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @elseif(count($tags))
            <div>
                <div class="mb-6"><h3 class="font-bold text-2xl sm:text-3xl sh">Services Offered</h3></div>
                <div class="flex flex-wrap gap-3">
                    @foreach($tags as $tag)
                    <span class="flex items-center gap-2 bg-teal-50 border border-teal-100 text-teal-800 font-semibold px-5 py-3 rounded-2xl text-sm">
                        <i class="fas fa-circle-check text-teal-400 text-xs"></i> {{ ucfirst($tag) }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

        </section>{{-- end without-gallery grid --}}
        @endif{{-- end @else (no gallery) --}}

        @endif{{-- end @if($hasBio || $hasPricing) --}}

        {{-- ── REVIEWS / TESTIMONIALS ───────────────────────────────── --}}
        @if($user->reviews->count())
        <section id="testimonials">
            <div class="mb-6">
                <h3 class="font-bold text-2xl sm:text-3xl sh">Client Reviews</h3>
                <p class="text-slate-500 mt-5 text-sm font-medium">{{ $reviewCount }} verified reviews &nbsp;·&nbsp; Avg. {{ $avgRating }} / 5 stars</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach($user->reviews->take(3) as $review)
                <div class="testimonial-card rounded-2xl p-6 lift">
                    <div class="flex gap-1 mb-3">
                        @for($i=1;$i<=5;$i++)
                        <i class="fas fa-star text-base {{ $i <= ($review->rating ?? 5) ? 'text-yellow-400' : 'text-slate-200' }}"></i>
                        @endfor
                    </div>
                    <p class="text-base text-slate-600 leading-relaxed">"{{ Str::limit($review->review ?? '', 120) }}"</p>
                    <div class="mt-5 flex items-center gap-3">
                        <div class="w-9 h-9 bg-teal-100 rounded-full flex items-center justify-center text-teal-800 font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($review->reviewer?->name ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-base font-semibold">{{ $review->reviewer?->name ?? 'Verified Client' }}</p>
                            <p class="text-sm text-slate-500">{{ $review->created_at?->format('M Y') }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ── Experience & Membership ─────────────────────────────── --}}
        @if($hasExperiences || $hasMemberships)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-teal-700 px-6 py-4 flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-briefcase text-white text-base"></i>
                </div>
                <h4 class="font-bold text-lg text-white">{{ $expSectionTitle }}</h4>
            </div>
            <div class="grid grid-cols-1 {{ $hasExperiences && $hasMemberships ? 'md:grid-cols-2' : '' }} divide-y md:divide-y-0 md:divide-x divide-slate-100">

                {{-- Work Experience column --}}
                @if($hasExperiences)
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5 pb-3 border-b border-indigo-50">
                        <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-briefcase text-indigo-600 text-xs"></i>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Work Experience</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($user->experiences as $exp)
                        <div class="flex gap-3 items-start">
                            <div class="flex flex-col items-center pt-1.5">
                                <div class="w-2.5 h-2.5 {{ $loop->first ? 'bg-indigo-600 ring-4 ring-indigo-100' : 'bg-slate-200' }} rounded-full shrink-0"></div>
                                @if(!$loop->last)<div class="w-px flex-1 bg-slate-100 mt-1 min-h-[36px]"></div>@endif
                            </div>
                            <div class="pb-4 min-w-0">
                                <p class="font-semibold text-base text-slate-800 leading-tight">{{ $exp->title }}</p>
                                @if($exp->company)<p class="text-sm text-indigo-600 font-medium mt-0.5">{{ $exp->company }}</p>@endif
                                @if($exp->start_date || $exp->is_current || $exp->end_date)
                                <p class="text-sm text-slate-400 mt-0.5">{{ $exp->start_date ?? '' }}@if($exp->start_date) – @endif{{ $exp->is_current ? 'Present' : ($exp->end_date ?? '') }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Memberships column --}}
                @if($hasMemberships)
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5 pb-3 border-b border-teal-50">
                        <div class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-id-badge text-teal-700 text-xs"></i>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-teal-700">Memberships</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($user->memberships as $m)
                        <div class="flex gap-3 items-start">
                            <div class="flex flex-col items-center pt-1.5">
                                <div class="w-2.5 h-2.5 {{ $loop->first ? 'bg-teal-600 ring-4 ring-teal-100' : 'bg-slate-200' }} rounded-full shrink-0"></div>
                                @if(!$loop->last)<div class="w-px flex-1 bg-slate-100 mt-1 min-h-[36px]"></div>@endif
                            </div>
                            <div class="pb-4 min-w-0">
                                <p class="font-semibold text-base text-slate-800 leading-tight">{{ $m->name }}</p>
                                @if($m->start || $m->end)<p class="text-sm text-slate-400 mt-0.5">{{ $m->start ?? '' }}{{ ($m->start && $m->end) ? ' – ' : '' }}{{ $m->end ?? 'Present' }}</p>@endif
                                @if(!empty($m->address))<p class="text-sm text-slate-500 mt-0.5">{{ $m->address }}</p>@endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif

        {{-- ── Education & Certification ────────────────────────────── --}}
        @if($hasEducation || $hasCertifications)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-amber-500 px-6 py-4 flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-graduation-cap text-white text-base"></i>
                </div>
                <h4 class="font-bold text-lg text-white">{{ $eduSectionTitle }}</h4>
            </div>
            <div class="grid grid-cols-1 {{ $hasEducation && $hasCertifications ? 'md:grid-cols-2' : '' }} divide-y md:divide-y-0 md:divide-x divide-slate-100">

                {{-- Education column --}}
                @if($hasEducation)
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5 pb-3 border-b border-emerald-50">
                        <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-graduation-cap text-emerald-600 text-xs"></i>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">Education</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($user->educations as $edu)
                        <div class="flex gap-3 items-start">
                            <div class="flex flex-col items-center pt-1.5">
                                <div class="w-2.5 h-2.5 {{ $loop->first ? 'bg-emerald-600 ring-4 ring-emerald-100' : 'bg-slate-200' }} rounded-full shrink-0"></div>
                                @if(!$loop->last)<div class="w-px flex-1 bg-slate-100 mt-1 min-h-[36px]"></div>@endif
                            </div>
                            <div class="pb-4 min-w-0">
                                <p class="font-semibold text-base text-slate-800 leading-tight">{{ $edu->degree }}</p>
                                @if($edu->institution)<p class="text-sm text-emerald-600 font-medium mt-0.5">{{ $edu->institution }}</p>@endif
                                @if($edu->passing_year)<p class="text-sm text-slate-400 mt-0.5">{{ $edu->passing_year }}</p>@endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Certifications column --}}
                @if($hasCertifications)
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5 pb-3 border-b border-amber-50">
                        <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-certificate text-amber-600 text-xs"></i>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-amber-600">Certifications</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($user->certifications as $cert)
                        <div class="flex items-start gap-3 pb-3 border-b border-slate-50 last:border-0 last:pb-0">
                            <div class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fas fa-award text-amber-500 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-base text-slate-800 leading-tight">{{ $cert->name }}</p>
                                @if($cert->issuer)<p class="text-sm text-amber-600 mt-0.5">{{ $cert->issuer }}</p>@endif
                                @if($cert->issued_year)<p class="text-sm text-slate-400 mt-0.5">{{ $cert->issued_year }}{{ $cert->expiry_year ? ' – '.$cert->expiry_year : '' }}</p>@endif
                                @if($cert->credential_id)<p class="text-sm text-slate-400 mt-0.5">ID: {{ $cert->credential_id }}</p>@endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif

        {{-- ── FAQ ───────────────────────────────────────────────────── --}}
        @if($user->faqs->count())
        <section>
            <div class="mb-6">
                <h3 class="font-bold text-2xl sm:text-3xl sh">Frequently Asked Questions</h3>
            </div>
            <div class="space-y-3">
                @foreach($user->faqs as $faq)
                <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm hover:border-teal-100 hover:shadow-md transition-all duration-200 group">
                    <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-teal-50/20 transition">
                        <span class="font-semibold text-base pr-4 text-slate-800">{{ $faq->question }}</span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-teal-100 flex items-center justify-center flex-shrink-0 transition-colors">
                            <i class="fas fa-chevron-down text-slate-400 text-xs faq-icon transition-transform duration-300"></i>
                        </div>
                    </button>
                    <div class="faq-content border-t border-slate-100">
                        <p class="px-6 py-4 text-base text-slate-600 leading-relaxed">{{ $faq->answer }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ── AVAILABILITY & WORKING HOURS ────────────────────────── --}}
        @if($showOH)
        @php
        $rtLabels = ['30_min'=>'Responds within 30 min','1_hour'=>'Responds within 1 hour','4_hours'=>'Responds within 4 hours','24_hours'=>'Responds within 24 hours','48_hours'=>'Responds within 2 days'];
        $ohDayNames = ['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'];
        $ohTodayKey = strtolower(\Carbon\Carbon::now($oh['timezone'] ?? 'America/New_York')->format('D'));
        @endphp
        <section>
            <div class="mb-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="font-bold text-2xl sm:text-3xl sh">Availability & Working Hours</h3>
                        <p class="text-slate-500 mt-2 text-sm font-medium">When you can reach this professional — office hours, response time & availability</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full {{ $ohStatusColor }} mt-1 flex-shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full {{ $ohIsOpen ? 'bg-emerald-500' : 'bg-red-400' }} inline-block"></span>
                        {{ $ohStatusText }}
                    </span>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <tbody>
                        @foreach($ohDayNames as $key => $name)
                        @php
                            $d       = $oh['days'][$key] ?? null;
                            $isOpen  = $d && ($d['open'] ?? false);
                            $isToday = $key === $ohTodayKey;
                        @endphp
                        <tr class="{{ $isToday ? 'bg-teal-50 border-l-4 border-teal-500' : 'border-l-4 border-transparent' }} border-b border-slate-50 last:border-b-0">
                            <td class="px-5 py-3.5 w-28 {{ $isToday ? 'font-black text-teal-800' : 'font-semibold text-slate-600' }}">
                                {{ $name }}
                                @if($isToday)<span class="ml-1.5 text-[10px] font-bold bg-teal-600 text-white px-1.5 py-0.5 rounded-full">Today</span>@endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($isOpen)
                                    @php $slots = $d['slots'] ?? []; @endphp
                                    @if($slots)
                                        <span class="text-slate-700 font-medium">
                                            @foreach($slots as $si => $sl)
                                                @if($si > 0) <span class="text-slate-300 mx-1.5">·</span> @endif
                                                {{ \Carbon\Carbon::createFromTimeString($sl['from'] ?? '00:00')->format('g:i A') }}
                                                <span class="text-slate-400 mx-0.5">–</span>
                                                {{ \Carbon\Carbon::createFromTimeString($sl['to'] ?? '00:00')->format('g:i A') }}
                                            @endforeach
                                        </span>
                                    @else
                                        <span class="text-slate-500">Open</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Closed</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right w-16">
                                @if($isOpen)
                                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Footer badges --}}
                @php
                    $rt = $oh['response_time'] ?? null;
                    $emergency = $oh['emergency_available'] ?? false;
                    $note = trim($oh['note'] ?? '');
                    $hasBadges = $rt || $emergency || $note;
                @endphp
                @if($hasBadges)
                <div class="px-5 py-3.5 border-t border-slate-100 flex flex-wrap items-center gap-2">
                    @if($rt && isset($rtLabels[$rt]))
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1.5 rounded-full">
                        <i class="fa-solid fa-bolt text-blue-400 text-[10px]"></i>
                        {{ $rtLabels[$rt] }}
                    </span>
                    @endif
                    @if($emergency)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-red-50 text-red-700 border border-red-100 px-3 py-1.5 rounded-full">
                        <i class="fa-solid fa-phone text-red-400 text-[10px]"></i>
                        Emergency calls accepted
                    </span>
                    @endif
                    @if($note)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200 px-3 py-1.5 rounded-full">
                        <i class="fa-solid fa-circle-info text-slate-400 text-[10px]"></i>
                        {{ $note }}
                    </span>
                    @endif
                </div>
                @endif
            </div>
        </section>
        @endif

    </div>

    {{-- ── CONTACT / BOOKING ────────────────────────────────────────── --}}
    <div id="contact" class="bg-gradient-to-br from-teal-800 via-teal-800 to-indigo-700 text-white py-12 md:py-16">
        @if($isOverdue ?? false)
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center py-8">
            <div class="inline-flex flex-col items-center gap-4 bg-white/10 border border-white/20 rounded-3xl px-8 py-10 max-w-lg mx-auto">
                <div class="w-16 h-16 bg-amber-400/20 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-clock text-amber-300 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Temporarily Unavailable</h3>
                <p class="text-teal-200 text-sm leading-relaxed">This professional is temporarily unavailable for new inquiries. Please check back soon or explore other professionals.</p>
                <a href="{{ route('frontend.service.all') }}"
                   class="mt-2 bg-white text-teal-800 hover:bg-yellow-300 font-bold px-6 py-3 rounded-2xl text-sm transition">
                    Browse Other Professionals
                </a>
            </div>
        </div>
        @else
        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            {{-- 2-col header: text left, button right --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 md:gap-12">
                <div class="text-center md:text-left">
                    <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-teal-100 text-sm font-semibold px-4 py-2 rounded-full mb-4">
                        <i class="fas fa-calendar-check text-yellow-300"></i> Free Initial Consultation
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-4xl font-bold leading-snug">Book a Free Consultation with {{ $user->business_name ?? $user->name }}</h2>
                    <p class="text-teal-200 mt-2 text-base">We will respond within 24 hours</p>
                </div>
                <div class="flex-shrink-0 flex flex-col items-center gap-4">
                    @if(session('inquiry_success'))
                    <div class="bg-green-500 text-white rounded-2xl px-6 py-3 font-bold text-sm flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>{{ session('inquiry_success') }}
                    </div>
                    @endif
                    <button id="bookingToggleBtn" onclick="toggleBooking()"
                            class="flex items-center gap-3 bg-white text-teal-800 hover:bg-yellow-300 px-10 py-4 rounded-2xl font-bold text-base transition shadow-xl whitespace-nowrap">
                        <i class="fas fa-calendar-plus" id="bookingIcon"></i>
                        <span id="bookingBtnText">Open Booking Form</span>
                    </button>
                </div>
            </div>

            <div id="bookingBody" class="booking-body mt-8">
                <div class="bg-white/10 backdrop-blur-lg rounded-3xl border border-white/20 text-left overflow-hidden">
                    <div class="px-5 md:px-8 pt-6 pb-4 border-b border-white/10">
                        <h4 class="font-semibold text-lg">Booking Request — {{ $user->name }}</h4>
                    </div>
                    <form action="{{ route('service.inquiry', $user->slug) }}" method="POST"
                          class="px-5 md:px-8 pb-6 md:pb-8 pt-5"
                          data-track="inquiry_submit" data-track-lead="1">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                            <div>
                                <label class="block text-base mb-2 font-medium">Full Name *</label>
                                <input type="text" name="name" required value="{{ old('name') }}" placeholder="John Smith"
                                       class="w-full px-5 py-3.5 bg-white/20 border border-white/30 rounded-2xl text-white placeholder:text-teal-200 focus:outline-none focus:border-white focus:bg-white/25 transition">
                            </div>
                            <div>
                                <label class="block text-base mb-2 font-medium">Phone Number *</label>
                                <input type="tel" name="phone" required value="{{ old('phone') }}" placeholder="(917) 000-0000"
                                       class="w-full px-5 py-3.5 bg-white/20 border border-white/30 rounded-2xl text-white placeholder:text-teal-200 focus:outline-none focus:border-white focus:bg-white/25 transition">
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="block text-sm mb-2 font-medium">Email Address *</label>
                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="john@email.com"
                                   class="w-full px-5 py-3.5 bg-white/20 border border-white/30 rounded-2xl text-white placeholder:text-teal-200 focus:outline-none focus:border-white focus:bg-white/25 transition">
                        </div>
                        @if($activeServices->count())
                        <div class="mt-5">
                            <label class="block text-sm mb-2 font-medium">Service Needed</label>
                            <select name="service" class="w-full px-5 py-3.5 bg-white/20 border border-white/30 rounded-2xl text-white focus:outline-none focus:border-white focus:bg-white/25 transition">
                                <option value="" class="text-slate-800">Select a service...</option>
                                @foreach($activeServices as $svc)
                                <option class="text-slate-800" value="{{ $svc->title }}" {{ old('service')==$svc->title?'selected':'' }}>
                                    {{ $svc->title }}{{ $svc->price ? ' — $'.$svc->price : '' }}
                                </option>
                                @endforeach
                                <option class="text-slate-800" value="Other">Other / General Inquiry</option>
                            </select>
                        </div>
                        @endif
                        <div class="mt-5">
                            <label class="block text-sm mb-2 font-medium">Message / Details</label>
                            <textarea name="message" rows="4" placeholder="Tell us about your needs..."
                                      class="w-full px-5 py-3.5 bg-white/20 border border-white/30 rounded-2xl text-white placeholder:text-teal-200 focus:outline-none focus:border-white focus:bg-white/25 transition resize-none">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit"
                                class="w-full mt-6 bg-white text-teal-800 hover:bg-yellow-300 py-4 rounded-3xl font-semibold text-xl flex items-center justify-center gap-2 transition shadow-xl">
                            <i class="fas fa-paper-plane"></i> Send Booking Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

{{-- ── CUSTOM FOOTER ───────────────────────────────────────────── --}}
    <footer class="bg-slate-900 text-slate-400 py-8">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-6 border-b border-slate-700">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-teal-700 rounded-xl flex items-center justify-center text-white font-bold text-sm">Z</div>
                    <span class="text-white font-bold text-lg">Zonely</span>
                </div>
                <div class="flex items-center gap-5 text-sm">
                    @if($callNumber)
                    <a href="tel:{{ $callNumber }}" class="flex items-center gap-1.5 hover:text-white transition">
                        <i class="fas fa-phone text-teal-400 text-xs"></i> {{ $callNumber }}
                    </a>
                    @endif
                    @if($waNumber)
                    <button onclick="trackWa('{{ route('service.wa.click', $user->slug) }}')" class="flex items-center gap-1.5 hover:text-white transition cursor-pointer bg-transparent border-0 text-inherit p-0">
                        <i class="fab fa-whatsapp text-emerald-400 text-xs"></i> WhatsApp
                    </button>
                    @endif
                </div>
            </div>
            <div class="pt-5 text-center text-xs opacity-50">&copy; {{ date('Y') }} Zonely &bull; All Rights Reserved</div>
        </div>
    </footer>

{{-- ── SHARE MODAL ──────────────────────────────────────────────── --}}
<div id="shareModal" class="fixed inset-0 z-[999] flex items-end sm:items-center justify-center p-4" style="display:none!important;">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeShareModal()"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-800">Share this profile</h3>
            <button onclick="closeShareModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        {{-- Profile mini-card --}}
        <div class="flex items-center gap-3 bg-slate-50 rounded-2xl p-3 mb-5">
            <img src="{{ str_starts_with($user->profile_photo ?? '', 'http') ? $user->profile_photo : asset($user->profile_photo ?? '') }}"
                 alt="{{ $user->name }}"
                 class="w-12 h-12 rounded-xl object-cover flex-shrink-0"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0F766E&color=fff&size=48'">
            <div class="min-w-0">
                <div class="font-bold text-slate-800 text-sm truncate">{{ $user->name }}</div>
                <div class="text-xs text-slate-500 truncate">{{ $user->category?->title ?? $user->title ?? 'Professional' }}{{ $cityName ? ' · '.$cityName : '' }}</div>
            </div>
        </div>
        {{-- Copy link --}}
        <div class="flex gap-2 mb-5">
            <input id="shareLinkInput" type="text" readonly value="{{ url()->current() }}"
                   class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-600 focus:outline-none truncate">
            <button onclick="copyShareLink()" id="copyBtn"
                    class="flex items-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition whitespace-nowrap">
                <i class="fas fa-copy text-xs"></i> <span id="copyBtnText">Copy</span>
            </button>
        </div>
        {{-- Share options --}}
        <div class="grid grid-cols-4 gap-3">
            <button onclick="shareViaWhatsApp()" class="flex flex-col items-center gap-1.5 p-3 bg-green-50 hover:bg-green-100 rounded-2xl transition group">
                <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                    <i class="fab fa-whatsapp text-white text-lg"></i>
                </div>
                <span class="text-xs text-slate-600 font-medium">WhatsApp</span>
            </button>
            <button onclick="shareViaFacebook()" class="flex flex-col items-center gap-1.5 p-3 bg-blue-50 hover:bg-blue-100 rounded-2xl transition group">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                    <i class="fab fa-facebook-f text-white text-lg"></i>
                </div>
                <span class="text-xs text-slate-600 font-medium">Facebook</span>
            </button>
            <button onclick="shareViaX()" class="flex flex-col items-center gap-1.5 p-3 bg-slate-50 hover:bg-slate-100 rounded-2xl transition group">
                <div class="w-10 h-10 bg-black rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                    <i class="fab fa-x-twitter text-white text-lg"></i>
                </div>
                <span class="text-xs text-slate-600 font-medium">X</span>
            </button>
            <button onclick="shareViaNative()" class="flex flex-col items-center gap-1.5 p-3 bg-teal-50 hover:bg-teal-100 rounded-2xl transition group">
                <div class="w-10 h-10 bg-teal-600 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                    <i class="fas fa-ellipsis text-white text-lg"></i>
                </div>
                <span class="text-xs text-slate-600 font-medium">More</span>
            </button>
        </div>
    </div>
</div>

{{-- ── EMAIL MODAL ──────────────────────────────────────────────── --}}
<div id="emailModal" class="hidden fixed inset-0 bg-black/50 z-[999] flex items-end sm:items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900 text-lg">Send Email to {{ $user->name }}</h3>
            <button onclick="document.getElementById('emailModal').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        @if(session('email_success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-2xl mb-4 flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('email_success') }}
        </div>
        @endif
        <form action="{{ route('service.email', $user->slug) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" required placeholder="John Smith" value="{{ old('name') }}"
                       class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-400 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Your Email *</label>
                <input type="email" name="email" required placeholder="john@gmail.com" value="{{ old('email') }}"
                       class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-400 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Phone (optional)</label>
                <input type="tel" name="phone" placeholder="(917) 000-0000" value="{{ old('phone') }}"
                       class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-400 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Message *</label>
                <textarea name="message" required rows="3" placeholder="Describe what you need..."
                          class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-400 transition resize-none">{{ old('message') }}</textarea>
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-2xl text-sm transition">
                <i class="fas fa-paper-plane mr-2"></i> Send Email
            </button>
        </form>
    </div>
</div>

{{-- ── MOBILE STICKY CTA ────────────────────────────────────────── --}}
@if(!($isOverdue ?? false))
<div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 shadow-2xl px-4 py-3 flex gap-3">
    @if($waNumber)
    <button onclick="trackWa('{{ route('service.wa.click', $user->slug) }}')"
       class="flex-1 flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white py-3 rounded-2xl font-semibold text-base transition cursor-pointer">
        <i class="fab fa-whatsapp"></i> WhatsApp
    </button>
    @endif
    <button onclick="document.getElementById('emailModal').classList.remove('hidden')"
       class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-2xl font-semibold text-base transition">
        <i class="fas fa-envelope"></i> Email
    </button>
    <a href="#contact"
       class="flex-1 flex items-center justify-center gap-2 bg-teal-700 hover:bg-teal-800 text-white py-3 rounded-2xl font-semibold text-base transition">
        <i class="fas fa-calendar-check"></i> Book
    </a>
</div>
@endif

</div>
@endsection

@section('scripts')
<script>
    function toggleAccordion(btn) {
        const content = btn.nextElementSibling;
        const chevron = btn.querySelector('.accordion-icon');
        content.classList.toggle('open');
        if (chevron) chevron.style.transform = content.classList.contains('open') ? 'rotate(180deg)' : '';
    }

    function toggleFaq(btn) {
        const content = btn.nextElementSibling;
        const icon    = btn.querySelector('.faq-icon');
        document.querySelectorAll('.faq-content').forEach(c => { if (c !== content) c.classList.remove('open'); });
        document.querySelectorAll('.faq-icon').forEach(ic => { if (ic !== icon) ic.style.transform = ''; });
        content.classList.toggle('open');
        icon.style.transform = content.classList.contains('open') ? 'rotate(180deg)' : '';
    }

    function toggleBooking() {
        const body    = document.getElementById('bookingBody');
        const icon    = document.getElementById('bookingIcon');
        const btnText = document.getElementById('bookingBtnText');
        const isOpen  = body.classList.toggle('open');
        icon.className      = isOpen ? 'fas fa-minus-circle' : 'fas fa-plus-circle';
        btnText.textContent = isOpen ? 'Close Booking Form' : 'Open Booking Form';
        if (isOpen) setTimeout(() => body.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
    }

    @if(session('inquiry_success'))
        // Auto-open form and scroll to success message
        const _body = document.getElementById('bookingBody');
        if (!_body.classList.contains('open')) toggleBooking();
        setTimeout(() => document.getElementById('contact').scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
    @endif

    function trackWa(url) {
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => window.open(data.url, '_blank'))
        .catch(() => window.open('https://wa.me/{{ preg_replace('/[^0-9]/', '', $waNumber ?? '') }}', '_blank'));
    }

    const _shareUrl  = '{{ url()->current() }}';
    const _shareName = '{{ addslashes($user->name) }}';
    const _shareText = _shareName + ' — {{ addslashes($user->category?->title ?? $user->title ?? 'Professional') }}{{ $cityName ? ' in '.$cityName : '' }} | Zonely';

    function openShareModal() {
        const m = document.getElementById('shareModal');
        m.style.removeProperty('display');
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeShareModal() {
        document.getElementById('shareModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function copyShareLink() {
        navigator.clipboard.writeText(_shareUrl).then(() => {
            const btn  = document.getElementById('copyBtn');
            const text = document.getElementById('copyBtnText');
            text.textContent = 'Copied!';
            btn.classList.replace('bg-teal-600', 'bg-green-600');
            btn.classList.replace('hover:bg-teal-700', 'hover:bg-green-700');
            setTimeout(() => {
                text.textContent = 'Copy';
                btn.classList.replace('bg-green-600', 'bg-teal-600');
                btn.classList.replace('hover:bg-green-700', 'hover:bg-teal-700');
            }, 2000);
        }).catch(() => {
            document.getElementById('shareLinkInput').select();
            document.execCommand('copy');
        });
    }

    function shareViaWhatsApp() {
        window.open('https://wa.me/?text=' + encodeURIComponent(_shareText + '\n' + _shareUrl), '_blank');
    }

    function shareViaFacebook() {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(_shareUrl), '_blank');
    }

    function shareViaX() {
        window.open('https://x.com/intent/tweet?text=' + encodeURIComponent(_shareText) + '&url=' + encodeURIComponent(_shareUrl), '_blank');
    }

    function shareViaNative() {
        if (navigator.share) {
            navigator.share({ title: _shareName, text: _shareText, url: _shareUrl }).catch(() => {});
        } else {
            copyShareLink();
        }
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeShareModal(); });
</script>
@endsection
