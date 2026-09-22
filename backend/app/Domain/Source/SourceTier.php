<?php

namespace App\Domain\Source;

enum SourceTier: string
{
    case Tier1 = 'tier1';
    case Tier2 = 'tier2';
    case Tier3 = 'tier3';

    public static function fromName(string $value): self
    {
        $normalised = strtolower(trim($value));

        return match ($normalised) {
            'tier1', 'tier_1' => self::Tier1,
            'tier2', 'tier_2' => self::Tier2,
            'tier3', 'tier_3' => self::Tier3,
            default => throw new \InvalidArgumentException("Unsupported source tier: {$value}"),
        };
    }

    public function priority(): int
    {
        return (int) $this->value[-1];
    }
}