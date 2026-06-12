@extends('layouts.admin2')
@section('title', 'Dashboard')

@section('content')
<div class="mt-5 pt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Admin Dashboard</h4>
            <p class="text-muted small mb-0">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.leads') }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-bolt me-1"></i> Leads
            </a>
            <a href="{{ route('admin.profiles.index', ['status'=>'unverified']) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-user-check me-1"></i> Verify
                @if($unverified > 0)<span class="badge bg-dark ms-1">{{ $unverified }}</span>@endif
            </a>
        </div>
    </div>

    {{-- KPI Row 1: Users --}}
    <div class="kpi-grid mb-0" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));margin-bottom:1rem">
        <a href="{{ route('admin.profiles.index',['type'=>'seller']) }}" class="kpi-card text-decoration-none" style="border-left-color:#0ea5e9">
            <h3 class="text-dark">{{ number_format($sellers) }}</h3>
            <p><i class="fas fa-user-tie text-primary"></i> Sellers</p>
        </a>
        <a href="{{ route('admin.profiles.index',['type'=>'user']) }}" class="kpi-card text-decoration-none" style="border-left-color:#10b981">
            <h3 class="text-dark">{{ number_format($buyers) }}</h3>
            <p><i class="fas fa-users text-success"></i> Buyers</p>
        </a>
        <a href="{{ route('admin.profiles.index',['status'=>'unverified']) }}" class="kpi-card text-decoration-none" style="border-left-color:#f59e0b">
            <h3 class="text-dark">{{ number_format($unverified) }}</h3>
            <p><i class="fas fa-user-clock text-warning"></i> Pending</p>
        </a>
        <a href="{{ route('admin.hierarchy') }}" class="kpi-card text-decoration-none" style="border-left-color:#8b5cf6">
            <h3 class="text-dark">{{ number_format($staffCount) }}</h3>
            <p><i class="fas fa-sitemap" style="color:#8b5cf6"></i> Staff</p>
        </a>
    </div>

    {{-- KPI Row 2: Business --}}
    <div class="kpi-grid mb-4" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr))">
        <a href="{{ route('admin.leads') }}" class="kpi-card text-decoration-none" style="border-left-color:#ef4444">
            <h3 class="text-dark">{{ number_format($totalLeads) }}</h3>
            <p><i class="fas fa-bolt text-danger"></i> Total Leads</p>
        </a>
        <a href="{{ route('admin.leads',['status'=>'new']) }}" class="kpi-card text-decoration-none" style="border-left-color:#06b6d4">
            <h3 class="text-dark">{{ number_format($newLeads) }}</h3>
            <p><i class="fas fa-star text-info"></i> New Leads</p>
        </a>
        <div class="kpi-card" style="border-left-color:#10b981">
            <h3>${{ number_format($revenue,0) }}</h3>
            <p><i class="fas fa-dollar-sign text-success"></i> Revenue</p>
        </div>
        <a href="{{ route('admin.affiliate') }}" class="kpi-card text-decoration-none" style="border-left-color:#f59e0b">
            <h3 class="text-dark">${{ number_format($pendingComm,0) }}</h3>
            <p><i class="fas fa-share-nodes text-warning"></i> Comm. Pending</p>
        </a>
        <a href="{{ route('admin.blogs.index') }}" class="kpi-card text-decoration-none" style="border-left-color:#6366f1">
            <h3 class="text-dark">{{ number_format($blogCount) }}</h3>
            <p><i class="fas fa-pen-nib" style="color:#6366f1"></i> Blog Posts</p>
        </a>
        <a href="{{ route('admin.categories.index') }}" class="kpi-card text-decoration-none" style="border-left-color:#14b8a6">
            <h3 class="text-dark">{{ number_format($catCount) }}</h3>
            <p><i class="fas fa-tags" style="color:#14b8a6"></i> Categories</p>
        </a>
        <a href="{{ route('admin.locations',['tab'=>'cities']) }}" class="kpi-card text-decoration-none" style="border-left-color:#94a3b8">
            <h3 class="text-dark">{{ number_format($cityCount) }}</h3>
            <p><i class="fas fa-city text-secondary"></i> Cities</p>
        </a>
    </div>

    {{-- Charts Row --}}
    <div class="row g-4 mb-4">
        {{-- Lead Trend Line --}}
        <div class="col-lg-8">
            <div class="section-card h-100">
                <div class="card-header bg-dark text-white p-4">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Activity (Last 6 Months)</h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="activityChart" height="100"></canvas>
                </div>
            </div>
        </div>
        {{-- Lead Status Doughnut --}}
        <div class="col-lg-4">
            <div class="section-card h-100">
                <div class="card-header bg-primary text-white p-4">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Lead Status</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                    <canvas id="leadStatusChart" style="max-height:220px"></canvas>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        @foreach(['New'=>'#06b6d4','Pending'=>'#f59e0b','Won'=>'#10b981','Lost'=>'#ef4444'] as $lbl=>$col)
                        <span class="badge" style="background:{{ $col }};font-size:11px">
                            {{ $lbl }}: {{ $leadStatusData[strtolower($lbl)] }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Tables Row --}}
    <div class="row g-4 mb-4">

        {{-- Recent Leads --}}
        <div class="col-lg-6">
            <div class="section-card h-100">
                <div class="card-header bg-danger text-white p-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Recent Leads</h5>
                    <a href="{{ route('admin.leads') }}" class="btn btn-sm btn-outline-light">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentLeads->count())
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Contact</th><th>Seller</th><th class="text-center">Fee</th><th class="text-center">Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentLeads as $lead)
                            @php $sc = match($lead->status){'won'=>'bg-success','lost'=>'bg-danger','pending'=>'bg-warning text-dark',default=>'bg-primary'}; @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold small">{{ $lead->name }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $lead->created_at?->format('M d') }}</div>
                                </td>
                                <td class="small text-muted">{{ $lead->seller?->name ?? '—' }}</td>
                                <td class="text-center fw-bold small">${{ number_format($lead->fee,0) }}</td>
                                <td class="text-center"><span class="badge {{ $sc }}">{{ ucfirst($lead->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-4 text-muted small">No leads yet.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pending Verification --}}
        <div class="col-lg-6">
            <div class="section-card h-100">
                <div class="card-header bg-warning text-dark p-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Pending Verification</h5>
                    <a href="{{ route('admin.profiles.index',['status'=>'unverified']) }}" class="btn btn-sm btn-outline-dark">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($pendingVerify->count())
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Seller</th><th>City</th><th>Joined</th><th class="text-center">Action</th></tr>
                        </thead>
                        <tbody>
                            @foreach($pendingVerify as $s)
                            <tr>
                                <td>
                                    <div class="fw-semibold small">{{ $s->name }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $s->designation ?? $s->email }}</div>
                                </td>
                                <td class="small text-muted">{{ $s->city ?? '—' }}</td>
                                <td class="small text-muted">{{ $s->created_at?->format('M d') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.profiles.edit',$s->id) }}"
                                       class="btn btn-xs btn-sm btn-outline-success" style="font-size:11px">
                                        Verify
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-check-circle text-success me-1"></i> All sellers verified!
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Bottom Row --}}
    <div class="row g-4 mb-4">

        {{-- Recent Commissions --}}
        <div class="col-lg-6">
            <div class="section-card h-100">
                <div class="card-header bg-success text-white p-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-share-nodes me-2"></i>Recent Affiliate</h5>
                    <a href="{{ route('admin.affiliate') }}" class="btn btn-sm btn-outline-light">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentCommissions->count())
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Referrer</th><th class="text-center">Amount</th><th class="text-center">Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentCommissions as $c)
                            <tr>
                                <td class="fw-semibold small">{{ $c->referrer?->name ?? '—' }}</td>
                                <td class="text-center fw-bold small">${{ number_format($c->amount,2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $c->status==='paid'?'bg-success':'bg-warning text-dark' }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $c->created_at?->format('M d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-4 text-muted small">No commissions yet.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Staff Summary --}}
        <div class="col-lg-6">
            <div class="section-card h-100">
                <div class="card-header bg-dark text-white p-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Hierarchy Summary</h5>
                    <a href="{{ route('admin.hierarchy') }}" class="btn btn-sm btn-outline-light">Manage</a>
                </div>
                <div class="card-body p-4">
                    @php
                        $hier = [
                            'area_manager'     => ['label'=>'Area Managers',     'color'=>'#0ea5e9','icon'=>'fa-map-pin'],
                            'city_manager'     => ['label'=>'City Managers',     'color'=>'#8b5cf6','icon'=>'fa-city'],
                            'district_manager' => ['label'=>'District Managers', 'color'=>'#f59e0b','icon'=>'fa-map'],
                            'country_manager'  => ['label'=>'Country Managers',  'color'=>'#ef4444','icon'=>'fa-flag-usa'],
                        ];
                    @endphp
                    <div class="row g-3">
                        @foreach($hier as $role => $info)
                        @php $cnt = $staffRoleCounts[$role] ?? 0; @endphp
                        <div class="col-6">
                            <a href="{{ route('admin.hierarchy',['role'=>$role]) }}"
                               class="d-flex align-items-center gap-3 p-3 rounded border text-decoration-none"
                               style="border-color:{{ $info['color'] }}20!important;background:{{ $info['color'] }}08">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:40px;height:40px;background:{{ $info['color'] }}20;flex-shrink:0">
                                    <i class="fas {{ $info['icon'] }}" style="color:{{ $info['color'] }}"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color:{{ $info['color'] }};font-size:20px;line-height:1">{{ $cnt }}</div>
                                    <div class="text-muted small" style="font-size:11px">{{ $info['label'] }}</div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3 p-3 bg-light rounded text-center">
                        <div class="text-muted small mb-1">CEO / Founder</div>
                        <span class="badge bg-dark"><i class="fas fa-crown me-1"></i> Zonely HQ</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Quick Actions --}}
    <div class="section-card mb-4">
        <div class="card-header bg-primary text-white p-4">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @php
                $actions = [
                    ['route'=>route('admin.profiles.index',['status'=>'unverified']), 'icon'=>'fa-user-check', 'color'=>'text-warning', 'label'=>'Verify Sellers', 'badge'=>$unverified.' pending', 'bc'=>'bg-warning text-dark'],
                    ['route'=>route('admin.leads'), 'icon'=>'fa-bolt', 'color'=>'text-danger', 'label'=>'Lead Dashboard', 'badge'=>$newLeads.' new', 'bc'=>'bg-danger'],
                    ['route'=>route('admin.affiliate'), 'icon'=>'fa-share-nodes', 'color'=>'text-success', 'label'=>'Affiliate', 'badge'=>'$'.number_format($pendingComm,0).' pending', 'bc'=>'bg-warning text-dark'],
                    ['route'=>route('admin.hierarchy'), 'icon'=>'fa-sitemap', 'color'=>'text-primary', 'label'=>'Hierarchy', 'badge'=>$staffCount.' staff', 'bc'=>'bg-primary'],
                    ['route'=>route('admin.blogs.create'), 'icon'=>'fa-pen', 'color'=>'text-primary', 'label'=>'New Blog Post', 'badge'=>'Write', 'bc'=>'bg-primary'],
                    ['route'=>route('admin.categories.index'), 'icon'=>'fa-tags', 'color'=>'text-info', 'label'=>'Categories', 'badge'=>$catCount.' cats', 'bc'=>'bg-info'],
                    ['route'=>route('admin.locations'), 'icon'=>'fa-map-marked-alt', 'color'=>'text-secondary', 'label'=>'Locations', 'badge'=>$cityCount.' cities', 'bc'=>'bg-secondary'],
                    ['route'=>route('admin.clear.cache'), 'icon'=>'fa-broom', 'color'=>'text-secondary', 'label'=>'Clear Cache', 'badge'=>'Run', 'bc'=>'bg-secondary', 'confirm'=>true],
                ];
                @endphp
                @foreach($actions as $a)
                <div class="col-6 col-md-3">
                    <a href="{{ $a['route'] }}"
                       class="d-block p-3 border rounded text-center text-decoration-none hover-shadow"
                       {!! isset($a['confirm']) ? "onclick=\"return confirm('Clear all cache?')\"" : '' !!}>
                        <i class="fas {{ $a['icon'] }} fa-2x {{ $a['color'] }} mb-2"></i>
                        <div class="fw-bold text-dark small mb-1">{{ $a['label'] }}</div>
                        <span class="badge {{ $a['bc'] }}">{{ $a['badge'] }}</span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Sellers --}}
    <div class="section-card">
        <div class="card-header bg-dark text-white p-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Recently Joined Sellers</h5>
            <a href="{{ route('admin.profiles.index',['type'=>'seller']) }}" class="btn btn-sm btn-outline-light">View All</a>
        </div>
        <div class="card-body p-0">
            @if($recentSellers->count())
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Category</th>
                            <th class="text-center">Status</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSellers as $seller)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($seller->profile_photo)
                                    <img src="{{ str_starts_with($seller->profile_photo, 'http') ? $seller->profile_photo : asset($seller->profile_photo) }}"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($seller->name) }}&size=32&background=0ea5e9&color=fff'"
                                         class="rounded-circle" width="32" height="32" style="object-fit:cover">
                                    @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                         style="width:32px;height:32px;font-size:12px;flex-shrink:0">
                                        {{ strtoupper(substr($seller->name,0,1)) }}
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold small">{{ $seller->name }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ $seller->designation ?? $seller->title ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted">{{ $seller->email }}</td>
                            <td class="small">{{ $seller->city ?? '—' }}</td>
                            <td class="small text-muted">{{ $seller->category?->title ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $seller->status ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $seller->status ? 'Verified' : 'Pending' }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $seller->created_at?->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.profiles.edit',$seller->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-user-slash fa-2x mb-3"></i>
                <p>No sellers yet.</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Activity Chart (leads + users line)
new Chart(document.getElementById('activityChart'), {
    type: 'line',
    data: {
        labels: @json($leadMonths),
        datasets: [
            {
                label: 'Leads',
                data: @json($leadCounts),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239,68,68,.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
            },
            {
                label: 'New Users',
                data: @json($userCounts),
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14,165,233,.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

// Lead Status Doughnut
new Chart(document.getElementById('leadStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['New', 'Pending', 'Won', 'Lost'],
        datasets: [{
            data: [
                {{ $leadStatusData['new'] }},
                {{ $leadStatusData['pending'] }},
                {{ $leadStatusData['won'] }},
                {{ $leadStatusData['lost'] }}
            ],
            backgroundColor: ['#06b6d4','#f59e0b','#10b981','#ef4444'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        cutout: '65%',
    }
});
</script>
@endsection
