@extends('frontend.layouts._app')
@section('title', 'My Pricing Rates — Zonely')

@section('content')
@include('frontend.seller._nav')

<div class="min-h-screen bg-slate-50 pt-32 pb-24 px-4">
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-900">My Pricing Rates</h1>
        <p class="text-sm text-slate-500 mt-1">Your current rates based on your category and location. Rates are set by Zonely admin and update automatically.</p>
    </div>

    {{-- Current Rates --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

        {{-- Lead Fee --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 bg-teal-100 text-teal-700 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bolt text-lg"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">Lead Fee</p>
                    <p class="text-xs text-slate-400">Charged per verified lead</p>
                </div>
            </div>
            <p class="text-4xl font-black text-teal-700">${{ number_format($leadFee, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">per verified lead</p>
            <div class="mt-4 pt-4 border-t border-slate-100 space-y-1 text-xs text-slate-500">
                <div class="flex justify-between">
                    <span>Category</span>
                    <span class="font-semibold text-slate-700">{{ $user->category?->title ?? 'Global Default' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>State</span>
                    <span class="font-semibold text-slate-700">{{ $user->state ?? 'Global Default' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>City</span>
                    <span class="font-semibold text-slate-700">{{ $user->city ?? 'Global Default' }}</span>
                </div>
            </div>
        </div>

        {{-- Affiliate Commission --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-link text-lg"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">Affiliate Commission</p>
                    <p class="text-xs text-slate-400">Earned per referred seller</p>
                </div>
            </div>
            <p class="text-4xl font-black text-purple-600">${{ number_format($affiliateComm, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">when referred seller gets first lead</p>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="{{ route('seller.affiliate') }}"
                   class="flex items-center gap-2 text-xs font-bold text-purple-600 hover:underline">
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    View your affiliate dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="bg-teal-50 border border-teal-100 rounded-2xl px-5 py-4 flex items-start gap-3 mb-6">
        <i class="fa-solid fa-circle-info text-teal-600 mt-0.5 shrink-0"></i>
        <div class="text-sm text-teal-700">
            <p class="font-bold mb-0.5">Rates are set by Zonely admin</p>
            <p class="text-xs text-teal-600">Your rates are determined by your service category and location. If Zonely updates a rate, this page reflects it automatically. You will be notified by email at least <strong>14 days</strong> before any rate change takes effect.</p>
        </div>
    </div>

    {{-- Rate History --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-900">Active Pricing Rules</h2>
            <p class="text-xs text-slate-400 mt-0.5">All rules that apply to your category and location</p>
        </div>

        @if($rules->count())
        <div class="divide-y divide-slate-50">
            @foreach($rules as $rule)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-sm text-slate-900">
                        {{ $rule->type === 'lead_fee' ? 'Lead Fee' : 'Affiliate Commission' }}
                        <span class="text-xs font-normal text-slate-400 ml-1">
                            — {{ $rule->city?->name ?? $rule->state?->name ?? $rule->category?->title ?? 'Global Default' }}
                        </span>
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Effective: {{ $rule->effective_from?->format('M d, Y') ?? 'Always' }}
                        @if($rule->effective_to)
                            → {{ $rule->effective_to->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <p class="font-black text-lg text-teal-700 shrink-0">${{ number_format($rule->amount, 2) }}</p>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-6 py-10 text-center">
            <i class="fa-solid fa-tag text-3xl text-slate-200 mb-3"></i>
            <p class="text-sm font-semibold text-slate-400">Using global default rates</p>
            <p class="text-xs text-slate-400 mt-1">No specific rules set for your category or location.</p>
        </div>
        @endif
    </div>

</div>
</div>
@endsection
