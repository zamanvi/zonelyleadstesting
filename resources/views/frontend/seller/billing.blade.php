@extends('frontend.layouts.__prof_app')
@section('title', 'Billing')
@section('content')
<div class="pb-10">
    <div class="max-w-3xl mx-auto py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Billing</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manage lead fees and payment history</p>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Balance Summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-red-50 border border-red-100 rounded-2xl p-5">
                <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest mb-1">Unpaid Balance</p>
                <p class="text-3xl font-black text-red-600">${{ number_format($balance['unpaid'] ?? 0, 2) }}</p>
                <p class="text-xs text-red-400 mt-1">{{ $balance['unpaid_count'] ?? 0 }} leads pending payment</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Paid This Month</p>
                <p class="text-3xl font-black text-emerald-600">${{ number_format($balance['paid_month'] ?? 0, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $balance['paid_count'] ?? 0 }} leads paid</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Paid</p>
                <p class="text-3xl font-black text-slate-900">${{ number_format($balance['total_paid'] ?? 0, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">All time</p>
            </div>
        </div>

        {{-- Unpaid Leads --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm mb-6 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-bold text-slate-900">Unpaid Lead Fees</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pay to continue receiving leads and unlock full chat</p>
                </div>
                @if(($balance['unpaid_count'] ?? 0) > 0)
                <button onclick="payAll()" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-3 sm:py-2.5 rounded-2xl text-sm transition">
                    Pay All ${{ number_format($balance['unpaid'] ?? 0, 2) }}
                </button>
                @endif
            </div>

            <div class="divide-y divide-slate-50">
                @forelse($unpaidLeads ?? [] as $lead)
                <div class="px-5 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center font-black text-sm text-amber-600">
                            {{ strtoupper(substr($lead->phone ?? 'LD', -2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-900">{{ $lead->phone ?? 'Unknown' }}</p>
                            <p class="text-sm text-slate-500">{{ $lead->service ?? 'General' }} · {{ $lead->created_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <p class="font-bold text-slate-900">${{ number_format($lead->fee ?? 0, 2) }}</p>
                        <button data-lead-id="{{ $lead->id }}" onclick="payLead(this.dataset.leadId, this)"
                            class="bg-teal-700 hover:bg-teal-800 text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                            Pay Now
                        </button>
                    </div>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-slate-400">
                    <i class="fa-solid fa-circle-check text-4xl text-emerald-300 mb-3 block"></i>
                    <p class="font-bold text-slate-700">No unpaid fees</p>
                    <p class="text-xs mt-1">You're all caught up. New leads will appear here when they arrive.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Payment History --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h2 class="font-bold text-slate-900">Payment History</h2>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($paidLeads ?? [] as $lead)
                <div class="px-5 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center font-black text-sm text-emerald-600">
                            {{ strtoupper(substr($lead->phone ?? 'LD', -2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-900">{{ $lead->phone ?? 'Unknown' }}</p>
                            <p class="text-sm text-slate-500">{{ $lead->service ?? 'General' }} · {{ $lead->paid_at?->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-emerald-600">-${{ number_format($lead->fee ?? 0, 2) }}</p>
                        <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">Paid</span>
                    </div>
                </div>
                @empty
                <div class="px-5 py-10 text-center text-slate-400">
                    <i class="fa-solid fa-receipt text-4xl text-slate-200 mb-3 block"></i>
                    <p class="font-bold text-slate-700">No payment history yet</p>
                    <p class="text-xs mt-1">Paid lead fees will appear here.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function payLead(id, btn) {
    if (!id) return; // demo row — no-op
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    fetch(`/seller/billing/${id}/pay`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.ok ? location.reload() : Promise.reject(r.status))
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = 'Pay Now';
        alert('Payment failed. Please try again.');
    });
}
function payAll() {
    if (!confirm('Pay all unpaid lead fees now?')) return;
    document.querySelectorAll('[data-lead-id]').forEach(btn => payLead(btn.dataset.leadId, btn));
}
</script>
@endsection
