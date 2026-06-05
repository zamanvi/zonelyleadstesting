@extends('frontend.layouts.__prof_app')
@section('title', 'Lead Details')
@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-16 px-4">
    <div class="max-w-2xl mx-auto py-6">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('seller.dashboard') ?? '#' }}" class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Lead Details</h1>
                <p class="text-xs text-slate-500">Review and take action on this lead</p>
            </div>
        </div>

        {{-- Status Banner --}}
        @php
            $status = $lead->status ?? 'pending';
            $bannerClass = match($status) {
                'won'    => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                'lost'   => 'bg-red-50 border-red-200 text-red-700',
                default  => 'bg-amber-50 border-amber-200 text-amber-800',
            };
            $statusIcon = match($status) {
                'won'    => 'fa-trophy text-emerald-500',
                'lost'   => 'fa-xmark text-red-500',
                default  => 'fa-clock text-amber-500',
            };
        @endphp
        <div class="border rounded-2xl p-3.5 mb-5 flex items-center gap-3 {{ $bannerClass }}">
            <i class="fa-solid {{ $statusIcon }}"></i>
            <p class="text-sm font-bold">{{ ucfirst($status) }} Lead
                @if($status === 'pending') — Take action below @endif
            </p>
            <span class="ml-auto text-xs opacity-70">{{ $lead->created_at?->format('M d, Y') }}</span>
        </div>

        {{-- Contact Info --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user text-teal-700 text-sm"></i> Contact Information
            </h2>

            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-teal-100 flex items-center justify-center font-bold text-teal-700 text-xl shrink-0">
                        {{ $lead->name ? strtoupper(substr($lead->name, 0, 2)) : strtoupper(substr($lead->phone ?? '??', -2)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-lg">{{ $lead->phone ?? '—' }}</p>
                        <p class="text-xs text-slate-500">Phone number</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if($lead->phone)
                    <a href="tel:{{ $lead->phone }}"
                       class="flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm transition">
                        <i class="fa-solid fa-phone"></i> Call Now
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}" target="_blank"
                       class="flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm transition">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                    @elseif($lead->email)
                    <a href="mailto:{{ $lead->email }}"
                       class="col-span-2 flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm transition">
                        <i class="fa-solid fa-envelope"></i> Email Client
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Request Details --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-teal-700 text-sm"></i> What They Need
            </h2>
            <div class="space-y-3">
                <div class="flex gap-3">
                    <span class="text-xs font-bold text-slate-400 w-24 shrink-0 pt-0.5">Service</span>
                    <span class="text-sm font-semibold text-slate-800">{{ $lead->service ?? '—' }}</span>
                </div>
                @if($lead->message ?? false)
                <div class="flex gap-3">
                    <span class="text-xs font-bold text-slate-400 w-24 shrink-0 pt-0.5">Message</span>
                    <span class="text-sm text-slate-700 leading-relaxed">{{ $lead->message }}</span>
                </div>
                @endif
                @if($lead->location ?? false)
                <div class="flex gap-3">
                    <span class="text-xs font-bold text-slate-400 w-24 shrink-0 pt-0.5">Location</span>
                    <span class="text-sm text-slate-700">{{ $lead->location }}</span>
                </div>
                @endif
                <div class="flex gap-3">
                    <span class="text-xs font-bold text-slate-400 w-24 shrink-0 pt-0.5">Received</span>
                    <span class="text-sm text-slate-700">{{ $lead->created_at?->format('D, M d Y \a\t g:i A') }}</span>
                </div>
                <div class="flex gap-3">
                    <span class="text-xs font-bold text-slate-400 w-24 shrink-0 pt-0.5">Lead Fee</span>
                    <span class="text-sm font-bold {{ ($lead->paid_at ?? false) ? 'text-emerald-600' : 'text-red-600' }}">
                        ${{ number_format($lead->fee ?? 0, 2) }}
                        <span class="font-normal text-xs">{{ ($lead->paid_at ?? false) ? '(paid)' : '(unpaid)' }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-note-sticky text-teal-700 text-sm"></i> My Notes
            </h2>
            <textarea id="notesArea" rows="3" placeholder="Add private notes about this lead (only you can see these)..."
                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition resize-none">{{ $lead->notes ?? '' }}</textarea>
            <button onclick="saveNotes(this)" class="mt-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Save Notes
            </button>
        </div>

        {{-- Actions --}}
        @if($status === 'pending')
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-1">Update Status</h2>
            <p class="text-xs text-slate-400 mb-4">Did you win this job, or did it not work out?</p>
            <div class="flex gap-3">
                <button onclick="setStatus('won')"
                    class="flex-1 py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trophy"></i> Mark as Won
                </button>
                <button onclick="setStatus('lost')"
                    class="flex-1 py-3.5 rounded-2xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-xmark"></i> Mark as Lost
                </button>
            </div>
        </div>
        @endif

        {{-- Request Review — show for any lead that has buyer contact info --}}
        @if($lead->phone || $lead->email)
        @php
            $existingReview = \App\Models\Review::where('seller_id', auth()->id())
                ->where('lead_id', $lead->id)->first();
        @endphp
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-star text-amber-400 text-sm"></i> Get a Review
            </h2>
            @if($existingReview && $existingReview->isSubmitted())
            <p class="text-xs text-slate-400 mb-3">Review received from this client.</p>
            <div class="flex gap-0.5 mb-1">
                @for($i=1;$i<=5;$i++)
                <i class="fa-solid fa-star text-sm {{ $i <= $existingReview->rating ? 'text-amber-400' : 'text-slate-200' }}"></i>
                @endfor
            </div>
            <p class="text-sm text-slate-600 italic">"{{ $existingReview->review }}"</p>
            <p class="text-xs text-slate-400 mt-1">— {{ $existingReview->reviewer_name }}</p>
            @elseif($existingReview && !$existingReview->isSubmitted())
            <p class="text-xs text-slate-400 mb-4">Review request sent. Waiting for client to respond.</p>
            <div class="flex gap-2">
                <input type="text" readonly id="reviewLinkBox"
                       value="{{ url('/r/' . $existingReview->review_token) }}"
                       class="flex-1 text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 font-mono focus:outline-none truncate">
                <button onclick="copyReviewLink()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition shrink-0">
                    <i class="fa-solid fa-copy"></i> Copy
                </button>
            </div>
            @else
            <p class="text-xs text-slate-400 mb-4">Send a review link to your client. No account needed — they just click and rate.</p>
            <button onclick="requestReview(this)"
                class="bg-amber-500 hover:bg-amber-400 text-slate-900 font-bold px-5 py-2.5 rounded-xl text-sm transition flex items-center gap-2">
                <i class="fa-solid fa-link text-xs"></i> Generate Review Link
            </button>
            <div id="reviewLinkWrap" class="hidden mt-3">
                <p class="text-xs text-slate-500 mb-2">Share this link with your client:</p>
                <div class="flex gap-2">
                    <input type="text" readonly id="reviewLinkBox"
                           class="flex-1 text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 font-mono focus:outline-none truncate">
                    <button onclick="copyReviewLink()" class="px-4 py-2.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shrink-0">
                        <i class="fa-solid fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(!$lead->paid_at)
        <div class="bg-red-50 border border-red-100 rounded-2xl p-5">
            <p class="font-bold text-red-700 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> Lead fee unpaid
            </p>
            <p class="text-xs text-red-500 mb-3">Pay to unlock full contact details and keep receiving new leads.</p>
            <a href="{{ route('seller.billing') ?? '#' }}"
               class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition">
                <i class="fa-solid fa-credit-card"></i> Pay ${{ number_format($lead->fee ?? 0, 2) }} Now
            </a>
        </div>
        @endif

    </div>
</div>

<script>
const _csrf = document.querySelector('meta[name="csrf-token"]')?.content;
if (!_csrf) console.error('CSRF token meta tag missing');

function setStatus(status) {
    if (!confirm('Mark this lead as ' + status + '?')) return;
    fetch('/seller/leads/{{ $lead->id }}/status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
        body: JSON.stringify({ status })
    }).then(() => location.reload());
}
function requestReview(btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Generating...';
    fetch('{{ route('seller.lead.review-request', $lead->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('reviewLinkBox').value = data.link;
        document.getElementById('reviewLinkWrap').classList.remove('hidden');
        btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> Link Generated';
        btn.className = btn.className.replace('bg-amber-500 hover:bg-amber-400','bg-emerald-100 text-emerald-700');
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-link text-xs"></i> Generate Review Link';
    });
}
function copyReviewLink() {
    const box = document.getElementById('reviewLinkBox');
    if (navigator.clipboard) {
        navigator.clipboard.writeText(box.value).then(() => {
            box.select();
        });
    } else {
        box.select();
        document.execCommand('copy');
    }
}
function saveNotes(btn) {
    const notes = document.getElementById('notesArea').value;
    fetch('/seller/leads/{{ $lead->id }}/notes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
        body: JSON.stringify({ notes })
    }).then(() => {
        btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Saved';
        setTimeout(() => btn.innerHTML = 'Save Notes', 2000);
    });
}
</script>
@endsection
