<?php

namespace App\Domain\Organisation;

enum OrganisationStatus: string
{
    case Active = 'active';
    case Dormant = 'dormant';
    case Disbanded = 'disbanded';

    public static function fromName(string $value): self
    {
        $normalised = strtolower(trim($value));

        return match ($normalised) {
            'active' => self::Active,
            'dormant' => self::Dormant,
            'disbanded' => self::Disbanded,
            default => throw new \InvalidArgumentException("Unsupported organisation status: {$value}"),
        };
    }
}