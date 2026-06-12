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
        <p class="text-muted small mb-0">Find businesses on Google Maps → auto SMS → turn them into Zonely sellers</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── STATS ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fw-black fs-2 text-primary">{{ number_format($stats['total_found']) }}</div>
            <div class="text-muted small">Total Hunted</div>
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

{{-- ── LEFT: New Hunt + Templates ── --}}
<div class="col-lg-4">

    {{-- New Hunt --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-crosshairs text-danger me-2"></i>New Hunt</h6>
            <form action="{{ route('admin.huntbot.hunt') }}" method="POST" id="huntForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold small">City <span class="text-danger">*</span></label>
                    <input type="text" name="city" required
                        placeholder="e.g. New York, Brooklyn, Queens"
                        class="form-control rounded-3" value="{{ old('city') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">State</label>
                    <input type="text" name="state"
                        placeholder="e.g. NY"
                        class="form-control rounded-3" value="{{ old('state') }}" maxlength="50">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold small">Category / Business Type <span class="text-danger">*</span></label>
                    <select name="category" required class="form-select rounded-3">
                        <option value="">-- Select --</option>
                        <optgroup label="Professional Services">
                            <option value="accountant">Accountant / CPA</option>
                            <option value="lawyer">Lawyer / Attorney</option>
                            <option value="financial advisor">Financial Advisor</option>
                            <option value="insurance agent">Insurance Agent</option>
                            <option value="real estate agent">Real Estate Agent</option>
                        </optgroup>
                        <optgroup label="Healthcare">
                            <option value="doctor">Doctor / Physician</option>
                            <option value="dentist">Dentist</option>
                            <option value="therapist">Therapist / Counselor</option>
                            <option value="chiropractor">Chiropractor</option>
                            <option value="optometrist">Optometrist</option>
                        </optgroup>
                        <optgroup label="Home Services">
                            <option value="plumber">Plumber</option>
                            <option value="electrician">Electrician</option>
                            <option value="HVAC contractor">HVAC / AC Repair</option>
                            <option value="house cleaner">House Cleaner</option>
                            <option value="landscaper">Landscaper</option>
                            <option value="handyman">Handyman</option>
                            <option value="roofer">Roofer</option>
                            <option value="painter">Painter</option>
                        </optgroup>
                        <optgroup label="Beauty & Personal Care">
                            <option value="hair salon">Hair Salon</option>
                            <option value="nail salon">Nail Salon</option>
                            <option value="barber shop">Barber Shop</option>
                            <option value="esthetician">Esthetician</option>
                            <option value="massage therapist">Massage Therapist</option>
                        </optgroup>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold" id="huntBtn">
                    <i class="fas fa-search me-2"></i>Start Hunt
                </button>
            </form>
        </div>
        <div class="card-footer bg-transparent border-top-0 px-4 pb-3">
            <p class="text-muted" style="font-size:11px">
                <i class="fas fa-info-circle me-1"></i>
                HuntBot searches Google Maps, detects businesses without a website, and loads them as targets.
                Requires <code>GOOGLE_MAPS_KEY</code> in your .env.
            </p>
        </div>
    </div>

    {{-- SMS Templates --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-1"><i class="fas fa-message text-teal me-2" style="color:#0d9488"></i>SMS Templates</h6>
            <p class="text-muted small mb-3">Edit the message sent to each business type. Variables: <code>{business_name}</code> <code>{city}</code> <code>{signup_link}</code></p>
            <form action="{{ route('admin.huntbot.templates') }}" method="POST">
                @csrf
                @foreach(['professional' => 'Professional', 'healthcare' => 'Healthcare', 'home' => 'Home Services', 'beauty' => 'Beauty'] as $key => $label)
                <div class="mb-3">
                    <label class="form-label fw-semibold small">{{ $label }}</label>
                    <textarea name="tpl_{{ $key }}" rows="3" class="form-control rounded-3" style="font-size:12px">{{ $templates[$key] }}</textarea>
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
            <div class="p-4 pb-3 border-bottom">
                <h6 class="fw-bold mb-0"><i class="fas fa-list-check text-primary me-2"></i>Hunt Campaigns</h6>
            </div>
            @if($campaigns->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="fas fa-robot fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No campaigns yet. Start a hunt above.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Campaign</th>
                            <th>Found</th>
                            <th>Contacted</th>
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
document.getElementById('huntForm').addEventListener('submit', function() {
    const btn = document.getElementById('huntBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Hunting... this may take 30–60 seconds';
});
</script>
@endsection
