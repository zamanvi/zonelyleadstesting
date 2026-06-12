@extends('layouts.admin2')
@section('title', 'Lead Dashboard')

@section('content')
<div class="mt-5 pt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Lead Dashboard</h4>
            <p class="text-muted small mb-0">All leads sent to sellers. Manage status and payments.</p>
        </div>
    </div>

    {{-- ── ROW 1: Payment Status KPIs (all-time) ── --}}
    <div class="row g-3 mb-3">
        @foreach([
            ['val' => $stats['total'],                              'label' => 'Total Leads',      'icon' => 'fa-bolt',          'color' => '#0ea5e9'],
            ['val' => $stats['paid'],                               'label' => 'Paid Leads',        'icon' => 'fa-circle-check',  'color' => '#10b981'],
            ['val' => $stats['unpaid'],                             'label' => 'Due Leads',         'icon' => 'fa-hourglass-half','color' => '#f59e0b'],
            ['val' => '$'.number_format($stats['revenue'], 2),      'label' => 'Revenue Collected', 'icon' => 'fa-dollar-sign',   'color' => '#10b981'],
            ['val' => '$'.number_format($stats['pending_revenue'],2),'label' => 'Pending Revenue',  'icon' => 'fa-clock',         'color' => '#ef4444'],
            ['val' => $stats['overdue_sellers'],                    'label' => 'Overdue Sellers',   'icon' => 'fa-triangle-exclamation', 'color' => '#dc2626'],
        ] as $s)
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $s['color'] }} !important;">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas {{ $s['icon'] }} small" style="color:{{ $s['color'] }}"></i>
                        <span class="text-muted" style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">{{ $s['label'] }}</span>
                    </div>
                    <h4 class="mb-0 fw-black">{{ $s['val'] }}</h4>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── ROW 2: Time Based KPIs (static counts) ── --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['val' => $stats['today'], 'label' => "Today's Leads",  'icon' => 'fa-sun',        'color' => '#f59e0b'],
            ['val' => $stats['week'],  'label' => 'This Week',       'icon' => 'fa-calendar-week','color' => '#8b5cf6'],
            ['val' => $stats['month'], 'label' => 'This Month',      'icon' => 'fa-calendar',   'color' => '#0ea5e9'],
            ['val' => $stats['year'],  'label' => 'This Year',       'icon' => 'fa-calendar-days','color' => '#10b981'],
        ] as $s)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $s['color'] }} !important;">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas {{ $s['icon'] }} small" style="color:{{ $s['color'] }}"></i>
                        <span class="text-muted" style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">{{ $s['label'] }}</span>
                    </div>
                    <h4 class="mb-0 fw-black">{{ $s['val'] }}</h4>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Revenue by Period ── --}}
    <div class="section-card mb-4">
        <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Revenue by Period</h6>
            <div class="btn-group btn-group-sm">
                @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $val=>$label)
                <a href="{{ request()->fullUrlWithQuery(['period'=>$val,'page'=>1]) }}"
                   class="btn {{ $period===$val ? 'btn-light' : 'btn-outline-light' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row g-0 divide-x">
                @foreach([
                    ['val'=>$periodStats['count'],                              'label'=>'Leads in Period',  'icon'=>'fa-inbox',       'color'=>'#0ea5e9'],
                    ['val'=>'$'.number_format($periodStats['revenue'],2),       'label'=>'Revenue Collected','icon'=>'fa-check-circle','color'=>'#10b981'],
                    ['val'=>'$'.number_format($periodStats['pending_revenue'],2),'label'=>'Pending Revenue', 'icon'=>'fa-clock',       'color'=>'#f59e0b'],
                ] as $p)
                <div class="col-12 col-md-4 p-4 text-center border-end">
                    <i class="fas {{ $p['icon'] }} fa-2x mb-2" style="color:{{ $p['color'] }}"></i>
                    <h3 class="fw-black mb-0">{{ $p['val'] }}</h3>
                    <p class="text-muted small mb-0">{{ $p['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Leads Table ── --}}
    <div class="section-card">
        <div class="card-header bg-dark text-white p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Leads</h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                {{-- Status filter --}}
                <div class="btn-group btn-group-sm">
                    @foreach([''=>'All','new'=>'New','pending'=>'Pending','won'=>'Won','lost'=>'Lost'] as $val=>$label)
                    <a href="{{ request()->fullUrlWithQuery(['status'=>$val?:null,'page'=>1]) }}"
                       class="btn {{ request('status','') === $val ? 'btn-light' : 'btn-outline-light' }}">{{ $label }}</a>
                    @endforeach
                </div>
                {{-- Source filter --}}
                <div class="btn-group btn-group-sm">
                    @foreach([''=>'All','form'=>'📋 Form','phone'=>'📞 Phone','whatsapp'=>'💬 WhatsApp','email'=>'📧 Email','booking'=>'📅 Booking'] as $val=>$label)
                    <a href="{{ request()->fullUrlWithQuery(['source'=>$val?:null,'page'=>1]) }}"
                       class="btn {{ request('source','') === $val ? 'btn-info text-white' : 'btn-outline-light' }}">{{ $label }}</a>
                    @endforeach
                </div>
                {{-- Search --}}
                <form method="GET" action="{{ route('admin.leads') }}" class="d-flex gap-1">
                    @foreach(request()->except(['search','page']) as $k=>$v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control form-control-sm" placeholder="Search name, email, phone…" style="min-width:180px">
                    <button class="btn btn-sm btn-outline-light"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            @if($leads->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lead</th>
                            <th>Channel</th>
                            <th>Contact</th>
                            <th>Seller</th>
                            <th>Service / Location</th>
                            <th class="text-center">Fee</th>
                            <th class="text-center">Paid</th>
                            <th class="text-center">Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                        @php
                            $srcBadge = match($lead->source ?? 'form') {
                                'whatsapp' => ['💬 WhatsApp', 'badge bg-success'],
                                'email'    => ['📧 Email',    'badge bg-primary'],
                                'phone'    => ['📞 Phone',    'badge bg-warning text-dark'],
                                'booking'  => ['📅 Booking',  'badge bg-info text-dark'],
                                default    => ['📋 Form',     'badge bg-secondary'],
                            };
                            $statusBadge = match($lead->status) {
                                'won'     => ['Won',     'badge bg-success'],
                                'lost'    => ['Lost',    'badge bg-danger'],
                                'pending' => ['Pending', 'badge bg-warning text-dark'],
                                default   => ['New',     'badge bg-primary'],
                            };
                            $sellerOverdue = in_array($lead->seller_id, $overdueSellers ?? []);
                        @endphp
                        <tr>
                            {{-- Lead ID --}}
                            <td class="fw-bold small text-nowrap">
                                <a href="{{ route('admin.leads.detail', $lead->id) }}" target="_blank"
                                   class="text-decoration-none text-primary fw-bold">
                                    #ZL-{{ $lead->id }}
                                </a>
                            </td>

                            {{-- Channel --}}
                            <td><span class="{{ $srcBadge[1] }}">{{ $srcBadge[0] }}</span></td>

                            {{-- Contact --}}
                            <td>
                                <div class="fw-bold small">{{ $lead->name }}</div>
                                @if($lead->phone)
                                <div style="font-size:11px" class="text-muted">
                                    <i class="fas fa-phone fa-xs me-1"></i>
                                    <a href="tel:{{ $lead->phone }}" class="text-decoration-none text-muted">{{ $lead->phone }}</a>
                                </div>
                                @endif
                                @if($lead->email)
                                <div style="font-size:11px" class="text-muted">
                                    <i class="fas fa-envelope fa-xs me-1"></i>
                                    <a href="mailto:{{ $lead->email }}" class="text-decoration-none text-muted">{{ $lead->email }}</a>
                                </div>
                                @endif
                            </td>

                            {{-- Seller --}}
                            <td>
                                @if($lead->seller)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                         style="width:30px;height:30px;font-size:11px;flex-shrink:0">
                                        {{ strtoupper(substr($lead->seller->name,0,1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.profiles.edit',$lead->seller_id) }}" target="_blank"
                                           class="small fw-semibold text-decoration-none text-dark d-block">
                                            {{ $lead->seller->name }}
                                        </a>
                                        @if($sellerOverdue)
                                        <span class="badge bg-danger" style="font-size:9px">🔴 Overdue</span>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Service / Location --}}
                            <td>
                                <div class="small">{{ $lead->service ?? '—' }}</div>
                                <div class="text-muted" style="font-size:11px">{{ $lead->location ?? $lead->zip_code ?? '' }}</div>
                            </td>

                            {{-- Fee --}}
                            <td class="text-center fw-bold small">${{ number_format($lead->fee,2) }}</td>

                            {{-- Paid toggle --}}
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.leads.pay',$lead->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $lead->paid_at ? 'btn-success' : 'btn-outline-secondary' }}"
                                            title="{{ $lead->paid_at ? 'Paid '.$lead->paid_at->format('M d').' — click to unpay' : 'Mark as paid' }}">
                                        <i class="fas {{ $lead->paid_at ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                    </button>
                                </form>
                            </td>

                            {{-- Status — read only badge --}}
                            <td class="text-center">
                                <span class="{{ $statusBadge[1] }}">{{ $statusBadge[0] }}</span>
                            </td>

                            {{-- Date --}}
                            <td class="small text-muted text-nowrap">{{ $lead->created_at?->format('M d, Y') }}</td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        {{-- View lead detail --}}
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.leads.detail', $lead->id) }}" target="_blank">
                                                <i class="fas fa-eye me-2 text-primary"></i> View Lead Details
                                            </a>
                                        </li>
                                        {{-- View seller profile --}}
                                        @if($lead->seller)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.profiles.edit',$lead->seller_id) }}" target="_blank">
                                                <i class="fas fa-user me-2 text-info"></i> View Seller Profile
                                            </a>
                                        </li>
                                        @endif
                                        {{-- View message --}}
                                        @if($lead->message)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                    data-bs-toggle="modal" data-bs-target="#msgModal{{ $lead->id }}">
                                                <i class="fas fa-comment me-2 text-secondary"></i> View Message
                                            </button>
                                        </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.leads.destroy',$lead->id) }}"
                                                  onsubmit="return confirm('Delete lead from {{ addslashes($lead->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-2"></i> Delete
                                                </button>
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

            {{-- Message modals (must be outside <table>) --}}
            @foreach($leads as $lead)
            @if($lead->message)
            <div class="modal fade" id="msgModal{{ $lead->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title">Message from {{ $lead->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-1"><strong>Lead:</strong> #ZL-{{ $lead->id }}</p>
                            <p class="mb-1"><strong>Service:</strong> {{ $lead->service ?? '—' }}</p>
                            <p class="mb-1"><strong>Location:</strong> {{ $lead->location ?? $lead->zip_code ?? '—' }}</p>
                            <hr>
                            <p class="mb-0">{{ $lead->message }}</p>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                            @if($lead->phone)
                            <a href="tel:{{ $lead->phone }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-phone me-1"></i> Call
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach

            @if($leads->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    Showing {{ $leads->firstItem() }}–{{ $leads->lastItem() }} of {{ $leads->total() }} leads
                </span>
                {{ $leads->links() }}
            </div>
            @endif

            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-bolt fa-3x mb-3 opacity-25"></i>
                <p class="mb-0 fw-bold">No leads found.</p>
                <small>Try adjusting your filters.</small>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
