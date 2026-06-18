<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManagerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = User::whereIn('type', ['manager', 'coo'])
            ->with('managerProfile')
            ->latest()
            ->paginate(20);

        return view('admin.managers.index', compact('managers'));
    }

    public function create()
    {
        $modules = ManagerProfile::MODULES;
        return view('admin.managers.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $isGeneral = $request->role === 'general_manager';

        if ($isGeneral && auth()->user()?->type === 'coo') {
            abort(403, 'General Manager cannot create another General Manager.');
        }

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'city'     => 'nullable|string|max:100',
            'state'    => 'nullable|string|max:100',
            'country'  => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
        ];

        if (!$isGeneral) {
            $rules['modules']   = 'required|array|min:1';
            $rules['modules.*'] = 'in:' . implode(',', array_keys(ManagerProfile::MODULES));
        }

        $request->validate($rules);

        $user = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'type'               => $isGeneral ? 'coo' : 'manager',
            'status'             => true,
            'email_verified_at'  => now(),
            'city'               => $request->city,
            'state'              => $request->state,
            'country'            => $request->country,
            'zip_code'           => $request->zip_code,
            'slug'               => generateUniqueSlug(User::class, $request->name),
        ]);

        $loginUrl = route('user.login');

        ManagerProfile::create([
            'user_id'        => $user->id,
            'modules'        => $isGeneral ? [] : $request->modules,
            'status'         => 'active',
            'notes'          => $request->notes,
            'plain_password' => encrypt($request->password),
            'login_url'      => $loginUrl,
        ]);

        $role = $isGeneral ? 'General Manager' : 'Manager';
        return redirect()->route('admin.managers.index')
            ->with('success', $user->name . ' created as ' . $role . '.');
    }

    public function edit($id)
    {
        if (auth()->user()?->type === 'coo') abort(403, 'COO cannot edit managers.');
        $manager = User::whereIn('type', ['manager', 'coo'])->with('managerProfile')->findOrFail($id);
        $modules = ManagerProfile::MODULES;
        return view('admin.managers.edit', compact('manager', 'modules'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()?->type === 'coo') abort(403, 'COO cannot edit managers.');
        $manager = User::whereIn('type', ['manager', 'coo'])->findOrFail($id);

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $manager->id,
        ];

        if ($manager->type !== 'coo') {
            $rules['modules']    = 'required|array|min:1';
            $rules['modules.*']  = 'in:' . implode(',', array_keys(ManagerProfile::MODULES));
        }

        $request->validate($rules);

        $manager->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        $profileData = [
            'modules' => $manager->type === 'coo' ? [] : $request->modules,
            'status'  => $request->status ?? 'active',
            'notes'   => $request->notes,
        ];

        if ($request->filled('password')) {
            $manager->update(['password' => Hash::make($request->password)]);
            $profileData['plain_password'] = encrypt($request->password);
        }

        $manager->managerProfile()->updateOrCreate(
            ['user_id' => $manager->id],
            $profileData
        );

        return redirect()->route('admin.managers.index')
            ->with('success', $manager->name . ' updated.');
    }

    public function destroy($id)
    {
        if (auth()->user()?->type === 'coo') abort(403, 'COO cannot remove managers.');
        $manager = User::whereIn('type', ['manager', 'coo'])->findOrFail($id);
        $manager->delete();
        return redirect()->route('admin.managers.index')
            ->with('success', 'Manager removed.');
    }
}
