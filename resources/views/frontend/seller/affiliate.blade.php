@extends('frontend.layouts.__prof_app')
@section('title', 'Affiliate Dashboard')

@section('css')
<style>
    .tier-bar { height: 8px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .tier-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #0d9488, #10b981); transition: width 0.8s ease; }
    .pulse-ring { animation: pulseRing 2s infinite; }
    @keyframes pulseRing { 0%,100%{box-shadow:0 0 0 0 rgba(13,148,136,0.3);} 50%{box-shadow:0 0 0 8px rgba(13,148,136,0);} }
    .card-hover { transition: transform .2s ease, box-shadow .2s ease; }
    .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -4px rgba(0,0,0,.1); }
    .share-btn { transition: all .2s ease; }
    .share-btn:hover { transform: scale(1.04); }
    .count-up { animation: fadeInUp 0.6s ease forwards; }
    @keyframes fadeInUp { from{opacity:0;transform:translateY(8px);} to{opacity:1;transform:translateY(0);} }
</style>
@endsection

@section('content')
@php
    $earned  = $stats['earned']   ?? 0;
    $pending = $stats['pending']  ?? 0;
    $paidOut = $stats['paid_out'] ?? 0;
@endphp

<div class="pb-12">
<div class="max-w-3xl mx-auto px-4 py-6 lg:px-6 lg:py-8">

    {{-- ── HERO BANNER ── --}}
    <div class="relative bg-gradient-to-br from-teal-700 via-teal-800 to-slate-900 rounded-3xl p-6 mb-6 overflow-hidden">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-32 translate-x-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-teal-400 rounded-full translate-y-24 -translate-x-24"></div>
        </div>

        <div class="relative z-10">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <div class="inline-flex items-center gap-2 bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full pulse-ring"></span>
                        Affiliate Program — Active
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                        Every seller you bring =<br>
                        <span class="text-emerald-400">cash + points + rank.</span>
                    </h1>
                    <p class="text-teal-200 text-sm mt-2 max-w-sm">
                        Share your link. When they join and get their first lead — you earn <strong class="text-white">${{ number_format($commRate, 0) }} cash</strong> and <strong class="text-white">+35 points</strong> instantly. Every milestone they hit keeps rewarding you.
                    </p>
                </div>
                <div class="shrink-0 hidden sm:block text-center bg-white/10 rounded-2xl px-5 py-4">
                    <p class="text-3xl font-black text-white">${{ number_format($earned, 0) }}</p>
                    <p class="text-teal-300 text-xs font-semibold mt-0.5">Total Earned</p>
                </div>
            </div>

            {{-- Referral Link --}}
            <div class="flex gap-2 mb-3">
                <input type="text" id="refLink" value="{{ $refUrl }}" readonly
                    class="flex-1 text-xs bg-white/10 border border-white/20 text-white rounded-xl px-4 py-3 focus:outline-none font-mono placeholder-teal-300 truncate">
                <button onclick="copyRef(this)"
                    class="bg-emerald-500 hover:bg-emerald-400 text-white font-bold px-5 py-3 rounded-xl text-sm transition shrink-0 flex items-center gap-2">
                    <i class="fa-solid fa-copy text-xs"></i> Copy Link
                </button>
            </div>

            {{-- Share buttons --}}
            <div class="flex flex-wrap gap-2">
                <a href="https://wa.me/?text={{ urlencode('I use Zonely to grow my business and you should too! Sign up with my link and get started: ' . $refUrl) }}"
                   target="_blank"
                   class="share-btn flex items-center gap-1.5 text-xs font-bold text-white bg-green-500/80 hover:bg-green-500 px-4 py-2 rounded-xl transition">
                    <i class="fab fa-whatsapp text-sm"></i> WhatsApp
                </a>
                <a href="sms:?body={{ urlencode('Join Zonely — get local service leads. Sign up free: ' . $refUrl) }}"
                   class="share-btn flex items-center gap-1.5 text-xs font-bold text-white bg-blue-500/80 hover:bg-blue-500 px-4 py-2 rounded-xl transition">
                    <i class="fa-solid fa-comment-sms text-sm"></i> SMS
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($refUrl) }}"
                   target="_blank"
                   class="share-btn flex items-center gap-1.5 text-xs font-bold text-white bg-blue-700/80 hover:bg-blue-700 px-4 py-2 rounded-xl transition">
                    <i class="fab fa-facebook text-sm"></i> Facebook
                </a>
                <button onclick="shareNative()"
                   class="share-btn flex items-center gap-1.5 text-xs font-bold text-white bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl transition">
                    <i class="fa-solid fa-share-nodes text-sm"></i> More
                </button>
            </div>
        </div>
    </div>

    {{-- ── STATS ROW ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="card-hover bg-white rounded-2xl border border-slate-100 p-4 shadow-sm text-center count-up">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Referrals</p>
            <p class="text-3xl font-black text-slate-900">{{ $totalRefs }}</p>
            <p class="text-[11px] text-teal-600 font-semibold mt-0.5">partners recruited</p>
        </div>
        <div class="card-hover bg-teal-700 rounded-2xl p-4 shadow-sm text-center count-up">
            <p class="text-[10px] font-bold text-teal-300 uppercase tracking-widest mb-1">Cash Earned</p>
            <p class="text-3xl font-black text-white">${{ number_format($earned, 0) }}</p>
            <p class="text-[11px] text-teal-300 font-semibold mt-0.5">total commission</p>
        </div>
        <div class="card-hover bg-white rounded-2xl border border-slate-100 p-4 shadow-sm text-center count-up">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pending</p>
            <p class="text-3xl font-black text-amber-500">${{ number_format($pending, 0) }}</p>
            <p class="text-[11px] text-amber-500 font-semibold mt-0.5">processing</p>
        </div>
        <div class="card-hover bg-white rounded-2xl border border-slate-100 p-4 shadow-sm text-center count-up">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Paid Out</p>
            <p class="text-3xl font-black text-emerald-600">${{ number_format($paidOut, 0) }}</p>
            <p class="text-[11px] text-emerald-600 font-semibold mt-0.5">in your pocket</p>
        </div>
    </div>

    {{-- ── TIER PROGRESS ── --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-400 text-sm"></i>
                    Your Affiliate Tier
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">More referrals = higher tier = bigger rewards</p>
            </div>
            <span class="text-xs font-black px-3 py-1.5 rounded-xl
                {{ $tierLabel === 'Zonely Pro' ? 'bg-gradient-to-r from-amber-400 to-orange-400 text-white' :
                   ($tierLabel === 'Elite'     ? 'bg-teal-700 text-white' :
                   ($tierLabel === 'Trusted'   ? 'bg-purple-600 text-white' :
                   ($tierLabel === 'Rising'    ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-600'))) }}">
                {{ $tierLabel }}
            </span>
        </div>

        {{-- Progress bar --}}
        <div class="mb-3">
            <div class="flex justify-between text-xs text-slate-500 mb-2">
                <span class="font-semibold">{{ $totalRefs }} referrals</span>
                @if($remaining > 0)
                <span class="text-teal-600 font-bold">{{ $remaining }} more → {{ $nextLabel }}</span>
                @else
                <span class="text-emerald-600 font-bold">Max tier reached 🏆</span>
                @endif
            </div>
            <div class="tier-bar">
                <div class="tier-bar-fill" style="width: {{ $tierPct }}%"></div>
            </div>
        </div>

        {{-- Tier milestones --}}
        <div class="grid grid-cols-5 gap-1 mt-4">
            @foreach([
                ['label'=>'Starter', 'req'=>0,  'color'=>'bg-slate-200 text-slate-500'],
                ['label'=>'Rising',  'req'=>3,  'color'=>'bg-blue-500 text-white'],
                ['label'=>'Trusted', 'req'=>5,  'color'=>'bg-purple-600 text-white'],
                ['label'=>'Elite',   'req'=>10, 'color'=>'bg-teal-700 text-white'],
                ['label'=>'Pro 🏆',  'req'=>25, 'color'=>'bg-gradient-to-br from-amber-400 to-orange-400 text-white'],
            ] as $tier)
            <div class="text-center">
                <div class="rounded-xl py-2 px-1 text-[10px] font-black mb-1
                    {{ $totalRefs >= $tier['req'] ? $tier['color'] : 'bg-slate-100 text-slate-300' }}">
                    {{ $tier['label'] }}
                </div>
                <p class="text-[9px] text-slate-400">{{ $tier['req'] }}+ refs</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── EARNINGS PROJECTOR ── --}}
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 mb-6">
        <h2 class="font-bold text-white mb-1 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-emerald-400 text-sm"></i>
            Earnings Projector
        </h2>
        <p class="text-slate-400 text-xs mb-5">See how much you earn by growing your referral network</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($projections as $p)
            <div class="bg-white/10 rounded-2xl p-4 text-center border border-white/10 hover:bg-white/15 transition cursor-default">
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-2">{{ $p['refs'] }} {{ $p['refs'] === 1 ? 'Referral' : 'Referrals' }}</p>
                <p class="text-2xl font-black text-emerald-400">${{ $p['cash'] }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">cash</p>
                <div class="w-full h-px bg-white/10 my-2"></div>
                <p class="text-lg font-black text-teal-300">+{{ $p['pts'] }}</p>
                <p class="text-[11px] text-slate-400">points</p>
            </div>
            @endforeach
        </div>

        <p class="text-[11px] text-slate-500 mt-4 text-center">
            * Bonus points earned as your referrals grow and hit milestones — rewards keep coming long after they join.
        </p>
    </div>

    {{-- ── HOW IT WORKS ── --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6">
        <h2 class="font-bold text-slate-900 mb-5 flex items-center gap-2">
            <i class="fa-solid fa-route text-teal-700 text-sm"></i>
            3 Steps to Start Earning
        </h2>
        <div class="space-y-4">
            @foreach([
                ['icon'=>'fa-share-nodes', 'color'=>'bg-teal-100 text-teal-700', 'num'=>'01',
                 'title'=>'Share your unique link',
                 'desc' =>'Send it to local businesses — plumbers, lawyers, cleaners, consultants. Anyone who wants more clients.',
                 'badge'=>'Takes 10 seconds'],
                ['icon'=>'fa-user-check',  'color'=>'bg-purple-100 text-purple-600', 'num'=>'02',
                 'title'=>'They join and set up',
                 'desc' =>'They register free, build their profile, and connect Twilio. You get +35 points the moment they sign up.',
                 'badge'=>'Instant points'],
                ['icon'=>'fa-coins',       'color'=>'bg-amber-100 text-amber-600', 'num'=>'03',
                 'title'=>'They get a lead — you get paid',
                 'desc' =>'First verified lead delivered to them → $' . number_format($commRate, 0) . ' cash + bonus points credited to you. Every milestone after keeps rewarding you.',
                 'badge'=>'Cash + points forever'],
            ] as $s)
            <div class="flex items-start gap-4 p-4 rounded-2xl hover:bg-slate-50 transition">
                <div class="w-11 h-11 {{ $s['color'] }} rounded-2xl flex items-center justify-center shrink-0 font-black text-sm">
                    <i class="fa-solid {{ $s['icon'] }}"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-0.5">
                        <p class="font-bold text-slate-900">{{ $s['title'] }}</p>
                        <span class="text-[10px] bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full font-bold">{{ $s['badge'] }}</span>
                    </div>
                    <p class="text-sm text-slate-500">{{ $s['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── REWARD BREAKDOWN ── --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-6">
        <h2 class="font-bold text-slate-900 mb-1 flex items-center gap-2">
            <i class="fa-solid fa-gift text-purple-500 text-sm"></i>
            Every Milestone Rewards You
        </h2>
        <p class="text-xs text-slate-500 mb-5">You earn when they join — and keep earning as they grow.</p>

        <div class="space-y-2">
            @foreach([
                ['event'=>'Referred seller joins platform',       'cash'=>'$'.number_format($commRate,0), 'pts'=>'+35', 'color'=>'text-emerald-600'],
                ['event'=>'They complete their first job',        'cash'=>'$'.number_format($commRate,0), 'pts'=>'+25', 'color'=>'text-emerald-600'],
                ['event'=>'They reach Rising tier',               'cash'=>'—',                            'pts'=>'+50', 'color'=>'text-blue-600'],
                ['event'=>'They reach Trusted tier',              'cash'=>'—',                            'pts'=>'+100','color'=>'text-purple-600'],
                ['event'=>'They reach Elite tier',                'cash'=>'—',                            'pts'=>'+200','color'=>'text-teal-600'],
                ['event'=>'They pay 3 months platform fees',      'cash'=>'—',                            'pts'=>'+75', 'color'=>'text-teal-600'],
                ['event'=>'They refer another seller (2nd level)','cash'=>'—',                            'pts'=>'+20', 'color'=>'text-amber-600'],
            ] as $r)
            <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full {{ str_contains($r['color'], 'emerald') ? 'bg-emerald-400' : (str_contains($r['color'], 'purple') ? 'bg-purple-400' : (str_contains($r['color'], 'blue') ? 'bg-blue-400' : (str_contains($r['color'], 'amber') ? 'bg-amber-400' : 'bg-teal-400'))) }} shrink-0"></div>
                    <p class="text-sm text-slate-700">{{ $r['event'] }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    @if($r['cash'] !== '—')
                    <span class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">{{ $r['cash'] }}</span>
                    @endif
                    <span class="text-sm font-bold {{ $r['color'] }} bg-slate-100 px-2.5 py-1 rounded-lg">{{ $r['pts'] }} pts</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 bg-teal-50 border border-teal-100 rounded-2xl p-4">
            <p class="text-sm font-bold text-teal-800 flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-teal-600"></i>
                One referral who reaches Elite = $45 cash + 430 points for you
            </p>
            <p class="text-xs text-teal-600 mt-1">Points boost your own tier, unlock AI assistant access, and push your profile higher in search results.</p>
        </div>
    </div>

    {{-- ── LEADERBOARD TEASER ── --}}
    @if($totalRefs > 0)
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-100 rounded-3xl p-5 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-amber-400 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-ranking-star text-white text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-900">You have {{ $totalRefs }} {{ $totalRefs === 1 ? 'referral' : 'referrals' }} — keep growing!</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($remaining > 0)
                        Just <strong class="text-teal-700">{{ $remaining }} more</strong> to reach <strong class="text-teal-700">{{ $nextLabel }}</strong> tier and unlock bigger rewards.
                    @else
                        You've reached the top tier. You're among Zonely's best partners. 🏆
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── REFERRAL HISTORY ── --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900">Referral History</h2>
            @if(isset($commissions) && $commissions->count())
            <span class="text-xs bg-teal-100 text-teal-700 font-bold px-3 py-1 rounded-full">{{ $commissions->count() }} partners</span>
            @endif
        </div>

        @if(isset($commissions) && $commissions->count())
        <div class="divide-y divide-slate-50">
            @foreach($commissions as $c)
            <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-teal-100 text-teal-700 rounded-xl flex items-center justify-center font-bold text-sm shrink-0">
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
            <div class="w-16 h-16 bg-teal-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-share-nodes text-teal-400 text-2xl"></i>
            </div>
            <p class="font-bold text-slate-700 mb-1">No referrals yet</p>
            <p class="text-sm text-slate-400 mb-5 max-w-xs mx-auto">Share your link above. Every business you bring earns you cash and points — permanently.</p>
            <button onclick="copyRef(document.querySelector('button'))"
                class="bg-teal-700 hover:bg-teal-800 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition">
                <i class="fa-solid fa-copy mr-2"></i> Copy My Referral Link
            </button>
        </div>
        @endif
    </div>

</div>
</div>

<script>
const _refUrl = "{{ $refUrl }}";

function copyRef(btn) {
    const input = document.getElementById('refLink');
    if (input) input.select();
    navigator.clipboard.writeText(_refUrl).then(() => {
        if (!btn) return;
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Copied!';
        btn.classList.add('bg-emerald-500');
        btn.classList.remove('bg-teal-700', 'bg-emerald-500');
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}

function shareNative() {
    if (navigator.share) {
        navigator.share({
            title: 'Join Zonely',
            text: 'Grow your local business with Zonely — get leads directly to your phone.',
            url: _refUrl
        });
    } else {
        copyRef(null);
        alert('Link copied! Share it anywhere.');
    }
}
</script>
@endsection
