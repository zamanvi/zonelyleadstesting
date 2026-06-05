@extends('frontend.layouts._app')
@section('title', 'Terms and Conditions')
@section('content')
    {{-- Hero --}}
    <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-28 pb-8 text-center">
        <span class="text-teal-700 text-[11px] font-black uppercase tracking-widest mb-3 block">Legal</span>
        <h1 class="font-serif text-4xl sm:text-5xl text-slate-900 leading-tight mb-3">
            Terms &amp; <em class="text-teal-700 font-normal italic">Conditions</em>
        </h1>
        <p class="text-slate-500 text-sm">Zonely – Discover &amp; Hire Local Experts Near Me.</p>
        <p class="text-slate-400 text-xs mt-1">Last updated: <time datetime="2026-03-30">March 30, 2026</time></p>
    </section>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 pb-16 space-y-4">
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">1. Introduction</h2>
            <p class="text-slate-600 leading-relaxed">Welcome to Zonely ("we", "us", "our"). These Terms & Conditions
                ("Terms") govern the relationship between Zonely, our Service Sellers (US-based SMBs/Pros), and our Service
                Buyers (Customers). By accessing our platform, landing pages, or communication tools, you agree to be bound
                by these Terms.</p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">2. Eligibility & US Compliance</h2>
            <p class="text-slate-600 leading-relaxed">Service Sellers: You must be a legally registered business entity or a
                qualified professional (18+) authorized to operate within the United States.
            </p>
            <ul class="list-disc pl-5 text-slate-600 leading-relaxed space-y-2">
                <li><strong>Service Buyers:</strong> You must be at least 18 years old to book services.</li>
            </ul>
            <p class="text-slate-600 leading-relaxed">
                You represent that all information—including professional licenses, EINs, and business details—is accurate
                and complies with local State and Federal laws.
            </p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">3. Account Registration & US Data Security</h2>
            <p class="text-slate-600 leading-relaxed">
                To access the Premium Dashboard, you must create an account. You are responsible for:
                Providing a valid business email for account management.
                Maintaining the confidentiality of your login credentials.
                All activity occurring under your account, including compliance with the TCPA (Telephone Consumer Protection
                Act) when messaging US customers.

            </p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">4. Service Description & "Instant Demo" Policy</h2>
            <p class="text-slate-600 leading-relaxed">Zonely provides a SaaS platform for US "near-me" SMBs to build landing
                pages and track leads.
                The Demo Policy: We use publicly available data from your Google Business Profile to create an automated
                "1-Minute Demo Page."
                You have the legal right to claim, modify, or request the permanent removal of this demo page at any time.
            </p>
        </section>

        @if(!auth()->check() || auth()->user()->type !== 'buyer')
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">5. Fees, Billing &amp; US Payment Standards</h2>
            <p class="text-slate-600 leading-relaxed"> We operate on a phased revenue model:</p>
            <ul class="list-disc pl-5 text-slate-600 leading-relaxed space-y-2">
                <li><strong>Phase 1 (Pay-Per-Lead):</strong> Sellers are charged <strong class="text-slate-800">$35&ndash;$50 per verified lead</strong> (exact amount depends on category and lead quality). A "Lead" is
                    defined as a unique US-based inquiry made via call, WhatsApp, or the Zonely booking form. This fee is subject to change with 14 days' notice.</li>
                <li><strong>Phase 2 (Commission):</strong> For specific categories, we may charge a percentage of the total
                    booking value.</li>
                <li><strong>Subscriptions:</strong> Monthly fees for the Premium Dashboard are processed in USD. All fees
                    are non-refundable unless required by specific State law. You authorize recurring charges to your credit
                    card or ACH until cancelled.</li>
            </ul>
        </section>
        @endif

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">6. Acceptable Use & Anti-Fraud</h2>
            <p class="text-slate-600 leading-relaxed">Users agree not to:</p>
            <ul class="list-disc pl-5 text-slate-600 leading-relaxed space-y-2">
                <li>Post fraudulent listings or engage in "bait-and-switch" pricing.</li>
                <li>Use the "Live 3-Way Call" features to record customers without required state-level consent (One-Party
                    vs. Two-Party consent laws).</li>
                <li>Attempt to circumvent Zonely’s lead-tracking system to avoid contractually owed fees.</li>
            </ul>
        </section>

        @if(!auth()->check() || auth()->user()->type !== 'buyer')
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">7. Intellectual Property &amp; Digital Millennium Copyright Act (DMCA)</h2>
            <p class="text-slate-600 leading-relaxed">Zonely retains all rights to the platform's software and trademarks.
            </p>
            <ul class="list-disc pl-5 text-slate-600 leading-relaxed space-y-2">
                <li><strong>Sellers:</strong> You grant Zonely a non-exclusive, worldwide license to host and display your
                    business media to drive traffic.</li>
                <li><strong>DMCA:</strong> We respond to notices of alleged copyright infringement according to the process
                    set out in the US Digital Millennium Copyright Act.</li>
            </ul>
        </section>
        @endif

        @if(!auth()->check() || auth()->user()->type !== 'buyer')
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">8. Lead Verification &amp; Call Tracking</h2>
            <p class="text-slate-600 leading-relaxed">To maintain marketplace integrity:</p>
            <p class="text-slate-600 leading-relaxed">We use US-based tracking numbers and call recording to verify lead
                quality.</p>
            <ul>
                <li><strong>Transparency:</strong> Sellers are responsible for ensuring their use of Zonely communication
                    tools complies with their specific state's recording and privacy laws.</li>
            </ul>
        </section>
        @endif

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">9. Third-Party US Integrations</h2>
            <p class="text-slate-600 leading-relaxed">Our Service integrates with US-standard tools (Google Maps, Stripe,
                WhatsApp, US VoIP carriers). Your use of these is subject to their terms. Zonely is not liable for service
                interruptions caused by these third-party providers.</p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">10. Disclaimers & "As-Is" Provision</h2>
            <p class="text-slate-600 leading-relaxed">The Service is provided "as is" and "as available." To the extent permitted by US law, Zonely disclaims all warranties, express or implied, including warranties of merchantability or fitness for a particular purpose.</p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">11. Limitation of Liability</h2>
            <p class="text-slate-600 leading-relaxed">To the maximum extent permitted by US law, Zonely and its officers shall not be liable for any indirect, incidental, or punitive damages. Our total liability shall not exceed the total fees paid by you to Zonely during the 12 months preceding the claim.</p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">12. Indemnification</h2>
            <p class="text-slate-600 leading-relaxed">You agree to defend, indemnify, and hold harmless Zonely from any claims, damages, or legal fees arising from your business operations, your violation of these Terms, or disputes with your US customers.</p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">13. Termination & Account Closure</h2>
            <p class="text-slate-600 leading-relaxed"></p>
            <ul>
                <li><strong>Sellers:</strong> You may terminate your relationship with Zonely at any time by removing the Zonely link from your Google Profile and closing your account.</li>
                <li><strong>Zonely:</strong> We reserve the right to suspend accounts for "Friendly Fraud," non-payment of lead fees, or violations of US trade regulations.</li>
            </ul>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">14. Governing Law & Arbitration</h2>
            <p class="text-slate-600 leading-relaxed">These Terms are governed by the laws of the United States. Any disputes shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association (AAA), rather than in court, except for small claims matters.</p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">15. Changes to Terms</h2>
            <p class="text-slate-600 leading-relaxed">We may modify these Terms to stay compliant with evolving US regulations (such as CCPA/CPRA). We will notify you of material changes via your registered email. Continued use of the platform constitutes agreement to the updated Terms.</p>
        </section>

        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">16. Contact & Support</h2>
            <p class="text-slate-600 leading-relaxed">For support or legal inquiries, please reach out via our official US-facing channels:</p>
            <address class="text-slate-600 leading-relaxed">
                {{-- <p><strong>Zonely</strong><br />Dhaka, Bangladesh</p> --}}
                <p>Support Email: <a href="mailto:contact@zonelyleads.com">contact@zonelyleads.com</a>
            </address>
        </section>

        {{-- <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-bold text-slate-900 mb-3">17. Miscellaneous</h2>
            <p class="text-slate-600 leading-relaxed">If any provision of these Terms is held invalid, the remaining
                provisions will continue in full force. These
                Terms constitute the entire agreement between you and Zonely regarding the Service.</p>
        </section> --}}

    </main>
@endsection
