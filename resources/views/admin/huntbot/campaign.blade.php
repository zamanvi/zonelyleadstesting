@extends('layouts.admin2')
@section('title', 'Campaign: ' . $campaign->city)

@section('content')
<div class="mt-5 pt-4">

{{-- Header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.huntbot.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-crosshairs text-danger me-2"></i>
            {{ $campaign->city }}{{ $campaign->state ? ', '.$campaign->state : '' }}
            <span class="text-muted fw-normal">·</span>
            {{ $campaign->category }}
        </h4>
        <p class="text-muted small mb-0">
            Campaign #{{ $campaign->id }} · {{ $campaign->created_at->format('M j, Y') }}
            @php $sc = ['draft'=>'secondary','running'=>'primary','paused'=>'warning','completed'=>'success'][$campaign->status] ?? 'secondary' @endphp
            <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} ms-1">{{ ucfirst($campaign->status) }}</span>
            @if($campaign->source === 'manual')
            <span class="badge bg-light text-secondary border ms-1" style="font-size:9px">Manual</span>
            @else
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size:9px">Auto Hunt</span>
            @endif
        </p>
    </div>
    {{-- Campaign status control --}}
    <form action="{{ route('admin.huntbot.campaign.status', $campaign->id) }}" method="POST" class="d-flex gap-2">
        @csrf @method('PATCH')
        @foreach(['running'=>['primary','Play'],'paused'=>['warning','Pause'],'completed'=>['success','Done']] as $s=>[$col,$lbl])
        @if($campaign->status !== $s)
        <button type="submit" name="status" value="{{ $s }}"
            class="btn btn-sm btn-outline-{{ $col }} rounded-3">
            <i class="fas fa-{{ $s==='running'?'play':($s==='paused'?'pause':'check') }} me-1"></i>{{ $lbl }}
        </button>
        @endif
        @endforeach
    </form>
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

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([['primary',$campaign->total_found,'Leads'],['warning',$campaign->total_contacted,'SMS Sent'],['info',$campaign->total_replied,'Replied'],['success',$campaign->total_registered,'Registered']] as [$col,$val,$lbl])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-3 text-{{ $col }}">{{ $val }}</div>
            <div class="text-muted small">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4">

{{-- ── LEFT: Add Leads + Launch ── --}}
<div class="col-lg-4">

    {{-- Add Lead Tabs --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom px-4 pt-4 pb-0">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panel-single">
                        <i class="fas fa-plus me-1"></i>Add One
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-bulk">
                        <i class="fas fa-list me-1"></i>Bulk Paste
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content">

            {{-- Single lead --}}
            <div class="tab-pane fade show active" id="panel-single">
                <div class="card-body p-4">
                    <form action="{{ route('admin.huntbot.lead.add', $campaign->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="business_name" required
                                placeholder="e.g. Ali's Plumbing Service"
                                class="form-control rounded-3">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" name="phone"
                                placeholder="+1 718 555 0100"
                                class="form-control rounded-3">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Address</label>
                            <input type="text" name="address"
                                placeholder="123 Main St, Brooklyn NY"
                                class="form-control rounded-3">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Website</label>
                            <input type="url" name="website_url"
                                placeholder="https://example.com (leave blank = no website)"
                                class="form-control rounded-3">
                        </div>
                        <button type="submit" class="btn btn-success w-100 rounded-3 fw-bold">
                            <i class="fas fa-plus me-2"></i>Add Lead
                        </button>
                    </form>
                </div>
            </div>

            {{-- Bulk paste --}}
            <div class="tab-pane fade" id="panel-bulk">
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        Paste one business per line. Format:<br>
                        <code>Business Name, Phone, Address</code><br>
                        Phone and Address are optional.
                    </p>
                    <form action="{{ route('admin.huntbot.lead.bulk', $campaign->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="bulk_data" rows="8" required
                                class="form-control rounded-3 font-monospace"
                                style="font-size:12px"
                                placeholder="Ali's Plumbing, +17185550100, Brooklyn NY&#10;Sunrise Hair Salon, +17185550200&#10;Green Lawn Care"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 rounded-3 fw-bold">
                            <i class="fas fa-file-import me-2"></i>Import Leads
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- SMS Launch --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-paper-plane text-primary me-2"></i>Send SMS</h6>
            <form action="{{ route('admin.huntbot.launch', $campaign->id) }}" method="POST" id="launchForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Template</label>
                    <select name="template_key" class="form-select rounded-3">
                        @foreach(['professional'=>'Professional','healthcare'=>'Healthcare','home'=>'Home Services','beauty'=>'Beauty'] as $key=>$label)
                        <option value="{{ $key }}" {{ ($campaign->sms_template_key ?? 'professional') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Send to</label>
                    <select id="filterTarget" class="form-select rounded-3" onchange="applyFilter(this.value)">
                        <option value="no_website">No website only (recommended)</option>
                        <option value="all_with_phone">All with phone number</option>
                        <option value="selected">Selected rows only</option>
                    </select>
                </div>
                <div id="leadIdsContainer"></div>
                <button type="submit" class="btn btn-danger w-100 rounded-3 fw-bold" id="launchBtn">
                    <i class="fas fa-rocket me-2"></i>Launch SMS
                </button>
            </form>
            <p class="text-muted mt-2 mb-0" style="font-size:11px">
                <i class="fas fa-info-circle me-1"></i>Only leads with a phone number receive SMS. ~$0.0075/SMS via Twilio.
            </p>
        </div>
    </div>

</div>

{{-- ── RIGHT: Leads Table ── --}}
<div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="p-4 pb-3 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-store text-muted me-2"></i>
                    {{ $leads->count() }} Leads
                </h6>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="selectAll()">All</button>
                    <button class="btn btn-sm btn-outline-secondary rounded-3" onclick="selectNone()">None</button>
                    <button class="btn btn-sm btn-outline-warning rounded-3" onclick="selectNoWebsite()">No Website</button>
                </div>
            </div>

            @if($leads->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="fas fa-store-slash fa-3x mb-3 opacity-25"></i>
                <p class="mb-1 fw-semibold">No leads yet</p>
                <p class="small mb-0">Add businesses using the form on the left.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:13px">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width:36px">
                                <input type="checkbox" class="form-check-input" id="checkAll" onchange="toggleAll(this)">
                            </th>
                            <th>Business</th>
                            <th>Phone</th>
                            <th>Website</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                        <tr class="lead-row" data-id="{{ $lead->id }}" data-has-website="{{ $lead->has_website ? '1' : '0' }}" data-has-phone="{{ $lead->phone ? '1' : '0' }}">
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input lead-check" value="{{ $lead->id }}"
                                    {{ (!$lead->has_website && $lead->phone) ? 'checked' : '' }}>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $lead->business_name }}</div>
                                @if($lead->address)
                                <div class="text-muted" style="font-size:11px">{{ Str::limit($lead->address, 45) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($lead->phone)
                                <a href="tel:{{ $lead->phone }}" class="text-decoration-none fw-semibold" style="font-size:12px">{{ $lead->phone }}</a>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($lead->has_website && $lead->website_url)
                                <a href="{{ $lead->website_url }}" target="_blank" class="text-success text-truncate d-block" style="max-width:120px;font-size:11px">
                                    <i class="fas fa-check-circle me-1"></i>{{ parse_url($lead->website_url, PHP_URL_HOST) ?? 'Yes' }}
                                </a>
                                @elseif($lead->has_website)
                                <span class="text-success" style="font-size:11px"><i class="fas fa-check-circle me-1"></i>Yes</span>
                                @else
                                <span class="badge bg-danger-subtle text-danger" style="font-size:10px"><i class="fas fa-times me-1"></i>None</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $map = ['found'=>['secondary','Found'],'selected'=>['info','Selected'],'contacted'=>['warning','SMS Sent'],'replied'=>['primary','Replied'],'registered'=>['success','Registered'],'skipped'=>['light','Skipped']];
                                    [$col,$lbl] = $map[$lead->status] ?? ['secondary','?'];
                                @endphp
                                <span class="badge bg-{{ $col }}-subtle text-{{ $col }} rounded-pill">{{ $lbl }}</span>
                                @if($lead->sms_sent_at)
                                <div class="text-muted" style="font-size:10px">{{ $lead->sms_sent_at->format('M j g:ia') }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-3 dropdown-toggle" data-bs-toggle="dropdown" style="font-size:11px">
                                        <i class="fas fa-ellipsis"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" style="font-size:12px">
                                        @foreach(['replied'=>'Mark Replied','registered'=>'Mark Registered','skipped'=>'Skip','found'=>'Reset to Found'] as $s=>$sl)
                                        <li>
                                            <form action="{{ route('admin.huntbot.lead.status', $lead->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $s }}">
                                                <button type="submit" class="dropdown-item">{{ $sl }}</button>
                                            </form>
                                        </li>
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.huntbot.lead.delete', $lead->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this lead?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">Delete</button>
                                            </form>
                                        </li>
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
        row.querySelector('.lead-check').checked = row.dataset.hasWebsite === '0' && row.dataset.hasPhone === '1';
    });
}
function applyFilter(val) {
    if (val === 'no_website') selectNoWebsite();
    else if (val === 'all_with_phone') {
        document.querySelectorAll('.lead-row').forEach(row => {
            row.querySelector('.lead-check').checked = row.dataset.hasPhone === '1';
        });
    }
    // 'selected' → leave as-is
}

document.getElementById('launchForm').addEventListener('submit', function(e) {
    const container = document.getElementById('leadIdsContainer');
    container.innerHTML = '';
    document.querySelectorAll('.lead-check:checked').forEach(cb => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'lead_ids[]'; inp.value = cb.value;
        container.appendChild(inp);
    });
    const count = document.querySelectorAll('.lead-check:checked').length;
    if (count === 0) { e.preventDefault(); alert('Select at least one business.'); return; }
    const btn = document.getElementById('launchBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending ' + count + ' SMS…';
});

// Default: select no-website leads
selectNoWebsite();
</script>
@endsection
