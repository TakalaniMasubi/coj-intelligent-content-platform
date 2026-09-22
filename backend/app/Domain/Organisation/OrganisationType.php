<?php

namespace App\Domain\Organisation;

enum OrganisationType: string
{
    case Entity = 'entity';
    case Directorate = 'directorate';
    case Office = 'office';

    public static function fromName(string $value): self
    {
        $normalised = strtolower(trim($value));

        return match ($normalised) {
            'entity', 'entities' => self::Entity,
            'directorate', 'directorates' => self::Directorate,
            'office', 'offices' => self::Office,
            default => throw new \InvalidArgumentException("Unsupported organisation type: {$value}"),
        };
    }
}