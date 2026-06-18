<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PlatformCharge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'category_id', 'state_id', 'city_id',
        'amount', 'effective_from', 'effective_to',
        'priority', 'is_active', 'is_promotion', 'promotion_label', 'notes', 'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_active'      => 'boolean',
        'is_promotion'   => 'boolean',
        'amount'         => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────
    public function category() { return $this->belongsTo(Category::class); }
    public function state()    { return $this->belongsTo(State::class); }
    public function city()     { return $this->belongsTo(City::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }

    // ── Scopes ─────────────────────────────────────────
    public function scopeActive($q)
    {
        return $q->where('is_active', true)
                 ->where('effective_from', '<=', today())
                 ->where(fn($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', today()));
    }

    public function scopeOfType($q, string $type)
    {
        return $q->where('type', $type);
    }

    public function scopePromotions($q)
    {
        return $q->where('is_promotion', true);
    }

    public static function activePromotions(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->promotions()->with(['category', 'state', 'city'])->orderByDesc('priority')->get();
    }

    public function daysRemaining(): ?int
    {
        if (!$this->effective_to) return null;
        return max(0, (int) now()->diffInDays($this->effective_to, false));
    }

    // ── Static Resolver ────────────────────────────────
    /**
     * Resolve charge for a given type, category, state, city.
     * Priority: city > state > category > global default
     */
    public static function resolve(string $type, ?int $categoryId = null, ?int $stateId = null, ?int $cityId = null): float
    {
        $gen      = \Illuminate\Support\Facades\Cache::get('platform_charges_gen', 0);
        $cacheKey = "platform_charge:{$gen}:{$type}:{$categoryId}:{$stateId}:{$cityId}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($type, $categoryId, $stateId, $cityId) {
            return static::resolveRaw($type, $categoryId, $stateId, $cityId);
        });
    }

    public static function bustCache(): void
    {
        \Illuminate\Support\Facades\Cache::increment('platform_charges_gen');
    }

    private static function resolveRaw(string $type, ?int $categoryId, ?int $stateId, ?int $cityId): float
    {
        $defaultKey = match($type) {
            'lead_fee'                  => 'default_lead_fee',
            'lead_threshold'            => 'default_lead_threshold',
            'payment_threshold'         => 'default_payment_threshold',
            'buyer_referral_commission' => 'default_buyer_referral_commission',
            default                     => 'default_affiliate_commission',
        };
        $fallback = match($type) {
            'lead_fee'          => 35,
            'lead_threshold'    => 3,
            'payment_threshold' => 30,
            default             => 10,
        };
        $default = (float) (Setting::where('key', $defaultKey)->value('value') ?? $fallback);

        $query = static::active()->ofType($type)->orderByDesc('priority');

        // Build OR conditions: city match OR state match OR category match OR global (no scope)
        $rules = $query->where(function ($q) use ($categoryId, $stateId, $cityId) {
            $q->where(function ($q) use ($cityId) {
                $q->whereNotNull('city_id')->when($cityId, fn($q) => $q->where('city_id', $cityId));
            })->orWhere(function ($q) use ($stateId) {
                $q->whereNull('city_id')->whereNotNull('state_id')
                  ->when($stateId, fn($q) => $q->where('state_id', $stateId));
            })->orWhere(function ($q) use ($categoryId) {
                $q->whereNull('city_id')->whereNull('state_id')->whereNotNull('category_id')
                  ->when($categoryId, fn($q) => $q->where('category_id', $categoryId));
            })->orWhere(function ($q) {
                $q->whereNull('city_id')->whereNull('state_id')->whereNull('category_id');
            });
        })->get();

        if ($rules->isEmpty()) return $default;

        // City-specific first, then state, then category, then global
        foreach ([fn($r) => $cityId && $r->city_id == $cityId,
                  fn($r) => $stateId && $r->state_id == $stateId && !$r->city_id,
                  fn($r) => $categoryId && $r->category_id == $categoryId && !$r->city_id && !$r->state_id,
                  fn($r) => !$r->city_id && !$r->state_id && !$r->category_id] as $filter) {
            $match = $rules->first($filter);
            if ($match) return (float) $match->amount;
        }

        return $default;
    }

    // ── Helpers ────────────────────────────────────────
    public function getLabel(): string
    {
        $parts = [];
        if ($this->city)     $parts[] = $this->city->name;
        if ($this->state)    $parts[] = $this->state->name;
        if ($this->category) $parts[] = $this->category->title;
        if (empty($parts))   $parts[] = 'Global Default';
        return implode(' · ', $parts);
    }

    public function isExpired(): bool
    {
        return $this->effective_to && $this->effective_to->isPast();
    }
}
