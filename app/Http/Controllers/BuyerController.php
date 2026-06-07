<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PlatformCharge;
use App\Models\Review;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BuyerController extends Controller
{
    use \App\Http\Controllers\Concerns\UpdatesUserCredentials;
    public function dashboard()
    {
        $user = Auth::user();

        // Run two focused DB queries instead of loading everything into PHP
        $activeLeads = Lead::where('email', $user->email)
            ->whereIn('status', ['new', 'pending'])
            ->with('seller')
            ->latest()
            ->limit(50)
            ->get();

        $resolvedLeads = Lead::where('email', $user->email)
            ->whereIn('status', ['won', 'lost', 'closed'])
            ->with('seller')
            ->latest()
            ->limit(20)
            ->get();

        $totalLeads = Lead::where('email', $user->email)->count();

        $pendingReviews = Review::with('lead.seller')
            ->where(function ($q) use ($user) {
                $q->where('reviewer_id', $user->id)
                  ->orWhere('reviewer_email', $user->email);
            })
            ->whereNull('token_used_at')
            ->whereNotNull('review_token')
            ->get();

        $commRate = '$' . number_format(PlatformCharge::resolve('buyer_referral_commission'), 0);

        $stats = [
            'bookings' => $totalLeads,
            'active'   => $activeLeads->count(),
            'resolved' => $resolvedLeads->count(),
        ];

        return view('frontend.buyer.dashboard', compact(
            'stats', 'pendingReviews', 'activeLeads', 'resolvedLeads', 'commRate'
        ));
    }

    public function bookings()
    {
        $user     = Auth::user();
        $bookings = Lead::where('email', $user->email)
            ->with('seller')
            ->latest()
            ->paginate(20);

        return view('frontend.buyer.bookings', compact('bookings'));
    }

    public function cancelBooking(Request $request, $id)
    {
        $lead = Lead::where('id', $id)
            ->where('email', Auth::user()->email)
            ->whereIn('status', ['new', 'pending'])
            ->firstOrFail();

        $lead->update(['status' => 'lost']);
        return response()->json(['success' => true]);
    }

    public function book(User $seller)
    {
        abort_unless($seller->type === 'seller' && $seller->status, 404);

        $schedule    = $seller->schedule ?? [
            'working_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'periods'      => [
                ['label' => 'Morning',   'from' => '09:00', 'to' => '12:00', 'duration' => 60],
                ['label' => 'Afternoon', 'from' => '13:00', 'to' => '17:00', 'duration' => 60],
            ],
        ];
        $bookedSlots = [];
        return view('frontend.buyer.book', compact('seller', 'schedule', 'bookedSlots'));
    }

    public function bookStore(Request $request)
    {
        $data = $request->validate([
            'seller_id'     => 'required|exists:users,id',
            'selected_date' => 'required|date|after_or_equal:today',
            'selected_slot' => 'required|string|max:50',
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:50',
            'email'         => 'nullable|email|max:150',
            'message'       => 'nullable|string|max:1000',
        ]);

        $seller = User::findOrFail($data['seller_id']);

        $lead = Lead::create([
            'seller_id' => $data['seller_id'],
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'email'     => $data['email'] ?? (Auth::user()?->email),
            'service'   => 'Booking: ' . $data['selected_date'] . ' @ ' . $data['selected_slot'],
            'message'   => $data['message'],
            'status'    => 'new',
            'fee'       => 0,
        ]);

        return redirect()->route('buyer.booking.confirmation', $lead->id)
            ->with('success', 'Booking confirmed!');
    }

    public function review($sellerId)
    {
        $seller  = User::where('id', $sellerId)->where('type', 'seller')->firstOrFail();
        $booking = (object)[
            'id'        => $sellerId,
            'date'      => now(),
            'slot_time' => null,
            'service'   => $seller->title ?? 'Service',
            'seller'    => $seller,
        ];
        return view('frontend.buyer.review', compact('booking'));
    }

    public function reviewStore(Request $request, $sellerId)
    {
        $seller = User::where('id', $sellerId)->where('type', 'seller')->firstOrFail();

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:10|max:1000',
            'tags'   => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        Review::updateOrCreate(
            ['seller_id' => $seller->id, 'reviewer_id' => $user->id],
            [
                'reviewer_name'  => $user->name,
                'reviewer_email' => $user->email,
                'rating'         => $data['rating'],
                'review'         => $data['review'],
                'tags'           => $data['tags'] ?? null,
                'token_used_at'  => now(),
            ]
        );

        return redirect()->route('frontend.service.show', $seller->slug)
            ->with('success', 'Review submitted. Thank you!');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('frontend.buyer.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:50',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'work_address'  => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:100',
            'zip_code'      => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:10240',
        ]);

        // Remove profile_photo from $data — only set it if a new file was uploaded
        unset($data['profile_photo']);
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = ImageOptimizer::saveProfilePhoto($request->file('profile_photo'));
        }

        $this->handleEmailChange($user, $request);
        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function profilePasswordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);
        Auth::user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password updated successfully.');
    }

    public function profileDestroy(Request $request)
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

    public function bookingConfirmation($id)
    {
        $lead = Lead::with('seller')->findOrFail($id);

        // Only allow buyer who made the booking to see confirmation
        $user = Auth::user();
        if ($lead->email && $lead->email !== $user->email) {
            abort(403);
        }

        return view('frontend.buyer.booking_confirmation', compact('lead'));
    }

    public function affiliate()
    {
        $user        = Auth::user();
        $commissions = $user->commissionsEarned()
                           ->with('referredUser')
                           ->where('referral_type', 'buyer')
                           ->latest()
                           ->get();

        $totalRefs = $user->referrals()->where('type', 'user')->count();

        // Tier progress
        $nextMilestone = $totalRefs < 3  ? 3  :
                        ($totalRefs < 5  ? 5  :
                        ($totalRefs < 10 ? 10 :
                        ($totalRefs < 25 ? 25 : 50)));
        $tierLabel  = $totalRefs < 3  ? 'Starter'    :
                     ($totalRefs < 5  ? 'Rising'     :
                     ($totalRefs < 10 ? 'Trusted'    :
                     ($totalRefs < 25 ? 'Elite'      : 'Zonely Pro')));
        $nextLabel  = $totalRefs < 3  ? 'Rising'     :
                     ($totalRefs < 5  ? 'Trusted'    :
                     ($totalRefs < 10 ? 'Elite'      :
                     ($totalRefs < 25 ? 'Zonely Pro' : 'Zonely Pro')));
        $tierPct    = min(100, $nextMilestone > 0 ? round($totalRefs / $nextMilestone * 100) : 100);
        $remaining  = max(0, $nextMilestone - $totalRefs);

        // Resolve once — reuse for projections and pass to blade
        $commRate = PlatformCharge::resolve('buyer_referral_commission');

        $projections = [
            ['refs' => 1,  'cash' => $commRate,      'pts' => 35],
            ['refs' => 3,  'cash' => $commRate * 3,  'pts' => 105],
            ['refs' => 5,  'cash' => $commRate * 5,  'pts' => 175],
            ['refs' => 10, 'cash' => $commRate * 10, 'pts' => 380],
        ];

        $stats = [
            'referrals' => $totalRefs,
            'earned'    => $commissions->sum('amount'),
            'pending'   => $commissions->where('status', 'pending')->sum('amount'),
            'paid_out'  => $commissions->where('status', 'paid')->sum('amount'),
        ];

        $refUrl = url('/user/register?ref=' . ($user->slug ?? $user->id));

        return view('frontend.buyer.affiliate', compact(
            'user', 'commissions', 'stats',
            'totalRefs', 'tierLabel', 'nextLabel', 'tierPct', 'remaining',
            'projections', 'refUrl', 'commRate'
        ));
    }

    public function notifications()
    {
        $user          = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $user->unreadNotifications()->update(['read_at' => now()]);
        return view('frontend.buyer.notifications', compact('notifications'));
    }

    public function notificationsReadAll()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
