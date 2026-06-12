@extends('layouts.admin2')
@section('title', 'Categories')

@section('content')
<div class="mt-5 pt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Categories</h4>
            <p class="text-muted small mb-0">Manage mother categories and sub-categories.</p>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-1"></i> Add Category
        </button>
    </div>

    {{-- Stats --}}
    <div class="kpi-grid mb-4" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
        <div class="kpi-card" style="border-left-color:#0ea5e9">
            <h3>{{ $stats['total'] }}</h3>
            <p><i class="fas fa-tags text-primary"></i> Total</p>
        </div>
        <div class="kpi-card" style="border-left-color:#8b5cf6">
            <h3>{{ $stats['mothers'] }}</h3>
            <p><i class="fas fa-folder" style="color:#8b5cf6"></i> Mother Cats</p>
        </div>
        <div class="kpi-card" style="border-left-color:#f59e0b">
            <h3>{{ $stats['subs'] }}</h3>
            <p><i class="fas fa-folder-tree text-warning"></i> Sub-cats</p>
        </div>
        <div class="kpi-card" style="border-left-color:#10b981">
            <h3>{{ $stats['active'] }}</h3>
            <p><i class="fas fa-check-circle text-success"></i> Active</p>
        </div>
        <div class="kpi-card" style="border-left-color:#94a3b8">
            <h3>{{ $stats['inactive'] }}</h3>
            <p><i class="fas fa-ban text-secondary"></i> Inactive</p>
        </div>
    </div>

    {{-- Category Table --}}
    <div class="section-card">
        <div class="card-header bg-dark text-white p-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>All Categories</h5>
            <input type="text" id="catSearch" class="form-control form-control-sm w-auto"
                   placeholder="Search..." style="min-width:200px"
                   oninput="filterCats(this.value)">
        </div>

        <div class="card-body p-0">
            @if($paginated->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="catTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Slug</th>
                            <th class="text-center">Sub-cats</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginated as $i => $cat)
                        {{-- Mother category row --}}
                        <tr class="table-white" data-cat-id="{{ $cat->id }}">
                            <td class="text-muted small">{{ ($paginated->currentPage()-1)*$paginated->perPage()+$i+1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center"
                                         style="width:32px;height:32px;background:#eff6ff;flex-shrink:0">
                                        <i class="fas fa-folder text-primary" style="font-size:13px"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">{{ $cat->title }}</span>
                                        @if($cat->children_count > 0)
                                        <button class="btn btn-link btn-sm p-0 ms-2 text-muted toggle-subs"
                                                data-target="subs-{{ $cat->id }}" title="Toggle sub-cats">
                                            <i class="fas fa-chevron-down" style="font-size:10px"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><code class="small text-muted">{{ $cat->slug }}</code></td>
                            <td class="text-center">
                                @if($cat->children_count > 0)
                                <span class="badge bg-primary">{{ $cat->children_count }}</span>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                    data-update-url="{{ route('admin.categories.update', $cat->id) }}"
                                                    onclick="openEditModal({{ $cat->id }}, this.dataset.updateUrl, @json($cat->title), '{{ $cat->slug }}', {{ $cat->is_active ? 1 : 0 }}, null)">
                                                <i class="fas fa-edit me-2 text-primary"></i> Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                    onclick="openAddSubModal({{ $cat->id }}, @json($cat->title))">
                                                <i class="fas fa-plus me-2 text-success"></i> Add Sub-cat
                                            </button>
                                        </li>
                                        @if($cat->children_count > 0)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.categories.show', $cat->id) }}">
                                                <i class="fas fa-eye me-2 text-info"></i> View Sub-cats
                                            </a>
                                        </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST"
                                                  onsubmit="return confirm('Delete ' + @json($cat->title) + ' and all sub-categories?')">
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

                        {{-- Sub-category rows (hidden by default) --}}
                        @if($cat->children_count > 0)
                        @php $subs = $cat->children ?? collect(); @endphp
                        <tr id="subs-{{ $cat->id }}" class="sub-rows" style="display:none">
                            <td colspan="6" class="p-0 border-0">
                                <table class="table table-sm mb-0" style="background:#f8fafc">
                                    <tbody>
                                        @foreach($subs as $j => $sub)
                                        <tr>
                                            <td width="5%" class="ps-5 text-muted small">{{ $j+1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2 ps-4">
                                                    <i class="fas fa-chevron-right text-muted" style="font-size:9px"></i>
                                                    <i class="fas fa-folder-open text-info" style="font-size:12px"></i>
                                                    <span class="small fw-semibold">{{ $sub->title }}</span>
                                                </div>
                                            </td>
                                            <td><code style="font-size:11px">{{ $sub->slug }}</code></td>
                                            <td class="text-center">—</td>
                                            <td class="text-center">
                                                <span class="badge {{ $sub->is_active ? 'bg-success' : 'bg-secondary' }}" style="font-size:10px">
                                                    {{ $sub->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-xs btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v" style="font-size:11px"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        <li>
                                                            <button class="dropdown-item small" type="button"
                                                                    data-update-url="{{ route('admin.categories.update', $sub->id) }}"
                                                                    onclick="openEditModal({{ $sub->id }}, this.dataset.updateUrl, @json($sub->title), '{{ $sub->slug }}', {{ $sub->is_active ? 1 : 0 }}, {{ $cat->id }})">
                                                                <i class="fas fa-edit me-2 text-primary"></i> Edit
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('admin.categories.destroy', $sub->id) }}" method="POST"
                                                                  onsubmit="return confirm('Delete ' + @json($sub->title) + '?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger small">
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
                            </td>
                        </tr>
                        @endif

                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($paginated->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small">Showing {{ $paginated->firstItem() }}–{{ $paginated->lastItem() }} of {{ $paginated->total() }}</span>
                {{ $paginated->links() }}
            </div>
            @endif

            @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-tags fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">No categories yet.</p>
                <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    Add first category
                </button>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Plumbing">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-muted small">(auto-generated if blank)</span></label>
                        <input type="text" name="slug" class="form-control" placeholder="plumbing">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Parent Category <span class="text-muted small">(leave blank for mother)</span></label>
                        <select name="parent_id" class="form-select">
                            <option value="">— No Parent (Mother Category) —</option>
                            @foreach($mothers as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Sub-category Modal --}}
<div class="modal fade" id="addSubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Sub-category to <span id="subParentName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}" id="addSubForm">
                @csrf
                <input type="hidden" name="parent_id" id="subParentId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sub-category Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Drain Cleaning">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Slug <span class="text-muted small">(optional)</span></label>
                        <input type="text" name="slug" class="form-control" placeholder="drain-cleaning">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save Sub-cat</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editCategoryForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="editTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug</label>
                        <input type="text" name="slug" id="editSlug" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Parent Category</label>
                        <select name="parent_id" id="editParentId" class="form-select">
                            <option value="">— No Parent (Mother Category) —</option>
                            @foreach($mothers as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" id="editIsActive" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function filterCats(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#catTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

document.querySelectorAll('.toggle-subs').forEach(btn => {
    btn.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        const icon   = this.querySelector('i');
        if (target.style.display === 'none') {
            target.style.display = '';
            icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        } else {
            target.style.display = 'none';
            icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        }
    });
});

function openEditModal(id, url, title, slug, isActive, parentId) {
    document.getElementById('editCategoryForm').action = url;
    document.getElementById('editTitle').value    = title;
    document.getElementById('editSlug').value     = slug;
    document.getElementById('editIsActive').value = isActive;
    const parentSel = document.getElementById('editParentId');
    if (parentId) parentSel.value = parentId;
    else parentSel.value = '';
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

function openAddSubModal(parentId, parentName) {
    document.getElementById('subParentId').value = parentId;
    document.getElementById('subParentName').textContent = parentName;
    new bootstrap.Modal(document.getElementById('addSubModal')).show();
}
</script>
@endsection
