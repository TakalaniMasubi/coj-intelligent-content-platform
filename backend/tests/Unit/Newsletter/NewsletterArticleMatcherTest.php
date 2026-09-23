<?php

namespace Tests\Unit\Newsletter;

use App\Application\Newsletter\NewsletterArticleMatcher;
use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class NewsletterArticleMatcherTest extends TestCase
{
    public function test_articles_with_no_term_overlap_are_never_forced_in(): void
    {
        $matcher = new NewsletterArticleMatcher($this->repository([
            $this->article('art-a', 'Budget approval for the fiscal year', 'finance', null),
        ]));

        $this->assertSame([], $matcher->forOrganisation('org-a', 'water services'));
    }

    public function test_relevance_weights_subject_above_title_above_summary(): void
    {
        $matcher = new NewsletterArticleMatcher($this->repository([
            // Title term only → 3.
            $this->article('art-a', 'Water metering rollout', 'infrastructure', null),
            // Subject term only → 4.
            $this->article('art-b', 'Tariff review committee', 'water regulatory', null),
            // Summary term only → 2.
            $this->article('art-c', 'Annual report', 'governance', 'The water distribution network expansion plan for 2026.'),
        ]));

        $matches = $matcher->forOrganisation('org-a', 'water', 3);

        $this->assertSame(
            ['art-b', 'art-a', 'art-c'],
            array_map(static fn (array $m): string => $m['article']->id, $matches),
        );
        $this->assertGreaterThan($matches[1]['score'], $matches[0]['score']);
        $this->assertGreaterThan($matches[2]['score'], $matches[1]['score']);
    }

    public function test_verified_articles_are_preferred_when_scores_tie(): void
    {
        $matcher = new NewsletterArticleMatcher($this->repository([
            $this->article('art-a', 'Water pipeline maintenance', 'water', null, ArticleStatus::Verified),
            $this->article('art-b', 'Water pipeline maintenance', 'water', null),
        ]));

        $matches = $matcher->forOrganisation('org-a', 'water');

        $this->assertSame(['art-a', 'art-b'], array_map(
            static fn (array $m): string => $m['article']->id,
            $matches,
        ));
    }

    public function test_newer_retrieved_articles_are_preferred_then_id_breaks_ties(): void
    {
        $matcher = new NewsletterArticleMatcher($this->repository([
            $this->article('art-a', 'Water quality report', 'water', null,
                retrievedAt: new DateTimeImmutable('2026-09-20T08:00:00+00:00')),
            $this->article('art-b', 'Water quality report', 'water', null,
                retrievedAt: new DateTimeImmutable('2026-09-22T08:00:00+00:00')),
            $this->article('art-c', 'Water quality report', 'water', null,
                retrievedAt: new DateTimeImmutable('2026-09-22T08:00:00+00:00')),
        ]));

        $matches = $matcher->forOrganisation('org-a', 'water', 3);

        $this->assertSame(['art-b', 'art-c', 'art-a'], array_map(
            static fn (array $m): string => $m['article']->id,
            $matches,
        ));
    }

    public function test_rejected_articles_are_excluded_entirely(): void
    {
        $matcher = new NewsletterArticleMatcher($this->repository([
            $this->article('art-a', 'Water services demand forecast', 'water', null, ArticleStatus::Rejected),
            $this->article('art-b', 'Water services demand forecast', 'water', null),
        ]));

        $matches = $matcher->forOrganisation('org-a', 'water');

        $this->assertSame(['art-b'], array_map(
            static fn (array $m): string => $m['article']->id,
            $matches,
        ));
    }

    public function test_limit_truncates_to_the_most_relevant(): void
    {
        $matcher = new NewsletterArticleMatcher($this->repository([
            $this->article('art-a', 'Water disinfection upgrade', 'water', null),
            $this->article('art-b', 'Water disinfection stations', 'water', null),
            $this->article('art-c', 'Water treatment investments', 'water', null),
        ]));

        $matches = $matcher->forOrganisation('org-a', 'water', 2);

        $this->assertCount(2, $matches);
    }

    public function test_the_same_topic_and_repository_produce_identical_results(): void
    {
        $repository = $this->repository([
            $this->article('art-a', 'Water disinfection upgrade', 'water', null),
            $this->article('art-b', 'Water disinfection stations', 'water', null),
        ]);

        $first = (new NewsletterArticleMatcher($repository))->forOrganisation('org-a', 'water');
        $second = (new NewsletterArticleMatcher($repository))->forOrganisation('org-a', 'water');

        $this->assertSame(
            array_map(static fn (array $m): array => [$m['article']->id, $m['score']], $first),
            array_map(static fn (array $m): array => [$m['article']->id, $m['score']], $second),
        );
    }

    /** @param Article[] $articles */
    private function repository(array $articles): ArticleRepositoryContract
    {
        return new class($articles) implements ArticleRepositoryContract
        {
            public function __construct(private array $articles) {}

            public function find(string $id): ?Article
            {
                foreach ($this->articles as $article) {
                    if ($article->id === $id) {
                        return $article;
                    }
                }

                return null;
            }

            public function all(): array
            {
                return $this->articles;
            }

            public function save(Article $article): void
            {
                $this->articles[] = $article;
            }

            public function delete(string $id): bool
            {
                return true;
            }

            public function forOrganisation(string $organisationId): array
            {
                return array_values(array_filter(
                    $this->articles,
                    static fn (Article $article): bool => in_array($organisationId, $article->organisationIds, true),
                ));
            }

            public function count(): int
            {
                return count($this->articles);
            }
        };
    }

    private function article(
        string $id,
        string $title,
        string $subject,
        ?string $summary,
        ArticleStatus $status = ArticleStatus::Unverified,
        ?DateTimeImmutable $retrievedAt = null,
    ): Article {
        return new Article(
            id: $id,
            sourceName: 'Source '.$id,
            sourceUrl: 'https://example.test/'.$id,
            title: $title,
            summary: $summary,
            subject: $subject,
            organisationIds: ['org-a'],
            status: $status,
            retrievedAt: $retrievedAt ?? new DateTimeImmutable('2026-09-22T08:00:00+00:00'),
        );
    }
}
