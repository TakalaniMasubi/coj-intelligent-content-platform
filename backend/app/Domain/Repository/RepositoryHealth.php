<?php

namespace App\Domain\Repository;

use DateTimeImmutable;

final readonly class RepositoryHealth
{
    public const STATUS_HEALTHY = 'healthy';

    public const STATUS_DEGRADED = 'degraded';

    public const STATUS_ERROR = 'error';

    public function __construct(
        public int $organisationCount,
        public int $articleCount,
        public int $sourceTrustCount,
        /** @var array<string, int> */
        public array $byStatus,
        public int $freshCount,
        public int $staleCount,
        public int $expiredCount,
        public int $archivedCount,
        public int $unverifiedCount,
        public int $rejectedCount,
        /** @var array<string, int> */
        public array $organisationsByType = [],
        /** @var array<string, int> */
        public array $organisationsByVerification = [],
        /** @var array<string, int> */
        public array $sourcesByVerification = [],
        public ?DateTimeImmutable $newestRetrievedAt = null,
        public ?DateTimeImmutable $oldestRetrievedAt,
        public string $status,
        /** @var string[] */
        public array $issues = [],
        public ?DateTimeImmutable $generatedAt = null,
    ) {
    }

    public function isHealthy(): bool
    {
        return $this->status === self::STATUS_HEALTHY;
    }

    public function toArray(): array
    {
        return [
            'organisation_count' => $this->organisationCount,
            'article_count' => $this->articleCount,
            'source_trust_count' => $this->sourceTrustCount,
            'by_status' => $this->byStatus,
            'fresh_count' => $this->freshCount,
            'stale_count' => $this->staleCount,
            'expired_count' => $this->expiredCount,
            'archived_count' => $this->archivedCount,
            'unverified_count' => $this->unverifiedCount,
            'rejected_count' => $this->rejectedCount,
            'organisations_by_type' => $this->organisationsByType,
            'organisations_by_verification' => $this->organisationsByVerification,
            'sources_by_verification' => $this->sourcesByVerification,
            'newest_retrieved_at' => $this->newestRetrievedAt?->format(DATE_ATOM),
            'oldest_retrieved_at' => $this->oldestRetrievedAt?->format(DATE_ATOM),
            'status' => $this->status,
            'issues' => $this->issues,
            'generated_at' => ($this->generatedAt ?? new DateTimeImmutable('now'))->format(DATE_ATOM),
        ];
    }
}