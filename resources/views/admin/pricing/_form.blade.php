@php $isEdit = $formId === 'edit'; $p = fn($k) => $isEdit ? "edit_{$k}" : $k; @endphp

<div class="row g-3">

    {{-- Type --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Charge Type <span class="text-danger">*</span></label>
        <select name="type" id="{{ $p('type') }}" class="form-select" required>
            <option value="">-- Select type --</option>
            <option value="lead_fee">Lead Fee — charged to seller per lead received</option>
            <option value="payment_threshold">Payment Threshold — minimum balance before seller must pay</option>
            <option value="affiliate_commission">Seller Affiliate Commission — paid to seller who refers another seller</option>
            <option value="buyer_referral_commission">Buyer Referral Commission — paid to buyer who refers another buyer</option>
        </select>
    </div>

    {{-- Amount --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold small">Amount ($) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" name="amount" id="{{ $p('amount') }}" class="form-control"
                   min="0" max="9999" step="0.01" placeholder="e.g. 45.00" required>
        </div>
    </div>

    {{-- Category --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Category <span class="text-muted fw-normal">(optional)</span></label>
        <select name="category_id" id="{{ $p('category_id') }}" class="form-select">
            <option value="">All categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->title }}</option>
            @foreach($cat->children as $child)
            <option value="{{ $child->id }}">&nbsp;&nbsp;↳ {{ $child->title }}</option>
            @endforeach
            @endforeach
        </select>
        <div class="form-text">Leave blank = applies to all categories</div>
    </div>

    {{-- State --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold small">State <span class="text-muted fw-normal">(optional)</span></label>
        <select name="state_id" id="{{ $p('state_id') }}" class="form-select"
                onchange="loadCities(this.value, '{{ $p('city_id') }}')">
            <option value="">All states</option>
            @foreach($states as $st)
            <option value="{{ $st->id }}">{{ $st->title }}</option>
            @endforeach
        </select>
    </div>

    {{-- City --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold small">City <span class="text-muted fw-normal">(optional)</span></label>
        <select name="city_id" id="{{ $p('city_id') }}" class="form-select">
            <option value="">All cities</option>
        </select>
        <div class="form-text">City rule overrides state and category</div>
    </div>

    {{-- Effective From --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Effective From <span class="text-danger">*</span></label>
        <input type="date" name="effective_from" id="{{ $p('effective_from') }}"
               class="form-control" value="{{ today()->toDateString() }}" required>
    </div>

    {{-- Effective To --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Effective To <span class="text-muted fw-normal">(optional)</span></label>
        <input type="date" name="effective_to" id="{{ $p('effective_to') }}" class="form-control">
        <div class="form-text">Leave blank for permanent rule</div>
    </div>

    {{-- Priority --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold small">Priority</label>
        <input type="number" name="priority" id="{{ $p('priority') }}" class="form-control"
               min="0" max="999" value="0" placeholder="0">
        <div class="form-text">Higher number = wins when rules conflict</div>
    </div>

    {{-- Notes --}}
    <div class="col-12">
        <label class="form-label fw-semibold small">Notes <span class="text-muted fw-normal">(optional)</span></label>
        <textarea name="notes" id="{{ $p('notes') }}" class="form-control" rows="2"
                  placeholder="e.g. Summer promotion for Nashville plumbers — July 2026"></textarea>
    </div>

</div>

{{-- Priority guide --}}
<div class="mt-3 p-3 rounded-3 bg-light small text-muted">
    <strong class="text-dark">Priority order (highest wins):</strong>
    City-specific rule → State-specific rule → Category-specific rule → Global default
</div>
