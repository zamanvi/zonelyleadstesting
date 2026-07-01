<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Blog;
use App\Models\Category;
use App\Models\City;
use App\Models\Lead;
use App\Models\PlatformCharge;
use App\Models\State;
use App\Models\User;
use App\Models\AffiliateCommission;
use App\Services\Sms\SmsService;
use App\Services\PointsService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    function user_login()
    {
        return view('frontend.auth.login');
    }
    function user_register1()
    {
        return view('frontend.auth.register1');
    }
    function user_register_category()
    {
        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('children')->get();
        return view('frontend.auth.register_category', compact('categories'));
    }

    function user_save_category(Request $request)
    {
        $categoryId = ($request->category_id && $request->category_id !== 'other')
            ? (int) $request->category_id
            : null;

        auth()->user()->update(['category_id' => $categoryId]);

        return redirect()->route('seller.onboarding')->with('success', 'Category saved! Now complete your business profile.');
    }

    function user_register2($type)
    {
        $categories = Category::where('is_active', true)->whereNull('parent_id')->get();
        return view('frontend.auth.register', compact('type', 'categories'));
    }
    function user_submit_login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->filled('remember');
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            return redirect()->route('dashboard')->with('success', 'Welcome back, ' . $user->name);
        }
        return back()->withErrors(['email' => 'The provided credentials do not match our records.',])->onlyInput('email');
    }
    function user_submit_register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:6'],
            'type'     => ['required', 'in:seller,user'],
        ]);

        $referrer = null;
        if ($request->filled('ref')) {
            $referrer = User::where('slug', $request->ref)
                ->orWhere('id', $request->ref)
                ->first();
        }

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'phone'         => $request->phone ?? null,
            'type'          => $validated['type'],
            'business_name' => $request->business_name ?? '',
            'password'      => Hash::make($validated['password']),
            'slug'          => generateUniqueSlug(User::class, $validated['name']),
            'referred_by'   => $referrer?->id,
            'status'        => true, // live immediately — no approval freeze
        ]);
        Auth::login($user);

        // Award points to referrer for this new signup
        PointsService::onReferralJoin($user);
        // Award 2nd-level points to grandparent referrer
        PointsService::onSecondLevelReferral($user);
        // Check if referrer just hit a tier milestone
        if ($user->referred_by) {
            $ref = User::find($user->referred_by);
            if ($ref) PointsService::checkTierMilestones($ref);
        }

        if ($user->type === 'user') {
            return redirect()->route('buyer.dashboard')->with('success', 'Welcome to Zonely! Find local experts near you.');
        }
        if ($user->type === 'seller') {
            return redirect()->route('user.register.category')->with('success', 'Account created! Now select your business category.');
        }
        return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome, ' . $user->name);
    }
    private function defaultMeta(): array
    {
        return [
            'meta_title'       => 'Zonely - Discover & Hire Local Experts Near Me',
            'meta_description' => 'Find trusted local experts near you with Zonely. Compare lawyers, consultants, and more professionals. Read reviews and contact verified pros instantly',
            'meta_keywords'    => 'Lawyers near me; Insurance agents near me; Consultants near me; Real estate agents near me; Local health professionals near me;',
        ];
    }

    function home()
    {
        ['meta_title' => $meta_title, 'meta_description' => $meta_description, 'meta_keywords' => $meta_keywords] = $this->defaultMeta();
        $users = User::activeSellers()->with('reviews')->latest()->take(8)->get();
        $stats = \Illuminate\Support\Facades\Cache::remember('home_stats', 600, fn() => [
            'pros'    => User::activeSellers()->count(),
            'cities'  => User::activeSellers()->whereNotNull('city')->distinct('city')->count('city'),
            'reviews' => \App\Models\Review::count(),
        ]);
        $categories = Category::where('is_active', true)->whereNull('parent_id')->withCount('children')->take(8)->get();
        $featuredReviews = \App\Models\Review::with('reviewer:id,name,profile_photo', 'seller:id,name,slug,title,designation')
            ->where('rating', '>=', 4)
            ->whereNotNull('reviewer_id')
            ->latest()
            ->take(6)
            ->get();
        return view('frontend.home', compact('users', 'meta_title', 'meta_description', 'meta_keywords', 'stats', 'categories', 'featuredReviews'));
    }
    function service_all()
    {
        $users = User::activeSellers()->latest()->paginate(12);
        $isSearch = false;
        ['meta_title' => $meta_title, 'meta_description' => $meta_description, 'meta_keywords' => $meta_keywords] = $this->defaultMeta();
        return view('frontend.service_all', compact('users', 'isSearch', 'meta_title', 'meta_description', 'meta_keywords'));
    }
    function service_search(Request $request)
    {
        $query = $request->input('q');
        $city  = $request->input('city');
        $users = User::activeSellers()
            ->when($city, fn($q) => $q->where(function($q) use ($city) {
                $q->where('city', 'like', '%' . $city . '%')
                  ->orWhere('state', 'like', '%' . $city . '%')
                  ->orWhere('zip_code', 'like', '%' . $city . '%');
            }))
            ->when($query, fn($q) => $q->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                    ->orWhere('title', 'like', '%' . $query . '%')
                    ->orWhere('designation', 'like', '%' . $query . '%')
                    ->orWhere('work_address', 'like', '%' . $query . '%')
                    ->orWhere('about', 'like', '%' . $query . '%')
                    ->orWhere('tags', 'like', '%' . $query . '%')
                    ->orWhere('remark', 'like', '%' . $query . '%');
            }))
            ->paginate(12)
            ->appends(['q' => $query, 'city' => $city]);
        $isSearch = true;
        ['meta_title' => $meta_title, 'meta_description' => $meta_description, 'meta_keywords' => $meta_keywords] = $this->defaultMeta();
        return view('frontend.service_all', compact('users', 'isSearch', 'query', 'city', 'meta_title', 'meta_description', 'meta_keywords'));
    }

    function category_show($slug)
    {
        $category = Category::where('slug', $slug)->with('children')->firstOrFail();
        $categoryIds = $category->children->pluck('id')->prepend($category->id);
        $users = User::activeSellers()
            ->whereIn('category_id', $categoryIds)
            ->latest()
            ->paginate(12);
        $meta_title = $category->title . ' — Zonely';
        $meta_description = 'Find trusted local ' . $category->title . ' experts near you with Zonely.';
        $meta_keywords = $category->title . ' near me;';
        $isSearch = false;
        return view('frontend.service_all', compact('users', 'category', 'isSearch', 'meta_title', 'meta_description', 'meta_keywords'));
    }

    function service_show($slug)
    {
        $user = User::activeSellers()->where('slug', $slug)
            ->with(['contacts','languages','educations','memberships','services.category','reviews.reviewer','category','twilioNumber','faqs','experiences','certifications','gallery'])
            ->firstOrFail();

        $isOverdue = $user->isOverdue();

        return view('frontend.service_details_professional', compact('user', 'isOverdue'));
    }

    function shareCard($slug)
    {
        $user = User::activeSellers()->where('slug', $slug)
            ->with(['services', 'category'])
            ->firstOrFail();
        return view('frontend.share_card', compact('user'));
    }

    function ogImage($slug)
    {
        $user = User::activeSellers()
            ->where('slug', $slug)
            ->with(['services', 'category', 'reviews'])
            ->firstOrFail();

        return (new \App\Services\OgImageService())->render($user);
    }

    function serviceInquiry(Request $request, $slug)
    {
        $seller = User::activeSellers()->where('slug', $slug)->firstOrFail();

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        $stateId    = \App\Models\State::where('title', $seller->state)->value('id');
        $cityId     = \App\Models\City::where('title', $seller->city)->value('id');
        $leadFee    = PlatformCharge::resolve('lead_fee', $seller->category_id, $stateId, $cityId);
        $affComm    = PlatformCharge::resolve('affiliate_commission', $seller->category_id, $stateId, $cityId);

        $lead = Lead::create([
            'seller_id' => $seller->id,
            'source'    => 'form',
            'name'      => $request->name,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'service'   => $request->service ?? 'General Inquiry',
            'message'   => $request->message ?? null,
            'status'    => 'new',
            'fee'       => $leadFee,
        ]);
        NotificationService::newLead($lead);

        // Auto-create affiliate commission on seller's FIRST lead
        // Use firstOrCreate keyed on referrer+referred — safe against double submissions
        if ($seller->referred_by) {
            $alreadyPaid = AffiliateCommission::where('referrer_id', $seller->referred_by)
                ->where('referred_user_id', $seller->id)
                ->where('referral_type', 'seller')
                ->exists();
            if (!$alreadyPaid) {
                AffiliateCommission::create([
                    'referrer_id'      => $seller->referred_by,
                    'referred_user_id' => $seller->id,
                    'amount'           => $affComm,
                    'status'           => 'pending',
                    'referral_type'    => 'seller',
                ]);
            }
        }

        // Auto-create buyer referral commission on buyer's FIRST booking
        $buyer = Auth::user();
        if ($buyer && $buyer->referred_by) {
            $buyerRefComm = PlatformCharge::resolve('buyer_referral_commission');
            $buyerAlreadyPaid = AffiliateCommission::where('referrer_id', $buyer->referred_by)
                ->where('referred_user_id', $buyer->id)
                ->where('referral_type', 'buyer')
                ->exists();
            if (!$buyerAlreadyPaid) {
                AffiliateCommission::create([
                    'referrer_id'      => $buyer->referred_by,
                    'referred_user_id' => $buyer->id,
                    'amount'           => $buyerRefComm,
                    'status'           => 'pending',
                    'referral_type'    => 'buyer',
                ]);
            }
        }

        // Award points: referrer of seller gets +25 on first lead
        PointsService::onReferralFirstJob($seller);

        // Award points: referrer of buyer gets +25 on first booking
        $buyer = Auth::user();
        if ($buyer) PointsService::onReferralFirstJob($buyer);

        if ($seller->twilio_enabled && $seller->phone) {
            $msg = "🔔 New Zonely Lead!\n"
                 . "Name: {$lead->name}\n"
                 . "Phone: {$lead->phone}\n"
                 . "Service: {$lead->service}\n"
                 . ($lead->message ? "Message: " . Str::limit($lead->message, 80) . "\n" : '')
                 . "View: " . route('seller.dashboard');
            (new SmsService())->send($seller->phone, $msg);
        }

        return back()->with('inquiry_success', 'Your request has been sent! ' . $seller->name . ' will contact you shortly.');
    }

    function waClick(Request $request, $slug)
    {
        $seller = User::activeSellers()->where('slug', $slug)->firstOrFail();

        $waNumber = $seller->contacts()->where('type', 'whatsapp')->value('value')
            ?? $seller->whatsapp;

        $waStateId  = \App\Models\State::where('title', $seller->state)->value('id');
        $waCityId   = \App\Models\City::where('title', $seller->city)->value('id');
        $waLeadFee  = PlatformCharge::resolve('lead_fee', $seller->category_id, $waStateId, $waCityId);

        $lead = Lead::create([
            'seller_id' => $seller->id,
            'source'    => 'whatsapp',
            'name'      => 'WhatsApp Lead',
            'phone'     => '',
            'email'     => '',
            'service'   => 'WhatsApp Click',
            'message'   => 'Client clicked WhatsApp from Zonely profile.',
            'status'    => 'new',
            'fee'       => $waLeadFee,
        ]);
        NotificationService::newLead($lead);

        if ($seller->twilio_enabled && $seller->phone) {
            $msg = "💬 New WhatsApp Lead!\nClient clicked your WhatsApp button on Zonely.\nView: " . route('seller.dashboard');
            (new SmsService())->send($seller->phone, $msg);
        }

        $clean = preg_replace('/[^0-9]/', '', $waNumber ?? '');
        return response()->json(['url' => 'https://wa.me/' . $clean]);
    }

    function emailInquiry(Request $request, $slug)
    {
        $seller = User::activeSellers()->where('slug', $slug)->firstOrFail();

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        $stateId = \App\Models\State::where('title', $seller->state)->value('id');
        $cityId  = \App\Models\City::where('title', $seller->city)->value('id');
        $leadFee = PlatformCharge::resolve('lead_fee', $seller->category_id, $stateId, $cityId);

        $lead = Lead::create([
            'seller_id' => $seller->id,
            'source'    => 'email',
            'name'      => $request->name,
            'phone'     => $request->phone ?? '',
            'email'     => $request->email,
            'service'   => 'Email Inquiry',
            'message'   => $request->message,
            'status'    => 'new',
            'fee'       => $leadFee,
        ]);
        NotificationService::newLead($lead);

        // Send email to seller
        try {
            \Mail::raw(
                "New email inquiry from {$request->name} via Zonely!\n\n"
                . "Name: {$request->name}\n"
                . "Email: {$request->email}\n"
                . ($request->phone ? "Phone: {$request->phone}\n" : '')
                . "Message: {$request->message}\n\n"
                . "View lead: " . route('seller.dashboard'),
                function ($m) use ($seller, $request) {
                    $m->to($seller->email)
                      ->subject("New Inquiry from {$request->name} — Zonely");
                }
            );
        } catch (\Throwable $e) {
            \Log::warning('Email inquiry mail failed: ' . $e->getMessage());
        }

        // SMS alert
        if ($seller->twilio_enabled && $seller->phone) {
            $msg = "📧 New Email Lead!\n"
                 . "From: {$request->name}\n"
                 . "Email: {$request->email}\n"
                 . "View: " . route('seller.dashboard');
            (new \App\Services\Sms\SmsService())->send($seller->phone, $msg);
        }

        return back()->with('email_success', 'Your message has been sent! ' . $seller->name . ' will reply to your email shortly.');
    }

    function termsAgree()
    {
        if (auth()->user()?->agreed_terms_at) {
            return redirect()->route('dashboard');
        }

        $user = auth()->user();
        if ($user && $user->type === 'seller') {
            return view('frontend.seller.terms');
        }
        if ($user && $user->type === 'user') {
            return view('frontend.buyer.terms');
        }

        return view('frontend.terms_agree');
    }

    function termsStore(Request $request)
    {
        $request->validate([
            'agree' => 'accepted',
        ]);

        auth()->user()->update(['agreed_terms_at' => now()]);

        return redirect()->route('dashboard')->with('success', 'Thank you for agreeing to our Terms & Conditions.');
    }

    function help()
    {
        return view('frontend.help');
    }
    function contact()
    {
        return view('frontend.contact');
    }
    function privacy_policy()
    {
        return view('frontend.privacy_policy');
    }
    function terms_and_condition()
    {
        return view('frontend.terms_and_condition');
    }
    function about_us()
    {
        return view('frontend.about_us');
    }
    function about_site_author()
    {
        return view('frontend.about_site_author');
    }
    function tools()
    {
        $meta_title = 'Free Car Insurance Calculator for NYC, USA';
        $meta_description = 'Use our Free Car Insurance Calculator for NYC, USA to instantly estimate your monthly and yearly auto insurance costs. Compare rates, save money, and get accurate results fast.';
        $meta_keywords = 'NYC Car Insurance Calculator; Free Auto Insurance Quote NYC; Compare Car Insurance Rates NYC; NYC Vehicle Insurance Estimator; Cheap Car Insurance NYC;';
        return view('frontend.tools', compact('meta_title', 'meta_description', 'meta_keywords'));
    }
    function blog()
    {
        $featuredBlog = Blog::latest()->first();
        $blogs        = $this->sideBlogs($featuredBlog?->id);
        $meta_title = 'Zonely - Discover & Hire Local Experts Near Me';
        $meta_description = 'Find trusted local experts near you with Zonely. Compare lawyers, consultants, and more professionals. Read reviews and contact verified pros instantly';
        $meta_keywords = 'Lawyers near me; Insurance agents near me; Consultants near me; Real estate agents near me; Local health professionals near me;';
        return view('frontend.blog', compact('featuredBlog', 'blogs', 'meta_title', 'meta_description', 'meta_keywords'));
    }

    function blog_show($slug)
    {
        $blog  = Blog::where('slug', $slug)->firstOrFail();
        $blog->increment('pageview');
        $blogs = $this->sideBlogs($blog->id);
        $meta_title = 'Zonely - Discover & Hire Local Experts Near Me';
        $meta_description = 'Find trusted local experts near you with Zonely. Compare lawyers, consultants, and more professionals. Read reviews and contact verified pros instantly';
        $meta_keywords = 'Lawyers near me; Insurance agents near me; Consultants near me; Real estate agents near me; Local health professionals near me;';
        return view('frontend.blog_details', compact('blog', 'meta_title', 'meta_description', 'meta_keywords'));
    }

    private function sideBlogs(?int $excludeId)
    {
        return Blog::when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->latest()
            ->take(10)
            ->get();
    }
    function sitemap()
    {
        $now = Carbon::now()->toAtomString();

        // Static pages — local SEO priorities
        $static = collect([
            // Home: hub for local service discovery
            ['loc' => route('frontend.home'),                'priority' => '1.0', 'changefreq' => 'daily',   'lastmod' => $now],
            // Browse all: high-intent "find local expert" page
            ['loc' => route('frontend.service.all'),         'priority' => '0.9', 'changefreq' => 'daily',   'lastmod' => $now],
            // Blog: local SEO content
            ['loc' => route('frontend.blog'),                'priority' => '0.6', 'changefreq' => 'weekly',  'lastmod' => $now],
            ['loc' => route('frontend.tools'),               'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('frontend.about-us'),            'priority' => '0.4', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('frontend.help'),                'priority' => '0.3', 'changefreq' => 'monthly', 'lastmod' => $now],
            ['loc' => route('frontend.privacy-policy'),      'priority' => '0.2', 'changefreq' => 'yearly',  'lastmod' => $now],
            ['loc' => route('frontend.terms-and-condition'), 'priority' => '0.2', 'changefreq' => 'yearly',  'lastmod' => $now],
        ]);

        // Category pages — top local SEO pages ("plumbers near me", "lawyers in [city]")
        $categories = Category::where('is_active', 1)
            ->whereNotNull('slug')
            ->select('slug', 'updated_at')
            ->get()
            ->map(fn($c) => [
                'loc'        => route('frontend.category', $c->slug),
                'priority'   => '0.9',
                'changefreq' => 'daily',
                'lastmod'    => optional($c->updated_at)->toAtomString() ?? $now,
            ]);

        // Seller profile pages — highest value: rank for "[name] + [city]" and "[service] in [city]"
        $sellers = User::where('type', 'seller')
            ->where('status', true)
            ->whereNotNull('slug')
            ->select('slug', 'updated_at')
            ->get()
            ->map(fn($u) => [
                'loc'        => route('frontend.service.show', $u->slug),
                'priority'   => '1.0',
                'changefreq' => 'weekly',
                'lastmod'    => optional($u->updated_at)->toAtomString() ?? $now,
            ]);

        // Blog posts — local SEO content marketing
        $blogs = Blog::select('slug', 'updated_at')
            ->get()
            ->map(fn($b) => [
                'loc'        => route('blog.show', $b->slug),
                'priority'   => '0.7',
                'changefreq' => 'monthly',
                'lastmod'    => optional($b->updated_at)->toAtomString() ?? $now,
            ]);

        $sitemapEntries = $static->merge($categories)->merge($sellers)->merge($blogs);

        return response()
            ->view('frontend.sitemap', compact('sitemapEntries'))
            ->header('Content-Type', 'application/xml');
    }
}
