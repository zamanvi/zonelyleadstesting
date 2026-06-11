@extends('frontend.layouts.__prof_app')
@section('title', 'Add Question')
@section('page-title', 'Add Question')

@section('content')
@php
    $motherCat = strtolower($user->category?->parent?->title ?? $user->category?->title ?? '');
    $isHealthcare = str_contains($motherCat, 'health') || str_contains($motherCat, 'wellness') || str_contains($motherCat, 'medical');
    $isHome       = str_contains($motherCat, 'home')   || str_contains($motherCat, 'repair')  || str_contains($motherCat, 'service');
    $isBeauty     = str_contains($motherCat, 'beauty') || str_contains($motherCat, 'personal care') || str_contains($motherCat, 'salon');

    $faqSuggestions = $isHealthcare ? [
        'Are you accepting new patients?',
        'Do you accept insurance?',
        'How do I book an appointment?',
        'What are your office hours?',
        'Do you offer telehealth visits?',
        'What should I bring to my first visit?',
    ] : ($isHome ? [
        'Do you offer free estimates?',
        'Are you licensed and insured?',
        'How quickly can you start the job?',
        'Do you guarantee your work?',
        'What areas do you serve?',
        'Do you clean up after the job?',
    ] : ($isBeauty ? [
        'How do I book an appointment?',
        'What is your cancellation policy?',
        'Do you offer a consultation before the service?',
        'What products do you use?',
        'How long does the service take?',
        'Do you do bridal or group bookings?',
    ] : [
        'How quickly do you respond?',
        'Do you offer virtual/remote services?',
        'What are your hours?',
        'How do I get started?',
        'Do you offer free consultations?',
        'What payment methods do you accept?',
    ]));
@endphp
<div class="pb-10 max-w-2xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('user.faqs.index') }}"
           class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-500 hover:text-teal-700 hover:border-teal-300 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Add FAQ</h1>
            <p class="text-xs text-gray-500 mt-0.5">Appears in the Q&A section of your public page</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Suggestions --}}
    <div class="mb-4 p-4 bg-slate-50 border border-slate-100 rounded-2xl">
        <p class="text-xs font-bold text-slate-600 mb-2">Common questions clients ask:</p>
        <div class="flex flex-wrap gap-2" id="suggestions">
            @foreach($faqSuggestions as $s)
            <button type="button" onclick="fillQuestion(this)"
                class="text-xs px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-slate-600 hover:border-teal-400 hover:text-teal-800 transition">
                {{ $s }}
            </button>
            @endforeach
        </div>
    </div>

    <form action="{{ route('user.faqs.store') }}" method="POST" class="space-y-4">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">
                Question <span class="text-red-500">*</span>
            </label>
            <input type="text" name="question" id="questionInput" value="{{ old('question') }}" required
                placeholder="e.g. How quickly do you respond to inquiries?"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <label class="block text-sm font-bold text-slate-700 mb-2">
                Answer <span class="text-red-500">*</span>
            </label>
            <textarea name="answer" rows="4" required
                placeholder="Provide a clear, helpful answer..."
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition resize-none">{{ old('answer') }}</textarea>
            <p class="text-xs text-slate-400 mt-1.5">Max 2,000 characters</p>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="px-8 py-3 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl text-sm transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Save Question
            </button>
        </div>
    </form>
</div>

<script>
function fillQuestion(btn) {
    document.getElementById('questionInput').value = btn.textContent.trim();
    document.getElementById('questionInput').focus();
}
</script>
@endsection
