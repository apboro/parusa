<?php

declare(strict_types=1);

namespace App\SberbankAcquiring;

/**
 * Order statuses.
 */
class OrderStatus
{
    // An order was successfully registered, but isn't paid yet
    public const CREATED = 0;

    // An order's amount was successfully held (for two-stage payments only)
    public const APPROVED = 1;

    // An order was deposited
    // If you want to check whether payment was successfully paid - use this constant
    public const DEPOSITED = 2;

    // An order was reversed
    public const REVERSED = 3;

    // An order was refunded
    public const REFUNDED = 4;

    // An order authorization was initialized by card emitter's ACS
    public const AUTHORIZATION_INITIALIZED = 5;

    // An order was declined
    public const DECLINED = 6;

    public static function isCreated($status): bool
    {
        // (int) '' === 0
        return '' !== $status && self::CREATED === (int) $status;
    }

    public static function isApproved($status): bool
    {
        return self::APPROVED === (int) $status;
    }

    public static function isDeposited($status): bool
    {
        return self::DEPOSITED === (int) $status;
    }

    public static function isReversed($status): bool
    {
        return self::REVERSED === (int) $status;
    }

    public static function isRefunded($status): bool
    {
        return self::REFUNDED === (int) $status;
    }

    public static function isAuthorizationInitialized($status): bool
    {
        return self::AUTHORIZATION_INITIALIZED === (int) $status;
    }

    public static function isDeclined($status): bool
    {
        return self::DECLINED === (int) $status;
    }

    public static function statusToString($status): string
    {
        switch ((int) $status) {
            case self::CREATED:
                return 'CREATED';
            case self::APPROVED:
                return 'APPROVED';
            case self::DEPOSITED:
                return 'DEPOSITED';
            case self::REVERSED:
                return 'REVERSED';
            case self::DECLINED:
                return 'DECLINED';
            case self::REFUNDED:
                return 'REFUNDED';
        }

        return '';
    }
}
