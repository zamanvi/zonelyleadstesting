@extends('frontend.layouts._app')
@section('title', 'Account Blocked — Zonely')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <div class="w-20 h-20 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-ban text-red-500 text-3xl"></i>
        </div>
        <h1 class="text-2xl font-black text-slate-900 mb-2">Account Suspended</h1>
        <p class="text-slate-500 text-sm mb-2">Dear <strong class="text-slate-700">{{ $user->name }}</strong>,</p>
        <p class="text-slate-500 text-sm mb-6">Your account has been temporarily suspended. Please contact Zonely support to resolve this issue.</p>
        <a href="mailto:contact@zonelyleads.com"
           class="inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white font-bold px-6 py-3 rounded-2xl text-sm transition">
            <i class="fa-solid fa-envelope"></i> Contact Support
        </a>
        <div class="mt-6">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-red-500 transition">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
