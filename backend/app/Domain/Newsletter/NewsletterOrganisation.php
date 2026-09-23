<?php

namespace App\Domain\Newsletter;

/**
 * One organisation featured in a composed newsletter.
 *
 * Mirrors the selected top-three decision (rank, relevance score, reasons,
 * verification status) and adds the resolved logo asset plus the curated
 * articles chosen for this organisation. Nothing is invented here — every
 * field traces back to the shared repository and selection pipeline.
 */
final readonly class NewsletterOrganisation
{
    /**
     * @param  string[]  $reasons
     * @param  string[]  $matchingTerms
     * @param  NewsletterArticle[]  $articles
     */
    public function __construct(
        public string $organisationId,
        public string $name,
        public int $rank,
        public int $score,
        public string $type,
        public string $verification,
        public string $abbreviation,
        public ?string $description,
        public ?string $officialWebsite,
        public array $reasons,
        public array $matchingTerms,
        public NewsletterMediaAsset $logo,
        public array $articles,
    ) {}

    public function logoSource(): string
    {
        return $this->logo->source;
    }

    public function toArray(): array
    {
        return [
            'organisation_id' => $this->organisationId,
            'name' => $this->name,
            'rank' => $this->rank,
            'score' => $this->score,
            'type' => $this->type,
            'verification' => $this->verification,
            'abbreviation' => $this->abbreviation,
            'description' => $this->description,
            'official_website' => $this->officialWebsite,
            'reasons' => array_values($this->reasons),
            'matching_terms' => array_values($this->matchingTerms),
            'logo' => $this->logo->toArray(),
            'articles' => array_map(
                static fn (NewsletterArticle $article): array => $article->toArray(),
                $this->articles,
            ),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            organisationId: (string) ($data['organisation_id'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            rank: (int) ($data['rank'] ?? 0),
            score: (int) ($data['score'] ?? 0),
            type: (string) ($data['type'] ?? 'directorate'),
            verification: (string) ($data['verification'] ?? 'pending'),
            abbreviation: (string) ($data['abbreviation'] ?? ''),
            description: isset($data['description']) && is_string($data['description']) ? $data['description'] : null,
            officialWebsite: isset($data['official_website']) && is_string($data['official_website'])
                ? $data['official_website']
                : null,
            reasons: array_values(array_filter((array) ($data['reasons'] ?? []), 'is_string')),
            matchingTerms: array_values(array_filter((array) ($data['matching_terms'] ?? []), 'is_string')),
            logo: NewsletterMediaAsset::fromArray($data['logo'] ?? []),
            articles: array_map(
                static fn (array $article): NewsletterArticle => NewsletterArticle::fromArray($article),
                is_array($data['articles'] ?? null) ? $data['articles'] : [],
            ),
        );
    }
}
