<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Review;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function onboarding()
    {
        $user = Auth::user()->load(['services', 'educations', 'memberships', 'languages', 'faqs', 'category']);
        if (Schema::hasTable('experiences'))    $user->load('experiences');
        else                                    $user->setRelation('experiences', collect());
        if (Schema::hasTable('certifications')) $user->load('certifications');
        else                                    $user->setRelation('certifications', collect());
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
        $user        = Auth::user();
        $commissions = $user->commissionsEarned()->with('referredUser')->latest()->get();

        $stats = [
            'referrals' => $user->referrals()->count(),
            'earned'    => $commissions->sum('amount'),
            'pending'   => $commissions->where('status', 'pending')->sum('amount'),
            'paid_out'  => $commissions->where('status', 'paid')->sum('amount'),
        ];

        return view('frontend.seller.affiliate', compact('user', 'commissions', 'stats'));
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

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = ImageOptimizer::saveProfilePhoto($request->file('profile_photo'));
        }

        $emailChanged = $user->email !== $request->email;
        $user->update($data);

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                $user->sendEmailVerificationNotification();
            }
        }

        if ($request->filled('password')) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password'         => ['required', 'confirmed', 'min:8'],
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Settings saved.');
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
        $lead->update(['paid_at' => now()]);
        return back()->with('success', 'Lead marked as paid.');
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
            'working_days.*'     => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
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
        return view('frontend.seller.notifications', ['notifications' => collect()]);
    }

    public function notificationsReadAll()
    {
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
