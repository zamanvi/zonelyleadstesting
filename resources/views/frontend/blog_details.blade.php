@php
    $meta_title       = $blog->name;
    $meta_description = $blog->short_description ?? Str::limit(strip_tags($blog->description ?? ''), 160);
@endphp
@extends('frontend.layouts._app')
@section('title', $blog->name)
@section('og_title', $blog->name)
@section('og_description', $meta_description)
@section('og_image', $blog->image_path ? get_file($blog->image_path, 'blog') : asset('frontend/img/zonely_logo.png'))

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{{ addslashes($blog->name) }}",
  "description": "{{ addslashes(Str::limit(strip_tags($blog->short_description ?? $blog->description ?? ''), 200)) }}",
  "url": "{{ url()->current() }}",
  "datePublished": "{{ $blog->created_at?->toIso8601String() }}",
  "dateModified": "{{ $blog->updated_at?->toIso8601String() }}",
  @if($blog->image_path ?? false)
  "image": "{{ get_file($blog->image_path, 'blog') }}",
  @endif
  "publisher": {
    "@type": "Organization",
    "name": "Zonely",
    "logo": { "@type": "ImageObject", "url": "{{ asset('frontend/img/zonely_logo.png') }}" }
  }
}
</script>
@endsection

@section('content')
<div class="mt-16 sm:mt-20 max-w-3xl mx-auto px-4 sm:px-6 pt-8 pb-16">

    {{-- Back --}}
    <a href="{{ route('frontend.blog') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-teal-700 mb-6 transition" style="min-height:unset;">
        <i class="fa-solid fa-arrow-left"></i> Back to Blog
    </a>

    {{-- Hero image --}}
    @if($blog->image_path ?? false)
    <div class="w-full aspect-video rounded-3xl overflow-hidden mb-8 bg-slate-100">
        <img src="{{ get_file($blog->image_path, 'blog') }}"
             class="w-full h-full object-cover"
             alt="{{ $blog->name }}" loading="eager">
    </div>
    @endif

    {{-- Title --}}
    <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl text-slate-900 leading-tight mb-4">
        {{ $blog->name }}
    </h1>

    {{-- Meta --}}
    <div class="flex items-center gap-4 text-xs text-slate-400 mb-8 pb-6 border-b border-slate-100">
        @if($blog->created_at ?? false)
        <span><i class="fa-solid fa-calendar mr-1"></i>{{ $blog->created_at->format('M d, Y') }}</span>
        @endif
        @if($blog->pageview ?? false)
        <span><i class="fa-solid fa-eye mr-1"></i>{{ number_format($blog->pageview) }} views</span>
        @endif
    </div>

    {{-- Content --}}
    <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base
                prose-headings:font-bold prose-headings:text-slate-900
                prose-a:text-teal-700 prose-a:no-underline hover:prose-a:underline
                prose-img:rounded-2xl prose-img:mx-auto">
        {!! $blog->description !!}
    </div>

    {{-- Share --}}
    <div class="mt-10 pt-6 border-t border-slate-100 flex flex-wrap items-center gap-3">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Share:</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
           class="flex items-center gap-2 px-4 py-2 bg-teal-50 text-teal-800 rounded-xl text-xs font-bold hover:bg-teal-100 transition" style="min-height:unset;">
            <i class="fab fa-facebook"></i> Facebook
        </a>
        <a href="https://wa.me/?text={{ urlencode($blog->name . ' ' . url()->current()) }}" target="_blank" rel="noopener"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold hover:bg-emerald-100 transition" style="min-height:unset;">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
    </div>

</div>
@endsection
