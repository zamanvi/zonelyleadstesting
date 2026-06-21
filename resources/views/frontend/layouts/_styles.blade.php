{{-- ── Preconnect (reduces DNS + TLS handshake time for CDN assets) ── --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com">

{{-- ── Fonts — display=swap prevents invisible text during load ── --}}
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

{{-- ── Font Awesome ── --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

{{-- ── Tailwind (compiled build — no CDN runtime overhead) ── --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    /* ── Base ────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }

    html {
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
        scroll-behavior: smooth;
        overflow-x: hidden;
    }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        padding-bottom: env(safe-area-inset-bottom);
    }

    /* ── Mobile viewport fix ─────────────────── */
    .min-h-screen { min-height: 100svh; }

    /* ── Touch targets — 44px minimum ───────── */
    button, a, [role="button"] { min-height: 44px; min-width: 44px; }

    /* ── Images ──────────────────────────────── */
    img, video { max-width: 100%; height: auto; }

    /* ── Fonts ───────────────────────────────── */
    .font-serif { font-family: 'DM Serif Display', Georgia, serif; }

    /* ── Glass ───────────────────────────────── */
    .glass {
        background: rgba(255,255,255,.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.4);
    }

    /* ── Cards ───────────────────────────────── */
    .bento-card { transition: transform .4s cubic-bezier(.175,.885,.32,1.275), box-shadow .4s; }
    @media (hover: hover) {
        .bento-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,.05); }
    }

    /* ── Gradient animation ──────────────────── */
    @keyframes gradient {
        0%,100% { background-position: 0% 50%; }
        50%      { background-position: 100% 50%; }
    }
    .animate-gradient {
        background: linear-gradient(-45deg,#2a8c87,#7c3aed,#db2777);
        background-size: 400% 400%;
        animation: gradient 15s ease infinite;
    }

    /* ── Scrollbar hide ──────────────────────── */
    .scroll-hide { -ms-overflow-style:none; scrollbar-width:none; }
    .scroll-hide::-webkit-scrollbar { display:none; }

    /* ── Form inputs ─────────────────────────── */
    .input {
        width:100%; padding:.75rem 1rem;
        border:1px solid #e2e8f0; border-radius:1rem;
        font-size:.875rem; outline:none;
        -webkit-appearance:none; appearance:none;
        transition:border-color .15s,box-shadow .15s;
    }
    .input:focus { border-color:#32a29d; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

    .btn-primary {
        display:inline-flex; align-items:center; justify-content:center;
        width:100%; padding:.875rem 1.5rem;
        background:#2a8c87; color:#fff;
        font-weight:700; font-size:.875rem;
        border-radius:1rem; border:none; cursor:pointer;
        transition:background .15s,transform .1s;
    }
    .btn-primary:hover  { background:#1e6e6a; }
    .btn-primary:active { transform:scale(.98); }

    /* ── Bottom nav ──────────────────────────── */
    .bottom-nav-safe { padding-bottom: calc(.75rem + env(safe-area-inset-bottom)); }
    .has-bottom-nav  { padding-bottom: calc(4.5rem + env(safe-area-inset-bottom)); }
    .has-bottom-nav footer { padding-bottom: calc(4.5rem + env(safe-area-inset-bottom)); }
    .bnav-item { transition:color .15s,background .15s; }
    .bnav-item.active { color:#2a8c87; }
    .bnav-item.active .bnav-icon { background:#F0FDFA; }

    /* ── Focus ring ──────────────────────────── */
    :focus-visible { outline:2px solid #32a29d; outline-offset:2px; }

    /* ── Tap highlight ───────────────────────── */
    * { -webkit-tap-highlight-color:rgba(59,130,246,.08); }


    /* ── Mobile type scale (320–375px phones) ── */
    @media (max-width:375px) {
        .text-3xl { font-size:1.5rem; }
        .text-4xl { font-size:1.75rem; }
        .text-5xl { font-size:2rem; }
        .text-6xl { font-size:2.5rem; }
        .text-7xl { font-size:3rem; }
    }

    /* ── Prevent iOS input zoom (needs 16px) ─── */
    @media (max-width:767px) {
        input,textarea,select { font-size:16px !important; }
    }
</style>
