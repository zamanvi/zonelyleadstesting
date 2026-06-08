<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPointsLog extends Model
{
    protected $table = 'user_points_log';

    protected $fillable = ['user_id', 'points', 'event', 'reason', 'related_user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
