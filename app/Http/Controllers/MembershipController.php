<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index()
    {
        $memberships = Membership::where('user_id', auth()->id())->paginate(10);
        return view('frontend.profile.memberships.index', compact('memberships'));
    }

    public function create()
    {
        $user = auth()->user()->load('category');
        return view('frontend.profile.memberships.create', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'start'   => 'nullable|string|max:20',
            'end'     => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Membership::create(array_merge($validated, ['user_id' => auth()->id()]));

        return redirect()->route('user.memberships.index')->with('success', 'Membership added.');
    }

    public function edit($id)
    {
        $membership = Membership::where('user_id', auth()->id())->findOrFail($id);
        return view('frontend.profile.memberships.edit', compact('membership'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'start'   => 'nullable|string|max:20',
            'end'     => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Membership::where('user_id', auth()->id())->findOrFail($id)->update($validated);

        return redirect()->route('user.memberships.index')->with('success', 'Membership updated.');
    }

    public function destroy($id)
    {
        Membership::where('user_id', auth()->id())->findOrFail($id)->delete();
        return redirect()->route('user.memberships.index')->with('success', 'Membership deleted.');
    }
}
