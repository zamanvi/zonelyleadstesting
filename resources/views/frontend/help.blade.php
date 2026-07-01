@extends('frontend.layouts._app')
@section('title', 'Help & Support')
@section('content')

<div class="min-h-screen bg-slate-50">

    {{-- Page Hero --}}
    <section class="pt-28 pb-10 px-4 text-center">
        <span class="text-teal-700 text-[11px] font-black uppercase tracking-widest mb-3 block">Support Center</span>
        <h1 class="font-serif text-4xl sm:text-5xl text-slate-900 leading-tight mb-4">
            Help &amp; <span class="italic text-teal-700">Support</span>
        </h1>
        <p class="text-slate-500 text-sm sm:text-base max-w-lg mx-auto">
            Find answers to common questions or get in touch with our team.
        </p>
    </section>

    <main class="max-w-3xl mx-auto px-4 pb-16 space-y-2">

        {{-- FAQ Accordion --}}
        @php
        $faqs = [
            ['q' => 'How does Zonely help my local business grow?', 'a' => 'Zonely is a specialized platform that takes your offline local business and gives it a high-performance digital presence in under 1 minute, focusing on capturing "near-me" search intent to drive more calls and bookings.'],
            ['q' => 'Why do I need a Zonely page if I already have a Google Business Profile?', 'a' => 'While Google shows where you are, a Zonely page shows who you are — it acts as a dedicated landing page that Google\'s AI uses to verify your services, which can significantly improve your ranking in the local map pack.'],
            ['q' => 'Is the initial website really free to use?', 'a' => 'Yes, we build a professional demo page using your existing public data at no cost, allowing you to activate a branded website link on your Google profile immediately without any upfront investment.'],
            ['q' => 'How does the Pay-Per-Lead model work?', 'a' => 'Our model is designed to be risk-free: you only pay when we deliver a verified, high-quality lead (a real person asking for your service), ensuring you never waste money on empty clicks or bot traffic.'],
            ['q' => 'What is the Premium Dashboard?', 'a' => 'The Premium Dashboard is an advanced management tool that tracks your visitors, records direct inquiries, and provides real-time analytics so you can respond to potential customers faster than your competitors.'],
            ['q' => 'Can I manage and update my services and photos myself?', 'a' => 'Absolutely. Once you activate your account, you have full control to update your business hours, add new service photos, or change your contact information at any time.'],
            ['q' => 'How does Zonely ensure the leads I receive are high-quality?', 'a' => 'We use lead verification and call tracking to filter out spam, ensuring that notifications you receive represent genuine customers ready to hire a local expert.'],
            ['q' => 'What happens after I activate a paid growth campaign?', 'a' => 'When you switch to a paid campaign, we use targeted digital marketing to push your business to the top of local searches, which typically increases order volume significantly for service providers.'],
            ['q' => 'Does using Zonely require any technical knowledge?', 'a' => 'None at all. Zonely is built for non-tech owners — we handle the hosting, SEO, and technical updates so you can focus entirely on serving your clients.'],
            ['q' => 'What is the long-term vision for businesses on Zonely?', 'a' => 'Zonely will evolve into a full ecosystem offering automated workflow tools and industry-specific widgets, moving from a simple lead-gen tool to a complete management system for your local service business.'],
        ];
        @endphp

        @foreach($faqs as $i => $faq)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <button onclick="toggleFaq({{ $i }})"
                class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left hover:bg-slate-50 transition">
                <span class="font-semibold text-slate-900 text-sm sm:text-base">{{ $faq['q'] }}</span>
                <i id="faq-icon-{{ $i }}" class="fa-solid fa-chevron-down text-teal-600 text-xs shrink-0 transition-transform duration-200"></i>
            </button>
            <div id="faq-body-{{ $i }}" class="hidden px-6 pb-5">
                <p class="text-slate-600 text-sm leading-relaxed">{{ $faq['a'] }}</p>
            </div>
        </div>
        @endforeach

        {{-- Contact CTA --}}
        <div class="bg-teal-700 rounded-2xl p-8 text-center mt-6">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-headset text-white text-xl"></i>
            </div>
            <h2 class="font-serif text-2xl text-white mb-2">Still need help?</h2>
            <p class="text-teal-100 text-sm mb-6">Our support team is ready to assist you anytime.</p>
            <a href="{{ route('frontend.contact') }}"
               class="inline-flex items-center gap-2 bg-white text-teal-800 font-bold px-6 py-3 rounded-xl hover:bg-teal-50 transition text-sm">
                <i class="fa-solid fa-envelope text-xs"></i> Contact Us
            </a>
        </div>

    </main>
</div>

<script>
function toggleFaq(i) {
    const body = document.getElementById('faq-body-' + i);
    const icon = document.getElementById('faq-icon-' + i);
    const isOpen = !body.classList.contains('hidden');
    body.classList.toggle('hidden', isOpen);
    icon.style.transform = isOpen ? '' : 'rotate(180deg)';
}
</script>

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How does Zonely help my local business grow?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Zonely is a specialized platform that takes your offline local business and gives it a high-performance digital presence in under 1 minute, focusing on capturing \"near-me\" search intent to drive more calls and bookings."
      }
    },
    {
      "@type": "Question",
      "name": "Why do I need a Zonely page if I already have a Google Business Profile?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "While Google shows where you are, a Zonely page shows who you are — it acts as a dedicated landing page that Google's AI uses to verify your services, which can significantly improve your ranking in the local map pack."
      }
    },
    {
      "@type": "Question",
      "name": "Is the initial website really free to use?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, we build a professional demo page using your existing public data at no cost, allowing you to activate a branded website link on your Google profile immediately without any upfront investment."
      }
    },
    {
      "@type": "Question",
      "name": "How does the Pay-Per-Lead model work?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Our model is designed to be risk-free: you only pay when we deliver a verified, high-quality lead (a real person asking for your service), ensuring you never waste money on empty clicks or bot traffic."
      }
    },
    {
      "@type": "Question",
      "name": "What is the Premium Dashboard?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "The Premium Dashboard is an advanced management tool that tracks your visitors, records direct inquiries, and provides real-time analytics so you can respond to potential customers faster than your competitors."
      }
    },
    {
      "@type": "Question",
      "name": "Can I manage and update my services and photos myself?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Absolutely. Once you activate your account, you have full control to update your business hours, add new service photos, or change your contact information at any time."
      }
    },
    {
      "@type": "Question",
      "name": "How does Zonely ensure the leads I receive are high-quality?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "We use lead verification and call tracking to filter out spam, ensuring that notifications you receive represent genuine customers ready to hire a local expert."
      }
    },
    {
      "@type": "Question",
      "name": "What happens after I activate a paid growth campaign?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "When you switch to a paid campaign, we use targeted digital marketing to push your business to the top of local searches, which typically increases order volume significantly for service providers."
      }
    },
    {
      "@type": "Question",
      "name": "Does using Zonely require any technical knowledge?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "None at all. Zonely is built for non-tech owners — we handle the hosting, SEO, and technical updates so you can focus entirely on serving your clients."
      }
    },
    {
      "@type": "Question",
      "name": "What is the long-term vision for businesses on Zonely?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Zonely will evolve into a full ecosystem offering automated workflow tools and industry-specific widgets, moving from a simple lead-gen tool to a complete management system for your local service business."
      }
    }
  ]
}
</script>
@endsection

@endsection
