<?php

namespace App\Domain\Newsletter;

use DateTimeImmutable;

/**
 * A persisted newsletter generation record (the newsletter history
 * foundation). Written when a PDF is generated so each generation is
 * auditable: the topic, layout, organisations and logos chosen, the articles
 * referenced, the PDF status, validated page count and generation timestamp.
 *
 * This mirrors the selection-history architecture (append-only records under
 * the same repository volume) without duplicating the organisation selection
 * history — newsletter generations are a distinct, separate concern.
 */
final readonly class NewsletterGenerationRecord
{
    public const PDF_PENDING = 'pending';

    public const PDF_SUCCESS = 'success';

    public const PDF_FAILED = 'failed';

    /**
     * @param  array<int, array<string, mixed>>  $organisations
     */
    public function __construct(
        public string $id,
        public string $topic,
        public string $layout,
        public array $organisations,
        public int $articleCount,
        public string $pdfStatus,
        public int $pageCount,
        public string $message,
        public DateTimeImmutable $generatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic,
            'layout' => $this->layout,
            'organisations' => $this->organisations,
            'article_count' => $this->articleCount,
            'pdf_status' => $this->pdfStatus,
            'page_count' => $this->pageCount,
            'message' => $this->message,
            'generated_at' => $this->generatedAt->format(DATE_ATOM),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            topic: (string) ($data['topic'] ?? ''),
            layout: (string) ($data['layout'] ?? NewsletterLayout::ExecutiveBrief->value),
            organisations: is_array($data['organisations'] ?? null) ? array_values($data['organisations']) : [],
            articleCount: (int) ($data['article_count'] ?? 0),
            pdfStatus: (string) ($data['pdf_status'] ?? self::PDF_PENDING),
            pageCount: (int) ($data['page_count'] ?? 0),
            message: (string) ($data['message'] ?? ''),
            generatedAt: isset($data['generated_at']) && is_string($data['generated_at'])
                ? new DateTimeImmutable($data['generated_at'])
                : new DateTimeImmutable,
        );
    }
}
