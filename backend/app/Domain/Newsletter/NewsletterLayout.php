<?php

namespace App\Domain\Newsletter;

/**
 * The selectable newsletter display layouts.
 *
 * Layouts are purely presentational: the composed organisation and article
 * content is shared unchanged across every layout, so changing a layout never
 * re-selects or recomposes repository data (design requirement of Phase 7).
 */
enum NewsletterLayout: string
{
    case ExecutiveBrief = 'executive-brief';
    case NewsInsights = 'news-insights';
    case MunicipalSpotlight = 'municipal-spotlight';
    case Magazine = 'magazine';

    /**
     * Resolve a requested slug back to a supported layout. Unknown or empty
     * slugs safely fall back to the Executive Brief layout.
     */
    public static function fromSlug(?string $slug): self
    {
        $normalised = strtolower(trim((string) $slug));

        foreach (self::cases() as $layout) {
            if ($layout->value === $normalised) {
                return $layout;
            }
        }

        return self::ExecutiveBrief;
    }

    /** @return string[] all supported layout slugs */
    public static function slugs(): array
    {
        return array_map(static fn (self $layout): string => $layout->value, self::cases());
    }
}
