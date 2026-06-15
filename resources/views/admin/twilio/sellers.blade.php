@extends('layouts.admin2')
@section('title', 'SMS — Seller Notifications')

@section('content')
<div class="mt-5 pt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-users text-primary me-2"></i>SMS — Seller Notifications</h4>
            <p class="text-muted small mb-0">Enable SMS lead alerts per seller</p>
        </div>
        <a href="{{ route('admin.twilio.settings') }}" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-key me-1"></i> SMS Settings
        </a>
    </div>

    @if(!$configured)
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-triangle-exclamation fa-lg"></i>
        <div>
            <strong>SMS provider not configured.</strong>
            <a href="{{ route('admin.twilio.settings') }}" class="alert-link ms-1">Add credentials first →</a>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Stats strip --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="section-card text-center p-3">
                <p class="fs-3 fw-black text-primary mb-0">{{ $sellers->count() }}</p>
                <p class="small text-muted mb-0">Total Sellers</p>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="section-card text-center p-3">
                <p class="fs-3 fw-black text-success mb-0">{{ $sellers->where('twilio_enabled', true)->count() }}</p>
                <p class="small text-muted mb-0">SMS Active</p>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="section-card text-center p-3">
                <p class="fs-3 fw-black text-warning mb-0">{{ $sellers->where('twilio_enabled', false)->count() }}</p>
                <p class="small text-muted mb-0">Not Enabled</p>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Seller</th>
                            <th>Phone (SMS target)</th>
                            <th>Status</th>
                            <th>SMS</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($sellers as $seller)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                @if($seller->profile_photo)
                                <img src="{{ get_file($seller->profile_photo, 'user') }}"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($seller->name) }}&size=36&background=0ea5e9&color=fff'"
                                     width="36" height="36" class="rounded-circle object-fit-cover">
                                @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                     style="width:36px;height:36px;font-size:14px;flex-shrink:0">
                                    {{ strtoupper(substr($seller->name,0,1)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="fw-semibold small">{{ $seller->name }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $seller->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($seller->phone)
                                <span class="font-monospace small">{{ $seller->phone }}</span>
                            @else
                                <span class="text-danger small"><i class="fas fa-triangle-exclamation me-1"></i>No phone set</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $seller->status ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $seller->status ? 'Verified' : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            @if($seller->twilio_enabled)
                                <span class="badge bg-success"><i class="fas fa-circle-check me-1"></i>Active</span>
                            @else
                                <span class="badge bg-secondary">Off</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                {{-- Toggle --}}
                                <form method="POST" action="{{ route('admin.twilio.toggle', $seller->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $seller->twilio_enabled ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            {{ !$configured ? 'disabled' : '' }}
                                            title="{{ $seller->twilio_enabled ? 'Disable SMS' : 'Enable SMS' }}">
                                        <i class="fas {{ $seller->twilio_enabled ? 'fa-toggle-on text-success' : 'fa-toggle-off' }} me-1"></i>
                                        {{ $seller->twilio_enabled ? 'Enabled' : 'Enable' }}
                                    </button>
                                </form>
                                {{-- Test SMS --}}
                                @if($seller->twilio_enabled && $seller->phone && $configured)
                                <form method="POST" action="{{ route('admin.twilio.test', $seller->id) }}"
                                      onsubmit="return confirm('Send test SMS to ' + @json($seller->phone) + '?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Send test SMS">
                                        <i class="fas fa-paper-plane me-1"></i>Test SMS
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No sellers found.</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
