@extends('frontend.layouts._app')
@section('title', ($type === 'seller' ? 'Join as a Local Expert' : 'Create Account') . ' — Zonely')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4 pt-24 pb-16">
    <div class="w-full {{ $type === 'seller' ? 'max-w-2xl' : 'max-w-sm' }}">

        @if($type === 'seller')
        {{-- Seller progress steps --}}
        <div class="flex items-center justify-center gap-2 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-teal-700 rounded-full flex items-center justify-center text-white text-xs font-black">1</div>
                <span class="text-xs font-bold text-teal-700 hidden sm:inline">Create Account</span>
            </div>
            <div class="w-8 h-px bg-slate-200 mx-1"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center text-slate-400 text-xs font-black">2</div>
                <span class="text-xs font-bold text-slate-400 hidden sm:inline">Business Type</span>
            </div>
            <div class="w-8 h-px bg-slate-200 mx-1"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center text-slate-400 text-xs font-black">3</div>
                <span class="text-xs font-bold text-slate-400 hidden sm:inline">Profile Setup</span>
            </div>
        </div>
        @endif

        {{-- Card --}}
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 sm:p-10">

            {{-- Header --}}
            <div class="text-center mb-8">
                <a href="{{ route('frontend.home') }}" class="inline-block mb-4">
                    <img src="{{ asset('frontend/img/zonely_logo.png') }}" class="w-12 h-12 mx-auto" alt="Zonely"
                         onerror="this.src='https://ui-avatars.com/api/?name=Z&background=2563eb&color=fff&size=48'">
                </a>
                @if($type === 'seller')
                    <h1 class="text-2xl font-black text-slate-900">Join as a Local Expert</h1>
                    <p class="text-sm text-slate-500 mt-1">Free to join · Pay only for verified leads</p>
                @else
                    <h1 class="text-2xl font-black text-slate-900">Create Your Account</h1>
                    <p class="text-sm text-slate-500 mt-1">Takes 10 seconds · No credit card needed</p>
                @endif
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-exclamation text-xs"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Form --}}
            <form id="registerForm" action="{{ route('user.submit.register') }}" method="POST"
                  data-track="sign_up" data-track-register="1">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                @if($type === 'user')
                {{-- ── BUYER: 3 fields only ── --}}
                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required autofocus value="{{ old('name') }}"
                               placeholder="Your name"
                               class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                               placeholder="you@example.com"
                               class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required minlength="6"
                               placeholder="Min. 6 characters"
                               class="w-full px-4 py-3.5 pr-12 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                        <button type="button" onclick="togglePwd('password',this)"
                                class="absolute right-4 top-[38px] text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>

                </div>

                @else
                {{-- ── SELLER: full form ── --}}
                <div class="grid md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Business Owner Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               placeholder="e.g. John Smith"
                               class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Business Name <span class="text-slate-400 font-normal text-xs">(optional)</span>
                        </label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}"
                               placeholder="e.g. Smith Legal Services"
                               class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                               placeholder="you@example.com"
                               class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone" required value="{{ old('phone') }}"
                               placeholder="+1 (555) 123-4567"
                               class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required minlength="6"
                               placeholder="Min. 6 characters"
                               class="w-full px-4 py-3.5 pr-12 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                        <button type="button" onclick="togglePwd('password',this)"
                                class="absolute right-4 top-[38px] text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="confirmPassword" name="confirm_password" required minlength="6"
                               placeholder="Repeat password"
                               class="w-full px-4 py-3.5 pr-12 rounded-2xl border border-slate-200 focus:border-teal-700 focus:ring-4 focus:ring-teal-100 outline-none transition text-sm">
                        <button type="button" onclick="togglePwd('confirmPassword',this)"
                                class="absolute right-4 top-[38px] text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>

                </div>
                @endif

                {{-- Terms --}}
                <div class="flex items-start gap-3 mt-6">
                    <input type="checkbox" id="terms" required
                           class="mt-0.5 w-4 h-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600 shrink-0">
                    <label for="terms" class="text-xs text-slate-500 leading-relaxed">
                        By creating an account you agree to our
                        <a href="{{ route('frontend.terms-and-condition') }}" class="text-teal-700 font-bold hover:underline" target="_blank">Terms</a> and
                        <a href="{{ route('frontend.privacy-policy') }}" class="text-teal-700 font-bold hover:underline" target="_blank">Privacy Policy</a>.
                    </label>
                </div>

                <button type="submit"
                        class="w-full mt-6 bg-amber-500 hover:bg-amber-400 text-slate-900 py-4 rounded-2xl font-black text-base flex items-center justify-center gap-2 shadow-lg transition">
                    {{ $type === 'seller' ? 'Create My Free Account' : 'Get Started' }}
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

            </form>

            <p class="text-center text-sm text-slate-500 mt-6">
                Already have an account?
                <a href="{{ route('user.login') }}" class="text-teal-700 font-bold hover:underline">Sign in</a>
            </p>

            @if($type === 'user')
            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    No account needed to contact a seller — you can inquire directly from any service page.
                </p>
            </div>
            @endif

        </div>

        @if($type === 'seller')
        <div class="flex flex-wrap justify-center gap-5 mt-8 text-xs text-slate-400">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-green-500"></i> 100% Free to Join</span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-lock text-teal-600"></i> Secure & Private</span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-teal-600"></i> No Credit Card Required</span>
        </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

@if($type === 'seller')
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const pwd     = document.getElementById('password').value;
    const confirm = document.getElementById('confirmPassword').value;
    if (pwd !== confirm) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
@endif
</script>
@endsection
