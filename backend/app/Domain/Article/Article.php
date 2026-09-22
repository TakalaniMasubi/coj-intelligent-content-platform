<?php

namespace App\Domain\Article;

use App\Domain\Source\SourceTier;
use DateTimeImmutable;

final readonly class Article
{
    public function __construct(
        public string $id,
        public string $sourceName,
        public string $sourceUrl,
        public string $title,
        public ?string $summary = null,
        public string $subject = '',
        /** @var string[] */
        public array $organisationIds = [],
        public SourceTier $tier = SourceTier::Tier2,
        public ArticleStatus $status = ArticleStatus::Unverified,
        public ?DateTimeImmutable $publishedAt = null,
        public ?DateTimeImmutable $retrievedAt = null,
        public ?string $contentHash = null,
        public ?DateTimeImmutable $archivedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! isset($data['id']) || ! is_string($data['id'])) {
            throw new \InvalidArgumentException('Article entries must include a string id.');
        }

        $required = ['source_name', 'source_url', 'title'];
        foreach ($required as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field])) {
                throw new \InvalidArgumentException("Article '{$data['id']}' is missing a string {$field}.");
            }
        }

        return new self(
            id: $data['id'],
            sourceName: $data['source_name'],
            sourceUrl: $data['source_url'],
            title: $data['title'],
            summary: isset($data['summary']) && is_string($data['summary']) ? $data['summary'] : null,
            subject: isset($data['subject']) && is_string($data['subject']) ? $data['subject'] : '',
            organisationIds: is_array($data['organisation_ids'] ?? null) ? array_values($data['organisation_ids']) : [],
            tier: isset($data['tier']) && is_string($data['tier']) ? SourceTier::fromName($data['tier']) : SourceTier::Tier2,
            status: isset($data['status']) && is_string($data['status']) ? ArticleStatus::fromName($data['status']) : ArticleStatus::Unverified,
            publishedAt: isset($data['published_at']) && is_string($data['published_at']) ? new DateTimeImmutable($data['published_at']) : null,
            retrievedAt: isset($data['retrieved_at']) && is_string($data['retrieved_at']) ? new DateTimeImmutable($data['retrieved_at']) : null,
            contentHash: isset($data['content_hash']) && is_string($data['content_hash']) ? $data['content_hash'] : null,
            archivedAt: isset($data['archived_at']) && is_string($data['archived_at']) ? new DateTimeImmutable($data['archived_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source_name' => $this->sourceName,
            'source_url' => $this->sourceUrl,
            'title' => $this->title,
            'summary' => $this->summary,
            'subject' => $this->subject,
            'organisation_ids' => array_values($this->organisationIds),
            'tier' => $this->tier->value,
            'status' => $this->status->value,
            'published_at' => $this->publishedAt?->format(DATE_ATOM),
            'retrieved_at' => $this->retrievedAt?->format(DATE_ATOM),
            'content_hash' => $this->contentHash,
            'archived_at' => $this->archivedAt?->format(DATE_ATOM),
        ];
    }
}