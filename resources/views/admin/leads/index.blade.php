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

    {{-- Stats --}}
    <div class="kpi-grid mb-4">
        @foreach([
            ['val'=>$stats['total'],           'label'=>'Total Leads',        'icon'=>'fa-bolt',          'color'=>'#0ea5e9'],
            ['val'=>$stats['new'],             'label'=>'New',                'icon'=>'fa-star',          'color'=>'#8b5cf6'],
            ['val'=>$stats['won'],             'label'=>'Won',                'icon'=>'fa-trophy',        'color'=>'#10b981'],
            ['val'=>$stats['lost'],            'label'=>'Lost',               'icon'=>'fa-times-circle',  'color'=>'#ef4444'],
            ['val'=>'$'.number_format($stats['revenue'],2),  'label'=>'Revenue Collected','icon'=>'fa-dollar-sign','color'=>'#10b981'],
            ['val'=>'$'.number_format($stats['pending_revenue'],2),'label'=>'Pending Payment','icon'=>'fa-clock','color'=>'#f59e0b'],
            ['val'=>$stats['paid'],            'label'=>'Paid Leads',         'icon'=>'fa-check-circle',  'color'=>'#10b981'],
            ['val'=>$stats['unpaid'],          'label'=>'Unpaid Leads',       'icon'=>'fa-hourglass-half','color'=>'#94a3b8'],
        ] as $s)
        <div class="kpi-card" style="border-left-color:{{ $s['color'] }}">
            <h3>{{ $s['val'] }}</h3>
            <p><i class="fas {{ $s['icon'] }}"></i> {{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Leads Table --}}
    <div class="section-card">
        <div class="card-header bg-dark text-white p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Leads</h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="btn-group btn-group-sm">
                    @foreach([''=>'All','new'=>'New','pending'=>'Pending','won'=>'Won','lost'=>'Lost'] as $val=>$label)
                    <a href="{{ request()->fullUrlWithQuery(['status'=>$val?:null,'page'=>1]) }}"
                       class="btn {{ request('status',$val===''?'':null)===$val ? 'btn-light' : 'btn-outline-light' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
                <div class="btn-group btn-group-sm">
                    @foreach([''=>'All Channels','form'=>'📋 Form','whatsapp'=>'💬 WhatsApp','email'=>'📧 Email'] as $val=>$label)
                    <a href="{{ request()->fullUrlWithQuery(['source'=>$val?:null,'page'=>1]) }}"
                       class="btn {{ request('source',$val===''?'':null)===$val ? 'btn-info text-white' : 'btn-outline-light' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
                <input type="text" id="leadSearch" class="form-control form-control-sm"
                       placeholder="Search..." style="min-width:180px"
                       oninput="filterLeads(this.value)">
            </div>
        </div>

        <div class="card-body p-0">
            @if($leads->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="leadsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
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
                        @foreach($leads as $i => $lead)
                        @php
                            $statusColor = match($lead->status) {
                                'won'     => 'bg-success',
                                'lost'    => 'bg-danger',
                                'pending' => 'bg-warning text-dark',
                                default   => 'bg-primary',
                            };
                        @endphp
                        <tr>
                            <td class="text-muted small">{{ ($leads->currentPage()-1)*$leads->perPage()+$i+1 }}</td>

                            {{-- Channel --}}
                            <td>
                                @php
                                    $srcBadge = match($lead->source ?? 'form') {
                                        'whatsapp' => ['💬 WhatsApp','badge bg-success'],
                                        'email'    => ['📧 Email','badge bg-primary'],
                                        'phone'    => ['📞 Phone','badge bg-warning text-dark'],
                                        default    => ['📋 Form','badge bg-secondary'],
                                    };
                                @endphp
                                <span class="{{ $srcBadge[1] }}">{{ $srcBadge[0] }}</span>
                            </td>

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
                                    <i class="fas fa-envelope fa-xs me-1"></i>{{ $lead->email }}
                                </div>
                                @endif
                                @if($lead->message)
                                <div style="font-size:11px" class="text-muted mt-1 fst-italic">"{{ Str::limit($lead->message,60) }}"</div>
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
                                    <a href="{{ route('admin.profiles.edit',$lead->seller_id) }}"
                                       class="small fw-semibold text-decoration-none text-dark">
                                        {{ $lead->seller->name }}
                                    </a>
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

                            {{-- Status --}}
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.leads.status',$lead->id) }}" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm border-0 p-0 fw-bold"
                                            style="font-size:12px;width:auto;cursor:pointer"
                                            onchange="this.form.submit()">
                                        @foreach(['new'=>'New','pending'=>'Pending','won'=>'Won','lost'=>'Lost'] as $val=>$label)
                                        <option value="{{ $val }}" {{ $lead->status===$val?'selected':'' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>

                            {{-- Date --}}
                            <td class="small text-muted">{{ $lead->created_at?->format('M d, Y') }}</td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        {{-- View message --}}
                                        @if($lead->message)
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#msgModal{{ $lead->id }}">
                                                <i class="fas fa-comment me-2 text-info"></i> View Message
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @endif
                                        <li>
                                            <form method="POST" action="{{ route('admin.leads.destroy',$lead->id) }}"
                                                  onsubmit="return confirm('Delete lead from ' + @json($lead->name) + '?')">
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

                        {{-- Message modal --}}
                        @if($lead->message)
                        <div class="modal fade" id="msgModal{{ $lead->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Message from {{ $lead->name }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
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
                    </tbody>
                </table>
            </div>

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
                <p class="mb-0">No leads yet.</p>
                <small>Leads appear when buyers contact sellers through the platform.</small>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function filterLeads(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#leadsTable tbody tr:not([id^="msgModal"])').forEach(row => {
        if (row.closest('.modal')) return;
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
@endsection
