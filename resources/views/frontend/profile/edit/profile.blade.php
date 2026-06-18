@extends('frontend.layouts.__prof_app')
@section('title', 'Profile & Bio')
@section('page-title', 'Profile & Bio')

@section('content')
@php
    $motherCat   = strtolower($user->category?->parent?->title ?? $user->category?->title ?? '');
    $isHealthcare = str_contains($motherCat, 'health') || str_contains($motherCat, 'wellness') || str_contains($motherCat, 'medical');
    $isHome       = str_contains($motherCat, 'home')   || str_contains($motherCat, 'repair')  || str_contains($motherCat, 'service');
    $isBeauty     = str_contains($motherCat, 'beauty') || str_contains($motherCat, 'personal care') || str_contains($motherCat, 'salon');

    $photoHint      = $isHealthcare
        ? 'Professional headshot — patients want to see who they\'re meeting before they book'
        : ($isHome
            ? 'Clear photo of yourself or your team — builds trust before you arrive at the job'
            : ($isBeauty
                ? 'Clear headshot or a photo at your workspace — your look is part of your brand'
                : 'Professional headshot — clients hire you personally, a face builds trust'));

    $titlePlaceholder = $isHealthcare
        ? 'e.g. Board-Certified Family Physician'
        : ($isHome
            ? 'e.g. Licensed Master Plumber & Gas Fitter'
            : ($isBeauty
                ? 'e.g. Licensed Cosmetologist & Color Specialist'
                : 'e.g. Certified Public Accountant'));

    $bioPlaceholder = $isHealthcare
        ? 'e.g. Board-certified family physician with 10 years of experience in preventive care, chronic disease management, and patient wellness...'
        : ($isHome
            ? 'e.g. Licensed master plumber with 12 years serving homeowners in the Bronx. Specialising in emergency repairs, remodeling, and gas line work...'
            : ($isBeauty
                ? 'e.g. Licensed cosmetologist with 8 years specialising in balayage, color correction, and keratin treatments. Based in Brooklyn...'
                : 'e.g. Certified public accountant with 14 years helping small businesses with tax planning, bookkeeping, and financial strategy...'));
@endphp
<div class="pb-10 max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('seller.onboarding') }}"
           class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-teal-700 hover:border-teal-300 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Profile & Bio</h1>
            <p class="text-xs text-gray-500 mt-0.5">Appears in the hero and about sections of your public page</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ route('save.seller.profile', ['type' => $type, 'setup' => 'profile']) }}"
          class="space-y-4">
        @csrf

        {{-- Profile Photo --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-1">Profile Photo</label>
            <p class="text-xs text-slate-400 mb-4">{{ $photoHint }}</p>
            <div class="flex items-center gap-5">
                <div class="relative flex-shrink-0">
                    @if($user->profile_photo)
                    <img id="photoPreview" src="{{ asset($user->profile_photo) }}"
                         class="w-20 h-20 rounded-2xl object-cover border-2 border-slate-200">
                    @else
                    <div id="photoPreviewPlaceholder"
                         class="w-20 h-20 rounded-2xl bg-teal-50 border-2 border-dashed border-teal-200 flex items-center justify-center">
                        <i class="fa-solid fa-user text-teal-300 text-2xl"></i>
                    </div>
                    <img id="photoPreview" src="" class="w-20 h-20 rounded-2xl object-cover border-2 border-slate-200 hidden">
                    @endif
                </div>
                <div class="flex-1">
                    <label for="photoInput"
                           class="inline-flex items-center gap-2 cursor-pointer px-4 py-2.5 bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-700 hover:text-teal-800 font-semibold text-sm rounded-xl transition">
                        <i class="fa-solid fa-upload text-xs"></i>
                        {{ $user->profile_photo ? 'Change Photo' : 'Upload Photo' }}
                    </label>
                    <input id="photoInput" type="file" name="profile_photo" accept="image/*" class="hidden"
                           onchange="previewPhoto(this)">
                    <p class="text-xs text-slate-400 mt-2">JPG, PNG or WEBP · Max 10MB</p>
                </div>
            </div>
        </div>

        {{-- Professional Title --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-1">Professional Title</label>
            <p class="text-xs text-slate-400 mb-3">Shown under your name on your public page</p>
            <input type="text" name="title" value="{{ old('title', $user->title ?? $user->designation) }}"
                placeholder="{{ $titlePlaceholder }}"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        {{-- Bio --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-1">Short Bio <span class="text-red-500">*</span></label>
            <p class="text-xs text-slate-400 mb-3">2–3 sentences about your expertise and what you offer clients</p>
            <textarea name="bio" rows="4"
                placeholder="{{ $bioPlaceholder }}"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition resize-none">{{ old('bio', $user->bio) }}</textarea>
            <p class="text-xs text-slate-400 mt-2 text-right" id="bioCount">{{ strlen($user->bio ?? '') }} / 2000</p>
        </div>

        {{-- About --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-1">About</label>
            <p class="text-xs text-slate-400 mb-3">Detailed description shown in the About section of your public page</p>
            <textarea name="about" rows="6"
                placeholder="e.g. A. K. Azad, CPA, PLLC is a public accounting firm based in the Bronx. We are dedicated to delivering value-added professional services..."
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition resize-none">{{ old('about', $user->about) }}</textarea>
            <p class="text-xs text-slate-400 mt-2 text-right" id="aboutCount">{{ strlen($user->about ?? '') }} / 3000</p>
        </div>

        {{-- Years of Experience --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-1">Years of Experience</label>
            <p class="text-xs text-slate-400 mb-3">Shown as a stat on your public page hero card</p>
            <input type="number" name="experience" value="{{ old('experience', $user->experience) }}"
                min="0" max="99" placeholder="e.g. 14"
                class="w-28 px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition text-center">
            <span class="text-sm text-slate-400 ml-2">years</span>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                class="px-8 py-3 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl text-sm transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Save & Continue
            </button>
        </div>
    </form>
</div>

<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('photoPreview');
        const placeholder = document.getElementById('photoPreviewPlaceholder');
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

const bioTextarea = document.querySelector('textarea[name="bio"]');
const bioCount    = document.getElementById('bioCount');
if (bioTextarea && bioCount) {
    bioTextarea.addEventListener('input', () => {
        bioCount.textContent = bioTextarea.value.length + ' / 2000';
    });
}

const aboutTextarea = document.querySelector('textarea[name="about"]');
const aboutCount    = document.getElementById('aboutCount');
if (aboutTextarea && aboutCount) {
    aboutTextarea.addEventListener('input', () => {
        aboutCount.textContent = aboutTextarea.value.length + ' / 3000';
    });
}
</script>
@endsection
