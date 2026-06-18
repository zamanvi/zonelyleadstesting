<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\PostalCode;
use App\Models\State;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user  = $request->user();
        $layout = $user->seller_service_type ? '__prof_app' : '__app';
        $_next  = $request->_next ?? 'business';
        return view('frontend.dashboard', compact('user', 'layout', '_next'));
    }

    public function profile(): View
    {
        $user = Auth::user();
        return view('frontend.profile.edit.index', compact('user'));
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        return view('frontend.profile.edit.index', compact('user'));
    }

    public function typeProfile(Request $request, $type, $setup)
    {
        $user       = auth()->user();
        $categories = [];
        $countries  = [];

        if ($setup === 'service_location') {
            $userCat = $user->category;
            if ($userCat && $userCat->parent_id) {
                // user has a child category → show siblings (children of same parent)
                $categories = Category::where('parent_id', $userCat->parent_id)->get();
            } elseif ($userCat) {
                // user has a parent-level category → show its children
                $categories = Category::where('parent_id', $userCat->id)->get();
            } else {
                $categories = collect();
            }
            $countries = Country::all();
        }

        $view = match ($setup) {
            'account'          => 'frontend.profile.edit.account',
            'service_location' => 'frontend.profile.edit.service_location',
            'contact'          => 'frontend.profile.edit.contact',
            'profile'          => 'frontend.profile.edit.profile',
            'review'           => 'frontend.profile.edit.review',
            default            => abort(404),
        };

        return view($view, compact('user', 'type', 'categories', 'countries'));
    }

    public function typeSellerProfile(Request $request, $type, $setup)
    {
        $user = auth()->user();

        if ($setup === 'account') {
            $request->validate([
                'name'          => 'required|string|max:255',
                'phone'         => 'nullable|string|max:50',
                'business_name' => 'nullable|string|max:255',
            ]);
            try {
                $user->update($request->only(['name', 'phone', 'business_name']));
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['save_error' => 'Could not save: ' . $e->getMessage()]);
            }
            return back()->with('success', 'Business info saved.');

        } elseif ($setup === 'service_location') {
            $request->validate([
                'category_id'        => 'nullable|integer|exists:categories,id',
                'country'            => 'nullable|integer',
                'state'              => 'nullable|integer',
                'city'               => 'nullable|integer',
                'zip_code'           => 'nullable|integer',
                'additional_details' => 'nullable|string|max:500',
            ]);
            try {
                $user->update([
                    'category_id'        => $request->category_id ?: null,
                    'country'            => $request->country ? (Country::find($request->country)?->title ?? $request->country) : null,
                    'state'              => $request->state   ? (State::find($request->state)?->title   ?? $request->state)   : null,
                    'city'               => $request->city    ? (City::find($request->city)?->title     ?? $request->city)    : null,
                    'zip_code'           => $request->zip_code ? (PostalCode::find($request->zip_code)?->code ?? $request->zip_code) : null,
                    'additional_details' => $request->additional_details,
                ]);
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['save_error' => 'Could not save: ' . $e->getMessage()]);
            }
            return back()->with('success', 'Location saved.');

        } elseif ($setup === 'contact') {
            $request->validate([
                'phone'    => 'nullable|string|max:50',
                'whatsapp' => 'nullable|string|max:50',
            ]);
            try {
                $user->update([
                    'phone'      => $request->phone,
                    'whatsapp'   => $request->whatsapp,
                    'show_phone' => $request->boolean('show_phone'),
                ]);
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['save_error' => 'Could not save: ' . $e->getMessage()]);
            }
            return back()->with('success', 'Contact info saved.');

        } elseif ($setup === 'profile') {
            $request->validate([
                'bio'           => 'nullable|string|max:200',
                'about'         => 'nullable|string|max:3000',
                'title'         => 'nullable|string|max:255',
                'experience'    => 'nullable|integer|min:0|max:99',
                'profile_photo' => 'nullable|image|max:10240',
            ]);

            if ($request->hasFile('profile_photo')) {
                try {
                    $user->profile_photo = ImageOptimizer::saveProfilePhoto($request->file('profile_photo'));
                } catch (\Throwable $e) {
                    try {
                        $path = $request->file('profile_photo')->store('profiles', 'public');
                        $user->profile_photo = 'storage/' . $path;
                    } catch (\Throwable $e2) {
                        // photo save failed silently
                    }
                }
            }

            $user->bio        = $request->bio;
            $user->about      = $request->about;
            $user->title      = $request->title;
            $user->experience = $request->experience;

            try {
                $user->save();
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['save_error' => 'Could not save profile: ' . $e->getMessage()]);
            }

            return back()->with('success', 'Profile saved successfully.');

        } elseif ($setup === 'review') {
            return redirect()->route('seller.dashboard')->with('success', 'Profile completed!');

        } else {
            abort(404);
        }
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile information updated');
    }

    public function profileUpdateDashboard(Request $request)
    {
        $request->validate([
            'name'                => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:50',
            'whatsapp'            => 'nullable|string|max:50',
            'bio'                 => 'nullable|string|max:5000',
            'about'               => 'nullable|string|max:10000',
            'work_address'        => 'nullable|string|max:500',
            'designation'         => 'nullable|string|max:255',
            'business_name'       => 'nullable|string|max:255',
            'seller_service_type' => 'nullable|string|max:255',
            'experience'          => 'nullable|string|max:255',
            'country'             => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'city'                => 'nullable|string|max:100',
            'zip_code'            => 'nullable|string|max:20',
            'tags'                => 'nullable|string|max:500',
            'show_phone'          => 'nullable|boolean',
        ]);

        $allowed = ['name', 'phone', 'whatsapp', 'bio', 'about', 'work_address',
                    'designation', 'business_name', 'seller_service_type', 'experience',
                    'country', 'state', 'city', 'zip_code', 'tags', 'show_phone'];

        $user = auth()->user();
        $user->update($request->only($allowed));

        $_next = $request->_next;
        $url = route('user.dashboard') . ($_next ? '?_next=' . urlencode($_next) : '');
        return redirect($url);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function blockedlist()
    {
        $user = Auth::user();
        return view('frontend.blocked', compact('user'));
    }
}
