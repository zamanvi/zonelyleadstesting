@extends('frontend.layouts.__prof_app')
@section('title', 'Add Certification')
@section('page-title', 'Add Certification')

@section('content')
@php
    $motherCat = strtolower($user->category?->parent?->title ?? $user->category?->title ?? '');
    $isHealthcare = str_contains($motherCat, 'health') || str_contains($motherCat, 'wellness') || str_contains($motherCat, 'medical');
    $isHome       = str_contains($motherCat, 'home')   || str_contains($motherCat, 'repair')  || str_contains($motherCat, 'service');
    $isBeauty     = str_contains($motherCat, 'beauty') || str_contains($motherCat, 'personal care') || str_contains($motherCat, 'salon');

    $certNamePlaceholder   = $isHealthcare ? 'e.g. MD, Board Certification in Internal Medicine'
        : ($isHome    ? 'e.g. Master Plumber License, OSHA 10 Certified'
        : ($isBeauty  ? 'e.g. Cosmetology License, Keratin Treatment Certified'
        :               'e.g. CPA, CFA, Series 65, PMP'));

    $certIssuerPlaceholder = $isHealthcare ? 'e.g. American Board of Internal Medicine'
        : ($isHome    ? 'e.g. NYC Dept. of Buildings, OSHA'
        : ($isBeauty  ? 'e.g. NY State Division of Licensing Services'
        :               'e.g. AICPA, CFA Institute, FINRA'));

    $credIdPlaceholder = $isHealthcare ? 'e.g. NPI #1234567890'
        : ($isHome    ? 'e.g. License #MPL-56789'
        : ($isBeauty  ? 'e.g. License #COS-12345'
        :               'e.g. License #123456'));
@endphp
<div class="pb-10 max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('user.certifications.index') }}"
           class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-teal-700 hover:border-teal-300 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Add Certification</h1>
            <p class="text-xs text-gray-500 mt-0.5">Licenses, certifications, professional credentials</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('user.certifications.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">Certification Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                placeholder="{{ $certNamePlaceholder }}"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">Issuing Organization</label>
            <input type="text" name="issuer" value="{{ old('issuer') }}"
                placeholder="{{ $certIssuerPlaceholder }}"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Issue Year</label>
                    <input type="text" name="issued_year" value="{{ old('issued_year') }}"
                        placeholder="e.g. 2015"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Expiry Year <span class="text-slate-400 font-normal">(if any)</span></label>
                    <input type="text" name="expiry_year" value="{{ old('expiry_year') }}"
                        placeholder="e.g. 2027"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">Credential ID <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="text" name="credential_id" value="{{ old('credential_id') }}"
                placeholder="{{ $credIdPlaceholder }}"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl text-sm transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Save
            </button>
        </div>
    </form>
</div>
@endsection
