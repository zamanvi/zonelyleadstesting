@extends('frontend.layouts.__prof_app')
@section('title', 'Affiliate Dashboard')
@section('content')
<div class="pb-10">
    <div class="max-w-3xl mx-auto py-6">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Affiliate Dashboard</h1>
            <p class="text-sm text-slate-500 mt-0.5">Earn by referring other businesses to Zonely</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6 text-center">
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Referrals</p>
                <p class="text-2xl sm:text-3xl font-black text-slate-900">{{ $stats['referrals'] ?? 0 }}</p>
            </div>
            <div class="bg-teal-700 rounded-2xl p-5 text-white text-center shadow-sm">
                <p class="text-[10px] font-bold opacity-70 uppercase tracking-widest mb-1">Earned</p>
                <p class="text-2xl sm:text-3xl font-black">${{ number_format($stats['earned'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pending</p>
                <p class="text-2xl sm:text-3xl font-black text-amber-500">${{ number_format($stats['pending'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Paid Out</p>
                <p class="text-2xl sm:text-3xl font-black text-emerald-600">${{ number_format($stats['paid_out'] ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Referral Link --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6">
            <h2 class="font-bold text-slate-900 mb-1">Your Referral Link</h2>
            <p class="text-xs text-slate-500 mb-4">Share this link. Earn <strong class="text-teal-700">$20</strong> for every business that signs up and receives their first lead.</p>
            <div class="flex gap-2">
                <input type="text" id="refLink"
                    value="{{ url('/user/register/seller?ref=' . (auth()->user()->slug ?? auth()->user()->id)) }}"
                    readonly
                    class="flex-1 text-sm bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 focus:outline-none text-slate-600 font-mono">
                <button onclick="copyRef(this)" class="bg-teal-700 hover:bg-teal-800 text-white font-bold px-5 py-3 rounded-2xl text-sm transition shrink-0">
                    <i class="fa-solid fa-copy mr-1"></i> Copy
                </button>
            </div>
            <div class="flex flex-wrap gap-3 mt-4">
                @php $refUrl = url('/user/register/seller?ref=' . (auth()->user()->slug ?? auth()->user()->id)); @endphp
                <a href="https://wa.me/?text={{ urlencode('Join Zonely and grow your local business! Use my link: ' . $refUrl) }}"
                   target="_blank"
                   class="flex items-center gap-2 text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-4 py-2.5 rounded-xl hover:bg-emerald-100 transition">
                    <i class="fab fa-whatsapp text-base"></i> Share on WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($refUrl) }}"
                   target="_blank"
                   class="flex items-center gap-2 text-xs font-bold text-teal-700 bg-teal-50 border border-teal-100 px-4 py-2.5 rounded-xl hover:bg-teal-100 transition">
                    <i class="fab fa-facebook text-base"></i> Share on Facebook
                </a>
            </div>
        </div>

        {{-- How it works --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6">
            <h2 class="font-bold text-slate-900 mb-4">How It Works</h2>
            <div class="space-y-4">
                @foreach([
                    ['icon'=>'fa-share-nodes','color'=>'bg-teal-100 text-teal-700','step'=>'1','title'=>'Share your link','desc'=>'Send your unique referral link to local businesses in your area.'],
                    ['icon'=>'fa-user-plus','color'=>'bg-purple-100 text-purple-600','step'=>'2','title'=>'They sign up','desc'=>'The business registers on Zonely and sets up their free landing page.'],
                    ['icon'=>'fa-phone','color'=>'bg-emerald-100 text-emerald-600','step'=>'3','title'=>'They receive a lead','desc'=>'As soon as their first verified lead is delivered, your commission is locked.'],
                    ['icon'=>'fa-dollar-sign','color'=>'bg-amber-100 text-amber-600','step'=>'4','title'=>'You earn $20','desc'=>'Your commission is credited within 7 days and paid out monthly.'],
                ] as $s)
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 {{ $s['color'] }} rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $s['icon'] }} text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-base text-slate-900">{{ $s['step'] }}. {{ $s['title'] }}</p>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $s['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Referral History --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h2 class="font-bold text-slate-900">Referral History</h2>
            </div>
            @if(isset($commissions) && $commissions->count())
            <div class="divide-y divide-slate-50">
                @foreach($commissions as $c)
                <div class="px-5 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-sm text-slate-600">
                            {{ strtoupper(substr($c->referredUser?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-slate-900">{{ $c->referredUser?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $c->created_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm text-emerald-600">+${{ number_format($c->amount, 2) }}</p>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $c->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $c->status === 'paid' ? 'Paid' : 'Pending' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-12 text-center">
                <i class="fa-solid fa-share-nodes text-4xl text-slate-200 mb-3"></i>
                <p class="font-semibold text-slate-400 text-sm">No referrals yet</p>
                <p class="text-xs text-slate-400 mt-1">Share your link above to start earning</p>
            </div>
            @endif
        </div>

    </div>
</div>

<script>
function copyRef(btn) {
    const input = document.getElementById('refLink');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Copied!';
        btn.classList.replace('bg-teal-700','bg-emerald-500');
        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-copy mr-1"></i> Copy';
            btn.classList.replace('bg-emerald-500','bg-teal-700');
        }, 2000);
    });
}
</script>
@endsection
