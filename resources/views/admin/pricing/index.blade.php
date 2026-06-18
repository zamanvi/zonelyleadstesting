@extends('layouts.admin2')
@section('title', 'Pricing Rules')

@section('content')
<div class="mt-5 pt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-tags text-white" style="font-size:15px;"></i>
                </div>
                <h4 class="mb-0 fw-bold">Pricing Rules</h4>
            </div>
            <p class="text-muted small mb-0" style="padding-left:44px;">Manage lead fees and affiliate commissions by location, category, and date range</p>
        </div>
        <button class="btn fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addRuleModal"
            style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);color:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(29,78,216,.25);">
            <i class="fas fa-plus me-2"></i> Add Pricing Rule
        </button>
    </div>

    @if(session('success'))
    <div class="alert border-0 mb-4 d-flex align-items-center gap-3" style="background:#f0fdf9;border-left:4px solid #0d9488 !important;border-radius:10px;" role="alert">
        <div style="width:32px;height:32px;background:#0d9488;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-check text-white" style="font-size:13px;"></i>
        </div>
        <div class="flex-grow-1 small fw-semibold text-success mb-0">{{ session('success') }}</div>
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── GLOBAL DEFAULTS ── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;overflow:hidden;">
        <div class="px-4 py-3 d-flex align-items-center gap-3" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <div style="width:34px;height:34px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-globe text-white" style="font-size:13px;"></i>
            </div>
            <div>
                <p class="mb-0 fw-bold text-dark" style="font-size:14px;">Global Defaults</p>
                <p class="mb-0 text-muted" style="font-size:12px;">Fallback pricing applied when no specific rule matches</p>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pricing.defaults') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Default Lead Fee ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="default_lead_fee" class="form-control"
                                   value="{{ $defaultLeadFee }}" min="0" max="9999" step="0.01" required>
                        </div>
                        <div class="form-text">Charged to seller per lead when no specific rule applies</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Default Seller Affiliate Commission ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="default_affiliate_commission" class="form-control"
                                   value="{{ $defaultAffComm }}" min="0" max="9999" step="0.01" required>
                        </div>
                        <div class="form-text">Paid to referrer when referred seller gets first lead</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Default Buyer Referral Commission ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="default_buyer_referral_commission" class="form-control"
                                   value="{{ $defaultBuyerRefComm }}" min="0" max="9999" step="0.01" required>
                        </div>
                        <div class="form-text">Paid to buyer who refers another buyer</div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn fw-bold w-100 mt-4" style="background:linear-gradient(135deg,#1e293b,#334155);color:#fff;border-radius:9px;">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── PREVIEW TOOL ── --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;overflow:hidden;">
        <div class="px-4 py-3 d-flex align-items-center gap-3" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <div style="width:34px;height:34px;background:linear-gradient(135deg,#0891b2,#06b6d4);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-magnifying-glass text-white" style="font-size:13px;"></i>
            </div>
            <div>
                <p class="mb-0 fw-bold text-dark" style="font-size:14px;">Rule Preview</p>
                <p class="mb-0 text-muted" style="font-size:12px;">Check what charge applies for a specific scenario</p>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pricing.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Charge Type</label>
                        <select name="preview_type" class="form-select" required>
                            <option value="">-- Select type --</option>
                            <option value="lead_fee"              {{ request('preview_type') === 'lead_fee'             ? 'selected' : '' }}>Lead Fee</option>
                            <option value="affiliate_commission"       {{ request('preview_type') === 'affiliate_commission'       ? 'selected' : '' }}>Seller Affiliate Commission</option>
                            <option value="buyer_referral_commission" {{ request('preview_type') === 'buyer_referral_commission' ? 'selected' : '' }}>Buyer Referral Commission</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Category (optional)</label>
                        <select name="preview_category" class="form-select" id="previewCategory">
                            <option value="">All categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('preview_category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->title }}
                            </option>
                            @foreach($cat->children as $child)
                            <option value="{{ $child->id }}" {{ request('preview_category') == $child->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;↳ {{ $child->title }}
                            </option>
                            @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">State (optional)</label>
                        <select name="preview_state" class="form-select" id="previewState" onchange="loadPreviewCities(this.value,'previewCitySelect')">
                            <option value="">All states</option>
                            @foreach($states as $st)
                            <option value="{{ $st->id }}" {{ request('preview_state') == $st->id ? 'selected' : '' }}>{{ $st->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">City (optional)</label>
                        <select name="preview_city" class="form-select" id="previewCitySelect">
                            <option value="">All cities</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn fw-semibold w-100 text-white" style="background:linear-gradient(135deg,#0891b2,#06b6d4);border-radius:9px;">
                            <i class="fas fa-search me-1"></i> Preview
                        </button>
                    </div>
                </div>
            </form>

            @if($preview)
            <div class="mt-3 p-3 rounded-3" style="background:#f0f9ff; border: 1px solid #bae6fd;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info d-flex align-items-center justify-content-center" style="width:40px;height:40px;shrink:0">
                        <i class="fas fa-circle-check text-white"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-bold text-dark">
                            {{ $preview['type'] === 'lead_fee' ? 'Lead Fee' : 'Affiliate Commission' }}
                            for
                            {{ $preview['category'] ?? 'Any Category' }}
                            in
                            {{ $preview['city'] ?? ($preview['state'] ?? 'Any Location') }}
                            =
                            <span class="text-success fs-5">${{ number_format($preview['amount'], 2) }}</span>
                        </p>
                        <p class="mb-0 small text-muted">This is the effective charge Zonely will apply based on active rules and priority.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── FILTERS ── --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius:14px;overflow:hidden;">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="lead_fee"             {{ request('type') === 'lead_fee'             ? 'selected' : '' }}>Lead Fee</option>
                        <option value="affiliate_commission"       {{ request('type') === 'affiliate_commission'       ? 'selected' : '' }}>Seller Affiliate Commission</option>
                        <option value="buyer_referral_commission" {{ request('type') === 'buyer_referral_commission' ? 'selected' : '' }}>Buyer Referral Commission</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                        @foreach($cat->children as $child)
                        <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>&nbsp;&nbsp;↳ {{ $child->title }}</option>
                        @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="state_id" class="form-select form-select-sm">
                        <option value="">All States</option>
                        @foreach($states as $st)
                        <option value="{{ $st->id }}" {{ request('state_id') == $st->id ? 'selected' : '' }}>{{ $st->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm fw-semibold w-100" style="background:linear-gradient(135deg,#1e293b,#334155);color:#fff;border-radius:8px;">Filter</button>
                    <a href="{{ route('admin.pricing.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── RULES TABLE ── --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
        <div class="px-4 py-3 d-flex align-items-center justify-content-between" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <div class="d-flex align-items-center gap-2">
                <strong style="font-size:14px;">Active Rules</strong>
                <span class="badge" style="background:#e2e8f0;color:#475569;font-size:11px;">{{ $charges->total() }}</span>
            </div>
            <small class="text-muted" style="font-size:11px;">Priority: City → State → Category → Global</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Scope</th>
                        <th>Amount</th>
                        <th>Effective Period</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Created By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charges as $charge)
                    <tr class="{{ !$charge->is_active ? 'opacity-50' : '' }}">
                        <td>
                            @if($charge->type === 'lead_fee')
                            <span class="badge bg-primary-subtle text-primary">Lead Fee</span>
                            @elseif($charge->type === 'affiliate_commission')
                            <span class="badge" style="background:#f3e8ff;color:#7c3aed">Seller Affiliate</span>
                            @else
                            <span class="badge" style="background:#fef9c3;color:#854d0e">Buyer Referral</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($charge->city)
                                <span class="badge bg-success-subtle text-success"><i class="fas fa-city me-1" style="font-size:10px"></i>{{ $charge->city->title }}</span>
                                @endif
                                @if($charge->state)
                                <span class="badge bg-info-subtle text-info"><i class="fas fa-map me-1" style="font-size:10px"></i>{{ $charge->state->title }}</span>
                                @endif
                                @if($charge->category)
                                <span class="badge bg-warning-subtle text-warning"><i class="fas fa-tag me-1" style="font-size:10px"></i>{{ $charge->category->title }}</span>
                                @endif
                                @if(!$charge->city && !$charge->state && !$charge->category)
                                <span class="badge bg-secondary-subtle text-secondary"><i class="fas fa-globe me-1" style="font-size:10px"></i>Global</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <strong class="{{ $charge->type === 'lead_fee' ? 'text-primary' : 'text-success' }} fs-6">
                                ${{ number_format($charge->amount, 2) }}
                            </strong>
                        </td>
                        <td>
                            <div class="small">
                                <div><i class="fas fa-calendar-check text-success me-1" style="font-size:10px"></i>{{ $charge->effective_from->format('M d, Y') }}</div>
                                @if($charge->effective_to)
                                <div><i class="fas fa-calendar-xmark text-danger me-1" style="font-size:10px"></i>{{ $charge->effective_to->format('M d, Y') }}
                                    @if($charge->isExpired()) <span class="badge bg-danger-subtle text-danger ms-1">Expired</span> @endif
                                </div>
                                @else
                                <div class="text-muted">Permanent</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-dark">{{ $charge->priority }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.pricing.toggle', $charge->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $charge->is_active ? 'btn-success' : 'btn-outline-secondary' }}" style="font-size:11px">
                                    {{ $charge->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <span class="small text-muted" title="{{ $charge->notes }}">
                                {{ $charge->notes ? Str::limit($charge->notes, 30) : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="small text-muted">{{ $charge->creator?->name ?? '—' }}</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="openEdit({{ $charge->toJson() }})"
                                data-bs-toggle="modal" data-bs-target="#editRuleModal">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.pricing.destroy', $charge->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Archive this rule?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-tags fa-2x mb-2 d-block opacity-25"></i>
                            No pricing rules yet. Global defaults apply everywhere.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($charges->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $charges->links() }}
        </div>
        @endif
    </div>

</div>

{{-- ════════════════════════════════════════ --}}
{{-- ADD RULE MODAL                          --}}
{{-- ════════════════════════════════════════ --}}
<div class="modal fade" id="addRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.pricing.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i>Add Pricing Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.pricing._form', ['charge' => null, 'formId' => 'add'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════ --}}
{{-- EDIT RULE MODAL                         --}}
{{-- ════════════════════════════════════════ --}}
<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editRuleForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-pen text-warning me-2"></i>Edit Pricing Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.pricing._form', ['charge' => null, 'formId' => 'edit'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white"><i class="fas fa-save me-1"></i>Update Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const statesData  = @json($states);
const categoriesData = @json($categories->toArray());

// Load cities dynamically
async function loadCities(stateId, selectId) {
    const select = document.getElementById(selectId);
    if (!stateId) { select.innerHTML = '<option value="">All cities</option>'; return; }
    const res  = await fetch(`{{ route('admin.pricing.cities', '') }}/${stateId}`);
    const data = await res.json();
    select.innerHTML = '<option value="">All cities</option>' +
        data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
}

function loadPreviewCities(stateId, selectId) { loadCities(stateId, selectId); }

// Populate edit modal
function openEdit(charge) {
    const form = document.getElementById('editRuleForm');
    form.action = `/admin/pricing/${charge.id}`;

    form.querySelector('#edit_type').value           = charge.type          ?? '';
    form.querySelector('#edit_amount').value         = charge.amount        ?? '';
    form.querySelector('#edit_effective_from').value = charge.effective_from?.substring(0,10) ?? '';
    form.querySelector('#edit_effective_to').value   = charge.effective_to?.substring(0,10)   ?? '';
    form.querySelector('#edit_priority').value       = charge.priority      ?? 0;
    form.querySelector('#edit_notes').value          = charge.notes         ?? '';

    // Category
    const catSel = form.querySelector('#edit_category_id');
    if (catSel) catSel.value = charge.category_id ?? '';

    // State → then city
    const stateSel = form.querySelector('#edit_state_id');
    if (stateSel) {
        stateSel.value = charge.state_id ?? '';
        if (charge.state_id) {
            loadCities(charge.state_id, 'edit_city_id').then(() => {
                const citySel = document.getElementById('edit_city_id');
                if (citySel) citySel.value = charge.city_id ?? '';
            });
        }
    }
}
</script>
@endsection
