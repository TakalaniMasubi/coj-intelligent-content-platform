<?php

namespace App\Infrastructure\Persistence\Json;

use App\Contracts\Repositories\NewsletterHistoryRepositoryContract;
use App\Domain\Newsletter\NewsletterGenerationRecord;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;

/**
 * JSON driver for the newsletter generation history. Records are appended
 * under the same repository volume as the curated data and the selection
 * history, in a file separate from both — mirroring the established JSON
 * repository pattern (single gateway, single storage mechanism).
 */
final readonly class JsonNewsletterHistoryRepository implements NewsletterHistoryRepositoryContract
{
    private const ENTITY = 'newsletters';

    private const KEY = 'newsletter_generations';

    public function __construct(
        private RepositoryJsonGateway $gateway,
    ) {}

    public function all(): array
    {
        try {
            $rows = $this->gateway->load(self::ENTITY, self::KEY);
        } catch (RepositoryFileNotFoundException) {
            // An append-only history legitimately starts with no file at all;
            // that is an empty history, not a repository error.
            return [];
        }

        return array_map(
            fn (array $row): NewsletterGenerationRecord => NewsletterGenerationRecord::fromArray($row),
            $rows,
        );
    }

    public function save(NewsletterGenerationRecord $record): void
    {
        $rows = array_map(
            static fn (NewsletterGenerationRecord $existing): array => $existing->toArray(),
            $this->all(),
        );

        $rows[] = $record->toArray();

        $this->gateway->save(self::ENTITY, self::KEY, $rows);
    }

    public function count(): int
    {
        return count($this->all());
    }
}
