@php
    $cr = Route::currentRouteName();
    $navItems = [
        ['route' => route('seller.dashboard'),     'name' => 'seller.dashboard',     'icon' => 'fa-gauge-high',          'label' => 'Dashboard'],
        ['route' => route('seller.onboarding'),    'name' => 'seller.onboarding',    'icon' => 'fa-user-pen',            'label' => 'Profile'],
        ['route' => route('seller.settings'),      'name' => 'seller.settings',      'icon' => 'fa-gear',                'label' => 'Settings'],
        ['route' => route('seller.pricing'),       'name' => 'seller.pricing',       'icon' => 'fa-tag',                 'label' => 'Pricing'],
        ['route' => route('seller.billing'),       'name' => 'seller.billing',       'icon' => 'fa-file-invoice-dollar', 'label' => 'Billing'],
        ['route' => route('seller.schedule'),      'name' => 'seller.schedule',      'icon' => 'fa-calendar-days',       'label' => 'Schedule'],
        ['route' => route('seller.reviews'),       'name' => 'seller.reviews',       'icon' => 'fa-star',                'label' => 'Reviews'],
        ['route' => route('seller.notifications'), 'name' => 'seller.notifications', 'icon' => 'fa-bell',                'label' => 'Notifications'],
        ['route' => route('seller.affiliate'),     'name' => 'seller.affiliate',     'icon' => 'fa-link',                'label' => 'Affiliate'],
    ];
@endphp

{{-- ── DESKTOP TOP SUBNAV ───────────────────────────────────────── --}}
<div class="hidden lg:flex fixed left-0 right-0 top-[68px] z-30 bg-white border-b border-slate-100 shadow-sm">
    <div class="w-full max-w-7xl mx-auto px-6 flex items-stretch">
        @foreach($navItems as $item)
        <a href="{{ $item['route'] }}"
           class="flex items-center gap-2 px-4 py-3.5 text-sm font-semibold whitespace-nowrap border-b-2 transition-all
                  {{ $cr === $item['name']
                      ? 'border-teal-700 text-teal-700'
                      : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300' }}">
            <i class="fa-solid {{ $item['icon'] }} text-[13px]"></i>
            {{ $item['label'] }}
        </a>
        @endforeach
        <div class="ml-auto flex items-center pl-4 shrink-0">
            <a href="{{ route('frontend.service.show', auth()->user()->slug ?? auth()->user()->id) }}"
               target="_blank"
               class="flex items-center gap-1.5 text-xs font-bold text-teal-700 hover:underline" title="Opens in new tab">
                <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> View Public Page <i class="fa-solid fa-external-link text-[9px] opacity-60"></i>
            </a>
        </div>
    </div>
</div>

{{-- ── MOBILE BOTTOM NAV ────────────────────────────────────────── --}}
<nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 z-40 flex justify-around px-1 py-1 shadow-lg">
    @foreach($navItems as $item)
    <a href="{{ $item['route'] }}"
       class="flex flex-col items-center gap-0.5 flex-1 py-2 rounded-xl text-center transition
              {{ $cr === $item['name'] ? 'text-teal-700' : 'text-slate-400 hover:text-teal-700' }}">
        <i class="fa-solid {{ $item['icon'] }} text-[15px]"></i>
        <span class="text-[10px] font-bold leading-none mt-0.5">{{ $item['label'] }}</span>
    </a>
    @endforeach
</nav>
