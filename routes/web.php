<?php

// Health check — no DB needed, Railway healthcheck passes immediately
Route::get('/health', function () { return response('OK', 200); });

use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\TwilioController;
use App\Http\Controllers\Admin\PhonePoolController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\HuntBotController;
use App\Http\Controllers\TwilioWebhookController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PostalCodeController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StateController;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\PostalCode;
use App\Models\State;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::name('frontend.')->group(function () {
    Route::get('/', [HomeController::class, 'home'])->name('home');
    Route::get('all-service', [HomeController::class, 'service_all'])->name('service.all');
    Route::get('search', [HomeController::class, 'service_search'])->name('service.search');
    Route::get('service/{slug}', [HomeController::class, 'service_show'])->name('service.show');
    Route::get('og-image/{slug}', [HomeController::class, 'ogImage'])->name('og.image');
    Route::get('card/{slug}', [HomeController::class, 'shareCard'])->name('share.card');
    Route::get('category/{slug}', [HomeController::class, 'category_show'])->name('category');
    Route::get('/help', [HomeController::class, 'help'])->name('help');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::get('/privacy-policy', [HomeController::class, 'privacy_policy'])->name('privacy-policy');
    Route::get('/terms-and-condition', [HomeController::class, 'terms_and_condition'])->name('terms-and-condition');
    Route::get('/about-us', [HomeController::class, 'about_us'])->name('about-us');
    Route::get('/about-site-author', [HomeController::class, 'about_site_author'])->name('about-site-author');
    Route::get('/tools', [HomeController::class, 'tools'])->name('tools');
    Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
});

Route::get('/get-subcategories/{id}', function (int $id) {
    return Category::where('parent_id', $id)->get();
});

Route::get('/countries', function () {
    return Country::where('status', 1)->get();
});

Route::get('/states/{country_id}', function (int $country_id) {
    return State::where('country_id', $country_id)->where('status', 1)->get();
});

Route::get('/cities/{state_id}', function (int $state_id) {
    return City::where('state_id', $state_id)->where('status', 1)->get();
});

Route::get('/postal-codes/{city_id}', function (int $city_id) {
    return PostalCode::where('city_id', $city_id)->where('status', 1)->get();
});
Route::get('user/login', [HomeController::class, 'user_login'])->name('user.login');
Route::get('user/register', [HomeController::class, 'user_register1'])->name('user.register1');
Route::get('user/register/seller/category', [HomeController::class, 'user_register_category'])->name('user.register.category')->middleware('auth');
Route::post('user/register/seller/category', [HomeController::class, 'user_save_category'])->name('user.save.category')->middleware('auth');
Route::get('user/register/{type}', [HomeController::class, 'user_register2'])->name('user.register');
Route::post('user/login', [HomeController::class, 'user_submit_login'])->name('user.submit.login');
Route::post('user/register', [HomeController::class, 'user_submit_register'])->name('user.submit.register');

Route::post('/service/{slug}/inquiry', [HomeController::class, 'serviceInquiry'])->name('service.inquiry');
Route::post('/service/{slug}/wa-click', [HomeController::class, 'waClick'])->name('service.wa.click');
Route::post('/service/{slug}/email', [HomeController::class, 'emailInquiry'])->name('service.email');

// Public review link — no login required (seller sends token link to buyer)
Route::get('/r/{token}', [ReviewController::class, 'show'])->name('review.show');
Route::post('/r/{token}', [ReviewController::class, 'store'])->name('review.store');

// Twilio webhooks — no auth, verified by Twilio signature
Route::post('/webhook/twilio/voice',  [TwilioWebhookController::class, 'voice'])->name('twilio.webhook.voice');
Route::post('/webhook/twilio/status', [TwilioWebhookController::class, 'status'])->name('twilio.webhook.status');
Route::get('/blog/{slug}', [HomeController::class, 'blog_show'])->name('blog.show');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap']);
Route::get('/sitemap', [HomeController::class, 'sitemap'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| User Profile
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (in_array($user->type, ['admin', 'coo'])) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->type === 'seller') {
            return redirect()->route('seller.dashboard');
        }
        if ($user->type === 'staff') {
            return redirect()->route('staff.dashboard');
        }
        if ($user->type === 'manager') {
            $profile = $user->managerProfile;
            if ($profile && $profile->status === 'active') {
                return redirect($profile->firstModuleRoute());
            }
            return redirect()->route('frontend.home');
        }
        if ($user->type === 'user') {
            return redirect()->route('buyer.dashboard');
        }
        return redirect()->route('user.dashboard');
    })->name('dashboard');

    Route::get('terms', [HomeController::class, 'termsAgree'])->name('terms.agree');
    Route::post('terms', [HomeController::class, 'termsStore'])->name('terms.store');

    Route::get('profile/first', [ProfileController::class, 'edit'])->name('profile.first');
    Route::get('profile/{type}/{setup}', [ProfileController::class, 'typeProfile'])->name('type.profile');
    Route::post('profile/{type}/{setup}', [ProfileController::class, 'typeSellerProfile'])->name('save.seller.profile');
    Route::get('/profile/maintainance', [ProfileController::class, 'blockedlist'])->name('profile.blockedlist');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile-update', [ProfileController::class, 'profileUpdateDashboard'])->name('profile.update.dashboard');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | user Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('dashboard', [ProfileController::class, 'dashboard'])->name('dashboard');
        Route::resource('services', ServiceController::class);
        Route::resource('educations', EducationController::class);
        Route::resource('experiences', ExperienceController::class);
        Route::resource('certifications', CertificationController::class);
        Route::resource('memberships', MembershipController::class);
        Route::resource('languages', LanguageController::class);
        Route::resource('contacts', ContactController::class);
        Route::resource('faqs', FaqController::class);
        Route::get('profile', [ProfileController::class, 'profile'])->name('profile');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Seller Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['seller', 'terms'])->prefix('seller')->name('seller.')->group(function () {
        Route::get('dashboard', [SellerController::class, 'dashboard'])->name('dashboard');
        Route::get('onboarding', [SellerController::class, 'onboarding'])->name('onboarding');
        Route::get('affiliate', [SellerController::class, 'affiliate'])->name('affiliate');
        Route::get('settings', [SellerController::class, 'settings'])->name('settings');
        Route::put('settings', [SellerController::class, 'settingsUpdate'])->name('settings.update');
        Route::put('settings/password', [SellerController::class, 'settingsPasswordUpdate'])->name('settings.password');
        Route::delete('settings/account', [SellerController::class, 'settingsDestroy'])->name('settings.destroy');
        Route::post('settings/notifications', [SellerController::class, 'settingsNotifications'])->name('settings.notifications');
        Route::get('pricing', [SellerController::class, 'pricing'])->name('pricing');
        Route::get('billing', [SellerController::class, 'billing'])->name('billing');
        Route::post('billing/{lead}/pay', [SellerController::class, 'payLead'])->name('billing.pay');
        Route::get('schedule', [SellerController::class, 'schedule'])->name('schedule');
        Route::post('schedule', [SellerController::class, 'scheduleUpdate'])->name('schedule.update');
        Route::get('reviews', [SellerController::class, 'reviews'])->name('reviews');
        Route::post('reviews/{id}/reply', [SellerController::class, 'reviewReply'])->name('reviews.reply');
        Route::get('notifications', [SellerController::class, 'notifications'])->name('notifications');
        Route::post('notifications/read-all', [SellerController::class, 'notificationsReadAll'])->name('notifications.read-all');
        Route::get('leads/{id}', [SellerController::class, 'leadDetail'])->name('lead.detail');
        Route::post('leads/{id}/status', [SellerController::class, 'leadStatus'])->name('lead.status');
        Route::post('leads/{id}/notes', [SellerController::class, 'leadNotes'])->name('lead.notes');
        Route::post('leads/{id}/review-request', [SellerController::class, 'reviewRequest'])->name('lead.review-request');

        // Gallery
        Route::get('gallery',                  [GalleryController::class, 'index'])->name('gallery');
        Route::post('gallery',                 [GalleryController::class, 'store'])->name('gallery.store');
        Route::delete('gallery/{id}',          [GalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::post('gallery/{id}/caption',    [GalleryController::class, 'captionUpdate'])->name('gallery.caption');
    });

    /*
    |--------------------------------------------------------------------------
    | Staff Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Buyer Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['user', 'terms'])->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('dashboard', [BuyerController::class, 'dashboard'])->name('dashboard');
        Route::get('bookings', [BuyerController::class, 'bookings'])->name('bookings');
        Route::post('bookings/{id}/cancel', [BuyerController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::get('book/{seller}', [BuyerController::class, 'book'])->name('book');
        Route::post('book', [BuyerController::class, 'bookStore'])->name('book.store');
        Route::get('review/{booking}', [BuyerController::class, 'review'])->name('review');
        Route::post('review/{booking}', [BuyerController::class, 'reviewStore'])->name('review.store');
        Route::get('profile', [BuyerController::class, 'profile'])->name('profile');
        Route::put('profile', [BuyerController::class, 'profileUpdate'])->name('profile.update');
        Route::put('profile/password', [BuyerController::class, 'profilePasswordUpdate'])->name('profile.password');
        Route::delete('profile', [BuyerController::class, 'profileDestroy'])->name('profile.destroy');
        Route::get('booking/{id}/confirmation', [BuyerController::class, 'bookingConfirmation'])->name('booking.confirmation');
        Route::get('affiliate', [BuyerController::class, 'affiliate'])->name('affiliate');
        Route::get('notifications', [BuyerController::class, 'notifications'])->name('notifications');
        Route::post('notifications/read-all', [BuyerController::class, 'notificationsReadAll'])->name('notifications.read-all');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::middleware('manager.module:dashboard')->get('dashboard', [PageController::class, 'admin_dashboard'])->name('dashboard');
        Route::get('clear-cache', [PageController::class, 'clear_cache'])->name('clear.cache');
        Route::get('storage-link', [PageController::class, 'storage_link'])->name('storage.link');

        // Profiles module
        Route::middleware('manager.module:profiles')->prefix('profiles')->name('profiles.')->group(function () {
            Route::get('index', [PageController::class, 'profiles_index'])->name('index');
            Route::get('edit/{id}', [PageController::class, 'profiles_edit'])->name('edit');
            Route::put('update/{id}', [PageController::class, 'profiles_update'])->name('update');
            Route::delete('destroy/{id}', [PageController::class, 'profiles_destroy'])->name('destroy');
            Route::post('verify/{id}', [PageController::class, 'profiles_verify'])->name('verify');
        });

        // Leads module
        Route::middleware('manager.module:leads')->group(function () {
            Route::get('leads', [PageController::class, 'leads'])->name('leads');
            Route::post('leads/{id}/status', [PageController::class, 'leadUpdateStatus'])->name('leads.status');
            Route::post('leads/{id}/pay', [PageController::class, 'leadMarkPaid'])->name('leads.pay');
            Route::delete('leads/{id}', [PageController::class, 'leadDestroy'])->name('leads.destroy');
        });

        // Affiliate module
        Route::middleware('manager.module:affiliate')->group(function () {
            Route::get('affiliate', [PageController::class, 'affiliate'])->name('affiliate');
            Route::post('affiliate/commission/{id}/pay', [PageController::class, 'affiliateCommissionPay'])->name('affiliate.commission.pay');
            Route::post('affiliate/commission/create', [PageController::class, 'affiliateCommissionCreate'])->name('affiliate.commission.create');
            Route::delete('affiliate/commission/{id}', [PageController::class, 'affiliateCommissionDestroy'])->name('affiliate.commission.destroy');
        });

        // Hierarchy module
        Route::middleware('manager.module:hierarchy')->group(function () {
            Route::get('hierarchy', [PageController::class, 'hierarchy'])->name('hierarchy');
            Route::get('hierarchy/parents', [PageController::class, 'hierarchyParents'])->name('hierarchy.parents');
            Route::post('hierarchy', [PageController::class, 'hierarchyStore'])->name('hierarchy.store');
            Route::put('hierarchy/{id}', [PageController::class, 'hierarchyUpdate'])->name('hierarchy.update');
            Route::delete('hierarchy/{id}', [PageController::class, 'hierarchyDestroy'])->name('hierarchy.destroy');
            Route::post('hierarchy/{id}/status', [PageController::class, 'hierarchyStatusToggle'])->name('hierarchy.status');
        });

        // Locations module
        Route::middleware('manager.module:locations')->group(function () {
            Route::get('locations', [PageController::class, 'locations'])->name('locations');
            Route::resource('countries', CountryController::class);
            Route::resource('countries.states', StateController::class);
            Route::resource('states.cities', CityController::class);
            Route::resource('cities.postal-codes', PostalCodeController::class);
            Route::resource('states', StateController::class)->only(['index','edit','update','destroy']);
            Route::resource('cities', CityController::class)->only(['index','edit','update','destroy']);
        });

        // Blogs module
        Route::middleware('manager.module:blogs')->group(function () {
            Route::resource('blogs', BlogController::class);
        });

        // Categories module
        Route::middleware('manager.module:categories')->group(function () {
            Route::resource('categories', CategoryController::class);
        });

        // Services module
        Route::middleware('manager.module:services')->group(function () {
            Route::get('services',               [AdminServiceController::class, 'index'])->name('services.index');
            Route::post('services/{id}/toggle',  [AdminServiceController::class, 'toggleActive'])->name('services.toggle');
            Route::delete('services/{id}',       [AdminServiceController::class, 'destroy'])->name('services.destroy');
        });

        // Managers CRUD — admin only (no module middleware needed, managers can't manage other managers)
        Route::resource('managers', ManagerController::class)->only(['index','create','store','edit','update','destroy']);

        // Twilio SMS settings
        Route::middleware('manager.module:twilio')->prefix('twilio')->name('twilio.')->group(function () {
            Route::get('settings',             [TwilioController::class, 'settings'])->name('settings');
            Route::post('settings',            [TwilioController::class, 'settingsUpdate'])->name('settings.update');
            Route::get('sellers',              [TwilioController::class, 'sellers'])->name('sellers');
            Route::post('sellers/{id}/toggle', [TwilioController::class, 'toggle'])->name('toggle');
            Route::post('sellers/{id}/test',   [TwilioController::class, 'testSms'])->name('test');
        });

        // Pricing Rules
        Route::middleware('manager.module:pricing')->prefix('pricing')->name('pricing.')->group(function () {
            Route::get('/',                  [PricingController::class, 'index'])->name('index');
            Route::post('/',                 [PricingController::class, 'store'])->name('store');
            Route::put('{id}',               [PricingController::class, 'update'])->name('update');
            Route::delete('{id}',            [PricingController::class, 'destroy'])->name('destroy');
            Route::post('{id}/toggle',       [PricingController::class, 'toggle'])->name('toggle');
            Route::post('defaults',          [PricingController::class, 'updateDefaults'])->name('defaults');
            Route::get('cities/{stateId}',   [PricingController::class, 'citiesByState'])->name('cities');
        });

        // Phone number pool
        Route::middleware('manager.module:phone_pool')->prefix('phone-pool')->name('phone-pool.')->group(function () {
            Route::get('/',              [PhonePoolController::class, 'index'])->name('index');
            Route::post('/',             [PhonePoolController::class, 'store'])->name('store');
            Route::post('{id}/assign',   [PhonePoolController::class, 'assign'])->name('assign');
            Route::post('{id}/release',  [PhonePoolController::class, 'release'])->name('release');
            Route::delete('{id}',        [PhonePoolController::class, 'destroy'])->name('destroy');
            Route::get('call-logs',      [PhonePoolController::class, 'callLogs'])->name('call-logs');
        });

        // Contact Settings
        Route::get('settings/contact',  [PageController::class, 'contactSettings'])->name('settings.contact');
        Route::post('settings/contact', [PageController::class, 'contactSettingsUpdate'])->name('settings.contact.update');

        // HuntBot — AI Seller Acquisition
        Route::prefix('huntbot')->name('huntbot.')->group(function () {
            Route::get('/',                                  [HuntBotController::class, 'index'])->name('index');
            Route::post('hunt',                              [HuntBotController::class, 'hunt'])->name('hunt');
            Route::post('manual',                            [HuntBotController::class, 'manual'])->name('manual');
            Route::get('campaign/{campaign}',                [HuntBotController::class, 'campaign'])->name('campaign');
            Route::post('campaign/{campaign}/launch',        [HuntBotController::class, 'launch'])->name('launch');
            Route::patch('campaign/{campaign}/status',       [HuntBotController::class, 'updateCampaignStatus'])->name('campaign.status');
            Route::post('campaign/{campaign}/lead',          [HuntBotController::class, 'addLead'])->name('lead.add');
            Route::post('campaign/{campaign}/lead/bulk',     [HuntBotController::class, 'bulkLeads'])->name('lead.bulk');
            Route::patch('lead/{lead}/status',               [HuntBotController::class, 'updateLeadStatus'])->name('lead.status');
            Route::delete('lead/{lead}',                     [HuntBotController::class, 'deleteLead'])->name('lead.delete');
            Route::post('templates',                         [HuntBotController::class, 'saveTemplates'])->name('templates');
        });
    });

});

require __DIR__ . '/auth.php';
