<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PlatformCharge;
use App\Models\Review;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\NotificationService;

class SellerController extends Controller
{
    use \App\Http\Controllers\Concerns\UpdatesUserCredentials;
    public function onboarding()
    {
        $user = Auth::user()->load(['services', 'educations', 'memberships', 'languages', 'faqs', 'category', 'experiences', 'certifications']);
        return view('frontend.seller.onboarding', compact('user'));
    }

    public function dashboard()
    {
        $user   = Auth::user();
        $period = request('period', 'month');

        // Period query at DB level — no more loading all leads into memory
        $periodQuery = match($period) {
            'today' => $user->leads()->whereDate('created_at', today()),
            'week'  => $user->leads()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'year'  => $user->leads()->whereYear('created_at', now()->year),
            default => $user->leads()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
        };

        $leads = $periodQuery->latest()->get();

        // Stats
        $stats = [
            'total'    => $leads->count(),
            'today'    => $user->leads()->whereDate('created_at', today())->count(),
            'form'     => $leads->where('source', 'form')->count(),
            'phone'    => $leads->where('source', 'phone')->count(),
            'whatsapp' => $leads->where('source', 'whatsapp')->count(),
            'email'    => $leads->where('source', 'email')->count(),
            'booking'  => $leads->where('source', 'booking')->count(),
        ];

        // Weekly chart — last 7 days via single GROUP BY query
        $rawWeek = $user->leads()
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->selectRaw("DATE(created_at) as day, COUNT(*) as cnt")
            ->groupByRaw("DATE(created_at)")
            ->pluck('cnt', 'day');

        $weekDays   = [];
        $weekCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $weekDays[]   = $d->format('D');
            $weekCounts[] = (int) ($rawWeek[$d->toDateString()] ?? 0);
        }

        $unpaidCount   = $user->leads()->whereNull('paid_at')->count();
        $unpaidBalance = $user->leads()->whereNull('paid_at')->sum('fee');
        $allLeads      = $leads; // keep for view compatibility
        $activePromos  = PlatformCharge::activePromotions();

        return view('frontend.seller.dashboard', compact('user', 'leads', 'allLeads', 'stats', 'period', 'weekDays', 'weekCounts', 'unpaidCount', 'unpaidBalance', 'activePromos'));
    }

    public function affiliate()
    {
        $user        = Auth::user()->load('category');
        $commissions = $user->commissionsEarned()->with('referredUser')
                           ->where('referral_type', 'seller')
                           ->latest()->get();

        // Count only seller referrals — consistent with the commissions query above
        $totalRefs = $user->referrals()->where('type', 'seller')->count();

        // Tier progress
        $nextMilestone = $totalRefs < 3  ? 3  : ($totalRefs < 5  ? 5  : ($totalRefs < 10 ? 10 : ($totalRefs < 25 ? 25 : 50)));
        $tierLabel     = $totalRefs < 3  ? 'Starter' : ($totalRefs < 5  ? 'Rising' : ($totalRefs < 10 ? 'Trusted' : ($totalRefs < 25 ? 'Elite' : 'Zonely Pro')));
        $nextLabel     = $totalRefs < 3  ? 'Rising'  : ($totalRefs < 5  ? 'Trusted' : ($totalRefs < 10 ? 'Elite'   : 'Zonely Pro'));
        $tierPct       = min(100, $nextMilestone > 0 ? round($totalRefs / $nextMilestone * 100) : 100);
        $remaining     = max(0, $nextMilestone - $totalRefs);

        // Check + award tier milestone points every time affiliate page loads
        \App\Services\PointsService::checkTierMilestones($user);

        // Dynamic commission rate
        $stateId    = \App\Models\State::where('title', $user->state)->value('id');
        $cityId     = \App\Models\City::where('title', $user->city)->value('id');
        $commRate   = PlatformCharge::resolve('affiliate_commission', $user->category_id, $stateId, $cityId);

        // Earnings projections
        $projections = [
            ['refs' => 1,  'cash' => $commRate,      'pts' => 35],
            ['refs' => 3,  'cash' => $commRate * 3,  'pts' => 105],
            ['refs' => 5,  'cash' => $commRate * 5,  'pts' => 200],
            ['refs' => 10, 'cash' => $commRate * 10, 'pts' => 450],
        ];

        $stats = [
            'referrals' => $totalRefs,
            'earned'    => $commissions->sum('amount'),
            'pending'   => $commissions->where('status', 'pending')->sum('amount'),
            'paid_out'  => $commissions->where('status', 'paid')->sum('amount'),
        ];

        $refUrl     = url('/user/register/seller?ref=' . ($user->slug ?? $user->id));
        $userPoints = $user->fresh()->points; // refresh after possible tier award
        $pointsLog  = $user->pointsLog()->latest()->limit(10)->get();

        return view('frontend.seller.affiliate', compact(
            'user', 'commissions', 'stats',
            'totalRefs', 'tierLabel', 'nextLabel', 'tierPct', 'remaining',
            'projections', 'commRate', 'refUrl', 'userPoints', 'pointsLog'
        ));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('frontend.seller.settings', compact('user'));
    }

    public function settingsUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:50',
            'whatsapp'      => 'nullable|string|max:50',
            'title'         => 'nullable|string|max:20',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
        ]);

        // Remove profile_photo from $data — only set if new file uploaded
        unset($data['profile_photo']);
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = ImageOptimizer::saveProfilePhoto($request->file('profile_photo'));
        }

        $this->handleEmailChange($user, $request);
        $user->update($data);

        return back()->with('success', 'Settings saved.');
    }

    public function settingsPasswordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password updated successfully.');
    }

    public function settingsNotifications(Request $request)
    {
        $allowed = ['notify_new_lead', 'notify_payment', 'notify_review', 'notify_booking'];
        $key     = $request->input('key');
        if (!in_array($key, $allowed)) {
            return response()->json(['error' => 'Invalid key'], 422);
        }
        Auth::user()->update([$key => (bool) $request->input('value')]);
        return response()->json(['ok' => true]);
    }

    public function settingsDestroy(Request $request)
    {
        $request->validate(['password' => 'required']);
        $user = Auth::user();
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }
        $user->delete();
        Auth::logout();
        return redirect('/')->with('success', 'Account deleted.');
    }

    public function pricing()
    {
        $user = Auth::user()->load('category');

        $categoryId = $user->category_id;
        $stateId    = \App\Models\State::where('title', $user->state)->value('id');
        $cityId     = \App\Models\City::where('title', $user->city)->value('id');

        $leadFee      = PlatformCharge::resolve('lead_fee', $categoryId, $stateId, $cityId);
        $affiliateComm = PlatformCharge::resolve('affiliate_commission', $categoryId, $stateId, $cityId);

        $rules = PlatformCharge::active()
            ->whereIn('type', ['lead_fee', 'affiliate_commission'])
            ->where(function ($q) use ($categoryId, $stateId, $cityId) {
                $q->whereNull('category_id')->whereNull('state_id')->whereNull('city_id')
                  ->orWhere('category_id', $categoryId)
                  ->orWhere('state_id', $stateId)
                  ->orWhere('city_id', $cityId);
            })
            ->with(['category', 'state', 'city'])
            ->orderBy('type')
            ->orderByDesc('priority')
            ->get();

        return view('frontend.seller.pricing', compact('user', 'leadFee', 'affiliateComm', 'rules'));
    }

    public function billing()
    {
        $user   = Auth::user();
        $period = request('period', 'month');

        $periodQuery = match($period) {
            'today' => $user->leads()->whereDate('created_at', today()),
            'week'  => $user->leads()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'year'  => $user->leads()->whereYear('created_at', now()->year),
            default => $user->leads()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
        };

        $allLeads    = $user->leads()->latest()->get();
        $periodLeads = $periodQuery->get();

        $stateId   = \App\Models\State::where('title', $user->state)->value('id');
        $cityId    = \App\Models\City::where('title', $user->city)->value('id');
        $threshold = (int) PlatformCharge::resolve('lead_threshold', $user->category_id, $stateId, $cityId);

        $balance = [
            'unpaid'        => $user->leads()->whereNull('paid_at')->sum('fee'),
            'unpaid_count'  => $user->leads()->whereNull('paid_at')->count(),
            'total_paid'    => $user->leads()->whereNotNull('paid_at')->sum('fee'),
            'total_billed'  => $user->leads()->sum('fee'),
            'total_leads'   => $user->leads()->count(),
            'period_billed' => $periodQuery->sum('fee'),
            'period_paid'   => (clone $periodQuery)->whereNotNull('paid_at')->sum('fee'),
            'period_count'  => $periodLeads->count(),
        ];

        $activePromos = PlatformCharge::activePromotions();
        return view('frontend.seller.billing', compact('allLeads', 'balance', 'period', 'threshold', 'activePromos'));
    }

    public function payLead(Request $request, $id)
    {
        $orderId = $request->input('paypal_order_id');
        if (!$orderId) {
            return response()->json(['error' => 'Missing PayPal order ID'], 422);
        }

        $result = DB::transaction(function () use ($id, $orderId) {
            $lead = Lead::where('id', $id)
                ->where('seller_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lead->paid_at) {
                return ['error' => 'Already paid'];
            }

            $verified = $this->verifyPayPalOrder($orderId, $lead->fee);
            if (!$verified) {
                return ['error' => 'PayPal payment verification failed'];
            }

            $lead->update(['paid_at' => now(), 'paypal_order_id' => $orderId]);
            return ['ok' => true, 'fee' => $lead->fee];
        });

        if (isset($result['error'])) {
            Log::error('payLead failed', ['lead_id' => $id, 'seller_id' => Auth::id(), 'order_id' => $orderId, 'error' => $result['error']]);
            return response()->json(['error' => $result['error']], 422);
        }

        NotificationService::paymentReceived(Auth::user(), (float) $result['fee'], 1);
        return response()->json(['ok' => true]);
    }

    public function payLeads(Request $request)
    {
        $ids     = $request->input('lead_ids', []);
        $orderId = $request->input('paypal_order_id');

        if (empty($ids) || !$orderId) {
            return response()->json(['error' => 'Missing data'], 422);
        }

        // Check PayPal order hasn't already been used (replay attack prevention)
        if (Lead::where('paypal_order_id', $orderId)->exists()) {
            return response()->json(['error' => 'Payment already processed'], 422);
        }

        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($ids, $orderId) {
            $leads = Lead::whereIn('id', $ids)
                ->where('seller_id', Auth::id())
                ->whereNull('paid_at')
                ->lockForUpdate()
                ->get();

            if ($leads->isEmpty()) {
                return ['error' => 'No valid leads'];
            }

            $total    = $leads->sum('fee');
            $verified = $this->verifyPayPalOrder($orderId, $total);
            if (!$verified) {
                return ['error' => 'PayPal payment verification failed'];
            }

            $leads->each(fn($l) => $l->update(['paid_at' => now(), 'paypal_order_id' => $orderId]));
            return ['ok' => true, 'paid' => $leads->count(), 'fee' => $total];
        });

        if (isset($result['error'])) {
            Log::error('payLeads failed', ['lead_ids' => $ids, 'seller_id' => Auth::id(), 'order_id' => $orderId, 'error' => $result['error']]);
            return response()->json(['error' => $result['error']], 422);
        }

        NotificationService::paymentReceived(Auth::user(), $result['fee'], $result['paid']);

        return response()->json(['ok' => true, 'paid' => $result['paid']]);
    }

    private function verifyPayPalOrder(string $orderId, float $expectedAmount): bool
    {
        $clientId     = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');
        $mode         = config('services.paypal.mode', 'sandbox');
        $baseUrl      = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        // Get access token
        $tokenResponse = \Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$tokenResponse->ok()) return false;
        $accessToken = $tokenResponse->json('access_token');

        // Get order details
        $orderResponse = \Http::withToken($accessToken)
            ->get("{$baseUrl}/v2/checkout/orders/{$orderId}");

        if (!$orderResponse->ok()) return false;

        $order = $orderResponse->json();
        if (($order['status'] ?? '') !== 'COMPLETED') return false;

        $paid = (float) ($order['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0);

        return abs($paid - $expectedAmount) < 0.01;
    }

    public function schedule()
    {
        $user     = Auth::user();
        $schedule = $user->schedule ?? null;
        return view('frontend.seller.schedule', compact('user', 'schedule'));
    }

    public function scheduleUpdate(Request $request)
    {
        $data = $request->validate([
            // Booking schedule
            'working_days'       => 'nullable|array',
            'working_days.*'     => 'string|in:mon,tue,wed,thu,fri,sat,sun',
            'periods'            => 'nullable|array|max:10',
            'periods.*.label'    => 'nullable|string|max:50',
            'periods.*.from'     => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'periods.*.to'       => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'periods.*.duration' => 'nullable|integer|min:5|max:480',
            'periods.*.buffer'   => 'nullable|integer|min:0|max:120',
            'max_per_day'        => 'nullable|integer|min:1|max:100',
            'advance_days'       => 'nullable|integer|min:0|max:365',
            'min_notice_hours'   => 'nullable|integer|min:0|max:72',
            'booking_type'       => 'nullable|in:instant,manual',
            // Working hours
            'show_office_hours'                              => 'nullable|boolean',
            'office_hours'                                   => 'nullable|array',
            'office_hours.timezone'                          => 'nullable|string|timezone',
            'office_hours.response_time'                     => 'nullable|in:30_min,1_hour,4_hours,24_hours,48_hours',
            'office_hours.emergency_available'               => 'nullable|boolean',
            'office_hours.note'                              => 'nullable|string|max:200',
            'office_hours.days'                              => 'nullable|array',
            'office_hours.days.*'                            => 'nullable|array',
            'office_hours.days.*.open'                       => 'nullable|boolean',
            'office_hours.days.*.slots'                      => 'nullable|array|max:2',
            'office_hours.days.*.slots.*.from'               => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'office_hours.days.*.slots.*.to'                 => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        Auth::user()->update(['schedule' => $data]);
        return back()->with('success', 'Schedule saved.');
    }

    public function reviews()
    {
        $user = Auth::user();

        // Only submitted reviews (have a rating)
        $reviews = Review::where('seller_id', $user->id)
            ->whereNotNull('rating')
            ->latest()
            ->get();

        // Pending review requests (sent but not yet submitted)
        $pendingRequests = Review::where('seller_id', $user->id)
            ->whereNull('rating')
            ->whereNotNull('review_token')
            ->latest()
            ->get();

        $avgRating = $reviews->count() ? round($reviews->avg('rating'), 1) : 0;

        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $reviews->where('rating', $i)->count();
            $ratingBreakdown[$i] = $reviews->count()
                ? round($count / $reviews->count() * 100)
                : 0;
        }

        return view('frontend.seller.reviews', [
            'reviews'         => $reviews,
            'pendingRequests' => $pendingRequests,
            'avgRating'       => $avgRating,
            'totalReviews'    => $reviews->count(),
            'ratingBreakdown' => $ratingBreakdown,
        ]);
    }

    public function reviewReply(Request $request, $id)
    {
        $request->validate(['reply' => 'required|string|max:500']);

        $review = Review::where('id', $id)
            ->where('seller_id', Auth::id())
            ->firstOrFail();

        $review->update([
            'reply'      => $request->reply,
            'replied_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function notifications()
    {
        $user          = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(30);
        $unreadCount   = $user->unreadNotifications()->count();
        return view('frontend.seller.notifications', compact('notifications', 'unreadCount'));
    }

    public function notificationsReadAll()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function notificationRead($id)
    {
        Auth::user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function leadDetail($id)
    {
        $lead = Lead::where('id', $id)->where('seller_id', Auth::id())->firstOrFail();
        return view('frontend.seller.lead_detail', compact('lead'));
    }

    public function leadStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:new,pending,won,lost,closed']);
        Lead::where('id', $id)->where('seller_id', Auth::id())->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function leadNotes(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);
        Lead::where('id', $id)->where('seller_id', Auth::id())->update(['notes' => $request->notes]);
        return response()->json(['success' => true]);
    }

    /**
     * Seller sends a review request link to the buyer of a lead.
     * Creates (or reuses) a pending Review row with a unique token.
     * The buyer opens /r/{token} — no login required.
     */
    public function reviewRequest(Request $request, $id)
    {
        $lead = Lead::where('id', $id)->where('seller_id', Auth::id())->firstOrFail();

        // Reuse existing token if sent within 30 days and not yet submitted
        $existing = Review::where('seller_id', Auth::id())
            ->where('lead_id', $lead->id)
            ->whereNull('token_used_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        if ($existing) {
            return response()->json([
                'link' => url('/r/' . $existing->review_token),
            ]);
        }

        $review = Review::create([
            'seller_id'     => Auth::id(),
            'lead_id'       => $lead->id,
            'reviewer_name' => $lead->name ?? 'Guest',
            'reviewer_email'=> $lead->email,
            'review_token'  => Str::random(48),
        ]);

        return response()->json([
            'link' => url('/r/' . $review->review_token),
        ]);
    }

    /**
     * Generate a review request link from the Reviews page directly (name + email, no lead required).
     */
    public function reviewRequestDirect(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:150',
        ]);

        // Reuse an unsent token for this email within 30 days
        $existing = Review::where('seller_id', Auth::id())
            ->where('reviewer_email', $data['email'])
            ->whereNull('token_used_at')
            ->whereNotNull('review_token')
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        if ($existing) {
            return response()->json(['link' => url('/r/' . $existing->review_token)]);
        }

        $review = Review::create([
            'seller_id'      => Auth::id(),
            'reviewer_name'  => $data['name'],
            'reviewer_email' => $data['email'],
            'review_token'   => Str::random(48),
        ]);

        return response()->json(['link' => url('/r/' . $review->review_token)]);
    }
}
