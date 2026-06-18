@php $isEdit = $formId === 'edit'; $p = fn($k) => $isEdit ? "edit_{$k}" : $k; @endphp

{{-- Section: Charge Details --}}
<div class="mb-4">
    <p class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size:13px;">
        <span style="width:22px;height:22px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
            <i class="fas fa-dollar-sign text-white" style="font-size:9px;"></i>
        </span>
        Charge Details
    </p>
    <div class="row g-3">
        <div class="col-md-7">
            <label class="form-label fw-semibold small">Charge Type <span class="text-danger">*</span></label>
            <select name="type" id="{{ $p('type') }}" class="form-select" required style="border-radius:9px;">
                <option value="">— Select charge type —</option>
                <option value="lead_fee">Lead Fee — charged to seller per lead received</option>
                <option value="payment_threshold">Payment Threshold — minimum balance before seller must pay</option>
                <option value="affiliate_commission">Seller Affiliate Commission — paid to seller who refers another seller</option>
                <option value="buyer_referral_commission">Buyer Referral Commission — paid to buyer who refers another buyer</option>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label fw-semibold small">Amount ($) <span class="text-danger">*</span></label>
            <div class="input-group" style="border-radius:9px;overflow:hidden;">
                <span class="input-group-text bg-white border-end-0 fw-bold text-success">$</span>
                <input type="number" name="amount" id="{{ $p('amount') }}" class="form-control border-start-0"
                       min="0" max="9999" step="0.01" placeholder="e.g. 45.00" required>
            </div>
        </div>
    </div>
</div>

{{-- Divider --}}
<hr style="border-color:#e2e8f0;margin:0 0 16px;">

{{-- Section: Scope --}}
<div class="mb-4">
    <p class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size:13px;">
        <span style="width:22px;height:22px;background:linear-gradient(135deg,#0891b2,#06b6d4);border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
            <i class="fas fa-map-location-dot text-white" style="font-size:9px;"></i>
        </span>
        Scope <span class="fw-normal text-muted ms-1" style="font-size:12px;">— leave blank to apply globally</span>
    </p>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Category <span class="text-muted fw-normal">(optional)</span></label>
            <select name="category_id" id="{{ $p('category_id') }}" class="form-select" style="border-radius:9px;">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                @foreach($cat->children as $child)
                <option value="{{ $child->id }}">&nbsp;&nbsp;↳ {{ $child->title }}</option>
                @endforeach
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">State <span class="text-muted fw-normal">(optional)</span></label>
            <select name="state_id" id="{{ $p('state_id') }}" class="form-select" style="border-radius:9px;"
                    onchange="loadCities(this.value, '{{ $p('city_id') }}')">
                <option value="">All states</option>
                @foreach($states as $st)
                <option value="{{ $st->id }}">{{ $st->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">City <span class="text-muted fw-normal">(optional)</span></label>
            <select name="city_id" id="{{ $p('city_id') }}" class="form-select" style="border-radius:9px;">
                <option value="">All cities</option>
            </select>
            <div class="form-text">City overrides state &amp; category</div>
        </div>
    </div>
</div>

{{-- Divider --}}
<hr style="border-color:#e2e8f0;margin:0 0 16px;">

{{-- Section: Schedule & Priority --}}
<div class="mb-4">
    <p class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size:13px;">
        <span style="width:22px;height:22px;background:linear-gradient(135deg,#0f766e,#0d9488);border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
            <i class="fas fa-calendar text-white" style="font-size:9px;"></i>
        </span>
        Schedule &amp; Priority
    </p>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Effective From <span class="text-danger">*</span></label>
            <input type="date" name="effective_from" id="{{ $p('effective_from') }}"
                   class="form-control" value="{{ today()->toDateString() }}" required style="border-radius:9px;">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Effective To <span class="text-muted fw-normal">(optional)</span></label>
            <input type="date" name="effective_to" id="{{ $p('effective_to') }}" class="form-control" style="border-radius:9px;">
            <div class="form-text">Leave blank = permanent rule</div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Priority</label>
            <input type="number" name="priority" id="{{ $p('priority') }}" class="form-control"
                   min="0" max="999" value="0" placeholder="0" style="border-radius:9px;">
            <div class="form-text">Higher = wins when rules conflict</div>
        </div>
    </div>
</div>

{{-- Notes --}}
<div>
    <label class="form-label fw-semibold small">Notes <span class="text-muted fw-normal">(optional)</span></label>
    <textarea name="notes" id="{{ $p('notes') }}" class="form-control" rows="2" style="border-radius:9px;"
              placeholder="e.g. Summer promotion for Nashville plumbers — July 2026"></textarea>
</div>

{{-- Priority guide --}}
<div class="mt-4 px-3 py-2 d-flex align-items-center gap-2" style="background:#f8fafc;border-radius:9px;border:1px solid #e2e8f0;">
    <i class="fas fa-info-circle text-muted" style="font-size:12px;flex-shrink:0;"></i>
    <span class="small text-muted"><strong class="text-dark">Priority order:</strong> City-specific → State-specific → Category-specific → Global default</span>
</div>
