@extends('layouts.admin2')
@section('title', 'HuntBot — AI Seller Acquisition')

@section('content')
<div class="mt-5 pt-4">

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-robot text-primary me-2"></i>HuntBot
            <span class="badge bg-primary-subtle text-primary ms-2" style="font-size:11px;vertical-align:middle">AI Acquisition</span>
        </h4>
        <p class="text-muted small mb-0">Find businesses → send SMS → turn them into Zonely sellers</p>
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

{{-- ── STATS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-2 text-primary">{{ number_format($stats['total_found']) }}</div>
            <div class="text-muted small">Total Leads</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-2 text-warning">{{ number_format($stats['total_contacted']) }}</div>
            <div class="text-muted small">SMS Sent</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-2 text-info">{{ number_format($stats['total_replied']) }}</div>
            <div class="text-muted small">Replied</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-2 text-success">{{ number_format($stats['total_registered']) }}</div>
            <div class="text-muted small">Registered</div>
        </div>
    </div>
</div>

<div class="row g-4">

{{-- ── LEFT COLUMN ── --}}
<div class="col-lg-4">

    {{-- Tab switcher: Auto Hunt vs Manual --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom px-4 pt-4 pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="huntTabs">
                <li class="nav-item">
                    <button class="nav-link {{ $googleMapsActive ? 'active' : 'disabled text-muted' }}" id="tab-auto" data-bs-toggle="tab" data-bs-target="#panel-auto">
                        <i class="fas fa-robot me-1"></i> Auto Hunt
                        @if(!$googleMapsActive)
                        <span class="badge bg-secondary ms-1" style="font-size:9px">API needed</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ !$googleMapsActive ? 'active' : '' }}" id="tab-manual" data-bs-toggle="tab" data-bs-target="#panel-manual">
                        <i class="fas fa-pen me-1"></i> Manual
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content">

            {{-- AUTO HUNT (Google Maps) --}}
            <div class="tab-pane fade {{ $googleMapsActive ? 'show active' : '' }}" id="panel-auto">
                <div class="card-body p-4">
                    @if(!$googleMapsActive)
                    <div class="alert alert-warning rounded-3 mb-3" style="font-size:12px">
                        <i class="fas fa-key me-2"></i>
                        <strong>Google Maps API not configured.</strong><br>
                        Add <code>GOOGLE_MAPS_KEY</code> to Railway env vars to enable auto-search.
                        <a href="https://console.cloud.google.com" target="_blank" class="d-block mt-1 fw-semibold">Get API key →</a>
                    </div>
                    @endif
                    <form action="{{ route('admin.huntbot.hunt') }}" method="POST" id="huntForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" required {{ !$googleMapsActive ? 'disabled' : '' }}
                                placeholder="e.g. Brooklyn, Queens, Bronx"
                                class="form-control rounded-3" value="{{ old('city') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">State</label>
                            <input type="text" name="state" {{ !$googleMapsActive ? 'disabled' : '' }}
                                placeholder="e.g. NY" maxlength="50"
                                class="form-control rounded-3" value="{{ old('state') }}">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                            @include('admin.huntbot._category_select', ['disabled' => !$googleMapsActive])
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold" id="huntBtn" {{ !$googleMapsActive ? 'disabled' : '' }}>
                            <i class="fas fa-search me-2"></i>Start Auto Hunt
                        </button>
                    </form>
                    <p class="text-muted mt-3 mb-0" style="font-size:11px">
                        <i class="fas fa-info-circle me-1"></i>
                        Searches Google Maps, finds businesses with no website, loads them as targets automatically.
                    </p>
                </div>
            </div>

            {{-- MANUAL CAMPAIGN --}}
            <div class="tab-pane fade {{ !$googleMapsActive ? 'show active' : '' }}" id="panel-manual">
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">Create a campaign and add businesses manually — from Facebook groups, Google searches, or any list you have.</p>
                    <form action="{{ route('admin.huntbot.manual') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Campaign Name / City <span class="text-danger">*</span></label>
                            <input type="text" name="city" required
                                placeholder="e.g. Brooklyn, NY"
                                class="form-control rounded-3" value="{{ old('city') }}">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                            @include('admin.huntbot._category_select', ['disabled' => false])
                        </div>
                        <button type="submit" class="btn btn-success w-100 rounded-3 fw-bold">
                            <i class="fas fa-folder-plus me-2"></i>Create Campaign
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- SMS Templates --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-1"><i class="fas fa-message me-2" style="color:#0d9488"></i>SMS Templates</h6>
            <p class="text-muted small mb-3">Variables: <code>{business_name}</code> <code>{city}</code> <code>{signup_link}</code></p>
            <form action="{{ route('admin.huntbot.templates') }}" method="POST">
                @csrf
                @foreach(['professional' => 'Professional', 'healthcare' => 'Healthcare', 'home' => 'Home Services', 'beauty' => 'Beauty'] as $key => $label)
                <div class="mb-3">
                    <label class="form-label fw-semibold small">{{ $label }}</label>
                    <textarea name="tpl_{{ $key }}" rows="3" class="form-control rounded-3" style="font-size:12px">{{ $templates[$key] }}</textarea>
                    <div class="text-muted mt-1" style="font-size:10px" id="charCount_{{ $key }}"></div>
                </div>
                @endforeach
                <button type="submit" class="btn btn-outline-secondary rounded-3 w-100 fw-semibold">
                    <i class="fas fa-floppy-disk me-2"></i>Save Templates
                </button>
            </form>
        </div>
    </div>

</div>

{{-- ── RIGHT: Campaign List ── --}}
<div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="p-4 pb-3 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0"><i class="fas fa-list-check text-primary me-2"></i>Campaigns</h6>
                <span class="text-muted small">{{ $campaigns->total() }} total</span>
            </div>
            @if($campaigns->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="fas fa-robot fa-3x mb-3 opacity-25"></i>
                <p class="mb-1 fw-semibold">No campaigns yet</p>
                <p class="small mb-0">Create a manual campaign on the left to get started.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Campaign</th>
                            <th>Leads</th>
                            <th>SMS Sent</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $c)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $c->city }}{{ $c->state ? ', '.$c->state : '' }}</div>
                                <div class="text-muted small">{{ $c->category }}</div>
                                @if($c->source === 'manual')
                                <span class="badge bg-light text-secondary border" style="font-size:9px">Manual</span>
                                @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:9px">Auto</span>
                                @endif
                            </td>
                            <td><span class="fw-bold text-primary">{{ $c->total_found }}</span></td>
                            <td><span class="fw-bold text-warning">{{ $c->total_contacted }}</span></td>
                            <td><span class="fw-bold text-success">{{ $c->total_registered }}</span></td>
                            <td>
                                @php $sc = ['draft'=>'secondary','running'=>'primary','paused'=>'warning','completed'=>'success'][$c->status] ?? 'secondary' @endphp
                                <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} rounded-pill">{{ ucfirst($c->status) }}</span>
                            </td>
                            <td class="text-muted small">{{ $c->created_at->format('M j') }}</td>
                            <td>
                                <a href="{{ route('admin.huntbot.campaign', $c->id) }}"
                                   class="btn btn-sm btn-outline-primary rounded-3">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $campaigns->links() }}</div>
            @endif
        </div>
    </div>
</div>

</div>
</div>

<script>
// SMS character counter
@foreach(['professional','healthcare','home','beauty'] as $key)
(function() {
    const ta = document.querySelector('[name="tpl_{{ $key }}"]');
    const counter = document.getElementById('charCount_{{ $key }}');
    function update() {
        const len = ta.value.length;
        const msgs = Math.ceil(len / 160) || 1;
        counter.textContent = len + ' chars · ' + msgs + ' SMS credit' + (msgs > 1 ? 's' : '') + ' per send';
        counter.style.color = len > 320 ? '#dc2626' : len > 160 ? '#d97706' : '#6b7280';
    }
    ta.addEventListener('input', update);
    update();
})();
@endforeach

// Auto hunt spinner
const huntForm = document.getElementById('huntForm');
if (huntForm) {
    huntForm.addEventListener('submit', function() {
        const btn = document.getElementById('huntBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Hunting… 30–60 sec';
    });
}
</script>
@endsection
