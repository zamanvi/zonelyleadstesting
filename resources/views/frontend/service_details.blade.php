@php
    $meta_title       = ($user->category?->title ? 'Trusted ' . $user->category->title . ' in ' : '') . ($user->city ? $user->city . ($user->state ? ', ' . $user->state : '') . ' | ' : '') . $user->name;
    $meta_description = $user->name . ($user->category?->title ? ' — verified ' . $user->category->title : '') . ($user->city ? ' in ' . $user->city : '') . '. ' . Str::limit(strip_tags($user->about ?? $user->bio ?? ''), 120);
@endphp
@extends('frontend.layouts._app')
@section('title', $meta_title)
@section('og_title',       $user->name)
@section('og_description', ($user->city ? $user->city.', '.$user->state : '') )
@section('og_image',       route('frontend.og.image', $user->slug).'?v='.($user->updated_at?->timestamp ?? 1))
@section('og_extra')
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type"   content="image/png">
@endsection

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "{{ $user->name }}",
  "description": "{{ Str::limit(strip_tags($user->about ?? $user->bio ?? ''), 200) }}",
  "url": "{{ url()->current() }}",
  "image": "{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo)) : '' }}",
  "@id": "{{ url()->current() }}",
  "priceRange": "Contact for pricing",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "{{ $user->city ?? '' }}",
    "addressRegion": "{{ $user->state ?? '' }}",
    "addressCountry": "US"
  }
  @if($user->contacts->where('type','phone')->first()),
  "telephone": "{{ $user->contacts->where('type','phone')->first()->value ?? '' }}"
  @endif
  @if($user->contacts->where('type','email')->first()),
  "email": "{{ $user->contacts->where('type','email')->first()->value ?? '' }}"
  @endif
}
</script>
@endsection

@section('content')
<main class="max-w-7xl mx-auto px-4 sm:px-6 pt-28 sm:pt-32 pb-12">

    {{-- ── Hero ────────────────────────────────────── --}}
    <div class="mb-10 sm:mb-14">
        <div class="flex flex-col sm:flex-row gap-8 sm:gap-12 items-start sm:items-end">

            {{-- Photo --}}
            <div class="relative group shrink-0 mx-auto sm:mx-0">
                <div class="absolute -inset-1 bg-gradient-to-r from-teal-700 to-violet-600 rounded-[2.5rem] blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                <div class="relative w-56 h-72 sm:w-64 sm:h-80 md:w-72 md:h-[380px] rounded-[2.2rem] overflow-hidden border-4 border-white shadow-2xl">
                    <img src="{{ str_starts_with($user->profile_photo ?? '', 'http') ? $user->profile_photo : asset($user->profile_photo ?? '') }}"
                         class="w-full h-full object-cover" alt="{{ $user->name }}"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0F766E&color=fff&size=400'">
                </div>
            </div>

            {{-- Info --}}
            <div class="flex-1 text-center sm:text-left pb-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 text-teal-700 text-[10px] font-bold uppercase tracking-widest mb-4">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-700"></span>
                    </span>
                    Verified Professional
                </div>

                <h1 class="font-serif text-2xl sm:text-3xl md:text-5xl text-slate-900 mb-4 leading-tight">
                    {{ $user->title ?? $user->name }}
                </h1>

                <blockquote class="border-l-4 border-teal-700 pl-4 sm:pl-6 italic text-slate-700 font-medium bg-slate-50 py-3 sm:py-4 rounded-r-2xl text-sm sm:text-base">
                    "{{ $user->bio ?? 'Dedicated professional committed to excellence.' }}"
                </blockquote>

                @if(!empty($user->tags))
                @php $tags = array_filter(array_map('trim', explode(',', $user->tags))); @endphp
                @if(count($tags))
                <div class="mt-6">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Services Offered</p>
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                        @foreach($tags as $tag)
                        <span class="px-3 py-1.5 rounded-full border border-slate-200 text-xs text-slate-700 bg-white hover:bg-teal-50 hover:border-teal-200 hover:text-teal-800 transition cursor-default">
                            {{ ucfirst($tag) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ── Main grid: content left, sidebar right ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

        {{-- ── Sidebar — ORDER-FIRST on mobile ──── --}}
        <div class="lg:col-span-4 order-first lg:order-last lg:sticky lg:top-28 h-fit space-y-5">

            {{-- Contact card --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xl shadow-teal-600/5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1.5 animate-gradient rounded-t-3xl"></div>

                <h3 class="text-lg sm:text-xl font-bold text-slate-900 mb-1">Work with {{ $user->name }}</h3>
                <p class="text-slate-500 text-sm mb-6">Get in touch directly below.</p>

                <div class="space-y-3">
                    @forelse($user->contacts as $contact)
                    @php
                        if ($contact->type === 'phone') continue; // Phone hidden until Twilio ready
                        $icon  = match($contact->type) { 'email'=>'fas fa-envelope','address'=>'fas fa-map-marker-alt','whatsapp'=>'fab fa-whatsapp',default=>'fas fa-info-circle' };
                        $href  = match($contact->type) { 'email'=>'mailto:'.$contact->value,'whatsapp'=>'https://wa.me/'.preg_replace('/[^0-9]/','', $contact->value),default=>'#' };
                        $color = match($contact->type) { 'whatsapp'=>'bg-emerald-500 hover:bg-emerald-600',default=>'bg-slate-800 hover:bg-slate-700' };
                    @endphp
                    <a href="{{ $href }}"
                       class="flex items-center gap-3 w-full {{ $color }} text-white font-bold py-3.5 px-5 rounded-2xl transition text-sm">
                        <i class="{{ $icon }} w-4 text-center"></i>
                        <span class="truncate">{{ $contact->value }}</span>
                    </a>
                    @empty
                    <p class="text-sm text-slate-400 text-center py-2">No contact info available.</p>
                    @endforelse
                </div>

                @auth
                @if(auth()->user()->type === 'user')
                <a href="{{ route('buyer.book', $user->slug ?? $user->id) ?? '#' }}"
                   class="mt-4 flex items-center justify-center gap-2 w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-3.5 rounded-2xl transition text-sm">
                    <i class="fa-solid fa-calendar-plus"></i> Book Appointment
                </a>
                @endif
                @else
                <a href="{{ route('user.login') }}"
                   class="mt-4 flex items-center justify-center gap-2 w-full border-2 border-teal-700 text-teal-700 hover:bg-teal-50 font-bold py-3.5 rounded-2xl transition text-sm">
                    <i class="fa-solid fa-calendar-plus"></i> Book Appointment
                </a>
                @endauth

                <p class="mt-5 text-[10px] text-center text-slate-400 uppercase tracking-widest">Secured by Zonely</p>
            </div>

            {{-- Languages --}}
            @if($user->languages->count())
            <div class="bg-slate-900 p-6 sm:p-8 rounded-3xl text-white">
                <h3 class="text-xs font-bold uppercase tracking-widest text-teal-400 mb-4">Languages</h3>
                <div class="space-y-2">
                    @foreach($user->languages as $lang)
                    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></div>
                        <span class="text-sm font-semibold">{{ $lang->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ── Main content ──────────────────────── --}}
        <div class="lg:col-span-8 space-y-8 order-last lg:order-first">

            {{-- Stats strip --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bento-card bg-white p-5 rounded-3xl border border-slate-100 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Experience</p>
                    <p class="text-xl sm:text-2xl font-black text-slate-900">{{ $user->experience ?? '5+' }} Yrs</p>
                </div>
                <div class="bento-card bg-teal-700 p-5 rounded-3xl text-center">
                    <p class="text-[10px] font-bold text-teal-200 uppercase mb-1">Success Rate</p>
                    <p class="text-xl sm:text-2xl font-black text-white">98%</p>
                </div>
                <div class="bento-card bg-white p-5 rounded-3xl border border-slate-100 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Cases Won</p>
                    <p class="text-xl sm:text-2xl font-black text-slate-900">450+</p>
                </div>
                <div class="bento-card bg-slate-800 p-5 rounded-3xl text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Response</p>
                    <p class="text-xl sm:text-2xl font-black text-white">&lt;2h</p>
                </div>
            </div>

            {{-- About --}}
            <section class="bg-white rounded-3xl p-7 sm:p-10 border border-slate-100 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-[0.04]">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.017 21v-3c0-1.105.895-2 2-2h3v-2c0-2.761-2.238-5-5-5V7c3.866 0 7 3.134 7 7v7h-7zm-11 0v-3c0-1.105.895-2 2-2h3v-2c0-2.761-2.238-5-5-5V7c3.866 0 7 3.134 7 7v7H3.017z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-5">About</h2>
                <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                    <strong>{{ $user->name }}</strong>{{ $user->title ? ', '.$user->title : '' }} —
                    {{ $user->about ?? 'No about information available.' }}
                </p>
            </section>

            {{-- Experience & Membership --}}
            @if($user->experiences->count() || $user->memberships->count())
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-teal-700 px-6 py-4 flex items-center gap-3">
                    <i class="fas fa-briefcase text-white"></i>
                    <h3 class="font-bold text-base text-white">Experience & Membership</h3>
                </div>
                <div class="grid grid-cols-1 {{ $user->experiences->count() && $user->memberships->count() ? 'sm:grid-cols-2' : '' }} divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                    @if($user->experiences->count())
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-indigo-50">
                            <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-briefcase text-indigo-600 text-xs"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-indigo-600">Work Experience</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($user->experiences as $exp)
                            <div class="border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                                <p class="text-sm font-bold text-slate-800">{{ $exp->title }}</p>
                                @if($exp->company)<p class="text-xs text-indigo-600 font-medium mt-0.5">{{ $exp->company }}</p>@endif
                                @if($exp->start_date)<p class="text-xs text-slate-400 mt-0.5">{{ $exp->start_date }} – {{ $exp->is_current ? 'Present' : ($exp->end_date ?? '') }}</p>@endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($user->memberships->count())
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-teal-50">
                            <div class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-id-badge text-teal-700 text-xs"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-teal-700">Memberships</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($user->memberships as $m)
                            <div class="border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                                <p class="text-sm font-semibold text-slate-800">{{ $m->name }}</p>
                                @if($m->start || $m->end)<p class="text-xs text-slate-400 mt-0.5">{{ $m->start ?? '' }}{{ ($m->start && $m->end) ? ' – ' : '' }}{{ $m->end ?? 'Present' }}</p>@endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Education & Certification --}}
            @if($user->educations->count() || $user->certifications->count())
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-600 to-amber-500 px-6 py-4 flex items-center gap-3">
                    <i class="fas fa-graduation-cap text-white"></i>
                    <h3 class="font-bold text-base text-white">Education & Certification</h3>
                </div>
                <div class="grid grid-cols-1 {{ $user->educations->count() && $user->certifications->count() ? 'sm:grid-cols-2' : '' }} divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                    @if($user->educations->count())
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-emerald-50">
                            <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-graduation-cap text-emerald-600 text-xs"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">Education</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($user->educations as $edu)
                            <div class="flex items-start gap-3 border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                                <div class="px-2 py-1 rounded-lg bg-teal-50 text-teal-800 text-xs font-bold shrink-0">{{ $edu->degree }}</div>
                                <div>
                                    @if($edu->institution)<p class="text-sm font-semibold text-slate-800 leading-snug">{{ $edu->institution }}</p>@endif
                                    @if($edu->passing_year)<p class="text-xs text-slate-400 mt-0.5">{{ $edu->passing_year }}</p>@endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($user->certifications->count())
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-amber-50">
                            <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-certificate text-amber-600 text-xs"></i>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-amber-600">Certifications</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($user->certifications as $cert)
                            <div class="flex items-start gap-3 border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                                <i class="fas fa-award text-amber-500 mt-0.5 text-sm shrink-0"></i>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ $cert->name }}</p>
                                    @if($cert->issuer)<p class="text-xs text-amber-600 mt-0.5">{{ $cert->issuer }}</p>@endif
                                    @if($cert->issued_year)<p class="text-xs text-slate-400">{{ $cert->issued_year }}{{ $cert->expiry_year ? ' – '.$cert->expiry_year : '' }}</p>@endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Services list --}}
            @if($user->services->count())
            <section class="bg-white rounded-3xl p-7 sm:p-10 border border-slate-100 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-5">Services</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($user->services as $svc)
                    <div class="flex items-center gap-3 p-4 rounded-2xl border border-slate-100 bg-slate-50">
                        <div class="w-8 h-8 bg-teal-100 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check text-teal-700 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $svc->title }}</p>
                            @if($svc->description)
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $svc->description }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

        </div>
    </div>
</main>
@endsection
