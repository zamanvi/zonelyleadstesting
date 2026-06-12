<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HuntCampaign extends Model
{
    protected $fillable = [
        'created_by', 'city', 'state', 'category', 'status',
        'total_found', 'total_contacted', 'total_replied', 'total_registered',
        'sms_template_key',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leads()
    {
        return $this->hasMany(HuntLead::class, 'campaign_id');
    }
}
