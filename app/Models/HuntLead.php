<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HuntLead extends Model
{
    protected $fillable = [
        'campaign_id', 'business_name', 'address', 'phone',
        'has_website', 'website_url', 'place_id', 'rating', 'review_count',
        'status', 'sms_sent_at', 'registered_user_id',
    ];

    protected $casts = [
        'has_website'  => 'boolean',
        'sms_sent_at'  => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(HuntCampaign::class, 'campaign_id');
    }

    public function registeredUser()
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }
}
