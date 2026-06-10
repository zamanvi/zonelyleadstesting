<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PlatformCharge;
use App\Models\Review;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $user  = Auth::user();
        $leads = $user->leads()->with('seller')->latest()->get();

        $stats = [
            'total'   => $leads->count(),
            'won'     => $leads->where('status', 'won')->count(),
            'pending' => $leads->where('status', 'pending')->count(),
            'unpaid'  => $leads->whereNull('paid_at')->count(),
        ];

        return view('frontend.seller.dashboard', compact('user', 'leads', 'stats'));
    }

    public function affiliate()
    {
        $user        = Auth::user()->load('category');
        $commissions = $user->commissionsEarned()->with('referredUser')
                           ->where('referral_type', 'seller')
                           ->latest()->get();

        $totalRefs = $user->referrals()->count();

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
            'title'         => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:10240',
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
        $user        = Auth::user();
        $unpaidLeads = $user->leads()->whereNull('paid_at')->latest()->get();
        $paidLeads   = $user->leads()->whereNotNull('paid_at')->latest()->get();

        $now = now();
        $balance = [
            'unpaid'       => $unpaidLeads->sum('fee'),
            'unpaid_count' => $unpaidLeads->count(),
            'paid_month'   => $paidLeads->filter(fn($l) => $l->paid_at && $l->paid_at->month === $now->month && $l->paid_at->year === $now->year)->sum('fee'),
            'paid_count'   => $paidLeads->count(),
            'total_paid'   => $paidLeads->sum('fee'),
        ];

        return view('frontend.seller.billing', compact('unpaidLeads', 'paidLeads', 'balance'));
    }

    public function payLead(Request $request, $id)
    {
        $lead = Lead::where('id', $id)->where('seller_id', Auth::id())->firstOrFail();

        if ($lead->paid_at) {
            return response()->json(['error' => 'Already paid'], 422);
        }

        $orderId = $request->input('paypal_order_id');
        if (!$orderId) {
            return response()->json(['error' => 'Missing PayPal order ID'], 422);
        }

        // Verify PayPal order
        $verified = $this->verifyPayPalOrder($orderId, $lead->fee);
        if (!$verified) {
            return response()->json(['error' => 'PayPal payment verification failed'], 422);
        }

        $lead->update(['paid_at' => now(), 'paypal_order_id' => $orderId]);

        return response()->json(['ok' => true]);
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
        $notifications = $user->notifications()->latest()->paginate(20);
        $user->unreadNotifications()->update(['read_at' => now()]);
        return view('frontend.seller.notifications', compact('notifications'));
    }

    public function notificationsReadAll()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function leadDetail($id)
    {
        $lead = Lead::where('id', $id)->where('seller_id', Auth::id())->firstOrFail();
        return view('frontend.seller.lead_detail', compact('lead'));
    }

    public function leadStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:won,lost,pending,new']);
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
}
