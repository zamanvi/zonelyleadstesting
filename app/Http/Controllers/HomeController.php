<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Lead;
use App\Models\User;
use App\Models\AffiliateCommission;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
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
        ]);
        Auth::login($user);
        if ($user->type === 'user') {
            return redirect()->route('buyer.dashboard')->with('success', 'Welcome to Zonely! Find local experts near you.');
        }
        if ($user->type === 'seller') {
            return redirect()->route('user.register.category')->with('success', 'Account created! Now select your business category.');
        }
        return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome, ' . $user->name);
    }
    function home()
    {
        $meta_title = 'Zonely - Discover & Hire Local Experts Near Me';
        $meta_description = 'Find trusted local experts near you with Zonely. Compare lawyers, consultants, and more professionals. Read reviews and contact verified pros instantly';
        $meta_keywords = 'Lawyers near me; Insurance agents near me; Consultants near me; Real estate agents near me; Local health professionals near me;';
        $users = User::activeSellers()->with('reviews')->latest()->take(8)->get();
        $stats = [
            'pros'   => User::activeSellers()->count(),
            'cities' => User::activeSellers()->whereNotNull('city')->distinct('city')->count('city'),
            'reviews'=> \App\Models\Review::count(),
        ];
        $categories = Category::where('is_active', true)->whereNull('parent_id')->withCount('children')->take(8)->get();
        $featuredReviews = \App\Models\Review::with('reviewer:id,name,profile_photo', 'seller:id,name,slug,title,designation')
            ->where('rating', '>=', 4)
            ->latest()
            ->take(6)
            ->get();
        return view('frontend.home', compact('users', 'meta_title', 'meta_description', 'meta_keywords', 'stats', 'categories', 'featuredReviews'));
    }
    function service_all()
    {
        $users = User::activeSellers()->latest()->paginate(12);
        $isSearch = false;
        $meta_title = 'Zonely - Discover & Hire Local Experts Near Me';
        $meta_description = 'Find trusted local experts near you with Zonely. Compare lawyers, consultants, and more professionals. Read reviews and contact verified pros instantly';
        $meta_keywords = 'Lawyers near me; Insurance agents near me; Consultants near me; Real estate agents near me; Local health professionals near me;';
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
        $meta_title = 'Zonely - Discover & Hire Local Experts Near Me';
        $meta_description = 'Find trusted local experts near you with Zonely. Compare lawyers, consultants, and more professionals. Read reviews and contact verified pros instantly';
        $meta_keywords = 'Lawyers near me; Insurance agents near me; Consultants near me; Real estate agents near me; Local health professionals near me;';
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
        $meta_title = $category->name . ' — Zonely';
        $meta_description = 'Find trusted local ' . $category->name . ' experts near you with Zonely.';
        $meta_keywords = $category->name . ' near me;';
        return view('frontend.service_all', compact('users', 'category', 'meta_title', 'meta_description', 'meta_keywords'));
    }

    function service_show($slug)
    {
        $user = User::activeSellers()->where('slug', $slug)
            ->with(['contacts','languages','educations','memberships','services.category','reviews.reviewer','category','twilioNumber','faqs'])
            ->firstOrFail();

        // Load new relationships only if their tables exist (guards first deploy)
        if (\Illuminate\Support\Facades\Schema::hasTable('experiences')) {
            $user->load('experiences');
        } else {
            $user->setRelation('experiences', collect());
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('certifications')) {
            $user->load('certifications');
        } else {
            $user->setRelation('certifications', collect());
        }

        $view = $user->seller_service_type === 'professional'
            ? 'frontend.service_details_professional'
            : 'frontend.service_details';
        return view($view, compact('user'));
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

        // Cache: serve previously generated PNG instantly so Facebook/LinkedIn/
        // Twitter crawlers (which have short timeouts) actually get the image.
        // Cache key includes updated_at so it auto-busts when the profile changes.
        $cacheDir  = storage_path('app/public/og-cache');
        $cacheFile = $cacheDir . '/' . $user->slug . '-' . $user->updated_at->timestamp . '.png';
        if (file_exists($cacheFile)) {
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=31536000, immutable');
            readfile($cacheFile);
            exit;
        }
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $W = 1080; $H = 1080;
        $img = imagecreatetruecolor($W, $H);
        imagealphablending($img, true);

        // Palette
        $cBg        = imagecolorallocate($img,  8,  42,  40);
        $cPanel     = imagecolorallocate($img, 13,  55,  52);
        $cGold      = imagecolorallocate($img, 212, 175,  55);
        $cGoldLight = imagecolorallocate($img, 245, 215, 140);
        $cGoldMid   = imagecolorallocate($img, 230, 195, 100);
        $cGoldDim   = imagecolorallocate($img, 170, 138,  40);
        $cWhite     = imagecolorallocate($img, 255, 255, 255);
        $cWhiteDim  = imagecolorallocate($img, 200, 222, 220);

        // Fonts
        $fontPaths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        ];
        $fontRegPaths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
        ];
        $fontB = collect($fontPaths)->first(fn($p) => file_exists($p));
        $fontR = collect($fontRegPaths)->first(fn($p) => file_exists($p)) ?? $fontB;
        $ttf   = (bool)$fontB;

        // Two-panel background — 50/50 split for square format
        $photoW = 540;
        imagefilledrectangle($img, 0,       0, $photoW, $H, $cBg);
        imagefilledrectangle($img, $photoW, 0, $W,      $H, $cPanel);

        // Outer gold border
        $borderC = imagecolorallocatealpha($img, 212, 175, 55, 70);
        imagesetthickness($img, 3);
        imagerectangle($img, 2, 2, $W - 2, $H - 2, $borderC);
        imagesetthickness($img, 1);

        // Corner accent marks (top-left, bottom-right) — small L-shapes
        $ac = 28; $at = 3;
        imagefilledrectangle($img, 14, 14, 14 + $ac, 14 + $at, $cGold); // top-left H
        imagefilledrectangle($img, 14, 14, 14 + $at, 14 + $ac, $cGold); // top-left V
        imagefilledrectangle($img, $W - 14 - $ac, $H - 14 - $at, $W - 14, $H - 14, $cGold); // bottom-right H
        imagefilledrectangle($img, $W - 14 - $at, $H - 14 - $ac, $W - 14, $H - 14, $cGold); // bottom-right V

        // Photo loading
        $photoLoaded = false;
        $src = false;
        if ($user->profile_photo) {
            if (str_starts_with($user->profile_photo, 'http')) {
                $imgData = @file_get_contents($user->profile_photo);
                if ($imgData) $src = @imagecreatefromstring($imgData);
            } else {
                $photo = ltrim($user->profile_photo, '/');
                $fsPaths = [
                    storage_path('app/public/' . preg_replace('#^storage/#', '', $photo)),
                    public_path($photo),
                    base_path('public/' . $photo),
                ];
                foreach ($fsPaths as $tryPath) {
                    if ($src) break;
                    if (!file_exists($tryPath)) continue;
                    $ext = strtolower(pathinfo($tryPath, PATHINFO_EXTENSION));
                    $src = match($ext) {
                        'jpg','jpeg' => @imagecreatefromjpeg($tryPath),
                        'png'        => @imagecreatefrompng($tryPath),
                        'webp'       => @imagecreatefromwebp($tryPath),
                        default      => false,
                    };
                }
            }
            if ($src) {
                $sw = imagesx($src); $sh = imagesy($src);
                $ratio = $photoW / $H;
                if ($sw / $sh > $ratio) {
                    $cropH = $sh; $cropW = (int)($sh * $ratio);
                    $srcX  = (int)(($sw - $cropW) / 2); $srcY = 0;
                } else {
                    $cropW = $sw; $cropH = (int)($sw / $ratio);
                    $srcX  = 0;   $srcY  = 0;
                }
                imagecopyresampled($img, $src, 0, 0, $srcX, $srcY, $photoW, $H, $cropW, $cropH);
                imagedestroy($src);
                $photoLoaded = true;
            }
        }

        if (!$photoLoaded) {
            $cPanelBg = imagecolorallocate($img, 14, 68, 64);
            imagefilledrectangle($img, 0, 0, $photoW - 1, $H, $cPanelBg);
            $logoPath = public_path('frontend/img/zonely_logo.png');
            $logo     = file_exists($logoPath) ? @imagecreatefrompng($logoPath) : false;
            if ($logo) {
                $lw = imagesx($logo); $lh = imagesy($logo);
                $scale = min(280 / $lw, 170 / $lh, 1);
                $dw = (int)($lw * $scale); $dh = (int)($lh * $scale);
                imagealphablending($img, true);
                imagesavealpha($logo, true);
                imagecopyresampled($img, $logo, (int)(($photoW - $dw) / 2), (int)(($H - $dh) / 2), 0, 0, $dw, $dh, $lw, $lh);
                imagedestroy($logo);
            }
        }

        // Subtle teal tint over entire photo (blends grey/white backgrounds into palette)
        if ($photoLoaded) {
            $tintC = imagecolorallocatealpha($img, 8, 42, 40, 100); // ~21% opaque teal tint
            imagefilledrectangle($img, 0, 0, $photoW - 1, $H, $tintC);
        }

        // Right-edge scrim: photo fades into content panel
        $scrimW = 180;
        for ($sx = 0; $sx < $scrimW; $sx++) {
            $alpha = (int)(127 * (1 - $sx / $scrimW));
            $c = imagecolorallocatealpha($img, 8, 42, 40, $alpha);
            imageline($img, $photoW - $scrimW + $sx, 0, $photoW - $scrimW + $sx, $H, $c);
        }

        // Thin vertical gold separator
        $sepC = imagecolorallocatealpha($img, 212, 175, 55, 85);
        imagesetthickness($img, 2);
        imageline($img, $photoW, 6, $photoW, $H - 6, $sepC);
        imagesetthickness($img, 1);

        // Verified badge on photo (top-left)
        if ($user->status) {
            $bw = 120; $bh = 30; $bx = 18; $by = 18; $rb = 15;
            imagefilledrectangle($img, $bx + $rb, $by, $bx + $bw - $rb, $by + $bh, $cGold);
            imagefilledrectangle($img, $bx, $by + $rb, $bx + $bw, $by + $bh - $rb, $cGold);
            imagefilledellipse($img, $bx + $rb,       $by + $rb, $rb * 2, $rb * 2, $cGold);
            imagefilledellipse($img, $bx + $bw - $rb, $by + $rb, $rb * 2, $rb * 2, $cGold);
            if ($ttf) imagettftext($img, 12, 0, $bx + 16, $by + 21, $cBg, $fontB, 'VERIFIED');
        }

        // ── Content panel ─────────────────────────────────────────────────
        $rx = $photoW + 58;
        $rw = $W - $rx - 20;

        $name      = $user->name ?? 'Professional';
        $desig     = Str::limit($user->designation ?? $user->category?->title ?? '', 44);
        $specialty = Str::limit(trim(Str::before($user->title ?? '', '|')), 46);
        $loc       = $user->city ? ($user->city . ($user->state ? ', ' . $user->state : '')) : '';
        $exp       = (int)($user->experience ?? 0);
        $svcs      = $user->services->take(6)->filter(function($s) {
            $t = preg_replace('/[^\x20-\x7E]/u', '', $s->title ?? '');
            return trim($t) !== '';
        })->take(4)->values();
        $rating    = $user->reviews->whereNotNull('rating')->avg('rating');
        $revCount  = $user->reviews->whereNotNull('rating')->count();

        // Adaptive name size — must fit panel width
        $fs = 60;
        if ($ttf) {
            foreach ([60, 50, 40, 30] as $try) {
                $b = @imagettfbbox($try, 0, $fontB, $name);
                if (!$b || abs($b[4] - $b[0]) <= $rw) { $fs = $try; break; }
            }
        }

        // Measure total content height for vertical centering
        $btnY1 = $H - 72;
        $cH    = $fs + 18;
        if ($desig)              $cH += 42;
        $cH += 14;
        if ($specialty)          $cH += 36;
        if ($loc)                $cH += 36;
        if ($exp)                $cH += 34;
        $cH += 18;
        $cH += $svcs->count() * 38;
        if ($rating && $revCount > 0) $cH += 36;
        $cy = max(36, (int)(($btnY1 - $cH) / 2));

        // Gold accent line above name
        imagefilledrectangle($img, $rx, $cy - 18, $rx + 60, $cy - 13, $cGold);

        // Name — largest element, gold
        if ($ttf) imagettftext($img, $fs, 0, $rx, $cy, $cGoldLight, $fontB, $name);
        $cy += $fs + 18;

        // Category
        if ($ttf && $desig) {
            imagettftext($img, 25, 0, $rx, $cy, $cGoldMid, $fontB, $desig);
            $cy += 42;
        }

        $cy += 14;

        // Specialty
        if ($ttf && $specialty) {
            imagettftext($img, 23, 0, $rx, $cy, $cWhite, $fontB, $specialty);
            $cy += 36;
        }

        // Location
        if ($ttf && $loc) {
            imagettftext($img, 22, 0, $rx, $cy, $cGoldMid, $fontR ?? $fontB, $loc);
            $cy += 36;
        }

        // Experience
        if ($ttf && $exp) {
            imagettftext($img, 20, 0, $rx, $cy, $cGoldLight, $fontB, $exp . '+ Yrs Experience');
            $cy += 34;
        }

        $cy += 10;

        // Gold divider
        $divC = imagecolorallocatealpha($img, 212, 175, 55, 100);
        imagesetthickness($img, 2);
        imageline($img, $rx, $cy, $rx + $rw, $cy, $divC);
        imagesetthickness($img, 1);
        $cy += 18;

        // Services — larger bullets + text
        foreach ($svcs as $svc) {
            $t  = Str::limit(preg_replace('/[^\x20-\x7E]/u', '', $svc->title ?? ''), 40);
            if (!trim($t)) continue;
            $bx = $rx + 14; $by = $cy - 9;
            imageellipse($img, $bx, $by, 22, 22, $cGoldLight);
            imagefilledellipse($img, $bx, $by, 10, 10, $cGoldLight);
            if ($ttf) imagettftext($img, 20, 0, $rx + 38, $cy, $cWhiteDim, $fontR ?? $fontB, $t);
            $cy += 38;
        }

        // Rating
        if ($rating && $revCount > 0 && $ttf) {
            $stars    = str_repeat('★', (int)round($rating)) . str_repeat('☆', 5 - (int)round($rating));
            $ratingTx = number_format($rating, 1) . '/5  (' . $revCount . ' reviews)';
            imagettftext($img, 20, 0, $rx, $cy + 8, $cGold, $fontB, $ratingTx);
            $cy += 36;
        }

        // CTA Button — gold pill, full content-panel width
        $btnY2 = $H - 4;
        $btnX1 = $rx;
        $btnX2 = $W - 12;
        $btnH  = $btnY2 - $btnY1;
        $br    = (int)($btnH / 2);

        // Glow shadow behind button
        $glowC = imagecolorallocatealpha($img, 212, 175, 55, 80);
        imagefilledrectangle($img, $btnX1 + $br, $btnY1 + 5, $btnX2 - $br, $btnY2 + 5, $glowC);
        imagefilledellipse($img, $btnX1 + $br, $btnY1 + $br + 5, $br * 2, $br * 2, $glowC);
        imagefilledellipse($img, $btnX2 - $br, $btnY1 + $br + 5, $br * 2, $br * 2, $glowC);

        // Button fill — brighter gold
        $cBtnGold = imagecolorallocate($img, 230, 195, 60);
        imagefilledrectangle($img, $btnX1 + $br, $btnY1, $btnX2 - $br, $btnY2, $cBtnGold);
        imagefilledrectangle($img, $btnX1, $btnY1 + $br, $btnX2, $btnY2 - $br, $cBtnGold);
        imagefilledellipse($img, $btnX1 + $br, $btnY1 + $br, $br * 2, $br * 2, $cBtnGold);
        imagefilledellipse($img, $btnX2 - $br, $btnY1 + $br, $br * 2, $br * 2, $cBtnGold);

        // White highlight top edge — 3D effect
        $hiC = imagecolorallocatealpha($img, 255, 255, 255, 90);
        imagefilledrectangle($img, $btnX1 + $br, $btnY1, $btnX2 - $br, $btnY1 + 6, $hiC);

        if ($ttf) {
            $btnTx = 'VIEW PROFILE  >>';
            $bbox  = @imagettfbbox(23, 0, $fontB, $btnTx);
            $txW   = $bbox ? abs($bbox[4] - $bbox[0]) : 180;
            $txX   = $btnX1 + (int)(($btnX2 - $btnX1 - $txW) / 2);
            imagettftext($img, 23, 0, $txX, $btnY1 + (int)($btnH / 2) + 9, $cBg, $fontB, $btnTx);
        }

        // Save the generated image to cache, then serve it.
        // Subsequent requests will be served from the cache check at the top.
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            @imagepng($img, $cacheFile, 6);
            imagedestroy($img);
            if (file_exists($cacheFile)) {
                header('Content-Type: image/png');
                header('Cache-Control: public, max-age=31536000, immutable');
                readfile($cacheFile);
                exit;
            }
        }
        // Fallback if cache write failed — stream directly
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=3600');
        imagepng($img, null, 6);
        imagedestroy($img);
        exit;
    }

    function serviceInquiry(Request $request, $slug)
    {
        $seller = User::activeSellers()->where('slug', $slug)->firstOrFail();

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        $lead = Lead::create([
            'seller_id' => $seller->id,
            'name'      => $request->name,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'service'   => $request->service ?? 'General Inquiry',
            'message'   => $request->message ?? null,
            'status'    => 'new',
            'fee'       => 68,
        ]);

        // Auto-create affiliate commission on seller's FIRST lead
        if ($seller->referred_by && $lead && $seller->leads()->count() === 1) {
            AffiliateCommission::firstOrCreate(
                ['referrer_id' => $seller->referred_by, 'referred_user_id' => $seller->id],
                ['amount' => 10, 'status' => 'pending']
            );
        }

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

        $lead = Lead::create([
            'seller_id' => $seller->id,
            'name'      => 'WhatsApp Lead',
            'phone'     => '',
            'email'     => '',
            'service'   => 'WhatsApp Click',
            'message'   => 'Client clicked WhatsApp from Zonely profile.',
            'status'    => 'new',
            'fee'       => 68,
        ]);

        if ($seller->twilio_enabled && $seller->phone) {
            $msg = "💬 New WhatsApp Lead!\nClient clicked your WhatsApp button on Zonely.\nView: " . route('seller.dashboard');
            (new SmsService())->send($seller->phone, $msg);
        }

        $clean = preg_replace('/[^0-9]/', '', $waNumber ?? '');
        return response()->json(['url' => 'https://wa.me/' . $clean]);
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
        if ($user && $user->type === 'buyer') {
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
