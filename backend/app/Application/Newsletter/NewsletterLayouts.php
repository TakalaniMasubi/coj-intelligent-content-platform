<?php

namespace App\Application\Newsletter;

use App\Domain\Newsletter\NewsletterLayout;

/**
 * Config-driven registry of the newsletter display layouts.
 *
 * Layout metadata (name, tag, blurb) lives in config/cojmip.php under
 * `layouts.registry`; this service exposes it to the studio UI and normalises
 * requested layout slugs to safe, supported values.
 */
final readonly class NewsletterLayouts
{
    public const DEFAULT = NewsletterLayout::ExecutiveBrief->value;

    public function __construct(
        private array $layouts,
    ) {}

    public function default(): string
    {
        $default = $this->layouts['default'] ?? self::DEFAULT;

        return isset($this->layouts['registry'][$default]) ? $default : self::DEFAULT;
    }

    /** @return array<string, array{name: string, tag: string, blurb: string}> */
    public function all(): array
    {
        return $this->layouts['registry'] ?? [];
    }

    /** @return array{name: string, tag: string, blurb: string}|null */
    public function get(string $code): ?array
    {
        return $this->layouts['registry'][$code] ?? null;
    }

    public function normalize(?string $code): string
    {
        $normalised = strtolower(trim((string) $code));

        return isset($this->layouts['registry'][$normalised]) ? $normalised : $this->default();
    }
}
