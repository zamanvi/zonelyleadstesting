@extends('frontend.layouts._app')
@section('title', 'My Profile')
@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-16 px-4">
    <div class="max-w-2xl mx-auto py-6">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('buyer.dashboard') ?? '#' }}" class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-xl font-bold text-slate-900">My Profile</h1>
        </div>

        @if(session('success'))
        <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- Avatar --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4 flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-5">
            <div class="relative shrink-0">
                @if(auth()->user()->profile_photo)
                <img src="{{ asset(auth()->user()->profile_photo) }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=80&background=3b82f6&color=fff'"
                     class="w-20 h-20 rounded-full object-cover" id="profilePhotoImg">
                @else
                <div class="w-20 h-20 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold text-2xl" id="profilePhotoDiv">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                @endif
                <label for="photoUpload" class="absolute -bottom-2 -right-2 w-8 h-8 bg-teal-700 text-white rounded-xl flex items-center justify-center cursor-pointer hover:bg-teal-800 transition shadow">
                    <i class="fa-solid fa-camera text-xs"></i>
                </label>
            </div>
            <div>
                <p class="font-bold text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
                <p class="text-xs text-slate-400 mt-1">Member since {{ auth()->user()->created_at?->format('M Y') }}</p>
            </div>
        </div>

        {{-- Personal Info --}}
        <form action="{{ route('buyer.profile.update') ?? '#' }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <input type="file" name="profile_photo" id="photoUpload" class="hidden" accept="image/*"
                onchange="previewPhoto(this)">

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-700 text-white rounded-lg flex items-center justify-center text-xs font-black">1</span>
                    Personal Info
                </h2>
                <div class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                                placeholder="+1 555 000 0000"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">City</label>
                            <input type="text" name="city" value="{{ old('city', auth()->user()->city) }}"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">State / Province</label>
                            <input type="text" name="state" value="{{ old('state', auth()->user()->state) }}"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Street Address</label>
                        <input type="text" name="address" value="{{ old('address', auth()->user()->address) }}"
                            placeholder="123 Main St"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Country</label>
                            <input type="text" name="country" value="{{ old('country', auth()->user()->country) }}"
                                placeholder="United States"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Zip / Postal Code</label>
                            <input type="text" name="zip_code" value="{{ old('zip_code', auth()->user()->zip_code) }}"
                                placeholder="10001"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-4 rounded-2xl text-sm transition mb-4">
                Save Changes
            </button>
        </form>

        {{-- Change Password --}}
        <form action="{{ route('buyer.profile.update') }}" method="POST">
            @csrf @method('PUT')

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-700 text-white rounded-lg flex items-center justify-center text-xs font-black">2</span>
                    Change Password
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
                        <input type="password" name="current_password"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                            <input type="password" name="password"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-4 rounded-2xl text-sm transition mb-6">
                Update Password
            </button>
        </form>

        {{-- Danger Zone --}}
        <div class="bg-white rounded-3xl border border-red-100 shadow-sm p-6">
            <h2 class="font-bold text-red-600 mb-1">Danger Zone</h2>
            <p class="text-xs text-slate-500 mb-4">Deleting your account is permanent and cannot be undone. All your bookings and data will be removed.</p>
            <button type="button" onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold py-3.5 rounded-2xl text-sm border border-red-100 transition">
                <i class="fa-solid fa-trash mr-2"></i> Delete My Account
            </button>
        </div>

    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-sm">
        <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-triangle-exclamation text-red-500 text-2xl"></i>
        </div>
        <h3 class="font-black text-slate-900 text-center text-lg mb-1">Delete Account?</h3>
        <p class="text-sm text-slate-500 text-center mb-6">Enter your password to confirm. This cannot be undone.</p>
        <form action="{{ route('buyer.profile.destroy') ?? '#' }}" method="POST">
            @csrf @method('DELETE')
            <input type="password" name="password" required placeholder="Your password"
                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-red-400 mb-4 transition">
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')"
                    class="flex-1 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold transition">
                    Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const imgs = document.querySelectorAll('img[src*="profile"], .w-20.h-20');
        imgs.forEach(img => {
            if (img.tagName === 'IMG') img.src = e.target.result;
        });
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
