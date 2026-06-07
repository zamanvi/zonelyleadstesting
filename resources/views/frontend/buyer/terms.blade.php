@extends('frontend.layouts._app')
@section('title', 'Buyer Terms & Conditions — Zonely')

@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-16 px-4">
<div class="max-w-3xl mx-auto py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-teal-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-file-contract text-white text-2xl"></i>
        </div>
        <span class="text-teal-700 text-[11px] font-black uppercase tracking-widest mb-2 block">Legal — Buyers</span>
        <h1 class="text-2xl font-black text-slate-900">Buyer Terms & Conditions</h1>
        <p class="text-slate-500 text-sm mt-2">Please read and agree before continuing to Zonely as a Buyer</p>
        <p class="text-slate-400 text-xs mt-1">Last updated: <time datetime="2026-06-01">June 2026</time></p>
    </div>

    {{-- T&C Card --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">

        {{-- Scrollable content --}}
        <div class="overflow-y-auto max-h-[65vh] px-6 sm:px-8 py-6 text-sm text-slate-600 leading-relaxed space-y-6"
             id="termsContent">

            {{-- Intro --}}
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Last updated: June 2026</p>
                <p>Welcome to <strong class="text-slate-900">Zonely</strong>. These Terms & Conditions govern your use of the Zonely platform as a <strong class="text-slate-900">Buyer</strong> (someone seeking local services). By creating a buyer account, you agree to these terms in full.</p>
            </div>

            {{-- 1 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">1</span>
                    Platform Overview
                </h3>
                <p>Zonely is a local service discovery platform that connects buyers with trusted local professionals and businesses across the United States. Buyers can search, compare, and contact local service providers at no charge.</p>
            </div>

            {{-- 2 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">2</span>
                    Eligibility
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>You must be at least <strong class="text-slate-900">18 years old</strong> to create an account.</li>
                    <li>You must provide accurate, current, and complete information during registration.</li>
                    <li>One account per individual. Duplicate accounts may be removed.</li>
                    <li>You are responsible for keeping your login credentials confidential.</li>
                </ul>
            </div>

            {{-- 3 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">3</span>
                    Using the Platform — Buyer Rules
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Buyers may browse listings and submit service inquiries to sellers <strong class="text-slate-900">at no charge</strong>.</li>
                    <li>All inquiry information submitted must be genuine, accurate, and not misleading.</li>
                    <li>Spam, fake, test, or malicious inquiries are strictly prohibited. Accounts submitting fraudulent inquiries will be permanently banned.</li>
                    <li>Zonely is not a party to any transaction or service agreement between buyers and sellers. All agreements are directly between you and the seller.</li>
                    <li>Zonely does not guarantee the quality, safety, legality, or availability of any seller's services.</li>
                </ul>
            </div>

            {{-- 4 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">4</span>
                    Bookings & Cancellations
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Bookings made through Zonely are subject to the individual seller's availability and policies.</li>
                    <li>Cancellations must be made in a timely manner out of respect for the seller's time.</li>
                    <li>Repeated no-shows or last-minute cancellations may result in account restrictions.</li>
                    <li>Zonely is not responsible for any payment disputes between buyers and sellers.</li>
                </ul>
            </div>

            {{-- 5 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">5</span>
                    Reviews & Ratings
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Buyers may leave honest reviews for sellers after a completed booking.</li>
                    <li>Reviews must be truthful and based on genuine experience.</li>
                    <li>Fake, defamatory, or malicious reviews are prohibited and will be removed.</li>
                    <li>Zonely reserves the right to remove reviews that violate community standards.</li>
                </ul>
            </div>

            {{-- 6 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">6</span>
                    Buyer Referral Program
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Buyers can refer friends to Zonely using their unique referral link and earn <strong class="text-slate-900">cash commissions and points</strong>.</li>
                    <li>Referral commission amounts are set by Zonely admin and may vary over time. Your current rate is always visible in your <a href="{{ route('buyer.affiliate') }}" class="text-teal-700 underline font-semibold hover:text-teal-900">Referral Dashboard</a>.</li>
                    <li>A referral is counted when a friend signs up using your link and books their first service.</li>
                    <li>Self-referrals or fraudulent referral activity will result in commission forfeiture and account suspension.</li>
                    <li>Commissions are reviewed and paid monthly at admin discretion after verification.</li>
                    <li>Commission amounts and terms may change with <strong class="text-slate-900">14 days' notice</strong>.</li>
                </ul>
                <div class="mt-3 bg-teal-50 border border-teal-100 rounded-xl px-4 py-3 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-teal-600 mt-0.5 shrink-0"></i>
                    <p class="text-xs text-teal-700">Your current referral commission rate is always visible in your <a href="{{ route('buyer.affiliate') }}" class="font-bold underline hover:text-teal-900">Referral Dashboard</a>.</p>
                </div>
            </div>

            {{-- 7 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">7</span>
                    Privacy & Data
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>By using Zonely, you agree to our <a href="{{ route('frontend.privacy-policy') }}" class="text-teal-700 underline" target="_blank">Privacy Policy</a>.</li>
                    <li>We collect information necessary to operate the platform and do not sell your personal data to third parties.</li>
                    <li>Your contact information shared with sellers is used solely to facilitate service inquiries.</li>
                </ul>
            </div>

            {{-- 8 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">8</span>
                    Prohibited Conduct
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Harassment, abuse, or threatening behavior toward sellers or Zonely staff is prohibited.</li>
                    <li>Attempting to circumvent the platform to contact sellers outside of Zonely to avoid platform tracking is prohibited.</li>
                    <li>Using Zonely for any unlawful purpose is strictly prohibited.</li>
                </ul>
            </div>

            {{-- 9 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">9</span>
                    Termination
                </h3>
                <p>Zonely reserves the right to suspend or terminate any buyer account that violates these terms, submits fraudulent inquiries, engages in abusive behavior, or causes harm to the platform or other users.</p>
            </div>

            {{-- 10 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">10</span>
                    Limitation of Liability
                </h3>
                <p>Zonely is a technology platform and does not guarantee the quality, legality, or accuracy of services provided by sellers. To the maximum extent permitted by US law, Zonely's total liability for any claim shall not exceed fees paid by you in the 3 months preceding the claim.</p>
            </div>

            {{-- 11 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">11</span>
                    Governing Law
                </h3>
                <p>These Terms are governed by the laws of the United States. Disputes shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association (AAA), rather than in court, except for small claims matters.</p>
            </div>

            {{-- 12 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">12</span>
                    Changes to These Terms
                </h3>
                <p>Zonely may update these Terms at any time. We will notify registered buyers of material changes via email. Continued use of the platform after changes constitutes acceptance of the updated Terms.</p>
            </div>

            <div class="pt-2">
                <p class="text-xs text-slate-400">Questions? Contact us at <a href="mailto:contact@zonelyleads.com" class="text-teal-700 hover:underline">contact@zonelyleads.com</a></p>
            </div>

        </div>

        {{-- Scroll indicator --}}
        <div class="border-t border-slate-100 px-6 sm:px-8 py-4 bg-slate-50 text-xs text-slate-400 flex items-center gap-2" id="scrollHint">
            <i class="fa-solid fa-arrow-down animate-bounce text-slate-300"></i>
            Scroll down to read all terms before agreeing
        </div>

        {{-- Agreement form --}}
        <form method="POST" action="{{ route('terms.store') }}" id="termsForm">
            @csrf
            <div class="border-t border-slate-200 px-6 sm:px-8 py-6">
                @error('agree')
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm text-red-700 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation shrink-0"></i>
                    {{ $message }}
                </div>
                @enderror

                <label class="flex items-start gap-3 cursor-pointer group mb-6" for="agreeCheck">
                    <input type="checkbox" name="agree" id="agreeCheck" value="1"
                           class="mt-0.5 w-5 h-5 rounded border-slate-300 text-teal-700 accent-teal-700 shrink-0 cursor-pointer"
                           onchange="toggleSubmit(this)">
                    <span class="text-sm text-slate-700 leading-relaxed">
                        I have read and agree to Zonely's <strong class="text-slate-900">Buyer Terms & Conditions</strong>, including the referral program rules and privacy policy. I understand that submitting false inquiries or fraudulent referrals may result in account suspension.
                    </span>
                </label>

                <button type="submit" id="submitBtn" disabled
                        class="w-full bg-teal-700 text-white font-bold py-3.5 rounded-2xl text-sm transition
                               disabled:opacity-40 disabled:cursor-not-allowed
                               hover:bg-teal-800 disabled:hover:bg-teal-700">
                    <i class="fa-solid fa-check mr-2"></i> I Agree — Continue to Zonely as Buyer
                </button>

                <p class="text-center text-xs text-slate-400 mt-3">
                    Agreed on: <strong class="text-slate-600">{{ now()->format('F j, Y') }}</strong> · IP logged for compliance
                </p>
            </div>
        </form>
    </div>

    {{-- Sign out option --}}
    <p class="text-center text-xs text-slate-400">
        Don't want to agree?
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="text-red-500 hover:underline font-semibold">Sign out</a>
    </p>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

</div>
</div>

@section('scripts')
<script>
function toggleSubmit(checkbox) {
    document.getElementById('submitBtn').disabled = !checkbox.checked;
}
const content = document.getElementById('termsContent');
const hint = document.getElementById('scrollHint');
content.addEventListener('scroll', function () {
    if (this.scrollTop + this.clientHeight >= this.scrollHeight - 20) {
        hint.style.display = 'none';
    }
});
</script>
@endsection
@endsection
