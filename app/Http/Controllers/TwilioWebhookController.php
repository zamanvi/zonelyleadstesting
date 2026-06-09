<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Models\City;
use App\Models\Lead;
use App\Models\PlatformCharge;
use App\Models\Setting;
use App\Models\State;
use App\Models\TwilioNumber;
use App\Services\Sms\SmsService;
use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;

class TwilioWebhookController extends Controller
{
    public function voice(Request $request)
    {
        if (!$this->validSignature($request)) {
            return response('Unauthorized', 403);
        }

        $toNumber   = $request->input('To');
        $fromNumber = $request->input('From');
        $callSid    = $request->input('CallSid');

        $twilioNumber = TwilioNumber::with('seller')
            ->where('number', $toNumber)
            ->where('status', 'assigned')
            ->first();

        if (!$twilioNumber || !$twilioNumber->seller) {
            return response($this->twimlReject(), 200)->header('Content-Type', 'text/xml');
        }

        $seller = $twilioNumber->seller;

        try {
            $log = CallLog::create([
                'seller_id'     => $seller->id,
                'twilio_number' => $toNumber,
                'caller_number' => $fromNumber,
                'call_sid'      => $callSid,
                'status'        => 'ringing',
                'called_at'     => now(),
            ]);

            $stateId = State::where('title', $seller->state)->value('id');
            $cityId  = City::where('title', $seller->city)->value('id');
            $leadFee = PlatformCharge::resolve('lead_fee', $seller->category_id, $stateId, $cityId);

            $lead = Lead::create([
                'seller_id' => $seller->id,
                'name'      => 'Phone Lead',
                'phone'     => $fromNumber,
                'email'     => '',
                'service'   => 'Phone Call',
                'message'   => 'Inbound call via Zonely tracking number.',
                'status'    => 'new',
                'fee'       => $leadFee,
            ]);

            $log->update(['lead_id' => $lead->id]);

            if ($seller->twilio_enabled && $seller->phone) {
                (new SmsService())->send(
                    $seller->phone,
                    "📞 Incoming Zonely call!\nFrom: {$fromNumber}\nAnswering now — mark Won/Lost after: " . route('seller.dashboard')
                );
            }
        } catch (\Throwable $e) {
            \Log::error('TwilioWebhook voice DB error: ' . $e->getMessage(), [
                'call_sid' => $callSid,
                'to'       => $toNumber,
                'from'     => $fromNumber,
            ]);
            // Still forward the call even if DB logging fails
        }

        return response($this->twimlDial($seller->phone), 200)
            ->header('Content-Type', 'text/xml');
    }

    public function status(Request $request)
    {
        if (!$this->validSignature($request)) {
            return response('Unauthorized', 403);
        }

        $callSid  = $request->input('CallSid');
        $status   = $request->input('CallStatus');
        $duration = (int) $request->input('CallDuration', 0);

        CallLog::where('call_sid', $callSid)
            ->update(['status' => $status, 'duration' => $duration]);

        return response('OK', 200);
    }

    private function validSignature(Request $request): bool
    {
        $token = Setting::get('twilio_token', config('services.twilio.token'));
        if (!$token) {
            return false;
        }

        $validator = new RequestValidator($token);
        $signature = $request->header('X-Twilio-Signature', '');

        return $validator->validate($signature, $request->url(), $request->post());
    }

    private function twimlDial(string $number): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<Response>
    <Dial action="' . route('twilio.webhook.status') . '" method="POST">
        <Number>' . e($number) . '</Number>
    </Dial>
</Response>';
    }

    private function twimlReject(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<Response>
    <Say>Sorry, this number is not available. Please try again later.</Say>
    <Hangup/>
</Response>';
    }
}
