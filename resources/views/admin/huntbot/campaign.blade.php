@extends('layouts.admin2')
@section('title', 'Hunt: ' . $campaign->city . ' — ' . $campaign->category)

@section('content')
<div class="mt-5 pt-4">

{{-- Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.huntbot.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-crosshairs text-danger me-2"></i>
            {{ $campaign->city }}{{ $campaign->state ? ', '.$campaign->state : '' }}
            <span class="text-muted fw-normal">·</span>
            {{ $campaign->category }}
        </h4>
        <p class="text-muted small mb-0">
            Campaign #{{ $campaign->id }} · {{ $campaign->created_at->format('M j, Y') }}
            @php $sc = ['draft'=>'secondary','running'=>'primary','paused'=>'warning','completed'=>'success'][$campaign->status] ?? 'secondary' @endphp
            <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} ms-2">{{ ucfirst($campaign->status) }}</span>
        </p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Stats row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-3 text-primary">{{ $campaign->total_found }}</div>
            <div class="text-muted small">Found</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-3 text-warning">{{ $campaign->total_contacted }}</div>
            <div class="text-muted small">SMS Sent</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-3 text-info">{{ $campaign->total_replied }}</div>
            <div class="text-muted small">Replied</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-3 text-success">{{ $campaign->total_registered }}</div>
            <div class="text-muted small">Registered</div>
        </div>
    </div>
</div>

{{-- Launch SMS Panel --}}
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-paper-plane text-primary me-2"></i>Launch SMS Campaign</h6>
        <form action="{{ route('admin.huntbot.launch', $campaign->id) }}" method="POST" id="launchForm">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">SMS Template</label>
                    <select name="template_key" class="form-select rounded-3">
                        @foreach(['professional' => 'Professional Services', 'healthcare' => 'Healthcare', 'home' => 'Home Services', 'beauty' => 'Beauty'] as $key => $label)
                        <option value="{{ $key }}" {{ ($campaign->sms_template_key ?? 'professional') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Target</label>
                    <select id="filterTarget" class="form-select rounded-3">
                        <option value="no_website">No website only (recommended)</option>
                        <option value="all">All found businesses</option>
                        <option value="selected">Selected rows only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-danger w-100 rounded-3 fw-bold" id="launchBtn">
                        <i class="fas fa-rocket me-2"></i>Send SMS
                    </button>
                </div>
            </div>
            {{-- Hidden lead_ids populated by JS --}}
            <div id="leadIdsContainer"></div>
        </form>
        <p class="text-muted mt-2 mb-0" style="font-size:11px">
            <i class="fas fa-info-circle me-1"></i>
            Only businesses with a phone number will receive an SMS. Twilio charges ~$0.0075/SMS.
        </p>
    </div>
</div>

{{-- Leads Table --}}
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="p-4 pb-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="fw-bold mb-0">
                <i class="fas fa-store text-muted me-2"></i>
                {{ $leads->count() }} Businesses Found
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="selectAll()">Select All</button>
                <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="selectNone()">None</button>
                <button class="btn btn-sm btn-outline-warning rounded-3" onclick="selectNoWebsite()">No Website Only</button>
            </div>
        </div>

        @if($leads->isEmpty())
        <div class="p-5 text-center text-muted">
            <i class="fas fa-store-slash fa-3x mb-3 opacity-25"></i>
            <p class="mb-0">No leads in this campaign.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:40px">
                            <input type="checkbox" class="form-check-input" id="checkAll" onchange="toggleAll(this)">
                        </th>
                        <th>Business</th>
                        <th>Phone</th>
                        <th>Website</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                    <tr class="lead-row {{ $lead->has_website ? 'has-website' : 'no-website' }}"
                        data-id="{{ $lead->id }}"
                        data-has-website="{{ $lead->has_website ? '1' : '0' }}">
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input lead-check" value="{{ $lead->id }}"
                                {{ !$lead->has_website ? 'checked' : '' }}>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $lead->business_name }}</div>
                            <div class="text-muted small">{{ Str::limit($lead->address, 55) }}</div>
                        </td>
                        <td>
                            @if($lead->phone)
                                <a href="tel:{{ $lead->phone }}" class="text-decoration-none fw-semibold small">{{ $lead->phone }}</a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($lead->has_website)
                                <a href="{{ $lead->website_url }}" target="_blank" class="text-success small text-truncate d-block" style="max-width:160px">
                                    <i class="fas fa-check-circle me-1"></i>{{ parse_url($lead->website_url, PHP_URL_HOST) ?? 'Yes' }}
                                </a>
                            @else
                                <span class="badge bg-danger-subtle text-danger"><i class="fas fa-times me-1"></i>No Website</span>
                            @endif
                        </td>
                        <td>
                            @if($lead->rating)
                                <span class="fw-bold small">{{ $lead->rating }} ★</span>
                                <span class="text-muted small">({{ $lead->review_count }})</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statuses = [
                                    'found'      => ['secondary', 'Found'],
                                    'selected'   => ['info', 'Selected'],
                                    'contacted'  => ['warning', 'SMS Sent'],
                                    'replied'    => ['primary', 'Replied'],
                                    'registered' => ['success', 'Registered'],
                                    'skipped'    => ['light', 'Skipped'],
                                ];
                                [$color, $label] = $statuses[$lead->status] ?? ['secondary', 'Unknown'];
                            @endphp
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} rounded-pill">{{ $label }}</span>
                            @if($lead->sms_sent_at)
                                <div class="text-muted" style="font-size:10px">{{ $lead->sms_sent_at->format('M j g:ia') }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-3 dropdown-toggle" data-bs-toggle="dropdown">
                                    Update
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach(['replied' => 'Mark Replied', 'registered' => 'Mark Registered', 'skipped' => 'Skip'] as $s => $sl)
                                    <li>
                                        <form action="{{ route('admin.huntbot.lead.status', $lead->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $s }}">
                                            <button type="submit" class="dropdown-item">{{ $sl }}</button>
                                        </form>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

</div>

<script>
function toggleAll(master) {
    document.querySelectorAll('.lead-check').forEach(cb => cb.checked = master.checked);
}
function selectAll() {
    document.querySelectorAll('.lead-check').forEach(cb => cb.checked = true);
}
function selectNone() {
    document.querySelectorAll('.lead-check').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
}
function selectNoWebsite() {
    document.querySelectorAll('.lead-row').forEach(row => {
        const cb = row.querySelector('.lead-check');
        cb.checked = row.dataset.hasWebsite === '0';
    });
}

document.getElementById('filterTarget').addEventListener('change', function() {
    const val = this.value;
    if (val === 'no_website') selectNoWebsite();
    else if (val === 'all') selectAll();
    // 'selected' = leave checkboxes as-is
});

document.getElementById('launchForm').addEventListener('submit', function(e) {
    const container = document.getElementById('leadIdsContainer');
    container.innerHTML = '';
    document.querySelectorAll('.lead-check:checked').forEach(cb => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'lead_ids[]';
        inp.value = cb.value;
        container.appendChild(inp);
    });

    const checked = document.querySelectorAll('.lead-check:checked').length;
    if (checked === 0) {
        e.preventDefault();
        alert('Please select at least one business to contact.');
        return;
    }

    const btn = document.getElementById('launchBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending ' + checked + ' SMS...';
});

// Auto-select no-website on page load
selectNoWebsite();
</script>
@endsection
