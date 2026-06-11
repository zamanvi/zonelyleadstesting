<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificationController extends Controller
{
    public function index()
    {
        $certifications = Auth::user()->certifications()->get();
        return view('frontend.profile.certifications.index', compact('certifications'));
    }

    public function create()
    {
        $user = Auth::user()->load('category');
        return view('frontend.profile.certifications.create', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'issuer'        => 'nullable|string|max:255',
            'issued_year'   => 'nullable|string|max:20',
            'expiry_year'   => 'nullable|string|max:20',
            'credential_id' => 'nullable|string|max:255',
        ]);
        $data['user_id'] = Auth::id();
        Certification::create($data);
        return redirect()->route('user.certifications.index')->with('success', 'Certification added.');
    }

    public function edit($id)
    {
        $certification = Certification::where('user_id', Auth::id())->findOrFail($id);
        return view('frontend.profile.certifications.edit', compact('certification'));
    }

    public function update(Request $request, $id)
    {
        $certification = Certification::where('user_id', Auth::id())->findOrFail($id);
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'issuer'        => 'nullable|string|max:255',
            'issued_year'   => 'nullable|string|max:20',
            'expiry_year'   => 'nullable|string|max:20',
            'credential_id' => 'nullable|string|max:255',
        ]);
        $certification->update($data);
        return redirect()->route('user.certifications.index')->with('success', 'Certification updated.');
    }

    public function destroy($id)
    {
        Certification::where('user_id', Auth::id())->findOrFail($id)->delete();
        return back()->with('success', 'Certification deleted.');
    }
}
