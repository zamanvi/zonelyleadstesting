<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::where('user_id', Auth::id())->orderBy('sort_order')->orderBy('id')->get();
        return view('frontend.profile.faqs.index', compact('faqs'));
    }

    public function create()
    {
        $user = Auth::user()->load('category');
        return view('frontend.profile.faqs.create', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question'   => 'required|string|max:500',
            'answer'     => 'required|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['user_id']    = Auth::id();
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Faq::create($validated);
        return redirect()->route('user.faqs.index')->with('success', 'Question added.');
    }

    public function edit(Faq $faq)
    {
        abort_if($faq->user_id !== Auth::id(), 403);
        return view('frontend.profile.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        abort_if($faq->user_id !== Auth::id(), 403);
        $validated = $request->validate([
            'question'   => 'required|string|max:500',
            'answer'     => 'required|string|max:2000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $faq->update($validated);
        return redirect()->route('user.faqs.index')->with('success', 'Question updated.');
    }

    public function destroy(Faq $faq)
    {
        abort_if($faq->user_id !== Auth::id(), 403);
        $faq->delete();
        return redirect()->route('user.faqs.index')->with('success', 'Question deleted.');
    }
}
