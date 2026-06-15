<nav id="mainNav" class="fixed top-0 w-full z-[100] px-3 sm:px-4 md:px-8 py-3 sm:py-4 transition-all duration-300">
    <div class="max-w-7xl mx-auto glass rounded-2xl px-4 sm:px-6 py-3 shadow-sm">

        <div class="flex justify-between items-center">

            {{-- LOGO --}}
            <a href="{{ route('frontend.home') }}" class="flex items-center gap-2.5 shrink-0" style="min-height:unset;min-width:unset;">
                <img src="{{ asset('frontend/img/zonely_logo.png') }}" class="w-9 h-9 sm:w-10 sm:h-10" alt="Zonely">
                <span class="text-base font-extrabold text-teal-700 tracking-tight hidden sm:inline">ZONELY<span class="text-teal-900">.</span></span>
            </a>

            {{-- DESKTOP MENU --}}
            <div class="hidden lg:flex gap-5 xl:gap-7 text-[13px] font-semibold tracking-wide text-slate-600">
                @foreach ($allMenuCategories ?? [] as $category)
                <div class="relative group">
                    <a href="{{ route('frontend.category', $category->slug) }}" class="hover:text-teal-700 transition flex items-center gap-1.5 py-2 whitespace-nowrap" style="min-height:unset;">
                        {{ $category->title }}
                        @if ($category->children->count())
                        <svg class="w-3 h-3 mt-[2px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                        @endif
                    </a>
                    @if ($category->children->count())
                    <div class="absolute left-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        @foreach ($category->children as $child)
                        <a href="{{ route('frontend.category', $child->slug) }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition" style="min-height:unset;">
                            {{ $child->title }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach

                {{-- Others dropdown --}}
                <div class="relative group">
                    <a href="#" class="hover:text-teal-700 transition flex items-center gap-1.5 py-2 whitespace-nowrap" style="min-height:unset;">
                        Others
                        <svg class="w-3 h-3 mt-[2px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                    <div class="absolute left-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="{{ route('frontend.tools') }}"  class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition" style="min-height:unset;">Tools</a>
                        <a href="{{ route('frontend.blog') }}"   class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition" style="min-height:unset;">Blog</a>
                        <a href="{{ route('frontend.help') }}"   class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition" style="min-height:unset;">Help</a>
                    </div>
                </div>
            </div>

            {{-- DESKTOP AUTH --}}
            <div class="hidden lg:flex items-center gap-3">
                @auth
                <div class="relative" id="desktopUserMenu">
                    <button onclick="document.getElementById('desktopUserDropdown').classList.toggle('hidden')" class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-bold transition" style="min-height:unset;">
                        <span class="w-6 h-6 bg-teal-600 rounded-full flex items-center justify-center text-[10px] font-black shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        {{ Str::limit(auth()->user()->name, 14) }}
                        <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="desktopUserDropdown" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition font-semibold" style="min-height:unset;">
                            <i class="fas fa-gauge-high w-4 text-center text-slate-400"></i> Dashboard
                        </a>
                        <div class="border-t border-slate-100"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition font-semibold" style="min-height:unset;">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('user.login') }}"    class="text-xs font-bold text-slate-600 hover:text-teal-700 px-3 py-2 transition" style="min-height:unset;">Log in</a>
                <a href="{{ route('user.register1') }}" class="bg-teal-700 hover:bg-teal-800 text-white px-5 py-2 rounded-xl text-xs font-bold transition" style="min-height:unset;">Get Started</a>
                @endauth
            </div>

            {{-- MOBILE RIGHT SIDE --}}
            <div class="flex lg:hidden items-center gap-2">
                @auth
                <div class="relative" id="mobileUserMenu">
                    <button onclick="document.getElementById('mobileUserDropdown').classList.toggle('hidden')" class="w-9 h-9 bg-teal-700 text-white rounded-full flex items-center justify-center font-bold text-xs shrink-0" style="min-height:unset;min-width:unset;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </button>
                    <div id="mobileUserDropdown" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition font-semibold" style="min-height:unset;">
                            <i class="fas fa-gauge-high w-4 text-center text-slate-400"></i> Dashboard
                        </a>
                        <div class="border-t border-slate-100"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition font-semibold" style="min-height:unset;">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
                <button id="menuBtn" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-700 hover:bg-slate-100 transition" aria-label="Open menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

        </div>

        {{-- MOBILE MENU --}}
        <div id="mobileMenu" class="hidden mt-4 pb-2 space-y-1">

            @foreach ($allMenuCategories ?? [] as $category)
            <div>
                @if ($category->children->count())
                <button class="mobile-toggle w-full flex justify-between items-center px-3 py-3 text-sm font-semibold text-slate-700 rounded-xl hover:bg-slate-50 transition">
                    {{ $category->title }}
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="mobile-submenu hidden pl-4 pb-1 space-y-0.5">
                    <a href="{{ route('frontend.category', $category->slug) }}" class="block px-3 py-2.5 text-sm font-semibold text-teal-700 rounded-xl hover:bg-slate-50 transition">
                        All {{ $category->title }}
                    </a>
                    @foreach ($category->children as $child)
                    <a href="{{ route('frontend.category', $child->slug) }}" class="block px-3 py-2.5 text-sm text-slate-600 hover:text-teal-700 rounded-xl hover:bg-slate-50 transition">
                        {{ $child->title }}
                    </a>
                    @endforeach
                </div>
                @else
                <a href="{{ route('frontend.category', $category->slug) }}" class="block px-3 py-3 text-sm font-semibold text-slate-700 rounded-xl hover:bg-slate-50 transition">
                    {{ $category->title }}
                </a>
                @endif
            </div>
            @endforeach

            {{-- Others --}}
            <div>
                <button class="mobile-toggle w-full flex justify-between items-center px-3 py-3 text-sm font-semibold text-slate-700 rounded-xl hover:bg-slate-50 transition">
                    Others
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="mobile-submenu hidden pl-4 pb-1 space-y-0.5">
                    <a href="{{ route('frontend.tools') }}" class="block px-3 py-2.5 text-sm text-slate-600 hover:text-teal-700 rounded-xl hover:bg-slate-50 transition">Tools</a>
                    <a href="{{ route('frontend.blog') }}"  class="block px-3 py-2.5 text-sm text-slate-600 hover:text-teal-700 rounded-xl hover:bg-slate-50 transition">Blog</a>
                    <a href="{{ route('frontend.help') }}"  class="block px-3 py-2.5 text-sm text-slate-600 hover:text-teal-700 rounded-xl hover:bg-slate-50 transition">Help</a>
                </div>
            </div>

            {{-- Auth buttons --}}
            <div class="pt-3 border-t border-slate-100 flex gap-3">
                @auth
                <a href="{{ route('dashboard') }}" class="flex-1 py-3 bg-slate-900 text-white text-center text-sm font-bold rounded-2xl hover:bg-slate-800 transition">
                    Dashboard
                </a>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-3 border border-red-200 text-red-600 text-sm font-bold rounded-2xl hover:bg-red-50 transition">
                        Logout
                    </button>
                </form>
                @else
                <a href="{{ route('user.login') }}"     class="flex-1 py-3 border border-slate-200 text-slate-700 text-center text-sm font-bold rounded-2xl hover:bg-slate-50 transition">Log in</a>
                <a href="{{ route('user.register1') }}" class="flex-1 py-3 bg-teal-700 text-white text-center text-sm font-bold rounded-2xl hover:bg-teal-800 transition">Get Started</a>
                @endauth
            </div>

        </div>
    </div>
</nav>

<script>
// Close user dropdowns when clicking outside
document.addEventListener('click', function(e) {
    ['desktopUserMenu','mobileUserMenu'].forEach(function(id) {
        var menu = document.getElementById(id);
        if (menu && !menu.contains(e.target)) {
            var dd = menu.querySelector('[id$="Dropdown"]');
            if (dd) dd.classList.add('hidden');
        }
    });
});
</script>
