@php
    $meta_title       = $meta_title ?? 'All Professionals';
    $meta_description = $meta_description ?? '';
    $meta_keywords    = $meta_keywords ?? '';
@endphp
@extends('frontend.layouts._app')
@section('title', 'All Professionals')
@section('content')

{{-- ── Header ───────────────────────────────────────── --}}
<header class="mt-16 sm:mt-20 max-w-7xl mx-auto px-4 sm:px-6 pt-8 sm:pt-10 pb-6 sm:pb-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            @if(isset($category))
            <p class="text-xs font-bold text-teal-700 uppercase tracking-widest mb-1">
                @if($category->parent) {{ $category->parent->title }} · @endif Browse
            </p>
            @endif
            <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl text-slate-900 mb-1">
                {{ isset($category) ? $category->title : 'Top Professionals' }}
            </h1>
            <p class="text-slate-500 text-sm sm:text-base italic">
                Showing verified experts
                @if(isset($city)) in <span class="text-teal-700 font-bold">{{ $city }}</span>@endif
            </p>
            @if(isset($category) && $category->children->count())
            <div class="flex flex-wrap gap-2 mt-3">
                <a href="{{ route('frontend.category', $category->slug) }}"
                   class="px-3 py-1.5 bg-slate-900 text-white rounded-xl text-xs font-bold">All</a>
                @foreach($category->children as $child)
                <a href="{{ route('frontend.category', $child->slug) }}"
                   class="px-3 py-1.5 bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-600 rounded-xl text-xs font-semibold transition">
                    {{ $child->title }}
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Search bar --}}
        <form action="{{ route('frontend.service.search') }}" method="GET"
              class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Service..."
                class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 sm:w-36">
            <input type="text" name="city" value="{{ request('city') }}" placeholder="City or ZIP"
                class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 sm:w-32">
            <button type="submit"
                class="bg-amber-500 hover:bg-amber-400 text-slate-900 px-4 py-2.5 rounded-xl text-sm font-bold transition shrink-0">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

    {{-- Filter pills --}}
    @if($isSearch ?? false)
    <div class="flex gap-2 mt-4 overflow-x-auto scroll-hide pb-1">
        <button class="shrink-0 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold">All</button>
        <button class="shrink-0 px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 border border-slate-200 text-xs font-semibold transition">Lawyers</button>
        <button class="shrink-0 px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 border border-slate-200 text-xs font-semibold transition">Designers</button>
        <button class="shrink-0 px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 border border-slate-200 text-xs font-semibold transition">Tax Experts</button>
        <button class="shrink-0 px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 border border-slate-200 text-xs font-semibold transition">Plumbers</button>
    </div>
    @endif
</header>

{{-- ── Grid ─────────────────────────────────────────── --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 pb-12">

    @if($users->count())
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @foreach($users as $user)
        @php
            $specialty = $user->title ?? $user->designation ?? $user->category?->title ?? 'Professional';
            $specialty = Str::before($specialty, '|');
            $specialty = Str::limit(trim($specialty), 40);
            $initials  = strtoupper(substr($user->name, 0, 2));
        @endphp
        <div class="group bg-white rounded-2xl border border-slate-100 overflow-hidden hover:shadow-lg hover:border-teal-100 transition-all duration-300 flex flex-col">

            {{-- Photo --}}
            <div class="relative h-44 sm:h-52 bg-slate-100 overflow-hidden">
                @if($user->profile_photo)
                <img src="{{ asset($user->profile_photo) }}"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                     class="w-full h-full object-cover object-top grayscale group-hover:grayscale-0 transition duration-500"
                     alt="{{ $user->name }}" loading="lazy">
                <div class="hidden w-full h-full bg-teal-700 items-center justify-center text-white font-black text-3xl">
                    {{ $initials }}
                </div>
                @else
                <div class="w-full h-full bg-teal-700 flex items-center justify-center text-white font-black text-3xl">
                    {{ $initials }}
                </div>
                @endif

                @if($user->status)
                <span class="absolute top-3 left-3 bg-white text-teal-700 text-[9px] font-black px-2.5 py-1 rounded-full shadow-sm tracking-widest uppercase">
                    ✓ Verified
                </span>
                @endif
            </div>

            {{-- Info --}}
            <div class="p-4 flex flex-col flex-1 justify-between">
                <div>
                    <h3 class="font-serif text-base sm:text-lg text-slate-900 leading-snug truncate">
                        {{ $user->name }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $specialty }}</p>
                    @if($user->city)
                    <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-[10px] text-teal-400"></i>
                        {{ $user->city }}@if($user->state), {{ $user->state }}@endif
                    </p>
                    @endif
                    <div class="flex items-center gap-1 mt-2">
                        @for($i=0;$i<5;$i++)<i class="fa-solid fa-star text-amber-400 text-[9px]"></i>@endfor
                        <span class="text-xs font-semibold text-slate-600 ml-1">4.9</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-4">
                    <a href="{{ route('frontend.service.show', $user->slug ?? $user->id) }}"
                       class="flex-1 text-center bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold px-4 py-2.5 rounded-xl transition"
                       style="min-height:unset;">
                        View Profile
                    </a>
                    @auth
                    @if(auth()->user()->type === 'user')
                    <a href="{{ route('buyer.book', $user->slug ?? $user->id) }}"
                       class="bg-teal-50 hover:bg-teal-100 text-teal-700 px-3 py-2.5 rounded-xl text-xs font-bold transition shrink-0"
                       style="min-height:unset;" title="Book">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </a>
                    @endif
                    @endauth
                </div>
            </div>

        </div>
        @endforeach
    </div>
    @else
    {{-- ── Empty State ── --}}
    <div class="py-16 px-4 max-w-2xl mx-auto">

        {{-- Icon + heading --}}
        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-slate-100 rounded-3xl flex items-center justify-center mx-auto mb-5">
                <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-800">
                No {{ isset($category) ? $category->title : 'professionals' }} found
                @if(isset($city)) in {{ $city }}@endif
            </h2>
            <p class="text-slate-500 text-sm mt-2">We're growing fast — new professionals join every week.</p>
        </div>

        {{-- Two cards side by side --}}
        <div class="grid sm:grid-cols-2 gap-4 mb-8">

            {{-- Card 1: For the visitor (buyer) --}}
            <div class="bg-teal-50 border border-teal-100 rounded-3xl p-6">
                <div class="w-10 h-10 bg-teal-700 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fa-solid fa-bell text-white text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1">Get Notified</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">Leave your details — we'll alert you the moment a {{ isset($category) ? $category->title : 'professional' }} joins in your area.</p>
                <a href="{{ route('user.register1') }}"
                   class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition"
                   style="min-height:unset;">
                    <i class="fa-solid fa-arrow-right text-[10px]"></i> Create Free Account
                </a>
            </div>

            {{-- Card 2: For the seller --}}
            <div class="bg-amber-50 border border-amber-100 rounded-3xl p-6">
                <div class="w-10 h-10 bg-amber-500 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fa-solid fa-briefcase text-white text-sm"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1">
                    Are you a {{ isset($category) ? $category->title : 'professional' }}?
                </h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">Be the first in your area. Join free — pay only when you get a verified lead.</p>
                <a href="{{ route('user.register', 'seller') }}"
                   class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold px-4 py-2.5 rounded-xl transition"
                   style="min-height:unset;">
                    <i class="fa-solid fa-rocket text-[10px]"></i> Join Free — Be First
                </a>
            </div>

        </div>

        {{-- Related categories --}}
        @if(isset($category) && $category->parent && $category->parent->children->count() > 1)
        <div class="bg-white border border-slate-100 rounded-3xl p-6 mb-6">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Try a related category</p>
            <div class="flex flex-wrap gap-2">
                @foreach($category->parent->children->where('id', '!=', $category->id)->take(6) as $sibling)
                <a href="{{ route('frontend.category', $sibling->slug) }}"
                   class="px-3 py-1.5 bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-700 hover:text-teal-700 text-xs font-semibold rounded-xl transition"
                   style="min-height:unset;">
                    {{ $sibling->title }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Browse all fallback --}}
        <div class="text-center">
            <a href="{{ route('frontend.service.all') }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-teal-700 transition"
               style="min-height:unset;">
                <i class="fa-solid fa-grid-2 text-xs"></i> Browse all professionals
            </a>
        </div>

    </div>
    @endif

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="mt-10 sm:mt-14">
        <nav class="flex flex-wrap items-center justify-center gap-2">

            @if($users->onFirstPage())
            <span class="px-4 py-2.5 text-sm rounded-xl bg-slate-100 text-slate-400 cursor-not-allowed font-semibold">← Prev</span>
            @else
            <a href="{{ $users->previousPageUrl() }}"
               class="px-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 hover:bg-slate-900 hover:text-white hover:border-slate-900 font-semibold transition"
               style="min-height:unset;">← Prev</a>
            @endif

            @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
            @if($page == $users->currentPage())
            <span class="px-4 py-2.5 text-sm rounded-xl bg-slate-900 text-white font-bold">{{ $page }}</span>
            @else
            <a href="{{ $url }}"
               class="px-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 hover:bg-slate-900 hover:text-white hover:border-slate-900 font-semibold transition"
               style="min-height:unset;">{{ $page }}</a>
            @endif
            @endforeach

            @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}"
               class="px-4 py-2.5 text-sm rounded-xl bg-white border border-slate-200 hover:bg-slate-900 hover:text-white hover:border-slate-900 font-semibold transition"
               style="min-height:unset;">Next →</a>
            @else
            <span class="px-4 py-2.5 text-sm rounded-xl bg-slate-100 text-slate-400 cursor-not-allowed font-semibold">Next →</span>
            @endif

        </nav>
        <p class="text-center text-xs text-slate-400 mt-3">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} professionals
        </p>
    </div>
    @endif

</main>
@endsection
