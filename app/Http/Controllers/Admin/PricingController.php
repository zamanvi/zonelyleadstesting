<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\PlatformCharge;
use App\Models\Setting;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricingController extends Controller
{
    public function index(Request $request)
    {
        $charges = PlatformCharge::with(['category', 'state', 'city', 'creator'])
            ->when($request->type,        fn($q) => $q->where('type', $request->type))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->state_id,    fn($q) => $q->where('state_id', $request->state_id))
            ->when($request->status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $categories      = Category::whereNull('parent_id')->with('children')->orderBy('title')->get();
        $states          = State::orderBy('title')->get();
        $defaultLeadFee       = Setting::where('key', 'default_lead_fee')->value('value') ?? 35;
        $defaultAffComm       = Setting::where('key', 'default_affiliate_commission')->value('value') ?? 10;
        $defaultBuyerRefComm  = Setting::where('key', 'default_buyer_referral_commission')->value('value') ?? 5;

        // Preview resolver
        $preview = null;
        if ($request->filled('preview_type')) {
            $preview = [
                'type'     => $request->preview_type,
                'amount'   => PlatformCharge::resolve(
                    $request->preview_type,
                    $request->preview_category,
                    $request->preview_state,
                    $request->preview_city,
                ),
                'category' => $request->preview_category ? Category::find($request->preview_category)?->title : null,
                'state'    => $request->preview_state    ? State::find($request->preview_state)?->title       : null,
                'city'     => $request->preview_city     ? City::find($request->preview_city)?->title         : null,
            ];
        }

        return view('admin.pricing.index', compact(
            'charges', 'categories', 'states',
            'defaultLeadFee', 'defaultAffComm', 'defaultBuyerRefComm', 'preview'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'             => 'required|in:lead_fee,affiliate_commission,buyer_referral_commission',
            'category_id'      => 'nullable|exists:categories,id',
            'state_id'         => 'nullable|exists:states,id',
            'city_id'          => 'nullable|exists:cities,id',
            'amount'           => 'required|numeric|min:0|max:9999',
            'effective_from'   => 'required|date',
            'effective_to'     => 'nullable|date|after_or_equal:effective_from',
            'priority'         => 'nullable|integer|min:0|max:999',
            'is_promotion'     => 'nullable|boolean',
            'promotion_label'  => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
        ]);

        $data['is_active']    = true;
        $data['created_by']   = Auth::id();
        $data['priority']   ??= 0;
        $data['is_promotion'] = $request->boolean('is_promotion');

        PlatformCharge::create($data);
        PlatformCharge::bustCache();

        return back()->with('success', 'Pricing rule created.');
    }

    public function update(Request $request, $id)
    {
        $charge = PlatformCharge::findOrFail($id);

        $data = $request->validate([
            'type'             => 'required|in:lead_fee,affiliate_commission,buyer_referral_commission',
            'category_id'      => 'nullable|exists:categories,id',
            'state_id'         => 'nullable|exists:states,id',
            'city_id'          => 'nullable|exists:cities,id',
            'amount'           => 'required|numeric|min:0|max:9999',
            'effective_from'   => 'required|date',
            'effective_to'     => 'nullable|date|after_or_equal:effective_from',
            'priority'         => 'nullable|integer|min:0|max:999',
            'is_promotion'     => 'nullable|boolean',
            'promotion_label'  => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
        ]);

        $data['is_promotion'] = $request->boolean('is_promotion');

        $charge->update($data);
        PlatformCharge::bustCache();

        return back()->with('success', 'Pricing rule updated.');
    }

    public function toggle($id)
    {
        $charge = PlatformCharge::findOrFail($id);
        $charge->update(['is_active' => !$charge->is_active]);
        PlatformCharge::bustCache();
        return back()->with('success', 'Rule ' . ($charge->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy($id)
    {
        PlatformCharge::findOrFail($id)->delete();
        PlatformCharge::bustCache();
        return back()->with('success', 'Rule archived (soft deleted).');
    }

    public function updateDefaults(Request $request)
    {
        $request->validate([
            'default_lead_fee'                  => 'required|numeric|min:0|max:9999',
            'default_affiliate_commission'      => 'required|numeric|min:0|max:9999',
            'default_buyer_referral_commission' => 'required|numeric|min:0|max:9999',
        ]);

        foreach (['default_lead_fee', 'default_affiliate_commission', 'default_buyer_referral_commission'] as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->$key]);
        }
        PlatformCharge::bustCache();

        return back()->with('success', 'Global defaults saved.');
    }

    public function citiesByState($stateId)
    {
        $cities = City::where('state_id', $stateId)->orderBy('title')->get(['id', 'title']);
        return response()->json($cities);
    }
}
