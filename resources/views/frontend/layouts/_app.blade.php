<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Robots ─────────────────────────────────────── --}}
    <meta name="robots" content="index, follow, max-image-preview:large">

    {{-- SEO ────────────────────────────────────────── --}}
    @php
        $canonicalUrl  = url()->current();
        $ogTitle       = trim($__env->yieldContent('og_title'))       ?: ($meta_title ?? config('app.name'));
        $ogDescription = trim($__env->yieldContent('og_description'))  ?: ($meta_description ?? '');
        $ogImage       = trim($__env->yieldContent('og_image'))        ?: asset('frontend/img/zonely_logo.png');
        if ($ogImage && !str_starts_with($ogImage, 'http')) {
            $ogImage = asset($ogImage);
        }
    @endphp
    <title>Zonely — @yield('title', 'Find & Hire Local Experts Near You')</title>
    <meta name="title"       content="{{ $ogTitle }}">
    <meta name="description" content="{{ Str::limit(strip_tags($ogDescription), 160) }}">
    @if($meta_keywords ?? false)<meta name="keywords" content="{{ $meta_keywords }}">@endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph ──────────────────────────────────── --}}
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ $canonicalUrl }}">
    <meta property="og:title"       content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($ogDescription), 200) }}">
    <meta property="og:image"       content="{{ $ogImage }}">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type"   content="image/png">
    <meta property="og:site_name"   content="Zonely">
    @yield('og_extra')

    {{-- Twitter Card ────────────────────────────────── --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($ogDescription), 200) }}">
    <meta name="twitter:image"       content="{{ $ogImage }}">

    {{-- PWA / Mobile ─────────────────────────────────── --}}
    <meta name="theme-color"                  content="#2a8c87">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="format-detection"             content="telephone=yes">

    {{-- Favicon ─────────────────────────────────────── --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('frontend/img/zonely_logo.png') }}" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('frontend/img/zonely_logo.png') }}">

    <meta name="google-site-verification" content="dwwJ-8RPBJ7ZKJVORVBjX84ehyNkdpSXMj3JsAqlZZQ">

    {{-- JSON-LD slot (pages inject schema here) ──────── --}}
    @yield('schema')

    @include('frontend.layouts._styles')
    @yield('css')
</head>

<body class="bg-[#fcfdfe] text-slate-900 @auth has-bottom-nav @endauth">

    @include('frontend.layouts._header')

    @yield('content')

    @auth
        @include('frontend.layouts._account_nav')
    @endauth

    @unless(View::hasSection('hideLayoutFooter'))
        @include('frontend.layouts._footer')
    @endunless

    @include('frontend.layouts._scripts')

    <script>
    // Mobile menu toggle
    document.getElementById('menuBtn')?.addEventListener('click', () => {
        document.getElementById('mobileMenu').classList.toggle('hidden');
    });
    // Mobile submenu accordion
    document.querySelectorAll('.mobile-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.nextElementSibling?.classList.toggle('hidden');
        });
    });
    // Nav scroll shadow
    (function() {
        const nav = document.getElementById('mainNav');
        if (!nav) return;
        window.addEventListener('scroll', () => {
            nav.classList.toggle('drop-shadow-xl', window.scrollY > 10);
        }, { passive: true });
    })();
    </script>

    @yield('scripts')
</body>
</html>
