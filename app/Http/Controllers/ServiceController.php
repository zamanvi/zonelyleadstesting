<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('user_id', Auth::id())->paginate(10);
        return view('frontend.profile.services.index', compact('services'));
    }

    public function create()
    {
        $user = Auth::user()->load('category');
        return view('frontend.profile.services.create', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'price'        => 'nullable|numeric|min:0',
            'pricing_type' => 'nullable|string|max:50',
            'features'     => 'nullable|string',
        ]);
        $validated['is_active']   = $request->boolean('is_active');
        $validated['user_id']     = Auth::id();
        $validated['category_id'] = Auth::user()->category_id;

        Service::create($validated);

        return redirect()->route('user.services.index')->with('success', 'Service added.');
    }

    public function edit(string $id)
    {
        $service = Service::where('user_id', Auth::id())->findOrFail($id);
        return view('frontend.profile.services.edit', compact('service'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'price'        => 'nullable|numeric|min:0',
            'pricing_type' => 'nullable|string|max:50',
            'features'     => 'nullable|string',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        Service::where('user_id', Auth::id())->findOrFail($id)->update($validated);

        return redirect()->route('user.services.index')->with('success', 'Service updated.');
    }

    public function destroy(string $id)
    {
        Service::where('user_id', Auth::id())->findOrFail($id)->delete();
        return redirect()->route('user.services.index')->with('success', 'Service deleted.');
    }
}
