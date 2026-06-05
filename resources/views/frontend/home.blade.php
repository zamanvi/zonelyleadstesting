@php
    $meta_title       = 'Discover & Hire Local Experts Near Me | Zonely';
    $meta_description = 'Find verified local plumbers, lawyers, tax experts, electricians and more. Real reviews, same-day available. Serving the USA.';
    $meta_keywords    = 'local experts near me, plumber, lawyer, tax expert, electrician, locksmith, Zonely';
@endphp
@extends('frontend.layouts._app')
@section('title', 'Discover & Hire Local Experts Near Me')
@section('hideLayoutFooter', true)

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "url": "{{ url('/') }}",
  "name": "Zonely",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "{{ route('frontend.service.search') }}?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
@endsection

@section('css')
<style>
    .pro-card { transition: box-shadow .2s ease, transform .2s ease; }
    .pro-card:hover { transform: translateY(-2px); box-shadow: 0 16px 40px -12px rgba(0,0,0,0.12); }
    .search-pill:focus-within { box-shadow: 0 0 0 3px rgba(15,118,110,0.18); }
    .cat-card { transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease; }
    .cat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px -8px rgba(15,118,110,0.18); border-color: #0F766E; }
    .review-card { transition: box-shadow .2s ease; }
    .review-card:hover { box-shadow: 0 12px 32px -8px rgba(0,0,0,0.10); }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    @keyframes countup { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    .stat-num { animation: countup .6s ease both; }
</style>
@endsection

@section('content')

{{-- ═══ HERO ═══ --}}
<section class="pt-28 pb-14 px-4 sm:px-6 bg-gradient-to-b from-teal-50/60 via-white to-white">
    <div class="max-w-3xl mx-auto text-center">

        <div class="inline-flex items-center gap-2 bg-teal-100 text-teal-800 text-xs font-bold px-4 py-1.5 rounded-full mb-6 tracking-wide">
            <i class="fa-solid fa-shield-halved text-teal-600 text-xs"></i> Verified local professionals near you
        </div>

        <h1 class="font-serif text-3xl sm:text-5xl lg:text-7xl text-slate-900 leading-[1.05] tracking-tight mb-5">
            Discover &amp; Hire<br>
            <em class="text-teal-700" style="font-style:italic;">Local Experts</em> Near Me
        </h1>
        <p class="text-slate-500 text-base sm:text-lg mb-10 max-w-lg mx-auto leading-relaxed">
            Access the top 1% of verified professionals in your area. Fast, secure, and expert-led.
        </p>

        {{-- Single search pill --}}
        <div class="relative max-w-xl mx-auto">
            <form action="{{ route('frontend.service.search') }}" method="GET" id="searchForm">
                <div class="search-pill flex items-center bg-white border border-slate-200 rounded-full shadow-md px-5 py-1 gap-3 transition">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm shrink-0"></i>
                    <input type="text" name="q" id="searchQ" autocomplete="off"
                           placeholder="Who are you looking for?"
                           class="flex-1 text-sm text-slate-800 placeholder-slate-400 bg-transparent py-3 font-medium focus:outline-none">
                    <button type="submit"
                            class="shrink-0 bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold px-6 py-2.5 rounded-full transition" style="min-height:unset;">
                        Search
                    </button>
                </div>
            </form>

            {{-- Live results --}}
            <div id="liveResults" class="hidden absolute left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden mt-2">
                <div id="liveResultsList"></div>
                <a id="liveResultsMore" href="#"
                   class="block text-center text-xs font-bold text-teal-700 py-3 border-t border-slate-100 hover:bg-slate-50 transition">
                    See all results →
                </a>
            </div>
        </div>

        {{-- Popular searches --}}
        <div class="mt-6 flex flex-wrap gap-2 justify-center">
            @foreach([
                ['q'=>'Plumber','icon'=>'fa-wrench'],
                ['q'=>'Electrician','icon'=>'fa-bolt'],
                ['q'=>'Locksmith','icon'=>'fa-key'],
                ['q'=>'Tax Expert','icon'=>'fa-calculator'],
                ['q'=>'Lawyer','icon'=>'fa-scale-balanced'],
                ['q'=>'HVAC','icon'=>'fa-wind'],
                ['q'=>'Handyman','icon'=>'fa-screwdriver-wrench'],
                ['q'=>'Cleaning','icon'=>'fa-broom'],
            ] as $s)
            <a href="{{ route('frontend.service.search') }}?q={{ urlencode($s['q']) }}"
               class="shrink-0 flex items-center gap-1.5 bg-slate-50 border border-slate-200 hover:border-teal-300 hover:bg-teal-50 hover:text-teal-800 px-4 py-2 rounded-full text-sm font-medium text-slate-600 transition" style="min-height:unset;">
                <i class="fa-solid {{ $s['icon'] }} text-teal-600 text-xs"></i>
                {{ $s['q'] }}
            </a>
            @endforeach
        </div>

        <p class="mt-6 text-sm text-slate-400">
            Are you a professional?
            <a href="{{ route('user.register', 'seller') }}" class="text-teal-700 font-semibold hover:underline">List your business free →</a>
        </p>

    </div>
</section>

{{-- ═══ TRUST STRIP ═══ --}}
<div class="bg-slate-900 py-3.5 px-4">
    <div class="max-w-3xl mx-auto flex items-center justify-center gap-3 sm:gap-8 flex-wrap">
        @foreach([
            ['icon'=>'fa-star','color'=>'text-amber-400','text'=>'4.9 avg rating'],
            ['icon'=>'fa-circle-check','color'=>'text-emerald-400','text'=>'All pros verified'],
            ['icon'=>'fa-lock','color'=>'text-teal-400','text'=>'Secure booking'],
            ['icon'=>'fa-tag','color'=>'text-violet-400','text'=>'No subscription fees'],
        ] as $t)
        <span class="flex items-center gap-2 text-xs font-semibold text-slate-300">
            <i class="fa-solid {{ $t['icon'] }} {{ $t['color'] }} text-xs"></i> {{ $t['text'] }}
        </span>
        @endforeach
    </div>
</div>

{{-- ═══ STATS BAR ═══ --}}
<div class="bg-white border-b border-slate-100">
    <div class="max-w-3xl mx-auto px-4 py-8 grid grid-cols-3 gap-4 text-center">
        <div>
            @if($stats['pros'] > 0)
            <div class="stat-num text-2xl sm:text-4xl font-black text-teal-700" data-target="{{ $stats['pros'] }}" data-suffix="+">{{ $stats['pros'] }}+</div>
            @else
            <div class="text-2xl sm:text-4xl font-black text-teal-700">Growing</div>
            @endif
            <div class="text-[10px] sm:text-xs text-slate-500 font-semibold mt-1 uppercase tracking-wide">Verified Pros</div>
        </div>
        <div class="border-x border-slate-100">
            @if($stats['reviews'] > 0)
            <div class="stat-num text-2xl sm:text-4xl font-black text-teal-700" data-target="{{ $stats['reviews'] }}" data-suffix="+">{{ $stats['reviews'] }}+</div>
            @else
            <div class="text-2xl sm:text-4xl font-black text-teal-700">5★</div>
            @endif
            <div class="text-[10px] sm:text-xs text-slate-500 font-semibold mt-1 uppercase tracking-wide">Client Reviews</div>
        </div>
        <div>
            @if($stats['cities'] > 0)
            <div class="stat-num text-2xl sm:text-4xl font-black text-teal-700" data-target="{{ $stats['cities'] }}" data-suffix="+">{{ $stats['cities'] }}+</div>
            @else
            <div class="text-2xl sm:text-4xl font-black text-teal-700">USA</div>
            @endif
            <div class="text-[10px] sm:text-xs text-slate-500 font-semibold mt-1 uppercase tracking-wide">Cities Covered</div>
        </div>
    </div>
</div>

{{-- ═══ HOW IT WORKS ═══ --}}
<section class="py-16 px-4 sm:px-6 bg-white border-b border-slate-100">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-[10px] font-black uppercase tracking-widest text-teal-600 mb-2">Simple Process</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">How Zonely Works</h2>
        </div>
        <div class="grid sm:grid-cols-3 gap-6 sm:gap-10 relative">
            {{-- connector line desktop --}}
            <div class="hidden sm:block absolute top-8 left-1/3 right-1/3 h-px bg-slate-200 -translate-y-px z-0"></div>
            @foreach([
                ['step'=>'1','icon'=>'fa-magnifying-glass','title'=>'Search & Discover','body'=>'Type what you need — plumber, lawyer, tax expert. Filter by city or ZIP. See real profiles with reviews.'],
                ['step'=>'2','icon'=>'fa-address-card','title'=>'Review & Compare','body'=>'Read verified client reviews, check credentials, see service areas, and compare pros side by side.'],
                ['step'=>'3','icon'=>'fa-handshake','title'=>'Connect & Hire','body'=>'Call, WhatsApp, or send a booking request directly. No middlemen. No waiting. Pay the pro directly.'],
            ] as $s)
            <div class="flex flex-col items-center text-center relative z-10">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 border-2 border-teal-100 flex items-center justify-center mb-4 relative">
                    <i class="fa-solid {{ $s['icon'] }} text-teal-700 text-xl"></i>
                    <span class="absolute -top-2.5 -right-2.5 w-6 h-6 rounded-full bg-amber-500 text-slate-900 text-[10px] font-black flex items-center justify-center">{{ $s['step'] }}</span>
                </div>
                <h3 class="font-bold text-slate-900 mb-2">{{ $s['title'] }}</h3>
                <p class="text-sm text-slate-500 leading-relaxed">{{ $s['body'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ CATEGORIES ═══ --}}
@if($categories->count())
<section class="py-14 px-4 sm:px-6 bg-slate-50 border-b border-slate-100">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Browse By Category</p>
                <h2 class="text-xl font-extrabold text-slate-900">What do you need help with?</h2>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($categories->take(8) as $cat)
            <a href="{{ route('frontend.service.search') }}?q={{ urlencode($cat->title) }}"
               class="cat-card bg-white border border-slate-200 rounded-2xl p-5 flex flex-col items-center gap-3 text-center">
                <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center">
                    <i class="fa-solid fa-briefcase text-teal-700 text-lg"></i>
                </div>
                <span class="text-sm font-bold text-slate-800">{{ $cat->title }}</span>
                @if($cat->children_count ?? false)
                <span class="text-[10px] text-slate-400 font-semibold">{{ $cat->children_count }} specialties</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══ FEATURED EXPERTS ═══ --}}
<section class="py-14 px-4 sm:px-6 bg-white">
    <div class="max-w-3xl mx-auto">

        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Featured Experts Nearby</p>
                <div class="w-10 h-0.5 bg-slate-900 rounded-full"></div>
            </div>
            <a href="{{ route('frontend.service.all') }}"
               class="text-sm font-semibold text-slate-600 hover:text-teal-700 border border-slate-200 hover:border-teal-300 px-4 py-2 rounded-full transition" style="min-height:unset;">
                See All
            </a>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            @forelse(($users ?? collect())->take(8) as $user)
            @php $initials = strtoupper(substr($user->name, 0, 2)); @endphp
            <div class="pro-card bg-white border border-slate-100 rounded-2xl overflow-hidden flex shadow-sm">

                {{-- Photo --}}
                <div class="relative w-28 sm:w-36 shrink-0 self-stretch">
                    @if($user->profile_photo)
                    <img src="{{ str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo) }}"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                         class="w-full h-full object-cover object-center absolute inset-0" style="min-height:160px;">
                    <div class="w-full h-full bg-teal-700 items-center justify-center text-white font-black text-2xl absolute inset-0" style="display:none;min-height:160px;">
                        {{ $initials }}
                    </div>
                    @else
                    <div class="w-full h-full bg-teal-700 flex items-center justify-center text-white font-black text-2xl absolute inset-0" style="min-height:160px;">
                        {{ $initials }}
                    </div>
                    @endif
                    @if($user->status)
                    <span class="absolute bottom-3 left-3 bg-emerald-500 text-white text-[9px] font-black px-2.5 py-1 rounded-full tracking-widest uppercase flex items-center gap-1">
                        <i class="fa-solid fa-circle-check text-[8px]"></i> Verified
                    </span>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex flex-col justify-between p-5 flex-1 min-w-0">
                    <div>
                        <h3 class="font-serif text-base sm:text-lg font-bold text-slate-900 leading-tight truncate">
                            {{ $user->name }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 truncate">
                            {{ $user->title ?? $user->designation ?? ($user->category?->title ?? 'Professional') }}
                            @if($user->city) · {{ $user->city }}@endif
                        </p>
                        @php $rCount = $user->reviews->count(); $rAvg = $rCount ? round($user->reviews->avg('rating'),1) : null; @endphp
                        @if($rAvg)
                        <div class="flex items-center gap-1 mt-2">
                            @for($i=1;$i<=5;$i++)<i class="fa-solid fa-star text-amber-400 text-[9px]{{ $i > $rAvg ? ' opacity-30' : '' }}"></i>@endfor
                            <span class="text-xs font-semibold text-slate-600 ml-1">{{ $rAvg }} ({{ $rCount }})</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-4">
                        <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}"
                           class="bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold px-5 py-2 rounded-full transition shadow-sm" style="min-height:unset;">
                            Hire Expert
                        </a>
                        <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}"
                           class="text-sm text-slate-400 hover:text-teal-700 font-semibold transition" style="min-height:unset;">
                            View Profile →
                        </a>
                    </div>
                </div>

            </div>
            @empty
            <div class="sm:col-span-2 text-center text-slate-400 py-16">
                <i class="fa-solid fa-users text-slate-200 text-4xl mb-4 block"></i>
                <p class="text-sm font-medium">No professionals found yet.</p>
            </div>
            @endforelse
        </div>

    </div>
</section>

{{-- ═══ REVIEWS ═══ --}}
@if($featuredReviews->count())
<section class="py-14 px-4 sm:px-6 bg-slate-50 border-t border-slate-100">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-10">
            <p class="text-[10px] font-black uppercase tracking-widest text-teal-600 mb-2">Client Reviews</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">What Clients Are Saying</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($featuredReviews as $review)
            <div class="review-card bg-white rounded-2xl border border-slate-100 p-5 flex flex-col gap-3">
                <div class="flex items-center gap-1">
                    @for($i=1;$i<=5;$i++)<i class="fa-solid fa-star text-amber-400 text-xs{{ $i > $review->rating ? ' opacity-25' : '' }}"></i>@endfor
                </div>
                <p class="text-sm text-slate-600 leading-relaxed flex-1">"{{ Str::limit($review->review ?? 'Excellent service!', 120) }}"</p>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-50">
                    <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-black text-xs shrink-0">
                        {{ strtoupper(substr($review->reviewer?->name ?? $review->reviewer_name ?? 'C', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $review->reviewer?->name ?? $review->reviewer_name ?? 'Client' }}</p>
                        @if($review->seller)
                        <p class="text-[10px] text-slate-400 truncate">for <a href="{{ route('frontend.service.show', $review->seller->slug ?? $review->seller->id) }}" class="hover:text-teal-700 transition">{{ $review->seller->name }}</a></p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══ FOR PROS CTA ═══ --}}
<div class="h-20 bg-gradient-to-b from-white to-slate-950"></div>
<section class="pb-0 px-4 sm:px-6 bg-slate-950">
    <div class="max-w-4xl mx-auto">
        <div class="bg-gradient-to-r from-teal-800 to-indigo-700 rounded-3xl overflow-hidden relative shadow-2xl">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.08),transparent_60%)] pointer-events-none"></div>
            <div class="relative px-6 sm:px-14 py-10 sm:py-12 flex flex-col sm:flex-row items-center gap-8 sm:gap-10">
                <div class="flex-1">
                    <span class="inline-block text-[11px] font-black text-teal-200 uppercase tracking-widest mb-4">For Professionals</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-3 leading-snug">
                        Get qualified leads.<br>Pay only per call.
                    </h2>
                    <p class="text-teal-100 text-sm leading-relaxed mb-8 max-w-sm opacity-85">
                        Customers in your area are searching right now. Your verified profile shows up first. No subscription. No ads.
                    </p>
                    <a href="{{ route('user.register', 'seller') }}"
                       class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold px-7 py-3.5 rounded-xl text-sm transition shadow-lg" style="min-height:unset;">
                        Create free profile →
                    </a>
                </div>
                <div class="shrink-0 grid grid-cols-1 gap-3 w-full sm:w-56">
                    @foreach([
                        ['icon'=>'fa-circle-check','text'=>'Free to join'],
                        ['icon'=>'fa-circle-check','text'=>'Pay per verified lead only'],
                        ['icon'=>'fa-circle-check','text'=>'Real-time call forwarding'],
                        ['icon'=>'fa-circle-check','text'=>'Dashboard to track all leads'],
                    ] as $f)
                    <div class="flex items-center gap-3">
                        <i class="fa-solid {{ $f['icon'] }} text-emerald-300 shrink-0"></i>
                        <span class="text-sm font-semibold text-white">{{ $f['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@php
    $searchUsersJson = ($users ?? collect())->map(fn($u) => [
        'id'    => $u->id,
        'name'  => $u->name,
        'slug'  => $u->slug ?? $u->id,
        'title' => $u->title ?? $u->designation ?? null,
        'city'  => $u->city ?? null,
        'status'=> (bool)($u->status ?? false),
        'photo' => $u->profile_photo ?? null,
    ])->values()->toJson();
@endphp
{{-- ═══ FOOTER ═══ --}}
<footer class="bg-slate-950 border-t border-slate-800 pt-12 pb-8 px-4 sm:px-6">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 mb-10">
            <div class="col-span-2 sm:col-span-1">
                <span class="text-white font-extrabold text-xl tracking-tight">Zonely<span class="text-teal-600">.</span></span>
                <p class="text-slate-400 text-xs mt-3 leading-relaxed max-w-xs">Find verified local professionals near you. Fast, trusted, and transparent.</p>
            </div>
            <div>
                <p class="text-slate-300 font-bold text-xs uppercase tracking-widest mb-4">Explore</p>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('frontend.service.all') }}" class="hover:text-white transition">All Professionals</a></li>
                    <li><a href="{{ route('frontend.service.search') }}?q=Lawyer" class="hover:text-white transition">Lawyers</a></li>
                    <li><a href="{{ route('frontend.service.search') }}?q=Plumber" class="hover:text-white transition">Plumbers</a></li>
                    <li><a href="{{ route('frontend.service.search') }}?q=Tax+Expert" class="hover:text-white transition">Tax Experts</a></li>
                </ul>
            </div>
            <div>
                <p class="text-slate-300 font-bold text-xs uppercase tracking-widest mb-4">For Pros</p>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('user.register', 'seller') }}" class="hover:text-white transition">Join Free</a></li>
                    <li><a href="{{ route('user.login') }}" class="hover:text-white transition">Sign In</a></li>
                </ul>
            </div>
            <div>
                <p class="text-slate-300 font-bold text-xs uppercase tracking-widest mb-4">Company</p>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('frontend.about-us') }}" class="hover:text-white transition">About Us</a></li>
                    <li><a href="{{ route('frontend.contact') }}" class="hover:text-white transition">Contact</a></li>
                    <li><a href="{{ route('frontend.privacy-policy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                    <li><a href="{{ route('frontend.terms-and-condition') }}" class="hover:text-white transition">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">&copy; {{ date('Y') }} Zonely. All rights reserved.</p>
            <p class="text-xs text-slate-600">Connecting clients with verified local professionals across the USA.</p>
        </div>
    </div>
</footer>

@section('scripts')
<script>
// Stats counter animation on scroll
(function() {
    const stats = document.querySelectorAll('.stat-num[data-target]');
    if (!stats.length) return;
    const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            const target = parseInt(el.dataset.target);
            const suffix = el.dataset.suffix || '';
            if (isNaN(target)) return;
            let start = 0;
            const duration = 1200;
            const step = timestamp => {
                if (!start) start = timestamp;
                const p = Math.min((timestamp - start) / duration, 1);
                const ease = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.floor(ease * target) + suffix;
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
            io.unobserve(el);
        });
    }, { threshold: 0.5 });
    stats.forEach(el => io.observe(el));
})();

(function() {
    const allUsers = {!! $searchUsersJson !!};
    const input    = document.getElementById('searchQ');
    const box      = document.getElementById('liveResults');
    const list     = document.getElementById('liveResultsList');
    const moreLink = document.getElementById('liveResultsMore');
    const base     = "{{ route('frontend.service.search') }}";

    function avatar(u) {
        const i = (u.name || 'ZZ').substring(0,2).toUpperCase();
        const src = u.photo ? (u.photo.startsWith('storage/') || u.photo.startsWith('/storage/') ? '/'+u.photo.replace(/^\//,'') : '/storage/'+u.photo) : null;
        return src
            ? `<img src="${src}" onerror="this.style.display='none'" class="w-10 h-10 rounded-xl object-cover shrink-0">`
            : `<div class="w-10 h-10 bg-teal-700 rounded-xl flex items-center justify-center text-white font-black text-sm shrink-0">${i}</div>`;
    }

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        if (q.length < 2) { box.classList.add('hidden'); return; }
        const hits = allUsers.filter(u =>
            (u.name  && u.name.toLowerCase().includes(q))  ||
            (u.title && u.title.toLowerCase().includes(q)) ||
            (u.city  && u.city.toLowerCase().includes(q))
        ).slice(0, 5);
        if (!hits.length) { box.classList.add('hidden'); return; }
        list.innerHTML = hits.map(u => `
            <a href="/service/${u.slug}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition">
                ${avatar(u)}
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-900 text-sm truncate">${u.name || ''}</p>
                    <p class="text-xs text-slate-400 truncate">${u.title || 'Professional'}${u.city ? ' · '+u.city : ''}</p>
                </div>
                ${u.status ? '<span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded-full shrink-0">✓ Verified</span>' : ''}
            </a>`).join('');
        moreLink.href = base + '?q=' + encodeURIComponent(q);
        box.classList.remove('hidden');
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !box.contains(e.target)) box.classList.add('hidden');
    });
})();
</script>
@endsection

@endsection
