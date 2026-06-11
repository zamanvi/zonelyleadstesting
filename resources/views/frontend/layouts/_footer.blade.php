@php
    $footerFacebook   = \App\Models\Setting::get('social_facebook', 'https://www.facebook.com/profile.php?id=61581047693543');
    $footerLinkedin   = \App\Models\Setting::get('social_linkedin', 'https://www.linkedin.com/company/102732925/admin/dashboard');
    $footerEmail      = \App\Models\Setting::get('support_email');
    $footerWhatsapp   = \App\Models\Setting::get('support_whatsapp');
    $footerWaLink     = $footerWhatsapp ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $footerWhatsapp) : null;
    $sisterName       = \App\Models\Setting::get('sister_site_name', 'Sister Site');
    $sisterUrl        = \App\Models\Setting::get('sister_site_url', 'https://migotrucking.com');
    $copyrightRaw     = \App\Models\Setting::get('copyright_text', '© {{year}} Zonely. Empowering Local Experts.');
    $copyrightText    = str_replace('{{year}}', date('Y'), $copyrightRaw);
@endphp

<div id="zonely-platform-footer" class="bg-slate-950 text-white">
<footer class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-8">

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-10 mb-12">

        {{-- Brand --}}
        <div class="col-span-2 lg:col-span-1">
            <a href="{{ route('frontend.home') }}"
               class="inline-block text-2xl font-black mb-4 hover:text-teal-400 transition tracking-tight" style="min-height:unset;min-width:unset;">
                ZONELY<span class="text-teal-600">.</span>
            </a>
            <p class="text-sm text-slate-400 leading-relaxed mb-6 max-w-xs">
                Find verified local experts near you. Plumbers, lawyers, tax professionals, and more — all in one place.
            </p>
            <div class="flex gap-2.5">
                @if($footerFacebook)
                <a href="{{ $footerFacebook }}" target="_blank" rel="noopener"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/5 hover:bg-teal-700 text-slate-400 hover:text-white transition" style="min-height:unset;min-width:unset;">
                    <i class="fab fa-facebook text-sm"></i>
                </a>
                @endif
                @if($footerLinkedin)
                <a href="{{ $footerLinkedin }}" target="_blank" rel="noopener"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/5 hover:bg-teal-700 text-slate-400 hover:text-white transition" style="min-height:unset;min-width:unset;">
                    <i class="fab fa-linkedin text-sm"></i>
                </a>
                @endif
            </div>
        </div>

        {{-- Explore --}}
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-500 mb-5">Explore</p>
            <ul class="space-y-3.5">
                <li><a href="{{ route('frontend.service.all') }}"  class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">Browse Professionals</a></li>
                <li><a href="{{ route('frontend.tools') }}"        class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">Free Tools</a></li>
                <li><a href="{{ route('frontend.blog') }}"         class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">Blog</a></li>
                <li><a href="{{ route('frontend.help') }}"         class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">Help Center</a></li>
                <li><a href="{{ route('frontend.about-us') }}"     class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">About Us</a></li>
            </ul>
        </div>

        {{-- Legal --}}
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-500 mb-5">Legal</p>
            <ul class="space-y-3.5">
                <li><a href="{{ route('frontend.privacy-policy') }}"      class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">Privacy Policy</a></li>
                <li><a href="{{ route('frontend.terms-and-condition') }}" class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">Terms of Service</a></li>
                @if($sisterUrl)
                <li><a href="{{ $sisterUrl }}" target="_blank" rel="noopener" class="text-sm text-slate-400 hover:text-white transition" style="min-height:unset;">{{ $sisterName }}</a></li>
                @endif
            </ul>
        </div>

        {{-- CTA + Support --}}
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-500 mb-5">For Professionals</p>
            <p class="text-sm text-slate-400 leading-relaxed mb-5">
                Get qualified leads in your area. Free to join. Pay per verified lead only.
            </p>
            <a href="{{ route('user.register', 'seller') }}"
               class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-600 text-white text-sm font-bold px-5 py-3 rounded-xl transition shadow-lg shadow-teal-900/30 mb-6" style="min-height:unset;">
                <i class="fa-solid fa-briefcase text-xs"></i>
                List Your Business — It's Free
            </a>

            @if($footerEmail || $footerWaLink)
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-500 mb-3">Support</p>
            <ul class="space-y-2">
                @if($footerEmail)
                <li>
                    <a href="mailto:{{ $footerEmail }}" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-2" style="min-height:unset;">
                        <i class="fas fa-envelope text-xs text-teal-500"></i> {{ $footerEmail }}
                    </a>
                </li>
                @endif
                @if($footerWaLink)
                <li>
                    <a href="{{ $footerWaLink }}" target="_blank" rel="noopener" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-2" style="min-height:unset;">
                        <i class="fab fa-whatsapp text-xs text-green-500"></i> WhatsApp Us
                    </a>
                </li>
                @endif
            </ul>
            @endif
        </div>

    </div>

    <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-slate-600">{{ $copyrightText }}</p>
        <p class="text-xs text-slate-600">Made with <span class="text-red-500">♥</span> for local communities across the USA</p>
    </div>

</footer>
</div>
