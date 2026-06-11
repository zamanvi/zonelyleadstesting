<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'source', 'name', 'phone', 'email', 'service',
        'location', 'zip_code', 'message', 'status', 'fee', 'paid_at', 'paypal_order_id', 'notes',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNull('paid_at');
    }
}
