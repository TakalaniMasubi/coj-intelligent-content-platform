<?php

namespace App\Domain\Repository;

use DateTimeImmutable;

final class RepositoryPolicy
{
    public static function recentWindowDays(): int
    {
        return (int) config('cojmip.freshness.recent_window_days', 7);
    }

    public static function staleAfterDays(): int
    {
        return (int) config('cojmip.freshness.stale_after_days', 30);
    }

    public static function expiredAfterDays(): int
    {
        return (int) config('cojmip.freshness.expired_after_days', 180);
    }

    public static function archivedAfterDays(): int
    {
        return (int) config('cojmip.freshness.archived_after_days', 365);
    }

    public static function ageInDays(?DateTimeImmutable $date): ?int
    {
        if ($date === null) {
            return null;
        }

        return (int) (new DateTimeImmutable('now'))->diff($date)->days;
    }

    /**
     * Returns fresh|stale|expired|archived, or null when no date is available.
     */
    public static function classify(?DateTimeImmutable $date): ?string
    {
        $age = self::ageInDays($date);

        if ($age === null) {
            return null;
        }

        if ($age > self::archivedAfterDays()) {
            return 'archived';
        }

        if ($age > self::expiredAfterDays()) {
            return 'expired';
        }

        if ($age > self::staleAfterDays()) {
            return 'stale';
        }

        return 'fresh';
    }
}