@extends('frontend.layouts._app')
@section('title', 'Seller Terms & Conditions — Zonely')

@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-16 px-4">
<div class="max-w-3xl mx-auto py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-teal-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-file-contract text-white text-2xl"></i>
        </div>
        <span class="text-teal-700 text-[11px] font-black uppercase tracking-widest mb-2 block">Legal — Sellers</span>
        <h1 class="text-2xl font-black text-slate-900">Seller Terms & Conditions</h1>
        <p class="text-slate-500 text-sm mt-2">Please read and agree before continuing to Zonely as a Seller</p>
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
                <p>Welcome to <strong class="text-slate-900">Zonely</strong>. These Terms & Conditions govern your use of the Zonely platform as a <strong class="text-slate-900">Service Seller</strong> (local business or professional). By creating a seller account, you agree to these terms in full.</p>
            </div>

            {{-- 1 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">1</span>
                    Platform Overview
                </h3>
                <p>Zonely is a lead generation and local business discovery platform operating in the United States. We connect buyers (people seeking services) with sellers (local professionals and businesses). Zonely charges sellers a fee per verified lead received through the platform.</p>
            </div>

            {{-- 2 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">2</span>
                    Eligibility
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>You must be at least 18 years old.</li>
                    <li>You must be a legally registered business or qualified professional authorized to operate in the United States.</li>
                    <li>You must provide accurate business information including any required licenses, EINs, or professional credentials.</li>
                    <li>One account per individual or business. Duplicate accounts may be removed.</li>
                </ul>
            </div>

            {{-- 3 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">3</span>
                    Lead Fees & Billing
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Sellers are charged a <strong class="text-slate-900">fee per verified lead</strong>. The exact amount varies by <strong class="text-slate-900">service category, state, and city</strong>.</li>
                    <li>A "verified lead" is a genuine contact request submitted by a real buyer through the Zonely platform via call, WhatsApp, or the booking form.</li>
                    <li>Lead fees are non-refundable once a lead has been delivered and marked verified.</li>
                    <li>Dispute requests for lead quality must be submitted within <strong class="text-slate-900">7 days</strong> of the lead's creation date.</li>
                    <li>Sellers with unpaid lead fees may have their listings suspended until payment is made.</li>
                    <li>Fees are subject to change with <strong class="text-slate-900">14 days' notice</strong>.</li>
                </ul>
                <div class="mt-3 bg-teal-50 border border-teal-100 rounded-xl px-4 py-3 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-teal-600 mt-0.5 shrink-0"></i>
                    <p class="text-xs text-teal-700">Your current lead fee rate is always visible in your <a href="{{ route('seller.dashboard') }}" class="font-bold underline hover:text-teal-900">Seller Dashboard</a>. Rates may differ by category and location.</p>
                </div>
            </div>

            {{-- 4 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">4</span>
                    Listing Rules & Conduct
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Your profile, services, and business information must be accurate and not misleading.</li>
                    <li>You may not impersonate another business or professional.</li>
                    <li>Prohibited categories include illegal services, adult content, or anything that violates applicable US law.</li>
                    <li>Sellers agree to respond to verified leads in a timely and professional manner.</li>
                    <li>Zonely reserves the right to remove or suspend any listing that violates these terms.</li>
                </ul>
            </div>

            {{-- 5 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">5</span>
                    Affiliate & Referral Program
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Sellers who refer other businesses to Zonely may earn a <strong class="text-slate-900">referral commission</strong> when the referred seller receives their first verified lead.</li>
                    <li>Commission amounts vary and are set by Zonely admin. Your current commission rate is visible in your <a href="{{ route('seller.dashboard') }}" class="text-teal-700 underline font-semibold hover:text-teal-900">Seller Dashboard</a>.</li>
                    <li>Commissions are reviewed and paid monthly at admin discretion after verification.</li>
                    <li>Self-referrals or fraudulent referral activity will result in commission forfeiture and account suspension.</li>
                    <li>Commission amounts and terms may change with <strong class="text-slate-900">14 days' notice</strong>.</li>
                </ul>
            </div>

            {{-- 6 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">6</span>
                    Communications & SMS
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Sellers who opt in to SMS notifications consent to receive automated text messages regarding new leads and platform updates.</li>
                    <li>Standard message and data rates may apply depending on your carrier.</li>
                    <li>You may opt out of SMS notifications at any time via your account settings.</li>
                    <li>Zonely may use a tracking phone number on your public listing to measure lead volume. Your real phone number is never exposed to buyers unless you choose to share it.</li>
                    <li>You are responsible for ensuring your use of Zonely communication tools complies with your state's recording and privacy laws (one-party vs. two-party consent).</li>
                </ul>
            </div>

            {{-- 7 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">7</span>
                    Intellectual Property
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>Zonely retains all rights to the platform's software and trademarks.</li>
                    <li>You grant Zonely a non-exclusive, worldwide license to host and display your business media (photos, descriptions, logos) to drive traffic to your listing.</li>
                    <li>We respond to DMCA notices of alleged copyright infringement according to US law.</li>
                </ul>
            </div>

            {{-- 8 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">8</span>
                    Privacy & Data
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>By using Zonely, you agree to our <a href="{{ route('frontend.privacy-policy') }}" class="text-teal-700 underline" target="_blank">Privacy Policy</a>.</li>
                    <li>We collect information necessary to operate the platform and do not sell personal data to third parties.</li>
                    <li>Your use of the platform must comply with TCPA (Telephone Consumer Protection Act) when messaging US customers.</li>
                </ul>
            </div>

            {{-- 9 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">9</span>
                    Termination & Account Closure
                </h3>
                <ul class="list-disc list-inside space-y-1">
                    <li>You may close your account at any time via your account settings.</li>
                    <li>Zonely reserves the right to suspend or terminate accounts for fraud, non-payment of lead fees, or violations of US trade regulations.</li>
                    <li>Upon termination, all outstanding lead fees remain payable.</li>
                </ul>
            </div>

            {{-- 10 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">10</span>
                    Limitation of Liability
                </h3>
                <p>To the maximum extent permitted by US law, Zonely's total liability for any claim shall not exceed the total lead fees paid by you in the 12 months preceding the claim. Zonely is not liable for indirect, incidental, or punitive damages.</p>
            </div>

            {{-- 11 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">11</span>
                    Governing Law & Arbitration
                </h3>
                <p>These Terms are governed by the laws of the United States. Disputes shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association (AAA), rather than in court, except for small claims matters.</p>
            </div>

            {{-- 12 --}}
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">12</span>
                    Changes to These Terms
                </h3>
                <p>Zonely may update these Terms at any time to stay compliant with evolving US regulations. We will notify registered sellers of material changes via email. Continued use of the platform constitutes acceptance of the updated Terms.</p>
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
                        I have read and agree to Zonely's <strong class="text-slate-900">Seller Terms & Conditions</strong>, including the lead fee policy, affiliate program rules, and privacy policy. I understand that lead fees vary by category and location and are always visible in my Seller Dashboard. I understand that submitting false information may result in account suspension.
                    </span>
                </label>

                <button type="submit" id="submitBtn" disabled
                        class="w-full bg-teal-700 text-white font-bold py-3.5 rounded-2xl text-sm transition
                               disabled:opacity-40 disabled:cursor-not-allowed
                               hover:bg-teal-800 disabled:hover:bg-teal-700">
                    <i class="fa-solid fa-check mr-2"></i> I Agree — Continue to Zonely as Seller
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
