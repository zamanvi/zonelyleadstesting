@extends('frontend.layouts.__prof_app')
@section('title', 'Add Service')
@section('page-title', 'Add Service')

@section('content')
@php
    $motherCat = strtolower($user->category?->parent?->title ?? $user->category?->title ?? '');
    $isHealthcare = str_contains($motherCat, 'health') || str_contains($motherCat, 'wellness') || str_contains($motherCat, 'medical');
    $isHome       = str_contains($motherCat, 'home')   || str_contains($motherCat, 'repair')  || str_contains($motherCat, 'service');
    $isBeauty     = str_contains($motherCat, 'beauty') || str_contains($motherCat, 'personal care') || str_contains($motherCat, 'salon');

    $serviceTitlePlaceholder = $isHealthcare
        ? 'e.g. Initial Consultation (60 min)'
        : ($isHome
            ? 'e.g. Full Bathroom Remodel'
            : ($isBeauty
                ? 'e.g. Balayage + Toner'
                : 'e.g. Individual Tax Return (1040)'));

    $featurePlaceholder = $isHealthcare
        ? "Detailed health assessment\nPersonalized treatment plan\nFollow-up support included"
        : ($isHome
            ? "Free on-site estimate\nLicensed & insured technicians\nClean-up included"
            : ($isBeauty
                ? "Includes blow-dry & style\nComplimentary gloss treatment\nAftercare advice included"
                : "Federal & New York State Return\nItemized deductions & credits\nE-filed within 3 business days"));
@endphp
<div class="pb-10 max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('user.services.index') }}"
           class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-teal-700 hover:border-teal-300 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Add New Service</h1>
            <p class="text-xs text-gray-500 mt-0.5">Appears in the Pricing section of your public page</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('user.services.store') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Title --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">
                Service Title <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title') }}" required
                placeholder="{{ $serviceTitlePlaceholder }}"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        {{-- Price + Pricing Type --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-3">Pricing</label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Price ($)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                        <input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01"
                            placeholder="179"
                            class="w-full pl-7 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Leave blank → shows "Contact us"</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pricing Type</label>
                    <select name="pricing_type"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition bg-white">
                        <option value="starting_at" {{ old('pricing_type','starting_at')=='starting_at' ? 'selected' : '' }}>starting at</option>
                        <option value="per_month"   {{ old('pricing_type')=='per_month'   ? 'selected' : '' }}>per month</option>
                        <option value="per_hour"    {{ old('pricing_type')=='per_hour'    ? 'selected' : '' }}>per hour</option>
                        <option value="flat_rate"   {{ old('pricing_type')=='flat_rate'   ? 'selected' : '' }}>flat rate</option>
                        <option value="free"        {{ old('pricing_type')=='free'        ? 'selected' : '' }}>free</option>
                        <option value="contact"     {{ old('pricing_type')=='contact'     ? 'selected' : '' }}>Negotiable</option>
                    </select>
                </div>
            </div>

            {{-- Live preview --}}
            <div class="mt-4 flex items-center justify-between px-4 py-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-sm font-semibold text-slate-500" id="previewTitle">Your service name</span>
                <div class="text-right">
                    <div class="text-xl font-black text-teal-800" id="previewPrice">$—</div>
                    <div class="text-xs text-teal-600 font-semibold" id="previewType">starting at</div>
                </div>
            </div>
        </div>

        {{-- Feature Bullet Points --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-1">
                Feature Bullet Points <span class="text-slate-400 font-normal">(optional)</span>
            </label>
            <p class="text-xs text-slate-400 mb-3">
                One feature per line — displayed as <span class="text-emerald-600 font-semibold">✓ checkmarks</span> on your public page
            </p>
            <textarea name="features" rows="4"
                placeholder="{{ $featurePlaceholder }}"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition resize-none font-mono">{{ old('features') }}</textarea>
        </div>

        {{-- Description --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">
                Description <span class="text-slate-400 font-normal">(optional)</span>
            </label>
            <textarea name="description" rows="3"
                placeholder="Brief additional details shown when expanded..."
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition resize-none">{{ old('description') }}</textarea>
        </div>

        {{-- Visibility toggle --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-slate-700">Show on public page</p>
                <p class="text-xs text-slate-400 mt-0.5">Inactive services are hidden from visitors</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-checked:bg-teal-700 rounded-full transition-all
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5
                            after:bg-white after:rounded-full after:h-5 after:w-5
                            after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="px-8 py-3 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl text-sm transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Save Service
            </button>
        </div>

    </form>
</div>

<script>
const ptLabels = {starting_at:'starting at',per_month:'per month',per_hour:'per hour',flat_rate:'flat rate',free:'free',contact:'Negotiable'};
function updatePreview() {
    const title = document.querySelector('[name=title]').value || 'Your service name';
    const price = document.querySelector('[name=price]').value;
    const pt    = document.querySelector('[name=pricing_type]').value;
    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewPrice').textContent = price ? '$' + parseFloat(price).toLocaleString() : '—';
    document.getElementById('previewType').textContent  = ptLabels[pt] || 'starting at';
}
document.querySelector('[name=title]').addEventListener('input', updatePreview);
document.querySelector('[name=price]').addEventListener('input', updatePreview);
document.querySelector('[name=pricing_type]').addEventListener('change', updatePreview);
</script>
@endsection
