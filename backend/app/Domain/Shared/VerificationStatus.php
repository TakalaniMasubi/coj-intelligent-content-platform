<?php

namespace App\Domain\Shared;

enum VerificationStatus: string
{
    case Verified = 'verified';
    case Pending = 'pending';
    case Unverified = 'unverified';

    public static function fromName(string $value): self
    {
        $normalised = strtolower(trim($value));

        return match ($normalised) {
            'verified', 'confirm' => self::Verified,
            'pending', 'unconfirmed' => self::Pending,
            'unverified', 'unknown' => self::Unverified,
            default => throw new \InvalidArgumentException("Unsupported verification status: {$value}"),
        };
    }
}