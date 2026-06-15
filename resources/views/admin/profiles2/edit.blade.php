@extends('layouts.admin2')
@section('title', 'Edit User — ' . $user->name)

@section('content')
<div class="mt-5 pt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Edit User Profile</h4>
            <p class="text-muted small mb-0">{{ $user->email }} &mdash; joined {{ $user->created_at?->format('M d, Y') }}</p>
        </div>
        <a href="{{ route('admin.profiles.index', ['status' => $user->status ? 'verified' : 'unverified']) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.profiles.update', $user->id) }}">
        @csrf @method('PUT')

        <div class="row g-4">

            {{-- LEFT column --}}
            <div class="col-lg-4">

                {{-- Avatar card --}}
                <div class="section-card mb-4 text-center p-4">
                    @if($user->profile_photo)
                    <img src="{{ get_file($user->profile_photo, 'user') }}"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=120&background=0ea5e9&color=fff'"
                         class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover">
                    @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3 fw-bold"
                         style="width:100px;height:100px;font-size:36px">
                        {{ strtoupper(substr($user->name,0,1)) }}
                    </div>
                    @endif
                    <h5 class="mb-1 fw-bold">{{ $user->name }}</h5>
                    <div class="mb-2">
                        <span class="badge {{ $user->type==='seller'?'bg-primary':($user->type==='admin'?'bg-dark':($user->type==='staff'?'bg-warning text-dark':'bg-secondary')) }}">
                            {{ ucfirst($user->type==='user'?'buyer':$user->type) }}
                        </span>
                        <span class="badge {{ $user->status?'bg-success':'bg-warning text-dark' }} ms-1">
                            {{ $user->status?'Verified':'Pending' }}
                        </span>
                    </div>
                    @if($user->slug)
                    <div class="text-muted small font-monospace">/{{ $user->slug }}</div>
                    @endif
                    @if($user->staffProfile)
                    <div class="mt-2">
                        <span class="badge bg-info">
                            <i class="fas fa-sitemap me-1"></i>
                            {{ \App\Models\StaffProfile::ROLES[$user->staffProfile->role] ?? $user->staffProfile->role }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Admin Controls --}}
                <div class="section-card mb-4">
                    <div class="card-header bg-warning text-dark p-3">
                        <h6 class="mb-0"><i class="fas fa-shield-halved me-2"></i>Admin Controls</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Account Type</label>
                            <select name="type" class="form-select">
                                @foreach(['seller'=>'Seller','user'=>'Buyer','staff'=>'Staff','admin'=>'Admin'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ old('type',$user->type)===$val?'selected':'' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Verification Status</label>
                            <select name="status" class="form-select" required>
                                <option value="1" {{ old('status',$user->status)?'selected':'' }}>Verified</option>
                                <option value="0" {{ !old('status',$user->status)?'selected':'' }}>Pending / Unverified</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title / Badge</label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ old('title',$user->title) }}" placeholder="e.g. Pro, Featured">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Admin Remark</label>
                            <textarea name="remark" rows="3" class="form-control"
                                      placeholder="Internal notes about this user">{{ old('remark',$user->remark) }}</textarea>
                        </div>
                        @if($user->type === 'seller')
                        <div class="mb-3">
                            <label class="form-label fw-semibold d-flex align-items-center gap-2">
                                <i class="fas fa-phone-volume text-danger"></i> Twilio SMS Notifications
                            </label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="twilio_enabled" id="twilioEnabled" value="1"
                                       {{ old('twilio_enabled', $user->twilio_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="twilioEnabled">
                                    Send SMS to seller when new lead arrives
                                </label>
                            </div>
                            @if(!$user->phone)
                            <div class="text-warning small mt-1">
                                <i class="fas fa-triangle-exclamation me-1"></i>No phone set — seller won't receive SMS until phone is added.
                            </div>
                            @endif
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold d-flex align-items-center gap-2">
                                <i class="fas fa-hashtag text-primary"></i> Tracking Number (shown on frontend)
                            </label>
                            @if($user->twilioNumber)
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-success px-3 py-2 font-monospace fs-6">
                                    {{ $user->twilioNumber->number }}
                                </span>
                                <span class="text-muted small">assigned {{ $user->twilioNumber->assigned_at?->format('M d, Y') }}</span>
                                <a href="{{ route('admin.phone-pool.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                    <i class="fas fa-arrows-rotate me-1"></i>Change
                                </a>
                            </div>
                            @else
                            <div class="mt-1 d-flex align-items-center gap-2">
                                <span class="badge bg-warning text-dark px-3 py-2">No tracking number assigned</span>
                                <a href="{{ route('admin.phone-pool.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i>Assign from Pool
                                </a>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Terms Agreement --}}
                <div class="section-card mb-4">
                    <div class="card-header bg-secondary text-white p-3">
                        <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>Terms Agreement</h6>
                    </div>
                    <div class="card-body p-3">
                        @if($user->agreed_terms_at)
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-check text-success"></i>
                            <div>
                                <div class="fw-semibold text-success small">Agreed to Terms</div>
                                <div class="text-muted" style="font-size:12px">{{ $user->agreed_terms_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                        @else
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-xmark text-warning"></i>
                            <div>
                                <div class="fw-semibold text-warning small">Not Yet Agreed</div>
                                <div class="text-muted" style="font-size:12px">User will be prompted on next login</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Working Hours (sellers only) --}}
                @if($user->type === 'seller')
                @php
                    $adminSched    = is_array($user->schedule) ? $user->schedule : (json_decode($user->schedule, true) ?? []);
                    $adminOhOn     = (bool)($adminSched['show_office_hours'] ?? false);
                    $adminOh       = $adminSched['office_hours'] ?? null;
                    $adminOhDays   = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
                    $adminRtLabels = ['30_min'=>'~30 min','1_hour'=>'~1 hr','4_hours'=>'~4 hrs','24_hours'=>'~24 hrs','48_hours'=>'~2 days'];
                @endphp
                <div class="section-card mb-4">
                    <div class="card-header p-3 d-flex justify-content-between align-items-center"
                         style="background:#0f766e;color:#fff">
                        <h6 class="mb-0"><i class="fas fa-business-time me-2"></i>Working Hours</h6>
                        <span class="badge {{ $adminOhOn && $adminOh ? 'bg-success' : 'bg-secondary' }}">
                            {{ $adminOhOn && $adminOh ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="card-body p-3">
                        @if(!$adminOhOn || !$adminOh)
                            <p class="text-muted small mb-0">
                                <i class="fas fa-circle-xmark text-secondary me-1"></i>
                                Seller has not configured working hours, or they are hidden from the profile.
                            </p>
                        @else
                            {{-- Meta badges --}}
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @if($adminOh['timezone'] ?? null)
                                <span class="badge bg-light text-dark border" style="font-size:11px">
                                    <i class="fas fa-globe me-1 text-secondary"></i>{{ $adminOh['timezone'] }}
                                </span>
                                @endif
                                @if($adminOh['response_time'] ?? null)
                                <span class="badge bg-light text-dark border" style="font-size:11px">
                                    <i class="fas fa-bolt me-1 text-warning"></i>
                                    {{ $adminRtLabels[$adminOh['response_time']] ?? $adminOh['response_time'] }}
                                </span>
                                @endif
                                @if($adminOh['emergency_available'] ?? false)
                                <span class="badge bg-danger" style="font-size:11px">
                                    <i class="fas fa-phone me-1"></i>Emergency
                                </span>
                                @endif
                            </div>

                            {{-- Day rows --}}
                            <div class="border rounded-2 overflow-hidden mb-2">
                                @foreach($adminOhDays as $dk => $ds)
                                @php
                                    $ad      = $adminOh['days'][$dk] ?? null;
                                    $adOpen  = $ad && ($ad['open'] ?? false);
                                    $adSlots = $ad['slots'] ?? [];
                                @endphp
                                <div class="d-flex align-items-center gap-2 px-3 py-1 border-bottom {{ $loop->last ? 'border-0' : '' }}"
                                     style="font-size:12px;background:{{ $adOpen ? '#fff' : '#f8fafc' }}">
                                    <span class="fw-bold text-muted" style="width:28px;flex-shrink:0">{{ $ds }}</span>
                                    @if($adOpen)
                                        <span class="badge bg-success" style="font-size:9px;padding:2px 5px">Open</span>
                                        <span class="text-dark">
                                            @foreach($adSlots as $asi => $asl)
                                                @if($asi > 0) <span class="text-muted mx-1">·</span> @endif
                                                {{ \Carbon\Carbon::createFromTimeString($asl['from'] ?? '00:00')->format('g:i A') }}
                                                –
                                                {{ \Carbon\Carbon::createFromTimeString($asl['to'] ?? '00:00')->format('g:i A') }}
                                            @endforeach
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic">Closed</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            @if($adminOh['note'] ?? null)
                            <p class="small text-muted mb-2">
                                <i class="fas fa-circle-info me-1"></i>{{ $adminOh['note'] }}
                            </p>
                            @endif

                            @if($user->slug)
                            <a href="{{ route('service.show', $user->slug) }}" target="_blank"
                               class="btn btn-sm btn-outline-secondary w-100">
                                <i class="fas fa-external-link-alt me-1"></i> View Live on Profile
                            </a>
                            @endif
                        @endif
                    </div>
                </div>
                @endif

                {{-- Danger Zone --}}
                <div class="section-card border border-danger">
                    <div class="card-header bg-danger text-white p-3">
                        <h6 class="mb-0"><i class="fas fa-triangle-exclamation me-2"></i>Danger Zone</h6>
                    </div>
                    <div class="card-body p-3">
                        <p class="small text-muted mb-3">Permanently deletes the user and all their data. Cannot be undone.</p>
                        <form method="POST" action="{{ route('admin.profiles.destroy', $user->id) }}"
                              onsubmit="return confirm('Permanently delete ' + @json($user->name) + '? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash me-1"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            {{-- RIGHT column --}}
            <div class="col-lg-8">

                {{-- Basic Info --}}
                <div class="section-card mb-4">
                    <div class="card-header bg-primary text-white p-3">
                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Basic Information</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ old('name',$user->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" required
                                       value="{{ old('email',$user->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                       value="{{ old('phone',$user->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control"
                                       value="{{ old('whatsapp',$user->whatsapp) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Designation</label>
                                <input type="text" name="designation" class="form-control"
                                       value="{{ old('designation',$user->designation) }}" placeholder="e.g. Plumber, Electrician">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Experience</label>
                                <input type="text" name="experience" class="form-control"
                                       value="{{ old('experience',$user->experience) }}" placeholder="e.g. 5 years">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Info --}}
                <div class="section-card mb-4">
                    <div class="card-header bg-dark text-white p-3">
                        <h6 class="mb-0"><i class="fas fa-briefcase me-2"></i>Business / Seller Info</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Business Name</label>
                                <input type="text" name="business_name" class="form-control"
                                       value="{{ old('business_name',$user->business_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service Type</label>
                                <input type="text" name="seller_service_type" class="form-control"
                                       value="{{ old('seller_service_type',$user->seller_service_type) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- No Category --</option>
                                    @foreach(\App\Models\Category::whereNull('parent_id')->orderBy('title')->get() as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id',$user->category_id)==$cat->id?'selected':'' }}>
                                        {{ $cat->title }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Work Address</label>
                                <input type="text" name="work_address" class="form-control"
                                       value="{{ old('work_address',$user->work_address) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Tags</label>
                                <input type="text" name="tags" class="form-control"
                                       value="{{ old('tags',$user->tags) }}" placeholder="Comma-separated tags">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Location --}}
                <div class="section-card mb-4">
                    <div class="card-header bg-success text-white p-3">
                        <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control"
                                       value="{{ old('city',$user->city) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" name="state" class="form-control"
                                       value="{{ old('state',$user->state) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Country</label>
                                <input type="text" name="country" class="form-control"
                                       value="{{ old('country',$user->country) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ZIP Code</label>
                                <input type="text" name="zip_code" class="form-control"
                                       value="{{ old('zip_code',$user->zip_code) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bio / About --}}
                <div class="section-card mb-4">
                    <div class="card-header bg-secondary text-white p-3">
                        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Bio / About</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Short Bio</label>
                            <textarea name="bio" rows="3" class="form-control"
                                      placeholder="Short bio shown on profile">{{ old('bio',$user->bio) }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">About (Full)</label>
                            <textarea name="about" rows="4" class="form-control"
                                      placeholder="Full about section">{{ old('about',$user->about) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Save --}}
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.profiles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>

            </div>
        </div>

    </form>
</div>
@endsection
