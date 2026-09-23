<?php

namespace App\Domain\Newsletter;

use DateTimeImmutable;

/**
 * The shared newsletter composition.
 *
 * THIS is the single source of truth rendered by every layout in both the
 * web preview and the PDF: the same composition value object is passed to the
 * studio preview and the Dompdf renderer, so a layout switch never needs to
 * re-run selection or re-attach article assets.
 */
final readonly class NewsletterComposition
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_INSUFFICIENT = 'insufficient_results';

    /**
     * @param  NewsletterOrganisation[]  $organisations
     */
    public function __construct(
        public string $id,
        public string $topic,
        public string $status,
        public string $message,
        public NewsletterLayout $layout,
        public DateTimeImmutable $generatedAt,
        public string $title,
        public string $subtitle,
        public string $date,
        public string $introduction,
        public array $organisations,
        public string $footnote,
        public int $required,
        public int $available,
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /** @return NewsletterOrganisation[] */
    public function organisations(): array
    {
        return $this->organisations;
    }

    /**
     * A copy of this composition rendered under a different layout. Content
     * is untouched — layouts are purely presentational, so switching layouts
     * never re-runs selection or re-attaches article assets.
     */
    public function withLayout(NewsletterLayout $layout): self
    {
        return new self(
            id: $this->id,
            topic: $this->topic,
            status: $this->status,
            message: $this->message,
            layout: $layout,
            generatedAt: $this->generatedAt,
            title: $this->title,
            subtitle: $this->subtitle,
            date: $this->date,
            introduction: $this->introduction,
            organisations: $this->organisations,
            footnote: $this->footnote,
            required: $this->required,
            available: $this->available,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic,
            'status' => $this->status,
            'message' => $this->message,
            'layout' => $this->layout->value,
            'generated_at' => $this->generatedAt->format(DATE_ATOM),
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'date' => $this->date,
            'introduction' => $this->introduction,
            'organisations' => array_map(
                static fn (NewsletterOrganisation $organisation): array => $organisation->toArray(),
                $this->organisations,
            ),
            'footnote' => $this->footnote,
            'required' => $this->required,
            'available' => $this->available,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            topic: (string) ($data['topic'] ?? ''),
            status: (string) ($data['status'] ?? self::STATUS_INSUFFICIENT),
            message: (string) ($data['message'] ?? ''),
            layout: NewsletterLayout::fromSlug($data['layout'] ?? null),
            generatedAt: isset($data['generated_at']) && is_string($data['generated_at'])
                ? new DateTimeImmutable($data['generated_at'])
                : new DateTimeImmutable,
            title: (string) ($data['title'] ?? ''),
            subtitle: (string) ($data['subtitle'] ?? ''),
            date: (string) ($data['date'] ?? ''),
            introduction: (string) ($data['introduction'] ?? ''),
            organisations: array_map(
                static fn (array $organisation): NewsletterOrganisation => NewsletterOrganisation::fromArray($organisation),
                is_array($data['organisations'] ?? null) ? $data['organisations'] : [],
            ),
            footnote: (string) ($data['footnote'] ?? ''),
            required: (int) ($data['required'] ?? 0),
            available: (int) ($data['available'] ?? 0),
        );
    }
}
