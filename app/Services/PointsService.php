<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPointsLog;

class PointsService
{
    // ── Point values ──────────────────────────────────────────────────
    const REFERRAL_JOIN      = 35;   // Someone signs up using your link
    const REFERRAL_FIRST_JOB = 25;   // Referred seller/buyer gets first lead/booking
    const TIER_RISING        = 50;   // Referral reaches Rising tier (3 refs)
    const TIER_TRUSTED       = 100;  // Referral reaches Trusted tier (5 refs)
    const TIER_ELITE         = 200;  // Referral reaches Elite tier (10 refs)
    const SECOND_LEVEL       = 20;   // Referral refers someone else

    // ── Tier thresholds ───────────────────────────────────────────────
    const TIERS = [
        'Rising'     => 3,
        'Trusted'    => 5,
        'Elite'      => 10,
        'Zonely Pro' => 25,
    ];

    /**
     * Award points to a user — safe against duplicates via event+related_user_id.
     */
    public static function award(
        User $user,
        int $points,
        string $event,
        string $reason,
        ?int $relatedUserId = null
    ): bool {
        // Prevent duplicate awards for same event+related_user combination
        $alreadyAwarded = UserPointsLog::where('user_id', $user->id)
            ->where('event', $event)
            ->when($relatedUserId, fn($q) => $q->where('related_user_id', $relatedUserId))
            ->exists();

        if ($alreadyAwarded) {
            return false;
        }

        $user->increment('points', $points);

        UserPointsLog::create([
            'user_id'         => $user->id,
            'points'          => $points,
            'event'           => $event,
            'reason'          => $reason,
            'related_user_id' => $relatedUserId,
        ]);

        return true;
    }

    /**
     * Award "referral join" points to whoever referred this new user.
     * Called on registration.
     */
    public static function onReferralJoin(User $newUser): void
    {
        if (!$newUser->referred_by) return;

        $referrer = User::find($newUser->referred_by);
        if (!$referrer) return;

        $typeLabel = $newUser->type === 'user' ? 'buyer' : 'seller';

        self::award(
            $referrer,
            self::REFERRAL_JOIN,
            'referral_join',
            "{$newUser->name} joined Zonely as a {$typeLabel} using your referral link",
            $newUser->id
        );
    }

    /**
     * Award "first job" points to referrer when referred user gets first lead.
     * Called when lead created.
     */
    public static function onReferralFirstJob(User $referredUser): void
    {
        if (!$referredUser->referred_by) return;

        $referrer = User::find($referredUser->referred_by);
        if (!$referrer) return;

        self::award(
            $referrer,
            self::REFERRAL_FIRST_JOB,
            'referral_first_job',
            "{$referredUser->name} completed their first booking on Zonely",
            $referredUser->id
        );
    }

    /**
     * Check and award tier milestone points for a referrer.
     * Safe to call any time — uses event key to prevent duplicates.
     */
    public static function checkTierMilestones(User $referrer): void
    {
        $totalRefs = $referrer->referrals()->count();

        $milestones = [
            ['tier' => 'Rising',  'threshold' => 3,  'points' => self::TIER_RISING,  'event' => 'tier_rising'],
            ['tier' => 'Trusted', 'threshold' => 5,  'points' => self::TIER_TRUSTED, 'event' => 'tier_trusted'],
            ['tier' => 'Elite',   'threshold' => 10, 'points' => self::TIER_ELITE,   'event' => 'tier_elite'],
        ];

        foreach ($milestones as $m) {
            if ($totalRefs >= $m['threshold']) {
                self::award(
                    $referrer,
                    $m['points'],
                    $m['event'],
                    "You reached {$m['tier']} affiliate tier — {$m['threshold']} referrals!",
                    null
                );
            }
        }
    }

    /**
     * Award second-level points: when a referred user refers someone else.
     */
    public static function onSecondLevelReferral(User $newUser): void
    {
        if (!$newUser->referred_by) return;

        $directReferrer = User::find($newUser->referred_by);
        if (!$directReferrer || !$directReferrer->referred_by) return;

        $grandReferrer = User::find($directReferrer->referred_by);
        if (!$grandReferrer) return;

        self::award(
            $grandReferrer,
            self::SECOND_LEVEL,
            'second_level_referral',
            "{$directReferrer->name} referred {$newUser->name} to Zonely (2nd level)",
            $newUser->id
        );
    }

    /**
     * Get tier label for a given points total (for display).
     */
    public static function getTierByPoints(int $points): string
    {
        if ($points >= 1000) return 'Zonely Pro';
        if ($points >= 500)  return 'Elite';
        if ($points >= 200)  return 'Trusted';
        if ($points >= 50)   return 'Rising';
        return 'Starter';
    }
}
