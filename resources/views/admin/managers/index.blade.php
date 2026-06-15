@extends('layouts.admin2')
@section('title', 'Panel Managers')

@section('content')
<div class="mt-5 pt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Panel Managers</h4>
            <p class="text-muted small mb-0">Users with restricted access to specific admin sections.</p>
        </div>
        <a href="{{ route('admin.managers.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Manager
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="section-card">
        <div class="card-body p-0">
            @if($managers->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name / Email</th>
                            <th>Modules Access</th>
                            <th class="text-center">Status</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managers as $i => $manager)
                        @php $profile = $manager->managerProfile; @endphp
                        <tr>
                            <td class="text-muted small">{{ ($managers->currentPage()-1)*$managers->perPage()+$i+1 }}</td>

                            <td>
                                <div class="fw-bold small d-flex align-items-center gap-2">
                                    {{ $manager->name }}
                                    @if($manager->type === 'coo')
                                    <span class="badge bg-warning text-dark" style="font-size:10px"><i class="fas fa-crown me-1"></i>General Manager</span>
                                    @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size:10px"><i class="fas fa-user-shield me-1"></i>Manager</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:11px">{{ $manager->email }}</div>
                                @if($manager->city || $manager->country)
                                <div class="text-muted" style="font-size:11px"><i class="fas fa-map-marker-alt me-1"></i>{{ collect([$manager->city, $manager->state, $manager->country])->filter()->implode(', ') }}</div>
                                @endif
                            </td>

                            <td>
                                @if($manager->type === 'coo')
                                <span class="badge bg-warning text-dark" style="font-size:10px"><i class="fas fa-unlock-alt me-1"></i>Full Access</span>
                                @elseif($profile && count($profile->modules ?? []))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($profile->modules as $mod)
                                    @php $info = \App\Models\ManagerProfile::MODULES[$mod] ?? null; @endphp
                                    @if($info)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size:10px">
                                        <i class="fas {{ $info['icon'] }} me-1"></i>{{ $info['label'] }}
                                    </span>
                                    @endif
                                    @endforeach
                                </div>
                                @else
                                <span class="text-muted small">No modules assigned</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($profile)
                                <span class="badge {{ $profile->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($profile->status ?? 'unknown') }}
                                </span>
                                @else
                                <span class="badge bg-warning text-dark">No Profile</span>
                                @endif
                            </td>

                            <td class="small text-muted">{{ $manager->created_at->format('M d, Y') }}</td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.managers.edit', $manager->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.managers.destroy', $manager->id) }}"
                                          onsubmit="return confirm('Delete manager ' + @json($manager->name) + '? Their account will be deleted.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @if($profile && $profile->plain_password)
                        <tr style="background:#fffbea">
                            <td colspan="6" class="px-4 py-2">
                                <div class="d-flex flex-wrap align-items-center gap-4">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-muted" style="font-size:11px;font-weight:600;min-width:52px">USER ID</span>
                                        <code class="bg-white px-2 py-1 rounded border small" id="uid_{{ $manager->id }}">{{ $manager->id }}</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="copyText('uid_{{ $manager->id }}')" title="Copy"><i class="fas fa-copy" style="font-size:11px"></i></button>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-muted" style="font-size:11px;font-weight:600;min-width:44px">EMAIL</span>
                                        <code class="bg-white px-2 py-1 rounded border small" id="eml_{{ $manager->id }}">{{ $manager->email }}</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="copyText('eml_{{ $manager->id }}')" title="Copy"><i class="fas fa-copy" style="font-size:11px"></i></button>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-muted" style="font-size:11px;font-weight:600;min-width:64px">PASSWORD</span>
                                        <code class="bg-white px-2 py-1 rounded border small" id="pwd_{{ $manager->id }}">{{ try_decrypt($profile->plain_password) }}</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="copyText('pwd_{{ $manager->id }}')" title="Copy"><i class="fas fa-copy" style="font-size:11px"></i></button>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-muted" style="font-size:11px;font-weight:600;min-width:72px">LOGIN LINK</span>
                                        <code class="bg-white px-2 py-1 rounded border small text-truncate" style="max-width:320px" id="lnk_{{ $manager->id }}">{{ $profile->login_url ?? route('user.login') }}</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="copyText('lnk_{{ $manager->id }}')" title="Copy"><i class="fas fa-copy" style="font-size:11px"></i></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $managers->links() }}</div>
            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-user-shield fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No managers yet.</p>
                <small>Click "Add Manager" to create one.</small>
            </div>
            @endif
        </div>
    </div>

</div>

<script>
function copyText(id) {
    const el = document.getElementById(id);
    navigator.clipboard.writeText(el.innerText.trim()).then(() => {
        const btn = el.nextElementSibling;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i>', 1500);
    });
}
</script>
@endsection
