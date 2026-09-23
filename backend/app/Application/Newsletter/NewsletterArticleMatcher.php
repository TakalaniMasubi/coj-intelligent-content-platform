<?php

namespace App\Application\Newsletter;

use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;

/**
 * Deterministic article selection for the newsletter studio.
 *
 * Given an organisation and the newsletter topic, this matcher picks the most
 * relevant curated articles using ONLY signals present in the repository:
 *
 *   1. term overlap with the topic in the article title / subject / summary
 *   2. verification status (verified articles are preferred)
 *   3. recency (newer retrieved-at wins)
 *   4. article id (ascending) as a stable tie-breaker
 *
 * Relevance relies on real text matches only — an article with zero matched
 * terms is never forced in, and the caller surfaces the honest "no relevant
 * article" fallback instead. The ordering is fully deterministic so repeated
 * compositions for the same topic are identical.
 */
final readonly class NewsletterArticleMatcher
{
    public function __construct(
        private ArticleRepositoryContract $articles,
    ) {}

    /**
     * Relevant articles for an organisation, most relevant first.
     *
     * @return array<int, array{score: int, article: Article}>
     */
    public function forOrganisation(string $organisationId, string $topic, int $limit = 2): array
    {
        $terms = $this->terms($topic);

        $scored = [];
        foreach ($this->articles->forOrganisation($organisationId) as $article) {
            if ($article->status === ArticleStatus::Rejected) {
                continue;
            }

            $score = $this->relevance($article, $terms);
            if ($score < 1) {
                continue;
            }

            $scored[] = [
                'score' => $score,
                'verified' => $article->status === ArticleStatus::Verified ? 1 : 0,
                'timestamp' => $this->sortTimestamp($article),
                'article' => $article,
            ];
        }

        usort(
            $scored,
            static function (array $a, array $b): int {
                if ($a['score'] !== $b['score']) {
                    return $b['score'] <=> $a['score'];
                }
                if ($a['verified'] !== $b['verified']) {
                    return $b['verified'] <=> $a['verified'];
                }
                if ($a['timestamp'] !== $b['timestamp']) {
                    return $b['timestamp'] <=> $a['timestamp'];
                }

                return strcmp($a['article']->id, $b['article']->id);
            },
        );

        return array_map(
            static fn (array $match): array => ['score' => $match['score'], 'article' => $match['article']],
            array_slice($scored, 0, max(1, $limit)),
        );
    }

    /**
     * Relevance contribution per query term against real repository fields.
     *
     * @param  string[]  $terms
     */
    private function relevance(Article $article, array $terms): int
    {
        $title = strtolower((string) $article->title);
        $subject = strtolower((string) $article->subject);
        $summary = strtolower((string) $article->summary);

        $score = 0;
        foreach ($terms as $term) {
            if ($title !== '' && str_contains($title, $term)) {
                $score += 3;
            }
            if ($subject !== '' && str_contains($subject, $term)) {
                $score += 4;
            }
            if ($summary !== '' && str_contains($summary, $term)) {
                $score += 2;
            }
        }

        return $score;
    }

    /**
     * @return string[] lowercase single-word terms extracted from the topic
     */
    private function terms(string $topic): array
    {
        $terms = preg_split('/[^a-z0-9]+/', strtolower(trim($topic)));

        return array_values(array_filter(
            array_map('trim', $terms === false ? [] : $terms),
            static fn (string $term): bool => $term !== '',
        ));
    }

    private function sortTimestamp(Article $article): int
    {
        return $article->retrievedAt?->getTimestamp() ?? $article->publishedAt?->getTimestamp() ?? 0;
    }
}
