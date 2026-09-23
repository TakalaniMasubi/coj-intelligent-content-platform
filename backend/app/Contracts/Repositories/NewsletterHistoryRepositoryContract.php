<?php

namespace App\Contracts\Repositories;

use App\Domain\Newsletter\NewsletterGenerationRecord;

/**
 * Persists newsletter generation records (the newsletter history foundation).
 *
 * Records are append-only and stored under the same repository volume as the
 * selection history, but they are a DISTINCT concern: a newsletter generation
 * (topic, layout, chosen organisations/logos, referenced articles, PDF
 * status and page count) is not an organisation selection decision and is
 * never persisted by composing a draft — only by generating a PDF.
 */
interface NewsletterHistoryRepositoryContract
{
    /** @return NewsletterGenerationRecord[] */
    public function all(): array;

    public function save(NewsletterGenerationRecord $record): void;

    public function count(): int;
}
