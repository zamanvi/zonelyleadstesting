@extends('frontend.layouts.__prof_app')
@section('title', 'Build Your Page — Zonely')

@section('content')
@php
    $motherCat = $user->category?->parent?->title ?? $user->category?->title ?? '';
    $motherCat = strtolower($motherCat);
    $isHealthcare = str_contains($motherCat, 'health') || str_contains($motherCat, 'wellness') || str_contains($motherCat, 'medical');
    $isHome       = str_contains($motherCat, 'home')   || str_contains($motherCat, 'repair')   || str_contains($motherCat, 'service');
    $isBeauty     = str_contains($motherCat, 'beauty') || str_contains($motherCat, 'personal care') || str_contains($motherCat, 'salon');

    $certHint = $isHealthcare ? 'Medical license, board certifications, specializations'
        : ($isHome   ? 'Contractor license, insurance certificate, trade certifications'
        : ($isBeauty ? 'Cosmetology license, beauty certifications, specialist training'
        :              'CPA, CFA, bar license, professional credentials'));

    $memberHint = $isHealthcare ? 'Medical associations, health boards, specialist societies'
        : ($isHome   ? 'Trade associations, union memberships, contractor orgs'
        : ($isBeauty ? 'Beauty associations, professional orgs, industry groups'
        :              'Bar associations, boards, professional orgs'));

    $faqHint = $isBeauty
        ? 'Add at least 5 FAQs — pricing, availability, booking process, products used. <strong class="text-red-500">Minimum 5 required.</strong>'
        : 'Add at least 5 FAQs — pricing, turnaround times, service area questions. <strong class="text-red-500">Minimum 5 required.</strong>';

    $faqCount    = $user->faqs->count();
    $faqProgress = $faqCount > 0 && $faqCount < 5; // partial state

    $galleryCount = $user->gallery()->count();
    $galleryDone  = $galleryCount > 0;

    $langCount = $user->languages->count();

    $done = [
        'basics'      => filled($user->business_name) || filled($user->title),
        'bio'         => filled($user->bio),
        'contact'     => filled($user->whatsapp) || $user->contacts()->count() > 0,
        'faqs'        => $faqCount >= 5,
        'loc_services'=> (filled($user->city) || filled($user->zip_code)) && $user->services->count() > 0,
        'credentials' => $user->educations->count() > 0 || $user->memberships->count() > 0 || $user->experiences->count() > 0 || $user->certifications->count() > 0,
    ];

    $total = 6;

    $completed = collect($done)->filter()->count();
    $pct       = round($completed / $total * 100);
@endphp

<div class="pb-12 max-w-4xl mx-auto px-4 lg:px-6">

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-emerald-500"></i>
        <p class="text-sm text-emerald-700 font-semibold">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-3 mb-5 flex items-center gap-3">
        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
        <p class="text-sm text-red-700 font-semibold">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Page URL Banner --}}
    <div class="bg-slate-900 rounded-2xl px-5 py-3.5 mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 min-w-0">
            <span class="w-2 h-2 bg-emerald-400 rounded-full shrink-0 animate-pulse"></span>
            <span class="text-xs text-slate-400 font-medium shrink-0">Your live page:</span>
            <span class="text-sm text-white font-mono truncate">{{ parse_url(config('app.url'), PHP_URL_HOST) }}/{{ $user->slug }}</span>
        </div>
        <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}" target="_blank"
           class="shrink-0 flex items-center gap-1.5 text-xs font-bold text-teal-400 hover:text-teal-300 transition">
            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> Preview
        </a>
    </div>

    {{-- Progress Header --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-full bg-teal-700 flex items-center justify-center shrink-0 overflow-hidden shadow">
                @if($user->profile_photo)
                    <img src="{{ asset($user->profile_photo) }}" class="w-full h-full object-cover">
                @else
                    <span class="text-white font-black text-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-bold text-slate-900 truncate">{{ $user->name }}</h1>
                <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-2">
                    <span>{{ $user->category?->title ?? 'No category' }}</span>
                    @if(!$user->category)
                        · <a href="{{ route('user.register.category') }}" class="text-teal-700 hover:underline">Select category</a>
                    @endif
                </p>
            </div>
            @if($pct >= 80)
            <span class="shrink-0 px-2 sm:px-3 py-1.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl flex items-center gap-1">
                <i class="fa-solid fa-circle-check"></i> <span class="hidden sm:inline">Page Ready</span>
            </span>
            @endif
        </div>

        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-bold text-slate-700">Page Completion</span>
            <span class="text-sm font-black {{ $pct >= 80 ? 'text-emerald-600' : 'text-teal-700' }}">{{ $pct }}%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2.5 mb-2">
            <div class="h-2.5 rounded-full transition-all duration-500 {{ $pct >= 80 ? 'bg-emerald-500' : 'bg-teal-700' }}"
                 style="width: {{ $pct }}%"></div>
        </div>
        <p class="text-xs text-slate-400">{{ $completed }} of {{ $total }} sections complete
            · {{ $pct >= 80 ? 'Your page is live and ready to receive leads.' : 'Complete all sections to maximise lead conversions.' }}
        </p>
    </div>

    {{-- Section Cards --}}
    <div class="grid sm:grid-cols-2 gap-4">

        {{-- 1. Business Identity --}}
        @php $isDone = $done['basics']; @endphp
        <a href="{{ route('type.profile', ['seller', 'account']) }}"
           class="group block bg-white rounded-2xl border-2 {{ $isDone ? 'border-emerald-200' : 'border-dashed border-slate-200 hover:border-teal-300' }} shadow-sm p-5 transition-all hover:shadow-md">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 {{ $isDone ? 'bg-emerald-100' : 'bg-teal-50' }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-building {{ $isDone ? 'text-emerald-600' : 'text-teal-600' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">Your Business Identity</p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ Business name, your name & phone</p>
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg {{ $isDone ? 'bg-emerald-100 text-emerald-700' : 'bg-red-50 text-red-500' }}">
                    {{ $isDone ? 'Complete' : 'Required' }}
                </span>
            </div>
            @if($isDone)
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs text-slate-600 space-y-0.5">
                    @if($user->business_name)<p><span class="font-semibold">Business:</span> {{ $user->business_name }}</p>@endif
                    @if($user->title)<p><span class="font-semibold">Title:</span> {{ $user->title }}</p>@endif
                    @if($user->phone)<p><span class="font-semibold">Phone:</span> {{ $user->phone }}</p>@endif
                </div>
            @else
                <p class="text-xs text-slate-400">Your business name, owner name, professional title & phone number</p>
            @endif
            <div class="mt-3 flex items-center justify-end">
                <span class="text-xs font-bold {{ $isDone ? 'text-slate-400 group-hover:text-teal-700' : 'text-teal-700' }} flex items-center gap-1 transition">
                    <i class="fa-solid {{ $isDone ? 'fa-pen' : 'fa-plus' }} text-[10px]"></i>
                    {{ $isDone ? 'Edit' : 'Add Now' }}
                </span>
            </div>
        </a>

        {{-- 2. Profile Photo & Bio --}}
        @php $isDone = $done['bio']; @endphp
        <a href="{{ route('type.profile', ['seller', 'profile']) }}"
           class="group block bg-white rounded-2xl border-2 {{ $isDone ? 'border-emerald-200' : 'border-dashed border-slate-200 hover:border-teal-300' }} shadow-sm p-5 transition-all hover:shadow-md">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 {{ $isDone ? 'bg-emerald-100' : 'bg-teal-50' }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-camera {{ $isDone ? 'text-emerald-600' : 'text-teal-600' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">Profile Photo & Bio</p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ Your photo, professional title & bio</p>
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg {{ $isDone ? 'bg-emerald-100 text-emerald-700' : 'bg-red-50 text-red-500' }}">
                    {{ $isDone ? 'Complete' : 'Required' }}
                </span>
            </div>
            @if($isDone)
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs text-slate-600">
                    <p class="line-clamp-2 leading-relaxed">{{ $user->bio }}</p>
                </div>
            @else
                <p class="text-xs text-slate-400">
                    @if($isHealthcare) Clear headshot + bio — patients decide in seconds who to trust
                    @elseif($isHome) Your photo + bio — homeowners want to know who's coming to their door
                    @elseif($isBeauty) Your photo + bio — clients book the person, not just the service
                    @else Professional headshot + bio — clients hire you personally, a face converts 3× more @endif
                </p>
            @endif
            <div class="mt-3 flex items-center justify-end">
                <span class="text-xs font-bold {{ $isDone ? 'text-slate-400 group-hover:text-teal-700' : 'text-teal-700' }} flex items-center gap-1 transition">
                    <i class="fa-solid {{ $isDone ? 'fa-pen' : 'fa-plus' }} text-[10px]"></i>
                    {{ $isDone ? 'Edit' : 'Add Now' }}
                </span>
            </div>
        </a>

        {{-- 3. How Clients Contact You --}}
        @php $isDone = $done['contact']; @endphp
        <a href="{{ route('type.profile', ['seller', 'contact']) }}"
           class="group block bg-white rounded-2xl border-2 {{ $isDone ? 'border-emerald-200' : 'border-dashed border-slate-200 hover:border-teal-300' }} shadow-sm p-5 transition-all hover:shadow-md">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 {{ $isDone ? 'bg-emerald-100' : 'bg-teal-50' }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-address-card {{ $isDone ? 'text-emerald-600' : 'text-teal-600' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">How Clients Contact You</p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ Phone & WhatsApp number</p>
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg {{ $isDone ? 'bg-emerald-100 text-emerald-700' : 'bg-red-50 text-red-500' }}">
                    {{ $isDone ? 'Complete' : 'Required' }}
                </span>
            </div>
            @if($isDone)
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs text-slate-600 space-y-0.5">
                    @if($user->whatsapp)<p><i class="fab fa-whatsapp text-emerald-500 mr-1"></i>{{ $user->whatsapp }}</p>@endif
                    @if($user->phone)<p><i class="fa-solid fa-phone text-teal-600 mr-1"></i>{{ $user->phone }}</p>@endif
                </div>
            @else
                <p class="text-xs text-slate-400">WhatsApp number and phone — make it easy for clients to reach you</p>
            @endif
            <div class="mt-3 flex items-center justify-end">
                <span class="text-xs font-bold {{ $isDone ? 'text-slate-400 group-hover:text-teal-700' : 'text-teal-700' }} flex items-center gap-1 transition">
                    <i class="fa-solid {{ $isDone ? 'fa-pen' : 'fa-plus' }} text-[10px]"></i>
                    {{ $isDone ? 'Edit' : 'Add Now' }}
                </span>
            </div>
        </a>

        {{-- 4. Client FAQs — Fix 1: show in-progress state --}}
        @php
            $isDone      = $done['faqs'];
            $faqInProgress = $faqCount > 0 && !$isDone;
        @endphp
        <a href="{{ route('user.faqs.index') }}"
           class="group block bg-white rounded-2xl border-2 {{ $isDone ? 'border-emerald-200' : ($faqInProgress ? 'border-amber-200 hover:border-amber-300' : 'border-dashed border-slate-200 hover:border-teal-300') }} shadow-sm p-5 transition-all hover:shadow-md">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 {{ $isDone ? 'bg-emerald-100' : ($faqInProgress ? 'bg-amber-50' : 'bg-teal-50') }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-question {{ $isDone ? 'text-emerald-600' : ($faqInProgress ? 'text-amber-500' : 'text-teal-600') }} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">Client FAQs & Questions</p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ Answer questions before clients even ask</p>
                    </div>
                </div>
                @if($isDone)
                    <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700">Complete</span>
                @elseif($faqInProgress)
                    <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg bg-amber-100 text-amber-700">{{ $faqCount }}/5 added</span>
                @else
                    <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg bg-red-50 text-red-500">Required</span>
                @endif
            </div>

            @if($isDone)
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs text-slate-600">
                    <p class="font-semibold mb-1">{{ $faqCount }} questions added</p>
                    @if($user->faqs->first())
                        <p class="text-slate-500 truncate">{{ $user->faqs->first()->question }}</p>
                    @endif
                </div>
            @elseif($faqInProgress)
                <div class="bg-amber-50 rounded-xl px-3 py-2.5 text-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-semibold text-amber-700">{{ $faqCount }} of 5 added</span>
                        <span class="text-amber-500">{{ 5 - $faqCount }} more needed</span>
                    </div>
                    <div class="w-full bg-amber-100 rounded-full h-1.5">
                        <div class="bg-amber-400 h-1.5 rounded-full" style="width:{{ round($faqCount/5*100) }}%"></div>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-400">{!! $faqHint !!}</p>
            @endif

            <div class="mt-3 flex items-center justify-end">
                <span class="text-xs font-bold {{ $isDone ? 'text-slate-400 group-hover:text-teal-700' : 'text-teal-700' }} flex items-center gap-1 transition">
                    <i class="fa-solid {{ $isDone ? 'fa-pen' : 'fa-plus' }} text-[10px]"></i>
                    {{ $isDone ? 'Manage' : ($faqInProgress ? 'Continue' : 'Add Now') }}
                </span>
            </div>
        </a>

        {{-- 5. Location, Services & Pricing — Fix 5: no hover shadow (not a clickable div) --}}
        @php
            $locDone = filled($user->city) || filled($user->zip_code);
            $svcDone = $user->services->count() > 0;
            $isDone  = $done['loc_services'];
            $oc = $user->city  ? (is_numeric($user->city)  ? (\App\Models\City::find($user->city)?->title  ?? $user->city)  : $user->city)  : null;
            $os = $user->state ? (is_numeric($user->state) ? (\App\Models\State::find($user->state)?->title ?? $user->state) : $user->state) : null;
        @endphp
        <div class="bg-white rounded-2xl border-2 {{ $isDone ? 'border-emerald-200' : 'border-dashed border-slate-200' }} shadow-sm p-5">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 {{ $isDone ? 'bg-emerald-100' : 'bg-teal-50' }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-map-location-dot {{ $isDone ? 'text-emerald-600' : 'text-teal-600' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">Location, Services & Pricing</p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ Where you work & what you charge</p>
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg {{ $isDone ? 'bg-emerald-100 text-emerald-700' : 'bg-red-50 text-red-500' }}">
                    {{ $isDone ? 'Complete' : 'Required' }}
                </span>
            </div>

            <div class="space-y-2 mb-3">
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-location-dot {{ $locDone ? 'text-emerald-500' : 'text-slate-300' }} shrink-0"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Service Area</p>
                            @if($locDone)
                                <p class="text-slate-500 truncate">
                                    {{ collect([$oc, $os])->filter()->implode(', ') }}
                                    @if($user->zip_code) · ZIP {{ $user->zip_code }}@endif
                                </p>
                            @else
                                <p class="text-slate-400">City, state, zip code not set</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('type.profile', ['seller', 'service_location']) }}"
                       class="shrink-0 text-[10px] font-bold text-teal-700 hover:underline">
                        {{ $locDone ? 'Edit' : 'Add' }}
                    </a>
                </div>

                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-list-check {{ $svcDone ? 'text-emerald-500' : 'text-slate-300' }} shrink-0"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Services & Pricing</p>
                            @if($svcDone)
                                <p class="text-slate-500">{{ $user->services->count() }} service{{ $user->services->count() > 1 ? 's' : '' }}:
                                    {{ $user->services->take(2)->pluck('title')->implode(', ') }}{{ $user->services->count() > 2 ? '...' : '' }}
                                </p>
                            @else
                                <p class="text-slate-400">No services listed yet</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('user.services.index') }}"
                       class="shrink-0 text-[10px] font-bold text-teal-700 hover:underline">
                        {{ $svcDone ? 'Manage' : 'Add' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- 6. Credentials & Trust — Fix 2: add Languages sub-row, Fix 5: no hover shadow --}}
        @php
            $eduDone  = $user->educations->count() > 0;
            $memDone  = $user->memberships->count() > 0;
            $expDone  = $user->experiences->count() > 0;
            $certDone = $user->certifications->count() > 0;
            $langDone = $langCount > 0;
            $isDone   = $done['credentials'];
        @endphp
        <div class="bg-white rounded-2xl border-2 {{ $isDone ? 'border-emerald-200' : 'border-dashed border-slate-200' }} shadow-sm p-5">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 {{ $isDone ? 'bg-emerald-100' : 'bg-teal-50' }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-award {{ $isDone ? 'text-emerald-600' : 'text-teal-600' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">Credentials & Trust</p>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ Experience, education, certifications & languages</p>
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg {{ $isDone ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $isDone ? 'Complete' : 'Optional' }}
                </span>
            </div>

            <div class="space-y-2 mb-3">
                {{-- Work Experience --}}
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-briefcase {{ $expDone ? 'text-emerald-500' : 'text-slate-300' }} shrink-0"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Work Experience</p>
                            @if($expDone)
                                <p class="text-slate-500 truncate">{{ $user->experiences->count() }} position{{ $user->experiences->count() > 1 ? 's' : '' }}:
                                    {{ $user->experiences->first()->title ?? '' }}{{ $user->experiences->first()->company ? ' at '.$user->experiences->first()->company : '' }}
                                </p>
                            @else
                                <p class="text-slate-400">Past roles, companies, positions</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('user.experiences.index') }}" class="shrink-0 text-[10px] font-bold text-teal-700 hover:underline">
                        {{ $expDone ? 'Manage' : 'Add' }}
                    </a>
                </div>

                {{-- Education --}}
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-graduation-cap {{ $eduDone ? 'text-emerald-500' : 'text-slate-300' }} shrink-0"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Education</p>
                            @if($eduDone)
                                <p class="text-slate-500 truncate">{{ $user->educations->count() }} record{{ $user->educations->count() > 1 ? 's' : '' }}:
                                    {{ $user->educations->first()->degree ?? '' }}{{ $user->educations->first()->institution ? ', '.$user->educations->first()->institution : '' }}
                                </p>
                            @else
                                <p class="text-slate-400">Degrees and academic qualifications</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('user.educations.index') }}" class="shrink-0 text-[10px] font-bold text-teal-700 hover:underline">
                        {{ $eduDone ? 'Manage' : 'Add' }}
                    </a>
                </div>

                {{-- Certifications --}}
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-certificate {{ $certDone ? 'text-emerald-500' : 'text-slate-300' }} shrink-0"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Certifications & Licenses</p>
                            @if($certDone)
                                <p class="text-slate-500 truncate">{{ $user->certifications->count() }} certification{{ $user->certifications->count() > 1 ? 's' : '' }}:
                                    {{ $user->certifications->first()->name ?? '' }}
                                </p>
                            @else
                                <p class="text-slate-400">{{ $certHint }}</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('user.certifications.index') }}" class="shrink-0 text-[10px] font-bold text-teal-700 hover:underline">
                        {{ $certDone ? 'Manage' : 'Add' }}
                    </a>
                </div>

                {{-- Memberships --}}
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-id-badge {{ $memDone ? 'text-emerald-500' : 'text-slate-300' }} shrink-0"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Memberships & Associations</p>
                            @if($memDone)
                                <p class="text-slate-500 truncate">{{ $user->memberships->count() }} membership{{ $user->memberships->count() > 1 ? 's' : '' }}:
                                    {{ $user->memberships->first()->name ?? '' }}
                                </p>
                            @else
                                <p class="text-slate-400">{{ $memberHint }}</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('user.memberships.index') }}" class="shrink-0 text-[10px] font-bold text-teal-700 hover:underline">
                        {{ $memDone ? 'Manage' : 'Add' }}
                    </a>
                </div>

                {{-- Fix 2: Languages sub-row --}}
                <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-xs flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-language {{ $langDone ? 'text-emerald-500' : 'text-slate-300' }} shrink-0"></i>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-700">Languages</p>
                            @if($langDone)
                                <p class="text-slate-500 truncate">
                                    {{ $user->languages->take(3)->pluck('name')->implode(', ') }}{{ $langCount > 3 ? ' +'.($langCount-3).' more' : '' }}
                                </p>
                            @else
                                <p class="text-slate-400">Languages you speak — builds trust with bilingual clients</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('user.languages.index') }}" class="shrink-0 text-[10px] font-bold text-teal-700 hover:underline">
                        {{ $langDone ? 'Manage' : 'Add' }}
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- Gallery card — optional for all seller types --}}
    <a href="{{ route('seller.gallery') }}"
       class="group mt-4 block bg-white rounded-2xl border-2 {{ $galleryDone ? 'border-emerald-200' : 'border-dashed border-slate-200 hover:border-teal-300' }} shadow-sm p-5 transition-all hover:shadow-md">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 {{ $galleryDone ? 'bg-emerald-100' : 'bg-teal-50' }} rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-images {{ $galleryDone ? 'text-emerald-600' : 'text-teal-600' }} text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">Work Gallery</p>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ Show your best work photos</p>
                </div>
            </div>
            <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg {{ $galleryDone ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $galleryDone ? $galleryCount.' '.Str::plural('photo', $galleryCount) : 'Optional' }}
            </span>
        </div>
        @if($galleryDone)
        <div class="flex gap-1.5 mt-2">
            @foreach($user->gallery()->limit(4)->get() as $gp)
            <img src="{{ $gp->image_url }}" class="w-12 h-10 rounded-lg object-cover border border-slate-100">
            @endforeach
            @if($galleryCount > 4)
            <div class="w-12 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-xs text-slate-500 font-bold">+{{ $galleryCount - 4 }}</div>
            @endif
        </div>
        @else
        <p class="text-xs text-slate-400">Upload photos of your work to build trust with potential clients</p>
        @endif
        <div class="mt-3 flex items-center justify-end">
            <span class="text-xs font-bold {{ $galleryDone ? 'text-slate-400 group-hover:text-teal-700' : 'text-teal-700' }} flex items-center gap-1 transition">
                <i class="fa-solid {{ $galleryDone ? 'fa-pen' : 'fa-plus' }} text-[10px]"></i>
                {{ $galleryDone ? 'Manage' : 'Upload Photos' }}
            </span>
        </div>
    </a>

    {{-- Fix 4: Schedule / Availability card --}}
    @php $scheduleDone = !is_null($user->schedule); @endphp
    <a href="{{ route('seller.schedule') }}"
       class="group mt-4 block bg-white rounded-2xl border-2 {{ $scheduleDone ? 'border-emerald-200' : 'border-dashed border-slate-200 hover:border-teal-300' }} shadow-sm p-5 transition-all hover:shadow-md">
        <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 {{ $scheduleDone ? 'bg-emerald-100' : 'bg-teal-50' }} rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calendar-days {{ $scheduleDone ? 'text-emerald-600' : 'text-teal-600' }} text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">Availability & Schedule</p>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide">→ When you're open for bookings</p>
                </div>
            </div>
            <span class="shrink-0 text-[10px] font-bold px-2 py-1 rounded-lg {{ $scheduleDone ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $scheduleDone ? 'Complete' : 'Optional' }}
            </span>
        </div>
        <p class="text-xs text-slate-400 mb-3">
            @if($scheduleDone) Your availability is set — clients know when to expect you
            @else Let clients know your working hours and days — reduces back-and-forth messages @endif
        </p>
        <div class="flex items-center justify-end">
            <span class="text-xs font-bold {{ $scheduleDone ? 'text-slate-400 group-hover:text-teal-700' : 'text-teal-700' }} flex items-center gap-1 transition">
                <i class="fa-solid {{ $scheduleDone ? 'fa-pen' : 'fa-plus' }} text-[10px]"></i>
                {{ $scheduleDone ? 'Edit Schedule' : 'Set Availability' }}
            </span>
        </div>
    </a>

    {{-- Bottom CTA --}}
    <div class="mt-6 bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            @if($pct >= 80)
                <p class="font-black text-slate-900">Your page looks great!</p>
                <p class="text-sm text-slate-500 mt-0.5">You're set — leads can find and contact you.</p>
            @else
                <p class="font-black text-slate-900">{{ 100 - $pct }}% left to complete</p>
                <p class="text-sm text-slate-500 mt-0.5">Complete sellers get 3× more leads on average.</p>
            @endif
        </div>
        <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}" target="_blank"
           class="shrink-0 w-full sm:w-auto text-center px-6 py-3 rounded-2xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-bold flex items-center justify-center gap-2 transition">
            <i class="fa-solid fa-eye"></i> Preview My Page
        </a>
    </div>

</div>
@endsection
