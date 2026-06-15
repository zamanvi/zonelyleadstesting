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
    // ── Dashboard ──────────────────────────────────────────────────────────────

    public function index()
    {
        $stats = [
            'total_found'      => HuntLead::count(),
            'total_contacted'  => HuntLead::where('status', 'contacted')->count(),
            'total_replied'    => HuntLead::where('status', 'replied')->count(),
            'total_registered' => HuntLead::where('status', 'registered')->count(),
        ];

        $campaigns = HuntCampaign::with('creator')->orderByDesc('id')->paginate(15);
        $templates = $this->getTemplates();
        $googleMapsActive = !empty(config('services.google_maps.key'));

        return view('admin.huntbot.index', compact('stats', 'campaigns', 'templates', 'googleMapsActive'));
    }

    // ── Auto Hunt (Google Maps) ────────────────────────────────────────────────

    public function hunt(Request $request)
    {
        $request->validate([
            'city'     => 'required|string|max:100',
            'state'    => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
        ]);

        $apiKey = config('services.google_maps.key');
        if (empty($apiKey)) {
            return back()->with('error', 'Google Maps API key not configured. Add GOOGLE_MAPS_KEY to your .env on Railway.');
        }

        $location = trim($request->city . ($request->state ? ', ' . $request->state : ''));
        $query    = urlencode($request->category . ' in ' . $location);
        $results  = [];
        $nextPageToken = null;

        for ($page = 0; $page < 3; $page++) {
            $url = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$query}&key={$apiKey}";
            if ($nextPageToken) {
                $url .= '&pagetoken=' . $nextPageToken;
                sleep(2);
            }
            $data = Http::get($url)->json();
            if (($data['status'] ?? '') !== 'OK') break;

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

        // Fetch details (phone + website)
        $leads = [];
        foreach ($results as $place) {
            $detail = Http::get("https://maps.googleapis.com/maps/api/place/details/json?place_id={$place['place_id']}&fields=name,formatted_phone_number,website&key={$apiKey}")->json();
            $r = $detail['result'] ?? [];
            $place['phone']       = $r['formatted_phone_number'] ?? null;
            $place['has_website'] = !empty($r['website']);
            $place['website_url'] = $r['website'] ?? null;
            $leads[] = $place;
        }

        $campaign = HuntCampaign::create([
            'created_by' => Auth::id(),
            'city'       => $request->city,
            'state'      => $request->state,
            'category'   => $request->category,
            'status'     => 'draft',
            'source'     => 'auto',
        ]);

        foreach ($leads as $l) {
            HuntLead::create([
                'campaign_id'   => $campaign->id,
                'business_name' => $l['business_name'],
                'address'       => $l['address'],
                'phone'         => $l['phone'],
                'has_website'   => $l['has_website'],
                'website_url'   => $l['website_url'],
                'place_id'      => $l['place_id'],
                'rating'        => $l['rating'],
                'review_count'  => $l['review_count'],
                'status'        => 'found',
            ]);
        }

        $campaign->update(['total_found' => count($leads)]);

        return redirect()->route('admin.huntbot.campaign', $campaign->id)
            ->with('success', count($leads) . ' businesses found in ' . $location . '.');
    }

    // ── Manual Campaign ────────────────────────────────────────────────────────

    public function manual(Request $request)
    {
        $request->validate([
            'city'     => 'required|string|max:100',
            'category' => 'required|string|max:100',
        ]);

        $campaign = HuntCampaign::create([
            'created_by' => Auth::id(),
            'city'       => $request->city,
            'state'      => $request->state,
            'category'   => $request->category,
            'status'     => 'draft',
            'source'     => 'manual',
        ]);

        return redirect()->route('admin.huntbot.campaign', $campaign->id)
            ->with('success', 'Campaign created. Now add your leads below.');
    }

    // ── Campaign detail ────────────────────────────────────────────────────────

    public function campaign(HuntCampaign $campaign)
    {
        $leads     = $campaign->leads()->orderByDesc('id')->get();
        $templates = $this->getTemplates();
        return view('admin.huntbot.campaign', compact('campaign', 'leads', 'templates'));
    }

    // ── Add single lead manually ───────────────────────────────────────────────

    public function addLead(Request $request, HuntCampaign $campaign)
    {
        $request->validate([
            'business_name' => 'required|string|max:200',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:300',
            'website_url'   => 'nullable|url|max:500',
        ]);

        $hasWebsite = !empty($request->website_url);

        HuntLead::create([
            'campaign_id'   => $campaign->id,
            'business_name' => $request->business_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'has_website'   => $hasWebsite,
            'website_url'   => $request->website_url,
            'status'        => 'found',
        ]);

        $campaign->increment('total_found');

        return back()->with('success', '"' . $request->business_name . '" added.');
    }

    // ── Bulk paste leads ───────────────────────────────────────────────────────

    public function bulkLeads(Request $request, HuntCampaign $campaign)
    {
        $request->validate(['bulk_data' => 'required|string|max:50000']);

        $lines = array_filter(array_map('trim', explode("\n", $request->bulk_data)));
        $added = 0;

        foreach ($lines as $line) {
            if (empty($line)) continue;
            $parts = array_map('trim', explode(',', $line, 3));
            $name  = $parts[0] ?? null;
            if (empty($name)) continue;

            HuntLead::create([
                'campaign_id'   => $campaign->id,
                'business_name' => $name,
                'phone'         => $parts[1] ?? null,
                'address'       => $parts[2] ?? null,
                'has_website'   => false,
                'status'        => 'found',
            ]);
            $added++;
        }

        $campaign->increment('total_found', $added);

        return back()->with('success', $added . ' leads imported.');
    }

    // ── Launch SMS to selected leads ───────────────────────────────────────────

    public function launch(Request $request, HuntCampaign $campaign)
    {
        $request->validate([
            'lead_ids'     => 'required|array|min:1',
            'lead_ids.*'   => 'integer',
            'template_key' => 'required|string',
        ]);

        $template = Setting::get('huntbot_tpl_' . $request->template_key, $this->defaultTemplate($request->template_key));

        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.from');

        if (empty($sid) || empty($token) || empty($from)) {
            return back()->with('error', 'Twilio not configured. Check TWILIO_SID / TWILIO_TOKEN / TWILIO_FROM in your .env.');
        }

        $twilio = new TwilioClient($sid, $token);
        $sent   = 0;
        $errors = 0;

        $leads = HuntLead::whereIn('id', $request->lead_ids)
            ->where('campaign_id', $campaign->id)
            ->whereNotNull('phone')
            ->get();

        foreach ($leads as $lead) {
            try {
                $twilio->messages->create($lead->phone, [
                    'from' => $from,
                    'body' => $this->buildMessage($template, $lead, $campaign),
                ]);
                $lead->update(['status' => 'contacted', 'sms_sent_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        $campaign->increment('total_contacted', $sent);
        $campaign->update(['status' => 'running', 'sms_template_key' => $request->template_key]);

        $msg = "SMS sent to {$sent} businesses.";
        if ($errors) $msg .= " {$errors} failed (Twilio error).";

        return back()->with($errors && !$sent ? 'error' : 'success', $msg);
    }

    // ── Update campaign status ─────────────────────────────────────────────────

    public function updateCampaignStatus(Request $request, HuntCampaign $campaign)
    {
        $request->validate(['status' => 'required|in:running,paused,completed']);
        $campaign->update(['status' => $request->status]);
        return back()->with('success', 'Campaign marked as ' . $request->status . '.');
    }

    // ── Update lead status ─────────────────────────────────────────────────────

    public function updateLeadStatus(Request $request, HuntLead $lead)
    {
        $actor = auth()->user();
        if (!in_array($actor->type, ['admin', 'coo']) && $lead->campaign->created_by !== $actor->id) {
            abort(403, 'You do not have permission to update this lead.');
        }
        $request->validate(['status' => 'required|in:found,selected,contacted,replied,registered,skipped']);
        $old = $lead->status;
        $lead->update(['status' => $request->status]);

        if ($request->status === 'registered' && $old !== 'registered') {
            $lead->campaign->increment('total_registered');
        }

        return back()->with('success', 'Lead updated.');
    }

    // ── Delete lead ────────────────────────────────────────────────────────────

    public function deleteLead(HuntLead $lead)
    {
        $campaign = $lead->campaign;
        $actor = auth()->user();
        if (!in_array($actor->type, ['admin', 'coo']) && $campaign->created_by !== $actor->id) {
            abort(403, 'You do not have permission to delete this lead.');
        }
        $lead->delete();
        if ($campaign->total_found > 0) {
            $campaign->decrement('total_found');
        }
        return back()->with('success', 'Lead removed.');
    }

    // ── Save SMS templates ─────────────────────────────────────────────────────

    public function saveTemplates(Request $request)
    {
        foreach (['professional', 'healthcare', 'home', 'beauty'] as $key) {
            if ($request->filled('tpl_' . $key)) {
                Setting::set('huntbot_tpl_' . $key, $request->input('tpl_' . $key));
            }
        }
        return back()->with('success', 'SMS templates saved.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function getTemplates(): array
    {
        $templates = [];
        foreach (['professional', 'healthcare', 'home', 'beauty'] as $key) {
            $templates[$key] = Setting::get('huntbot_tpl_' . $key, $this->defaultTemplate($key));
        }
        return $templates;
    }

    private function defaultTemplate(string $key): string
    {
        return [
            'professional' => "Hi {business_name}! I found you on Google but noticed you don't have a website yet. Zonely helps local professionals in {city} get new clients online — free to list, takes 10 min. Interested? {signup_link}",
            'healthcare'   => "Hi {business_name}! Patients in {city} are searching for providers like you on Zonely. It's free to join and takes minutes to set up. Start here: {signup_link}",
            'home'         => "Hi {business_name}! Homeowners in {city} are looking for home services on Zonely. Get discovered by local clients — free to list. See how: {signup_link}",
            'beauty'       => "Hi {business_name}! We're building the top beauty directory in {city} on Zonely. Join free and start getting new client bookings: {signup_link}",
        ][$key] ?? "Hi {business_name}! Join Zonely in {city} and start getting new clients — it's free. {signup_link}";
    }

    private function buildMessage(string $template, HuntLead $lead, HuntCampaign $campaign): string
    {
        $link = config('app.url') . '/register?ref=huntbot&city=' . urlencode($campaign->city);
        return str_replace(
            ['{business_name}', '{city}', '{signup_link}'],
            [$lead->business_name, $campaign->city, $link],
            $template
        );
    }
}
