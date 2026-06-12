<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HuntCampaign;
use App\Models\HuntLead;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client as TwilioClient;

class HuntBotController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index()
    {
        $stats = [
            'today'      => HuntCampaign::whereDate('created_at', today())->count(),
            'this_week'  => HuntCampaign::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => HuntCampaign::whereMonth('created_at', now()->month)->count(),
            'total_found'       => HuntLead::count(),
            'total_contacted'   => HuntLead::where('status', 'contacted')->count(),
            'total_replied'     => HuntLead::where('status', 'replied')->count(),
            'total_registered'  => HuntLead::where('status', 'registered')->count(),
        ];

        $campaigns = HuntCampaign::with('creator')
            ->orderByDesc('id')
            ->paginate(10);

        $templates = $this->getTemplates();

        return view('admin.huntbot.index', compact('stats', 'campaigns', 'templates'));
    }

    // ── Hunt: search Google Maps ───────────────────────────────────────────────

    public function hunt(Request $request)
    {
        $request->validate([
            'city'     => 'required|string|max:100',
            'state'    => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
        ]);

        $apiKey = config('services.google_maps.key');

        if (empty($apiKey)) {
            return back()->with('error', 'Google Maps API key is not configured. Add GOOGLE_MAPS_KEY to your .env file.');
        }

        $location = trim($request->city . ($request->state ? ', ' . $request->state : ''));
        $query    = urlencode($request->category . ' in ' . $location);

        $results = [];
        $nextPageToken = null;

        // Fetch up to 3 pages (60 results max from Places API)
        for ($page = 0; $page < 3; $page++) {
            $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$query}&key={$apiKey}";
            if ($nextPageToken) {
                $url .= '&pagetoken=' . $nextPageToken;
                sleep(2); // Google requires a short delay before using pagetoken
            }

            $response = Http::get($url);
            $data     = $response->json();

            if (($data['status'] ?? '') !== 'OK') {
                break;
            }

            foreach ($data['results'] as $place) {
                $results[] = [
                    'place_id'      => $place['place_id'] ?? null,
                    'business_name' => $place['name'] ?? '',
                    'address'       => $place['formatted_address'] ?? '',
                    'rating'        => $place['rating'] ?? null,
                    'review_count'  => $place['user_ratings_total'] ?? 0,
                ];
            }

            $nextPageToken = $data['next_page_token'] ?? null;
            if (!$nextPageToken) break;
        }

        // Fetch place details to check website
        $leads = [];
        foreach ($results as $place) {
            $detailUrl = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place['place_id']}&fields=name,formatted_phone_number,website&key={$apiKey}";
            $detail    = Http::get($detailUrl)->json();
            $result    = $detail['result'] ?? [];

            $place['phone']       = $result['formatted_phone_number'] ?? null;
            $place['has_website'] = !empty($result['website']);
            $place['website_url'] = $result['website'] ?? null;

            $leads[] = $place;
        }

        // Create campaign
        $campaign = HuntCampaign::create([
            'created_by' => Auth::id(),
            'city'       => $request->city,
            'state'      => $request->state,
            'category'   => $request->category,
            'status'     => 'draft',
        ]);

        // Store all leads
        foreach ($leads as $lead) {
            HuntLead::create([
                'campaign_id'   => $campaign->id,
                'business_name' => $lead['business_name'],
                'address'       => $lead['address'],
                'phone'         => $lead['phone'],
                'has_website'   => $lead['has_website'],
                'website_url'   => $lead['website_url'],
                'place_id'      => $lead['place_id'],
                'rating'        => $lead['rating'],
                'review_count'  => $lead['review_count'],
                'status'        => 'found',
            ]);
        }

        $campaign->update(['total_found' => count($leads)]);

        return redirect()->route('admin.huntbot.campaign', $campaign->id)
            ->with('success', count($leads) . ' businesses found in ' . $location . '.');
    }

    // ── Campaign detail ───────────────────────────────────────────────────────

    public function campaign(HuntCampaign $campaign)
    {
        $leads     = $campaign->leads()->orderBy('has_website')->orderByDesc('review_count')->get();
        $templates = $this->getTemplates();
        return view('admin.huntbot.campaign', compact('campaign', 'leads', 'templates'));
    }

    // ── Launch SMS to selected leads ──────────────────────────────────────────

    public function launch(Request $request, HuntCampaign $campaign)
    {
        $request->validate([
            'lead_ids'     => 'required|array|min:1',
            'lead_ids.*'   => 'integer',
            'template_key' => 'required|string',
        ]);

        $templateKey = $request->template_key;
        $template    = Setting::get('huntbot_tpl_' . $templateKey, $this->defaultTemplate($templateKey));

        $sid    = config('services.twilio.sid');
        $token  = config('services.twilio.token');
        $from   = config('services.twilio.from');

        if (empty($sid) || empty($token) || empty($from)) {
            return back()->with('error', 'Twilio is not configured. Check your .env settings.');
        }

        $twilio = new TwilioClient($sid, $token);
        $sent   = 0;
        $errors = 0;

        $leads = HuntLead::whereIn('id', $request->lead_ids)
            ->where('campaign_id', $campaign->id)
            ->get();

        foreach ($leads as $lead) {
            if (empty($lead->phone)) continue;

            $message = $this->buildMessage($template, $lead, $campaign);

            try {
                $twilio->messages->create($lead->phone, [
                    'from' => $from,
                    'body' => $message,
                ]);
                $lead->update(['status' => 'contacted', 'sms_sent_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $campaign->increment('total_contacted', $sent);
        $campaign->update(['status' => 'running', 'sms_template_key' => $templateKey]);

        $msg = "SMS sent to {$sent} businesses.";
        if ($errors) $msg .= " {$errors} failed (no phone or Twilio error).";

        return back()->with('success', $msg);
    }

    // ── Update lead status manually ───────────────────────────────────────────

    public function updateLeadStatus(Request $request, HuntLead $lead)
    {
        $request->validate(['status' => 'required|in:found,selected,contacted,replied,registered,skipped']);
        $lead->update(['status' => $request->status]);

        if ($request->status === 'registered') {
            $lead->campaign->increment('total_registered');
        }

        return back()->with('success', 'Lead status updated.');
    }

    // ── Save SMS templates ────────────────────────────────────────────────────

    public function saveTemplates(Request $request)
    {
        $keys = ['professional', 'healthcare', 'home', 'beauty'];
        foreach ($keys as $key) {
            if ($request->filled('tpl_' . $key)) {
                Setting::set('huntbot_tpl_' . $key, $request->input('tpl_' . $key));
            }
        }
        return back()->with('success', 'SMS templates saved.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function getTemplates(): array
    {
        $keys = ['professional', 'healthcare', 'home', 'beauty'];
        $templates = [];
        foreach ($keys as $key) {
            $templates[$key] = Setting::get('huntbot_tpl_' . $key, $this->defaultTemplate($key));
        }
        return $templates;
    }

    private function defaultTemplate(string $key): string
    {
        $defaults = [
            'professional' => "Hi {business_name}! I found your business on Google but noticed you don't have a website yet. Zonely connects local professionals with clients in {city} — it's free to list and takes 10 minutes. Interested? {signup_link}",
            'healthcare'   => "Hi {business_name}! We're growing Zonely's healthcare directory in {city}. Patients in your area are searching for providers like you. Free to join — {signup_link}",
            'home'         => "Hi {business_name}! Homeowners in {city} are searching for home services on Zonely. Get discovered by local clients — free to list. See how: {signup_link}",
            'beauty'       => "Hi {business_name}! We're building the top beauty directory in {city} on Zonely. Join free and start getting client bookings: {signup_link}",
        ];
        return $defaults[$key] ?? $defaults['professional'];
    }

    private function buildMessage(string $template, HuntLead $lead, HuntCampaign $campaign): string
    {
        $signupLink = config('app.url') . '/register?ref=huntbot&city=' . urlencode($campaign->city);
        return str_replace(
            ['{business_name}', '{city}', '{signup_link}'],
            [$lead->business_name, $campaign->city, $signupLink],
            $template
        );
    }
}
