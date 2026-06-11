@extends('layouts.admin2')
@section('title', 'Platform Settings')

@section('content')
<div class="mt-5 pt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Platform Settings</h4>
            <p class="text-muted small mb-0">Manage contact details, social links, legal info, and branding shown across the site.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.contact.update') }}">
    @csrf
    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">

            {{-- CONTACT --}}
            <div class="section-card mb-4">
                <div class="card-header bg-dark text-white p-4">
                    <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Contact</h5>
                    <p class="mb-0 small opacity-75 mt-1">Used in seller suspension emails, dashboard banners, and the site footer.</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Team / Sender Name</label>
                            <input type="text" name="support_name"
                                   class="form-control @error('support_name') is-invalid @enderror"
                                   value="{{ old('support_name', $settings['support_name']) }}"
                                   placeholder="Zonely Admin Team">
                            <div class="form-text">Sign-off name used in automated emails.</div>
                            @error('support_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Support Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="support_email"
                                       class="form-control @error('support_email') is-invalid @enderror"
                                       value="{{ old('support_email', $settings['support_email']) }}"
                                       placeholder="support@zonely.com" required>
                            </div>
                            @error('support_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">WhatsApp Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                                <input type="text" name="support_whatsapp"
                                       class="form-control @error('support_whatsapp') is-invalid @enderror"
                                       value="{{ old('support_whatsapp', $settings['support_whatsapp']) }}"
                                       placeholder="+1 234 567 8900">
                            </div>
                            <div class="form-text">Include country code. Shown as clickable button in emails and footer.</div>
                            @error('support_whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- SOCIAL MEDIA --}}
            <div class="section-card mb-4">
                <div class="card-header bg-primary text-white p-4">
                    <h5 class="mb-0"><i class="fas fa-share-nodes me-2"></i>Social Media</h5>
                    <p class="mb-0 small opacity-75 mt-1">Social icons in the site footer link to these URLs.</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Facebook URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-facebook text-primary"></i></span>
                                <input type="url" name="social_facebook"
                                       class="form-control @error('social_facebook') is-invalid @enderror"
                                       value="{{ old('social_facebook', $settings['social_facebook']) }}"
                                       placeholder="https://facebook.com/yourpage">
                            </div>
                            @error('social_facebook')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">LinkedIn URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-linkedin text-primary"></i></span>
                                <input type="url" name="social_linkedin"
                                       class="form-control @error('social_linkedin') is-invalid @enderror"
                                       value="{{ old('social_linkedin', $settings['social_linkedin']) }}"
                                       placeholder="https://linkedin.com/company/yourpage">
                            </div>
                            @error('social_linkedin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- LEGAL --}}
            <div class="section-card mb-4">
                <div class="card-header bg-secondary text-white p-4">
                    <h5 class="mb-0"><i class="fas fa-scale-balanced me-2"></i>Legal</h5>
                    <p class="mb-0 small opacity-75 mt-1">Sister site link shown in the footer legal section.</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sister Site Name</label>
                            <input type="text" name="sister_site_name"
                                   class="form-control @error('sister_site_name') is-invalid @enderror"
                                   value="{{ old('sister_site_name', $settings['sister_site_name']) }}"
                                   placeholder="e.g. Migo Trucking">
                            @error('sister_site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sister Site URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-link text-muted"></i></span>
                                <input type="url" name="sister_site_url"
                                       class="form-control @error('sister_site_url') is-invalid @enderror"
                                       value="{{ old('sister_site_url', $settings['sister_site_url']) }}"
                                       placeholder="https://migotrucking.com">
                            </div>
                            @error('sister_site_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- BRANDING --}}
            <div class="section-card mb-4">
                <div class="card-header bg-info text-white p-4">
                    <h5 class="mb-0"><i class="fas fa-copyright me-2"></i>Branding</h5>
                    <p class="mb-0 small opacity-75 mt-1">Text shown at the bottom of every page.</p>
                </div>
                <div class="card-body p-4">
                    <div class="col-md-10">
                        <label class="form-label fw-semibold">Copyright Text</label>
                        <input type="text" name="copyright_text"
                               class="form-control @error('copyright_text') is-invalid @enderror"
                               value="{{ old('copyright_text', $settings['copyright_text']) }}"
                               placeholder="© {{ date('Y') }} Zonely. Empowering Local Experts.">
                        <div class="form-text">Shown in the footer bottom bar. Use <code>{{ '{{year}}' }}</code> to auto-insert the current year.</div>
                        @error('copyright_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                <i class="fas fa-save me-2"></i>Save All Settings
            </button>

        </div>

        {{-- RIGHT COLUMN — Where things appear --}}
        <div class="col-lg-4">
            <div class="section-card sticky-top" style="top:80px">
                <div class="card-header bg-dark text-white p-4">
                    <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Where Each Setting Appears</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <p class="fw-bold small mb-2 text-primary"><i class="fas fa-headset me-1"></i> Contact</p>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-1">📧 Seller suspension email</li>
                            <li class="mb-1">✅ Seller reactivation email</li>
                            <li class="mb-1">🔴 Suspended seller dashboard banner</li>
                            <li>🌐 Site footer support section</li>
                        </ul>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <p class="fw-bold small mb-2 text-primary"><i class="fas fa-share-nodes me-1"></i> Social Media</p>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li>🌐 Footer social icons (Facebook, LinkedIn)</li>
                        </ul>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <p class="fw-bold small mb-2 text-primary"><i class="fas fa-scale-balanced me-1"></i> Legal</p>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li>🌐 Footer legal section — Sister Site link</li>
                        </ul>
                    </div>
                    <hr>
                    <div>
                        <p class="fw-bold small mb-2 text-primary"><i class="fas fa-copyright me-1"></i> Branding</p>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li>🌐 Footer bottom bar copyright text</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </form>

</div>
@endsection
