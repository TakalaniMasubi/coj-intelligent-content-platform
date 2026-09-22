<?php

namespace App\Domain\Article;

enum ArticleStatus: string
{
    case Verified = 'verified';
    case Unverified = 'unverified';
    case Rejected = 'rejected';

    public static function fromName(string $value): self
    {
        $normalised = strtolower(trim($value));

        return match ($normalised) {
            'verified' => self::Verified,
            'unverified', 'pending', 'pending_verification' => self::Unverified,
            'rejected' => self::Rejected,
            default => throw new \InvalidArgumentException("Unsupported article status: {$value}"),
        };
    }
}