@extends('frontend.layouts._app')
@section('title', 'Terms & Conditions — Zonely')

@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-16 px-4">
<div class="max-w-3xl mx-auto py-8">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-teal-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-file-contract text-white text-2xl"></i>
        </div>
        <h1 class="text-2xl font-black text-slate-900">Terms & Conditions</h1>
        <p class="text-slate-500 text-sm mt-2">Please read and agree before continuing to Zonely</p>
    </div>

    {{-- T&C Card --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">

        {{-- Scrollable content --}}
        <div class="overflow-y-auto max-h-[60vh] px-6 sm:px-8 py-6 text-sm text-slate-600 leading-relaxed space-y-5"
             id="termsContent">

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Last updated: April 2026</p>
                <p>Welcome to <strong class="text-slate-900">Zonely</strong>. By creating an account and using our platform, you agree to these Terms & Conditions. Please read them carefully.</p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">1</span>
                    Platform Overview
                </h3>
                <p>Zonely is a lead generation and local business discovery platform that connects buyers (people seeking services) with sellers (local professionals and businesses). Zonely charges sellers a fee per verified lead received.</p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">2</span>
                    Account Registration
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>You must be at least 18 years old to create an account.</li>
                    <li>You agree to provide accurate, current, and complete information.</li>
                    <li>You are responsible for keeping your login credentials confidential.</li>
                    <li>One account per individual or business. Duplicate accounts may be removed.</li>
                </ul>
            </div>

            @if(!auth()->check() || auth()->user()->type !== 'buyer')
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">3</span>
                    For Sellers — Lead Fees & Payment
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>Sellers are charged <strong class="text-slate-900">$35–$50 per verified lead</strong>. Exact pricing depends on category and lead quality. This fee is subject to change with 14 days' notice.</li>
                    <li>A "verified lead" is defined as a contact request submitted by a real buyer through the Zonely platform.</li>
                    <li>Lead fees are non-refundable once a lead has been delivered and marked verified.</li>
                    <li>Sellers with unpaid lead fees may have their listings suspended until payment is made.</li>
                    <li>Dispute requests for lead quality must be submitted within 7 days of the lead's creation date.</li>
                </ul>
            </div>
            @endif

            @if(!auth()->check() || auth()->user()->type !== 'buyer')
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">4</span>
                    For Sellers — Listing Rules
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>Your profile, services, and business information must be accurate and not misleading.</li>
                    <li>You may not impersonate another business or professional.</li>
                    <li>Prohibited categories include illegal services, adult content, or anything that violates applicable law.</li>
                    <li>Zonely reserves the right to remove or suspend any listing that violates these terms.</li>
                    <li>Sellers agree to respond to verified leads in a timely and professional manner.</li>
                </ul>
            </div>
            @endif

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">5</span>
                    For Buyers — Using the Platform
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>Buyers may submit inquiries to sellers at no charge.</li>
                    <li>Inquiry information submitted must be genuine and accurate.</li>
                    <li>Spam, fake, or test inquiries are prohibited. Accounts submitting fraudulent inquiries will be banned.</li>
                    <li>Zonely is not a party to any transaction between buyers and sellers. All service agreements are between the buyer and the seller directly.</li>
                </ul>
            </div>

            @if(!auth()->check() || auth()->user()->type !== 'buyer')
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">6</span>
                    Affiliate & Referral Program (Sellers)
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>Users who refer new sellers earn a <strong class="text-slate-900">$10 commission</strong> when the referred seller receives their first verified lead.</li>
                    <li>Commissions are paid monthly at admin discretion after verification.</li>
                    <li>Self-referrals or fraudulent referral activity will result in commission forfeiture and account suspension.</li>
                    <li>Commission amounts and terms may change with 14 days' notice.</li>
                </ul>
            </div>
            @endif

            @if(auth()->check() && auth()->user()->type === 'buyer')
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">6</span>
                    Buyer Referral Program
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>Buyers who refer friends to Zonely may earn <strong class="text-slate-900">platform credits</strong> when their referral books a service.</li>
                    <li>Referral credits can be used toward future bookings on the platform.</li>
                    <li>Fraudulent referral activity will result in account suspension.</li>
                </ul>
            </div>
            @endif

            @if(!auth()->check() || auth()->user()->type !== 'buyer')
            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">7</span>
                    Communications & SMS
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>Sellers who opt in to SMS notifications consent to receive automated text messages regarding new leads and platform updates.</li>
                    <li>Standard message and data rates may apply depending on your carrier.</li>
                    <li>You may opt out of SMS notifications at any time via your account settings.</li>
                    <li>Zonely may use a tracking phone number on your public listing to measure lead volume. Real phone numbers are never exposed to buyers unless you choose to share them.</li>
                </ul>
            </div>
            @endif

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">8</span>
                    Privacy & Data
                </h3>
                <ul class="list-disc list-inside space-y-1 text-slate-600">
                    <li>By using Zonely, you agree to our <a href="{{ route('frontend.privacy-policy') }}" class="text-teal-700 underline" target="_blank">Privacy Policy</a>.</li>
                    <li>We collect information necessary to operate the platform and do not sell personal data to third parties.</li>
                    <li>Call recordings (if any) are subject to applicable wiretapping and consent laws in your jurisdiction.</li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">9</span>
                    Termination
                </h3>
                <p>Zonely reserves the right to suspend or terminate any account that violates these terms, engages in fraudulent activity, or causes harm to the platform or other users. Upon termination, outstanding lead fees remain payable.</p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">10</span>
                    Limitation of Liability
                </h3>
                <p>Zonely is a technology platform and does not guarantee the quality, legality, or accuracy of services provided by sellers. To the maximum extent permitted by law, Zonely's total liability for any claim shall not exceed the fees paid by you in the 3 months preceding the claim.</p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">11</span>
                    Governing Law
                </h3>
                <p>These Terms are governed by the laws of the State of New York, USA, without regard to conflict of law principles. Disputes shall be resolved through binding arbitration or the courts of New York County.</p>
            </div>

            <div>
                <h3 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-100 text-teal-700 rounded-lg flex items-center justify-center text-xs font-black shrink-0">12</span>
                    Changes to These Terms
                </h3>
                <p>Zonely may update these Terms at any time. Continued use of the platform after changes constitutes acceptance. We will notify registered users of material changes via email.</p>
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
                        I have read and agree to Zonely's <strong class="text-slate-900">Terms &amp; Conditions</strong>, including the lead fee policy ($35–$50/lead for sellers), the affiliate program rules, and the privacy policy. I understand that submitting false information may result in account suspension.
                    </span>
                </label>

                <button type="submit" id="submitBtn" disabled
                        class="w-full bg-teal-700 text-white font-bold py-3.5 rounded-2xl text-sm transition
                               disabled:opacity-40 disabled:cursor-not-allowed
                               hover:bg-teal-800 disabled:hover:bg-teal-700">
                    <i class="fa-solid fa-check mr-2"></i> I Agree — Continue to Zonely
                </button>

                <p class="text-center text-xs text-slate-400 mt-3">
                    Agreed on:
                    <strong class="text-slate-600">{{ now()->format('F j, Y') }}</strong>
                    · IP logged for compliance
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

// Hide scroll hint once user scrolls to bottom
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
