<?php

namespace App\Domain\Newsletter;

use DateTimeImmutable;

/**
 * A repository article included in a composed newsletter.
 *
 * Facts come verbatim from the curated Article record: title, summary,
 * subject, source name/URL and verification status. The relevance score is
 * the deterministic match strength computed by the article matcher and the
 * media asset is the resolved featured image (or a branded fallback panel).
 */
final readonly class NewsletterArticle
{
    public function __construct(
        public string $articleId,
        public string $title,
        public ?string $summary,
        public string $subject,
        public string $sourceName,
        public string $sourceUrl,
        public string $status,
        public ?DateTimeImmutable $retrievedAt,
        public int $relevanceScore,
        public NewsletterMediaAsset $image,
    ) {}

    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'title' => $this->title,
            'summary' => $this->summary,
            'subject' => $this->subject,
            'source_name' => $this->sourceName,
            'source_url' => $this->sourceUrl,
            'status' => $this->status,
            'retrieved_at' => $this->retrievedAt?->format(DATE_ATOM),
            'relevance_score' => $this->relevanceScore,
            'image' => $this->image->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            articleId: (string) ($data['article_id'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            summary: isset($data['summary']) && is_string($data['summary']) ? $data['summary'] : null,
            subject: (string) ($data['subject'] ?? ''),
            sourceName: (string) ($data['source_name'] ?? ''),
            sourceUrl: (string) ($data['source_url'] ?? ''),
            status: (string) ($data['status'] ?? 'unverified'),
            retrievedAt: isset($data['retrieved_at']) && is_string($data['retrieved_at'])
                ? new DateTimeImmutable($data['retrieved_at'])
                : null,
            relevanceScore: (int) ($data['relevance_score'] ?? 0),
            image: NewsletterMediaAsset::fromArray($data['image'] ?? []),
        );
    }
}
