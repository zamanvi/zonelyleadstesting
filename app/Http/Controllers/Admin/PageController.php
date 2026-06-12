<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\Blog;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Lead;
use App\Models\PostalCode;
use App\Models\Setting;
use App\Models\State;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    function admin_dashboard()
    {
        // User stats — 1 query instead of 3
        $userStats = User::selectRaw("
            SUM(CASE WHEN type='seller' THEN 1 ELSE 0 END) as sellers,
            SUM(CASE WHEN type='user' THEN 1 ELSE 0 END) as buyers,
            SUM(CASE WHEN type='seller' AND status=0 THEN 1 ELSE 0 END) as unverified
        ")->first();
        $sellers    = (int) ($userStats->sellers ?? 0);
        $buyers     = (int) ($userStats->buyers ?? 0);
        $unverified = (int) ($userStats->unverified ?? 0);
        $staffCount = StaffProfile::count();

        // Lead stats — 1 query instead of 5
        $leadStats = Lead::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status='new' THEN 1 ELSE 0 END) as new_count,
            SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) as won_count,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status='lost' THEN 1 ELSE 0 END) as lost_count,
            SUM(CASE WHEN paid_at IS NOT NULL THEN fee ELSE 0 END) as revenue,
            SUM(CASE WHEN paid_at IS NULL THEN fee ELSE 0 END) as pending_rev
        ")->first();
        $totalLeads = (int) ($leadStats->total ?? 0);
        $newLeads   = (int) ($leadStats->new_count ?? 0);
        $wonLeads   = (int) ($leadStats->won_count ?? 0);
        $revenue    = (float) ($leadStats->revenue ?? 0);
        $pendingRev = (float) ($leadStats->pending_rev ?? 0);
        $leadStatusData = [
            'new'     => $newLeads,
            'pending' => (int) ($leadStats->pending_count ?? 0),
            'won'     => $wonLeads,
            'lost'    => (int) ($leadStats->lost_count ?? 0),
        ];

        // Affiliate — 1 query instead of 2
        $commStats  = AffiliateCommission::selectRaw("
            SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) as pending_amount,
            SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) as paid_amount
        ")->first();
        $pendingComm = (float) ($commStats->pending_amount ?? 0);
        $paidComm    = (float) ($commStats->paid_amount ?? 0);

        $blogCount = Blog::count();
        $catCount  = Category::count();
        $cityCount = City::count();

        // Monthly chart data — 2 GROUP BY queries instead of 12
        $chartStart   = now()->subMonths(5)->startOfMonth();
        $rawLeadMonths = Lead::where('created_at', '>=', $chartStart)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->pluck('cnt', 'ym');
        $rawUserMonths = User::whereIn('type', ['seller', 'user'])
            ->where('created_at', '>=', $chartStart)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->pluck('cnt', 'ym');

        $leadMonths = $leadCounts = $userCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $leadMonths[] = $month->format('M');
            $leadCounts[] = (int) ($rawLeadMonths[$key] ?? 0);
            $userCounts[] = (int) ($rawUserMonths[$key] ?? 0);
        }

        $recentSellers     = User::where('type', 'seller')->with('category:id,title')->latest()->take(5)->get();
        $pendingVerify     = User::where('type', 'seller')->where('status', false)->latest()->take(5)->get();
        $recentLeads       = Lead::with('seller:id,name')->latest()->take(5)->get();
        $recentCommissions = AffiliateCommission::with('referrer:id,name')->latest()->take(5)->get();

        $staffRoleCounts = StaffProfile::selectRaw('role, count(*) as cnt')
            ->groupBy('role')->pluck('cnt', 'role');

        return view('admin.index2', compact(
            'sellers', 'buyers', 'unverified', 'staffCount',
            'totalLeads', 'newLeads', 'wonLeads', 'revenue', 'pendingRev',
            'pendingComm', 'paidComm', 'blogCount', 'catCount', 'cityCount',
            'leadMonths', 'leadCounts', 'userCounts',
            'recentSellers', 'pendingVerify', 'recentLeads', 'recentCommissions',
            'leadStatusData', 'staffRoleCounts'
        ));
    }
    public function profiles_index(Request $request)
    {
        $status = $request->query('status');
        $type   = $request->query('type');
        $search = trim($request->query('search', ''));
        $isManager = auth()->user()?->type === 'manager';

        $query = User::latest();

        // Managers and COO never see admin/coo/manager accounts
        if ($isManager || auth()->user()?->type === 'coo') {
            $query->whereNotIn('type', ['admin', 'coo', 'manager']);
        }

        if ($status === 'verified') {
            $query->where('status', true);
        } elseif ($status === 'unverified') {
            $query->where('status', false);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(50)->withQueryString();

        $sellerStats = [
            'today'   => User::where('type','seller')->whereDate('created_at', today())->count(),
            'week'    => User::where('type','seller')->whereBetween('created_at',[now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month'   => User::where('type','seller')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'year'    => User::where('type','seller')->whereYear('created_at', now()->year)->count(),
            'total'   => User::where('type','seller')->count(),
            'disabled'=> User::where('type','seller')->where('status', false)->count(),
        ];

        return view('admin.profiles2.index', compact('users', 'status', 'type', 'search', 'sellerStats'));
    }

    public function profiles_verify($id)
    {
        $user = User::findOrFail($id);
        $actorType = auth()->user()?->type;
        if (in_array($actorType, ['manager', 'coo']) && in_array($user->type, ['admin', 'coo', 'manager'])) {
            abort(403);
        }
        $user->update(['status' => true]);
        return back()->with('success', $user->name . ' verified successfully.');
    }
    function profiles_edit($id)
    {
        $target = User::findOrFail($id);
        $actorType = auth()->user()?->type;
        if (in_array($actorType, ['manager', 'coo']) && in_array($target->type, ['admin', 'coo', 'manager'])) {
            abort(403, 'Access denied.');
        }
        $user = $target;
        return view('admin.profiles2.edit', compact('user'));
    }
    public function profiles_update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $actorType = auth()->user()?->type;
        if (in_array($actorType, ['manager', 'coo']) && in_array($user->type, ['admin', 'coo', 'manager'])) {
            abort(403, 'Access denied.');
        }

        $actor = auth()->user()?->type;
        $allowedTypes = in_array($actor, ['manager', 'coo'])
            ? 'required|in:staff,seller,user'
            : 'required|in:admin,coo,staff,manager,seller,user';

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email,'.$id,
            'phone'               => 'nullable|string|max:50',
            'whatsapp'            => 'nullable|string|max:50',
            'designation'         => 'nullable|string|max:255',
            'title'               => 'nullable|string|max:255',
            'type'                => $allowedTypes,
            'status'              => 'required|boolean',
            'remark'              => 'nullable|string',
            'bio'                 => 'nullable|string',
            'about'               => 'nullable|string',
            'work_address'        => 'nullable|string|max:500',
            'business_name'       => 'nullable|string|max:255',
            'seller_service_type' => 'nullable|string|max:255',
            'experience'          => 'nullable|string|max:255',
            'city'                => 'nullable|string|max:255',
            'state'               => 'nullable|string|max:255',
            'country'             => 'nullable|string|max:255',
            'zip_code'            => 'nullable|string|max:20',
            'tags'                => 'nullable|string',
            'category_id'         => 'nullable|exists:categories,id',
            'twilio_enabled'      => 'nullable|boolean',
        ]);

        $validated['twilio_enabled'] = $request->boolean('twilio_enabled');

        $oldStatus = $user->status;
        $newStatus = (bool) $validated['status'];
        $user->update($validated);

        if ($user->type === 'seller' && $oldStatus !== $newStatus) {
            $supportEmail    = Setting::get('support_email', 'support@zonely.com');
            $supportWhatsapp = Setting::get('support_whatsapp', '');
            $waLink          = $supportWhatsapp ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $supportWhatsapp) : null;

            if (!$newStatus) {
                // Account disabled
                Mail::raw(
                    "Hi {$user->name},\n\n"
                    . "Your Zonely seller account has been temporarily suspended by our admin team.\n\n"
                    . "This may be due to incomplete profile information, a policy concern, or a routine review. "
                    . "Please contact us immediately so we can resolve this quickly.\n\n"
                    . "Contact us:\n"
                    . "📧 Email: {$supportEmail}\n"
                    . ($waLink ? "💬 WhatsApp: {$supportWhatsapp}\n" : '')
                    . "\nWe aim to resolve all account issues within 24 hours.\n\n"
                    . "— Zonely Admin Team",
                    fn($m) => $m->to($user->email)->subject('Your Zonely Account Has Been Suspended')
                );
            } else {
                // Account re-enabled
                Mail::raw(
                    "Hi {$user->name},\n\n"
                    . "Great news! Your Zonely seller account has been reactivated and is now live again.\n\n"
                    . "Your profile is visible to buyers and you can start receiving leads immediately.\n\n"
                    . "If you have any questions, feel free to contact us:\n"
                    . "📧 Email: {$supportEmail}\n"
                    . ($waLink ? "💬 WhatsApp: {$supportWhatsapp}\n" : '')
                    . "\nWelcome back!\n\n"
                    . "— Zonely Admin Team",
                    fn($m) => $m->to($user->email)->subject('Your Zonely Account Has Been Reactivated')
                );
            }
        }

        return redirect()
            ->route('admin.profiles.index', ['status' => $user->fresh()->status ? 'verified' : 'unverified'])
            ->with('success', 'Profile updated successfully.');
    }
    function profiles_destroy($id)
    {
        $target = User::findOrFail($id);
        $actorType = auth()->user()?->type;
        if (in_array($actorType, ['manager', 'coo']) && in_array($target->type, ['admin', 'coo', 'manager'])) {
            abort(403, 'Access denied.');
        }
        $target->delete();
        return redirect()->route('admin.profiles.index')->with('success', 'User deleted.');
    }
    public function leads()
    {
        // ── All-time payment stats ──────────────────────
        $s = Lead::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN paid_at IS NOT NULL THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN paid_at IS NULL THEN 1 ELSE 0 END) as unpaid_count,
            SUM(CASE WHEN paid_at IS NOT NULL THEN fee ELSE 0 END) as revenue,
            SUM(CASE WHEN paid_at IS NULL THEN fee ELSE 0 END) as pending_revenue
        ")->first();

        // Overdue sellers: count via subquery to avoid N+1
        // A seller is overdue when unpaid lead count >= their lead_threshold (default 3)
        $defaultThreshold = (int) (\App\Models\Setting::where('key', 'default_lead_threshold')->value('value') ?? 3);
        $overdueSellersCount = User::where('type', 'seller')
            ->where('status', true)
            ->whereHas('leads', fn($q) => $q->whereNull('paid_at'), '>=', $defaultThreshold)
            ->count();

        // ── Time-based lead counts ──────────────────────
        $stats = [
            'total'           => (int)   ($s->total ?? 0),
            'paid'            => (int)   ($s->paid_count ?? 0),
            'unpaid'          => (int)   ($s->unpaid_count ?? 0),
            'revenue'         => (float) ($s->revenue ?? 0),
            'pending_revenue' => (float) ($s->pending_revenue ?? 0),
            'today'           => Lead::whereDate('created_at', today())->count(),
            'week'            => Lead::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month'           => Lead::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'year'            => Lead::whereYear('created_at', now()->year)->count(),
            'overdue_sellers' => $overdueSellersCount,
        ];

        // ── Period revenue (responds to tab filter) ─────
        $period = request('period', 'month');
        $periodQuery = match($period) {
            'today' => Lead::whereDate('created_at', today()),
            'week'  => Lead::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'year'  => Lead::whereYear('created_at', now()->year),
            default => Lead::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
        };
        $periodStats = [
            'count'           => (clone $periodQuery)->count(),
            'revenue'         => (clone $periodQuery)->whereNotNull('paid_at')->sum('fee'),
            'pending_revenue' => (clone $periodQuery)->whereNull('paid_at')->sum('fee'),
        ];

        // ── Leads table ─────────────────────────────────
        $status = request('status');
        $source = request('source');
        $search = request('search');
        $leads  = Lead::with('seller:id,name,slug,category_id,state,city')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($source, fn($q) => $q->where('source', $source))
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
            ))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        // Pre-compute which sellers on this page are overdue (avoids N+1 in view)
        $overdueSellers = $leads->pluck('seller')->filter()->unique('id')
            ->filter(fn($u) => $u->isOverdue())
            ->pluck('id')
            ->toArray();

        return view('admin.leads.index', compact('stats', 'periodStats', 'period', 'leads', 'overdueSellers'));
    }

    public function leadDetail($id)
    {
        $lead = Lead::with('seller')->findOrFail($id);
        return view('frontend.seller.lead_detail', compact('lead'));
    }

    public function leadUpdateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:new,pending,won,lost']);
        Lead::findOrFail($id)->update(['status' => $request->status]);
        return back()->with('success', 'Lead status updated.');
    }

    public function leadMarkPaid($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->update(['paid_at' => $lead->paid_at ? null : now()]);
        return back()->with('success', $lead->paid_at ? 'Lead marked as paid.' : 'Lead marked as unpaid.');
    }

    public function leadDestroy($id)
    {
        Lead::findOrFail($id)->delete();
        return back()->with('success', 'Lead deleted.');
    }

    public function affiliate()
    {
        $stats = [
            'total_referrers'   => User::where('type', 'seller')->whereHas('referrals')->count(),
            'total_referrals'   => User::whereNotNull('referred_by')->count(),
            'pending_amount'    => AffiliateCommission::pending()->sum('amount'),
            'paid_amount'       => AffiliateCommission::paid()->sum('amount'),
            'pending_count'     => AffiliateCommission::pending()->count(),
            'paid_count'        => AffiliateCommission::paid()->count(),
        ];

        $filter = request('filter');
        $commissions = AffiliateCommission::with([
                'referrer:id,name,email,slug,profile_photo',
                'referredUser:id,name,email,slug,status,created_at',
            ])
            ->when($filter === 'pending', fn($q) => $q->pending())
            ->when($filter === 'paid',    fn($q) => $q->paid())
            ->latest()
            ->paginate(25);

        // 1 query: paid/pending totals per referrer
        $commTotals = AffiliateCommission::selectRaw(
            "referrer_id,
             SUM(CASE WHEN status='paid'    THEN amount ELSE 0 END) as earned_total,
             SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) as pending_total"
        )->groupBy('referrer_id')->get()->keyBy('referrer_id');

        $topReferrers = User::where('type', 'seller')
            ->whereHas('referrals')
            ->withCount('referrals')
            ->orderByDesc('referrals_count')
            ->get()
            ->each(function ($seller) use ($commTotals) {
                $t = $commTotals->get($seller->id);
                $seller->earned_total  = $t?->earned_total  ?? 0;
                $seller->pending_total = $t?->pending_total ?? 0;
            });

        $allSellers = User::where('type', 'seller')
            ->withCount('referrals')
            ->orderByDesc('referrals_count')
            ->get();

        return view('admin.affiliate.index', compact('stats', 'commissions', 'topReferrers', 'allSellers'));
    }

    public function affiliateCommissionPay($id)
    {
        $commission = AffiliateCommission::findOrFail($id);
        $commission->update(['status' => 'paid', 'paid_at' => now()]);
        return back()->with('success', 'Commission marked as paid.');
    }

    public function affiliateCommissionCreate(Request $request)
    {
        $request->validate([
            'referrer_id'      => 'required|exists:users,id',
            'referred_user_id' => 'required|exists:users,id',
            'amount'           => 'required|numeric|min:0',
        ]);
        AffiliateCommission::create(array_merge(
            $request->only('referrer_id', 'referred_user_id', 'amount'),
            ['status' => 'pending', 'referral_type' => 'seller']
        ));
        return back()->with('success', 'Commission created.');
    }

    public function affiliateCommissionDestroy($id)
    {
        AffiliateCommission::findOrFail($id)->delete();
        return back()->with('success', 'Commission deleted.');
    }

    public function locations(Request $request)
    {
        $tab = $request->query('tab', 'countries');

        $countries = Country::withCount('states')->orderBy('title')->get();
        $states    = State::with('country')->withCount('cities')->orderBy('title')->get();
        $cities    = City::with('state')->withCount('postalCodes')->orderBy('title')->get();
        $zips      = PostalCode::with('city')->orderBy('title')->get();

        $stats = [
            'countries' => $countries->count(),
            'states'    => $states->count(),
            'cities'    => $cities->count(),
            'zips'      => $zips->count(),
        ];

        return view('admin.locations.index', compact('tab', 'countries', 'states', 'cities', 'zips', 'stats'));
    }

    public function clear_cache()
    {
        Artisan::call('optimize:clear');
        return back()->with('success', 'All cache cleared successfully.');
    }

    public function storage_link()
    {
        if (file_exists(public_path('storage'))) {
            return back()->with('success', 'Storage already linked. Images should be visible now.');
        }
        Artisan::call('storage:link');
        return back()->with('success', 'Storage linked! Existing uploaded images are now accessible.');
    }

    public function hierarchy(Request $request)
    {
        $role = $request->query('role', 'area_manager');

        $counts = [];
        foreach (array_keys(StaffProfile::ROLES) as $r) {
            $counts[$r] = StaffProfile::where('role', $r)->count();
        }

        $staff = StaffProfile::with(['user', 'parent.user'])
            ->where('role', $role)
            ->latest()
            ->get();

        // For parent select in modals: managers one level up
        $parentRole = StaffProfile::ROLE_REPORTS_TO[$role] ?? null;
        $potentialParents = $parentRole
            ? StaffProfile::with('user')->where('role', $parentRole)->where('status', 'active')->get()
            : collect();

        // All users that don't yet have a staff profile (for assign-user select)
        $availableUsers = User::whereNotIn('id', StaffProfile::pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $totalRevenue = StaffProfile::sum('revenue_generated');
        $totalActive  = StaffProfile::where('status', 'active')->count();

        return view('admin.hierarchy.index', compact(
            'role', 'counts', 'staff', 'potentialParents', 'availableUsers', 'totalRevenue', 'totalActive'
        ));
    }

    public function hierarchyParents(Request $request)
    {
        $role = $request->query('role');
        $managers = StaffProfile::with('user')
            ->where('role', $role)
            ->where('status', 'active')
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'user_name'     => $p->user?->name ?? '—',
                'assigned_area' => $p->assigned_area,
                'assigned_state'=> $p->assigned_state,
            ]);
        return response()->json($managers);
    }

    public function hierarchyStore(Request $request)
    {
        $request->validate([
            'user_id'         => 'required|exists:users,id|unique:staff_profiles,user_id',
            'role'            => 'required|in:area_manager,city_manager,district_manager,country_manager',
            'assigned_area'   => 'nullable|string|max:255',
            'assigned_state'  => 'nullable|string|max:255',
            'parent_id'       => 'nullable|exists:staff_profiles,id',
            'base_salary'     => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'joined_at'       => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        StaffProfile::create($request->only([
            'user_id', 'role', 'assigned_area', 'assigned_state',
            'parent_id', 'base_salary', 'commission_rate', 'joined_at', 'notes',
        ]));

        User::find($request->user_id)?->update(['type' => 'staff']);

        return back()->with('success', 'Staff member added.');
    }

    public function hierarchyUpdate(Request $request, $id)
    {
        $profile = StaffProfile::findOrFail($id);

        $request->validate([
            'assigned_area'   => 'nullable|string|max:255',
            'assigned_state'  => 'nullable|string|max:255',
            'parent_id'       => 'nullable|exists:staff_profiles,id',
            'base_salary'     => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'joined_at'       => 'nullable|date',
            'notes'           => 'nullable|string',
            'sellers_onboarded' => 'nullable|integer|min:0',
            'active_sellers'  => 'nullable|integer|min:0',
            'dispute_rate'    => 'nullable|numeric|min:0|max:100',
            'revenue_generated' => 'nullable|numeric|min:0',
        ]);

        $profile->update($request->only([
            'assigned_area', 'assigned_state', 'parent_id',
            'base_salary', 'commission_rate', 'joined_at', 'notes',
            'sellers_onboarded', 'active_sellers', 'dispute_rate', 'revenue_generated',
        ]));

        return back()->with('success', 'Staff profile updated.');
    }

    public function hierarchyDestroy($id)
    {
        $profile = StaffProfile::findOrFail($id);
        $userId  = $profile->user_id;
        $profile->delete();
        User::find($userId)?->update(['type' => 'seller']);
        return back()->with('success', 'Staff member removed.');
    }

    public function hierarchyStatusToggle($id)
    {
        $profile = StaffProfile::findOrFail($id);
        $next    = match ($profile->status) {
            'active'    => 'inactive',
            'inactive'  => 'active',
            'probation' => 'active',
            default     => 'active',
        };
        $profile->update(['status' => $next]);
        return back()->with('success', 'Status updated to ' . $next . '.');
    }

    public function contactSettings()
    {
        $settings = [
            'support_email'    => Setting::get('support_email', ''),
            'support_whatsapp' => Setting::get('support_whatsapp', ''),
            'support_name'     => Setting::get('support_name', 'Zonely Admin Team'),
            'social_facebook'  => Setting::get('social_facebook', ''),
            'social_linkedin'  => Setting::get('social_linkedin', ''),
            'sister_site_name' => Setting::get('sister_site_name', 'Sister Site'),
            'sister_site_url'  => Setting::get('sister_site_url', ''),
            'copyright_text'   => Setting::get('copyright_text', ''),
        ];
        return view('admin.settings.contact', compact('settings'));
    }

    public function contactSettingsUpdate(Request $request)
    {
        $request->validate([
            'support_email'    => 'required|email|max:255',
            'support_whatsapp' => 'nullable|string|max:30',
            'support_name'     => 'nullable|string|max:100',
            'social_facebook'  => 'nullable|url|max:255',
            'social_linkedin'  => 'nullable|url|max:255',
            'sister_site_name' => 'nullable|string|max:100',
            'sister_site_url'  => 'nullable|url|max:255',
            'copyright_text'   => 'nullable|string|max:255',
        ]);

        Setting::set('support_email',    $request->support_email);
        Setting::set('support_whatsapp', $request->support_whatsapp ?? '');
        Setting::set('support_name',     $request->support_name ?? 'Zonely Admin Team');
        Setting::set('social_facebook',  $request->social_facebook ?? '');
        Setting::set('social_linkedin',  $request->social_linkedin ?? '');
        Setting::set('sister_site_name', $request->sister_site_name ?? 'Sister Site');
        Setting::set('sister_site_url',  $request->sister_site_url ?? '');
        Setting::set('copyright_text',   $request->copyright_text ?? '');

        return back()->with('success', 'Platform settings updated successfully.');
    }
}
