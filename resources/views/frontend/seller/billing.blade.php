@extends('frontend.layouts.__prof_app')
@section('title', 'Billing')

@section('css')
<style>
    .period-btn.active { background:#0d9488!important;color:#fff!important;border-color:#0d9488!important; }
    .filter-btn.active { background:#1e293b!important;color:#fff!important;border-color:#1e293b!important; }
    .scroll-hide { -ms-overflow-style:none;scrollbar-width:none; }
    .scroll-hide::-webkit-scrollbar { display:none; }
    .lead-row { transition:background .1s; }
    .lead-row:hover { background:#f8fafc; }
    .lead-row.selected { background:#f0fdf9; }
    .lead-row.hidden-row { display:none; }
</style>
@endsection

@section('content')
@php
    $unpaidCount = $balance['unpaid_count'];
    $threshold   = $threshold ?? 30;
    $overdue     = $balance['unpaid'] >= $threshold;
@endphp

<div class="max-w-2xl mx-auto px-4 py-6 pb-20 lg:px-6 lg:py-8">

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">Billing</h1>
    <p class="text-xs text-slate-500 mt-0.5">Lead fees, payment history, and account settings</p>
</div>

@if(session('success'))
<div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl flex items-center gap-2">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Overdue warning banner --}}
@if($overdue)
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg shrink-0"></i>
        <div>
            <p class="text-sm font-bold text-red-700">Balance has reached your payment threshold</p>
            <p class="text-xs text-red-500 mt-0.5">Pay now to keep receiving new leads — ${{ number_format($balance['unpaid'], 2) }} outstanding</p>
        </div>
    </div>
    <button onclick="payAllDue()"
        class="w-full sm:w-auto shrink-0 bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs transition text-center">
        Pay All Due
    </button>
</div>
@endif

{{-- Summary cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Billed</p>
        <p class="text-2xl font-black text-slate-900">${{ number_format($balance['total_billed'], 2) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">{{ $balance['total_leads'] }} leads all time</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Paid</p>
        <p class="text-2xl font-black text-emerald-600">${{ number_format($balance['total_paid'], 2) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">all time</p>
    </div>
    <div class="bg-white rounded-2xl border {{ $overdue ? 'border-red-200 bg-red-50' : 'border-slate-100' }} shadow-sm p-4 text-center">
        <p class="text-[10px] font-bold {{ $overdue ? 'text-red-400' : 'text-slate-400' }} uppercase tracking-widest mb-1">Outstanding</p>
        <p class="text-2xl font-black {{ $overdue ? 'text-red-600' : 'text-slate-900' }}">${{ number_format($balance['unpaid'], 2) }}</p>
        <p class="text-[11px] {{ $overdue ? 'text-red-400' : 'text-slate-400' }} mt-0.5">{{ $unpaidCount }} leads due</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Threshold</p>
        <p class="text-2xl font-black text-slate-900">${{ number_format($threshold, 2) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">set by Zonely</p>
    </div>
</div>

{{-- Payment settings --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="font-bold text-slate-900 text-sm">Payment settings</h2>
            <p class="text-xs text-slate-500 mt-0.5">How and when you get billed</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
        <div class="px-5 py-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Payment method</p>
            <p class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fab fa-paypal text-blue-500"></i> PayPal
            </p>
        </div>
        <div class="px-5 py-4">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Payment threshold</p>
            <p class="text-sm font-bold text-slate-800">${{ number_format($threshold, 2) }} balance limit</p>
            @php $pct = $threshold > 0 ? min(100, round($balance['unpaid'] / $threshold * 100)) : 0; @endphp
            <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-1.5 rounded-full transition-all {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 75 ? 'bg-amber-400' : 'bg-teal-500') }}"
                     style="width:{{ $pct }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
                ${{ number_format($balance['unpaid'], 2) }} of ${{ number_format($threshold, 2) }}
                @if($pct >= 100) · <span class="text-red-500 font-semibold">overdue</span>
                @elseif($pct >= 75) · almost due
                @endif
            </p>
        </div>
    </div>
</div>

{{-- Lead fees table --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" id="leadFeesSection">

    {{-- Table header --}}
    <div class="px-5 py-4 border-b border-slate-100">
        <div class="flex items-start justify-between gap-3 flex-wrap mb-3">
            <div>
                <h2 class="font-bold text-slate-900 text-sm">Lead fees</h2>
                <p class="text-xs text-slate-500 mt-0.5" id="periodSummary">
                    {{ $balance['period_count'] }} leads ·
                    ${{ number_format($balance['period_billed'], 2) }} billed ·
                    ${{ number_format($balance['period_paid'], 2) }} paid
                    <span class="text-slate-300 mx-1">·</span>
                    {{ ucfirst($period === 'week' ? 'this week' : ($period === 'today' ? 'today' : 'this ' . $period)) }}
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button id="paySelectedBtn" onclick="openPaySelected()"
                    class="hidden w-full sm:w-auto justify-center bg-teal-700 hover:bg-teal-800 text-white font-bold px-4 py-2.5 sm:py-2 rounded-xl text-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-credit-card text-[10px]"></i>
                    Pay Selected (<span id="selectedTotal">$0.00</span>)
                </button>
                <button onclick="payAllDue()"
                    class="w-full sm:w-auto justify-center bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2.5 sm:py-2 rounded-xl text-xs transition flex items-center gap-1.5 {{ $unpaidCount === 0 ? 'opacity-40 cursor-not-allowed' : '' }}"
                    {{ $unpaidCount === 0 ? 'disabled' : '' }}>
                    <i class="fa-solid fa-bolt text-[10px]"></i> Pay All Due
                </button>
            </div>
        </div>

        {{-- Period tabs --}}
        <div class="flex gap-2 overflow-x-auto scroll-hide pb-1 mb-3">
            @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $val=>$label)
            <a href="{{ request()->fullUrlWithQuery(['period' => $val]) }}"
               class="period-btn {{ $period === $val ? 'active' : '' }} px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Status filter --}}
        <div class="flex gap-2">
            <button onclick="filterStatus(this,'all')"   class="filter-btn active px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-700">
                All ({{ $allLeads->count() }})
            </button>
            <button onclick="filterStatus(this,'due')"   class="filter-btn px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
                Due ({{ $unpaidCount }})
            </button>
            <button onclick="filterStatus(this,'paid')"  class="filter-btn px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
                Paid ({{ $allLeads->whereNotNull('paid_at')->count() }})
            </button>
        </div>
    </div>

    {{-- Table --}}
    @if($allLeads->count())
    <div class="overflow-x-auto">
        <table class="w-full text-xs" id="billingTable">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="px-4 py-3 w-8">
                        <input type="checkbox" id="checkAll" onchange="toggleAll(this)"
                               class="rounded accent-teal-700 w-3.5 h-3.5">
                    </th>
                    <th class="px-4 py-3 text-left font-bold text-slate-500 uppercase tracking-wider text-[10px]">Lead</th>
                    <th class="px-4 py-3 text-left font-bold text-slate-500 uppercase tracking-wider text-[10px]">Channel</th>
                    <th class="hidden sm:table-cell px-4 py-3 text-left font-bold text-slate-500 uppercase tracking-wider text-[10px]">Date</th>
                    <th class="hidden sm:table-cell px-4 py-3 text-left font-bold text-slate-500 uppercase tracking-wider text-[10px]">Service</th>
                    <th class="px-4 py-3 text-right font-bold text-slate-500 uppercase tracking-wider text-[10px]">Fee</th>
                    <th class="px-4 py-3 text-center font-bold text-slate-500 uppercase tracking-wider text-[10px]">Status</th>
                </tr>
            </thead>
            <tbody id="billingBody">
                @foreach($allLeads as $lead)
                @php
                    $isPaid  = !is_null($lead->paid_at);
                    $source  = $lead->source ?? 'form';
                    $chIcon  = match($source) { 'phone'=>'📞', 'whatsapp'=>'💬', 'email'=>'📧', default=>'📋' };
                    $chLabel = match($source) { 'phone'=>'Phone', 'whatsapp'=>'WhatsApp', 'email'=>'Email', default=>'Form' };
                    $chColor = match($source) {
                        'phone'    => 'bg-amber-50 text-amber-700 border-amber-200',
                        'whatsapp' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'email'    => 'bg-blue-50 text-blue-700 border-blue-200',
                        default    => 'bg-slate-50 text-slate-600 border-slate-200',
                    };
                @endphp
                <tr class="lead-row border-b border-slate-50 {{ $isPaid ? 'opacity-60' : '' }}"
                    data-status="{{ $isPaid ? 'paid' : 'due' }}"
                    data-fee="{{ $isPaid ? 0 : $lead->fee }}"
                    data-id="{{ $lead->id }}">
                    <td class="px-4 py-3 text-center">
                        @if(!$isPaid)
                        <input type="checkbox" class="lead-check rounded accent-teal-700 w-3.5 h-3.5"
                               data-fee="{{ $lead->fee }}" data-id="{{ $lead->id }}"
                               onchange="updateSelected()">
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('seller.lead.detail', $lead->id) }}"
                           class="font-bold text-teal-700 hover:underline text-xs">#ZL-{{ $lead->id }}</a>
                        <p class="text-slate-400 text-[11px] mt-0.5">
                            {{ ($lead->name && $lead->name !== 'Phone Lead') ? $lead->name : 'Phone lead' }}
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full border {{ $chColor }}">
                            {{ $chIcon }} {{ $chLabel }}
                        </span>
                    </td>
                    <td class="hidden sm:table-cell px-4 py-3 text-slate-500">{{ $lead->created_at->format('M d, Y') }}</td>
                    <td class="hidden sm:table-cell px-4 py-3 text-slate-500">{{ $lead->service && !in_array($lead->service, ['Phone Call','General Inquiry']) ? $lead->service : '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">${{ number_format($lead->fee, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($isPaid)
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✓ Paid
                        </span>
                        @else
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-50 text-red-600 border border-red-200">
                            Due
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="py-14 text-center text-slate-400">
        <i class="fa-solid fa-receipt text-4xl text-slate-200 mb-3 block"></i>
        <p class="font-bold text-slate-700">No leads yet</p>
        <p class="text-xs mt-1">Lead fees appear here when buyers contact you.</p>
    </div>
    @endif

</div>

</div>

{{-- PayPal Modal --}}
<div id="paypal-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="align-items:center">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-center justify-between mb-1">
            <h3 class="font-bold text-slate-900">Pay Lead Fees</h3>
            <button onclick="closePayPalModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <p class="text-sm text-slate-500 mb-4">
            <span id="modal-lead-count"></span> ·
            Total: <span id="modal-amount" class="font-bold text-slate-900"></span>
        </p>
        <div id="paypal-button-container" class="min-h-[50px]"></div>
        <p class="text-xs text-slate-400 text-center mt-3">Secured by PayPal. Your card details are never stored.</p>
    </div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
let paypalButtons = null;
let pendingLeadIds = [];
let pendingAmount  = 0;

// ── Status filter ──────────────────────────────────────────
function filterStatus(btn, status) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.lead-row').forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    });
    uncheckAll();
}

// ── Checkbox logic ────────────────────────────────────────
function toggleAll(master) {
    document.querySelectorAll('.lead-check').forEach(cb => {
        const row = cb.closest('.lead-row');
        if (!row.classList.contains('hidden-row')) cb.checked = master.checked;
    });
    updateSelected();
}

function uncheckAll() {
    document.querySelectorAll('.lead-check').forEach(cb => cb.checked = false);
    const master = document.getElementById('checkAll');
    if (master) master.checked = false;
    updateSelected();
}

function updateSelected() {
    const checked = [...document.querySelectorAll('.lead-check:checked')];
    const total   = checked.reduce((sum, cb) => sum + parseFloat(cb.dataset.fee), 0);
    const btn     = document.getElementById('paySelectedBtn');
    document.getElementById('selectedTotal').textContent = '$' + total.toFixed(2);
    if (checked.length > 0) {
        btn.classList.remove('hidden');
    } else {
        btn.classList.add('hidden');
    }
}

// ── Pay actions ───────────────────────────────────────────
function openPaySelected() {
    const checked = [...document.querySelectorAll('.lead-check:checked')];
    if (!checked.length) return;
    pendingLeadIds = checked.map(cb => cb.dataset.id);
    pendingAmount  = checked.reduce((sum, cb) => sum + parseFloat(cb.dataset.fee), 0);
    openModal(pendingAmount, checked.length + ' lead' + (checked.length > 1 ? 's' : ''));
}

function payAllDue() {
    const allDue = [...document.querySelectorAll('.lead-check')];
    if (!allDue.length) return;
    pendingLeadIds = allDue.map(cb => cb.dataset.id);
    pendingAmount  = allDue.reduce((sum, cb) => sum + parseFloat(cb.dataset.fee), 0);
    openModal(pendingAmount, allDue.length + ' lead' + (allDue.length > 1 ? 's' : ''));
}

function openModal(amount, label) {
    document.getElementById('modal-amount').textContent = '$' + amount.toFixed(2);
    document.getElementById('modal-lead-count').textContent = label;
    document.getElementById('paypal-modal').classList.remove('hidden');
    document.getElementById('paypal-button-container').innerHTML = '';
    if (paypalButtons) { try { paypalButtons.close(); } catch(e){} }

    paypalButtons = paypal.Buttons({
        style: { layout:'vertical', color:'gold', shape:'rect', label:'pay' },
        createOrder: (data, actions) => actions.order.create({
            purchase_units: [{ amount: { value: amount.toFixed(2), currency_code:'USD' }, description:'Zonely Lead Fees' }]
        }),
        onApprove: (data, actions) => actions.order.capture().then(() => {
            fetch('{{ route("seller.billing.pay.bulk") }}', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ lead_ids: pendingLeadIds, paypal_order_id: data.orderID })
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) { closePayPalModal(); location.reload(); }
                else alert('Verification failed: ' + (res.error || 'Unknown error'));
            });
        }),
        onError: err => { alert('Payment failed. Please try again.'); console.error(err); }
    });
    paypalButtons.render('#paypal-button-container');
}

function closePayPalModal() {
    document.getElementById('paypal-modal').classList.add('hidden');
    pendingLeadIds = [];
    pendingAmount  = 0;
}
</script>
@endsection
