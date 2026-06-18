@extends('frontend.layouts.__prof_app')
@section('title', 'Settings')
@section('content')
<div class="pb-24">
    <div class="max-w-2xl mx-auto py-6 px-4 lg:px-0">
        <div class="mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Settings</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manage your account preferences</p>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Profile Photo (separate form — avoids submitting required fields prematurely) --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user text-teal-700 text-sm"></i> Profile Photo
            </h2>
            <form action="{{ route('seller.settings.update') }}" method="POST" enctype="multipart/form-data" id="photoForm">
                @csrf @method('PUT')
                {{-- hidden required fields so photo-only submit passes validation --}}
                <input type="hidden" name="name"          value="{{ auth()->user()->name }}">
                <input type="hidden" name="business_name" value="{{ auth()->user()->business_name }}">
                <input type="hidden" name="email"         value="{{ auth()->user()->email }}">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="shrink-0">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset(auth()->user()->profile_photo) }}"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=80&background=0D9488&color=fff'"
                                 class="w-20 h-20 rounded-full object-cover shadow">
                        @else
                            <div class="w-20 h-20 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold text-2xl shadow">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm">Change Photo</p>
                        <label class="mt-1.5 inline-flex items-center gap-1.5 text-sm font-bold text-teal-700 cursor-pointer hover:underline">
                            <i class="fa-solid fa-camera"></i> Upload New
                            <input type="file" name="profile_photo" accept="image/*" class="hidden"
                                   onchange="document.getElementById('photoForm').submit()">
                        </label>
                        <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, WebP · max 10MB</p>
                    </div>
                </div>
            </form>
        </div>

        {{-- Profile Info --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen text-teal-700 text-sm"></i> Profile Information
            </h2>
            <form action="{{ route('seller.settings.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Business Name</label>
                        <input type="text" name="business_name" value="{{ old('business_name', auth()->user()->business_name) }}"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        @error('business_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', auth()->user()->whatsapp) }}"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Professional Title <span class="text-xs text-slate-400 font-normal">(max 20 chars)</span></label>
                        <input type="text" name="title" id="titleInput" value="{{ old('title', auth()->user()->title) }}"
                            maxlength="20" oninput="updateTitleCount()"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        <p class="text-xs text-slate-400 mt-1"><span id="titleCount">{{ strlen(old('title', auth()->user()->title ?? '')) }}</span>/20 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">City</label>
                        <input type="text" name="city" value="{{ old('city', auth()->user()->city) }}" placeholder="e.g. New York"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">State</label>
                        <input type="text" name="state" value="{{ old('state', auth()->user()->state) }}" placeholder="e.g. New York"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                </div>
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white font-bold px-8 py-3 rounded-2xl text-sm transition">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Save Changes
                </button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-lock text-teal-700 text-sm"></i> Change Password
            </h2>
            <form action="{{ route('seller.settings.password') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
                    <input type="password" name="current_password"
                        class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                </div>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-8 py-3 rounded-2xl text-sm transition">
                    <i class="fa-solid fa-lock mr-2"></i> Update Password
                </button>
            </form>
        </div>

        {{-- Notifications --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-bell text-teal-700 text-sm"></i> Notifications
            </h2>
            <div class="space-y-4">
                @foreach([
                    ['key'=>'notify_new_lead','label'=>'New Lead Alerts',    'desc'=>'Get notified immediately when a new lead comes in'],
                    ['key'=>'notify_payment', 'label'=>'Payment Confirmations','desc'=>'Notify me when a payment is processed'],
                    ['key'=>'notify_review',  'label'=>'New Reviews',        'desc'=>'Alert me when a buyer leaves a review'],
                    ['key'=>'notify_booking', 'label'=>'Booking Requests',   'desc'=>'Notify me when someone books a time slot'],
                ] as $n)
                @php $checked = (bool)(is_null(auth()->user()->{$n['key']}) ? true : auth()->user()->{$n['key']}); @endphp
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-sm text-slate-900">{{ $n['label'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $n['desc'] }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                        <input type="checkbox" {{ $checked ? 'checked' : '' }} class="sr-only peer"
                               onchange="saveNotification('{{ $n['key'] }}', this.checked, this)">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-teal-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Public Page Link --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-link text-teal-700 text-sm"></i> Your Public Page
            </h2>
            <p class="text-xs text-slate-500 mb-3">Share this link with clients and on Google Business Profile</p>
            <div class="flex gap-2">
                <input type="text" readonly
                    value="{{ url('/service/' . (auth()->user()->slug ?? auth()->user()->id)) }}"
                    class="flex-1 min-w-0 text-xs bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-slate-600 font-mono focus:outline-none truncate">
                <a href="{{ url('/service/' . (auth()->user()->slug ?? auth()->user()->id)) }}" target="_blank"
                   class="bg-teal-700 hover:bg-teal-800 text-white font-bold px-4 py-3 rounded-2xl text-sm transition shrink-0">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        </div>

        {{-- Business Category --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
            <h2 class="font-bold text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-briefcase text-teal-700 text-sm"></i> Business Category
            </h2>
            <p class="text-xs text-slate-500 mb-4">Your category determines which profile fields and lead forms appear on your page.</p>
            <div class="flex items-center justify-between gap-3 p-4 bg-slate-50 rounded-2xl">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-tag text-teal-700 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm text-slate-900 truncate">{{ auth()->user()->category?->title ?? 'No category selected' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ auth()->user()->category?->parent?->title ?? 'Uncategorized' }}</p>
                    </div>
                </div>
                <a href="{{ route('user.register.category') }}"
                   class="shrink-0 px-3 sm:px-4 py-2.5 bg-white border border-slate-200 hover:border-teal-400 hover:text-teal-700 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <i class="fa-solid fa-pen text-[11px]"></i> <span class="hidden sm:inline">Change</span>
                </a>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-white rounded-3xl border border-red-100 shadow-sm p-4 sm:p-6">
            <h2 class="font-bold text-red-600 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-sm"></i> Danger Zone
            </h2>
            <p class="text-xs text-slate-500 mb-4">Permanently delete your account and all associated data. This cannot be undone.</p>
            <button onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                class="bg-red-50 hover:bg-red-100 text-red-600 font-bold px-5 py-2.5 rounded-2xl text-sm border border-red-200 transition">
                <i class="fa-solid fa-trash mr-1"></i> Delete Account
            </button>
        </div>

    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-end sm:items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl">
        <div class="text-center mb-5">
            <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="font-bold text-lg text-slate-900">Delete Account?</h3>
            <p class="text-sm text-slate-500 mt-1">All your data, leads, and profile will be permanently removed.</p>
        </div>
        <form action="{{ route('seller.settings.destroy') }}" method="POST" class="space-y-4">
            @csrf @method('DELETE')
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Enter password to confirm</label>
                <input type="password" name="password" required placeholder="Your current password"
                    class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-red-400 focus:ring-2 focus:ring-red-50 transition">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')"
                    class="flex-1 border border-slate-200 text-slate-600 font-bold py-3 rounded-2xl text-sm hover:bg-slate-50 transition">Cancel</button>
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-2xl text-sm transition">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function saveNotification(key, val, checkbox) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch('{{ route('seller.settings.notifications') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ key, value: val })
    }).then(r => {
        if (!r.ok) throw new Error();
    }).catch(() => {
        // Revert toggle on failure
        checkbox.checked = !val;
        alert('Could not save preference. Please try again.');
    });
}

function updateTitleCount() {
    const input = document.getElementById('titleInput');
    const count = document.getElementById('titleCount');
    if (input && count) {
        count.textContent = input.value.length;
        count.style.color = input.value.length >= 20 ? '#ef4444' : '';
    }
}

// Re-open delete modal if there were password errors
@if($errors->has('password'))
document.getElementById('deleteModal').classList.remove('hidden');
@endif

// Close delete modal on backdrop click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
@endsection
