<?php

namespace App\Application\Dashboard;

use App\Contracts\Intelligence\SelectionContract;
use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Contracts\Repositories\OrganisationRepositoryContract;
use App\Contracts\Repositories\RepositoryHealthContract;
use App\Contracts\Repositories\SelectionDecisionRepositoryContract;
use App\Contracts\Repositories\SourceTrustRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Organisation\OrganisationType;
use App\Domain\Repository\RepositoryHealth;
use App\Domain\Shared\Exceptions\InvalidRepositoryJsonException;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;
use App\Domain\Shared\VerificationStatus;
use DateTimeImmutable;
use Throwable;

/**
 * Aggregates the dashboard presentation data from the existing repository
 * contracts and intelligence services. This is deliberately a THIN view-model
 * layer: every number or list below comes from the same repositories and
 * services the rest of the platform uses — nothing is re-implemented and
 * nothing is hard-coded.
 *
 * Flow: Blade -> DashboardController -> DashboardService -> existing
 * contracts/services -> JSON repository.
 */
final readonly class DashboardService
{
    /** Maximum recent article records shown in "Latest Repository Content". */
    private const RECENT_ARTICLES_LIMIT = 6;

    /** Maximum persisted decisions shown in the recent-history panel. */
    private const RECENT_DECISIONS_LIMIT = 12;

    /**
     * The explainability preview reuses the Phase 5 selection pipeline with
     * a stable, well-understood query so the page demonstrates top-three
     * selection, adjusted scores and repetition control using real data.
     */
    private const PREVIEW_QUERY = 'waste management';

    public function __construct(
        private OrganisationRepositoryContract $organisations,
        private ArticleRepositoryContract $articles,
        private SourceTrustRepositoryContract $sources,
        private RepositoryHealthContract $health,
        private SelectionDecisionRepositoryContract $selectionHistory,
        private SelectionContract $selection,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        $organisations = $this->readOrEmpty('organisations');
        $articles = $this->readOrEmpty('articles');
        $sourceTrust = $this->readOrEmpty('sources');

        $health = $this->health->evaluate();

        $verified = $pending = $unverified = 0;
        $types = $this->emptyTypeBreakdown();
        foreach ($organisations as $organisation) {
            match ($organisation->verificationStatus) {
                VerificationStatus::Verified => $verified++,
                VerificationStatus::Pending => $pending++,
                VerificationStatus::Unverified => $unverified++,
            };

            $type = $organisation->organisationType->value;
            $types[$type]['count']++;
            $types[$type][$organisation->verificationStatus->value]++;
        }

        $total = count($organisations);
        foreach ($types as &$type) {
            $type['percent'] = $total > 0 ? (int) round($type['count'] / $total * 100) : 0;
        }
        unset($type);

        return [
            'metrics' => [
                'organisations' => $total,
                'entities' => $types[OrganisationType::Entity->value]['count'],
                'directorates' => $types[OrganisationType::Directorate->value]['count'],
                'offices' => $types[OrganisationType::Office->value]['count'],
                'verified_organisations' => $verified,
                'pending_organisations' => $pending,
                'unverified_organisations' => $unverified,
                'articles' => count($articles),
                'trusted_sources' => count($sourceTrust),
            ],
            'health' => $this->healthView($health),
            'type_breakdown' => array_values($types),
            'verification_overview' => [
                VerificationStatus::Verified->value => [
                    'count' => $verified,
                    'note' => 'Information has supporting verification evidence.',
                ],
                VerificationStatus::Pending->value => [
                    'count' => $pending,
                    'note' => 'Information exists in the curated repository but requires live verification.',
                ],
                VerificationStatus::Unverified->value => [
                    'count' => $unverified,
                    'note' => 'Information has not yet been sufficiently verified.',
                ],
            ],
            'provenance' => [
                'trusted_sources' => count($sourceTrust),
                'verified_sources' => count(array_filter(
                    $sourceTrust,
                    static fn (object $source): bool => $source->verificationStatus === VerificationStatus::Verified,
                )),
                'pending_sources' => count(array_filter(
                    $sourceTrust,
                    static fn (object $source): bool => $source->verificationStatus === VerificationStatus::Pending,
                )),
                'unverified_sources' => count(array_filter(
                    $sourceTrust,
                    static fn (object $source): bool => $source->verificationStatus === VerificationStatus::Unverified,
                )),
                'tiers' => [
                    'tier1' => count(array_filter($sourceTrust, static fn (object $source): bool => $source->tier->value === 'tier1')),
                    'tier2' => count(array_filter($sourceTrust, static fn (object $source): bool => $source->tier->value === 'tier2')),
                    'tier3' => count(array_filter($sourceTrust, static fn (object $source): bool => $source->tier->value === 'tier3')),
                ],
            ],
            'latest_articles' => array_slice($this->articleView($articles), 0, self::RECENT_ARTICLES_LIMIT),
            'selection_history' => $this->selectionHistory(self::RECENT_DECISIONS_LIMIT),
            'preview' => $this->preview(),
            'pending_present' => $pending > 0
                || $this->hasPending($sourceTrust)
                || (int) $health->unverifiedCount > 0,
            'generated_at' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
        ];
    }

    /**
     * RepositoryHealth view-model. The status and counts come verbatim from
     * the existing health service so the dashboard never re-computes a
     * second, competing health picture.
     *
     * @return array<string, mixed>
     */
    private function healthView(RepositoryHealth $health): array
    {
        $label = match ($health->status) {
            RepositoryHealth::STATUS_HEALTHY => 'Healthy',
            RepositoryHealth::STATUS_DEGRADED => 'Degraded',
            default => 'Error',
        };

        return [
            'status' => $health->status,
            'status_label' => $label,
            'organisation_count' => $health->organisationCount,
            'article_count' => $health->articleCount,
            'source_count' => $health->sourceTrustCount,
            'verified_organisations' => $health->organisationsByVerification[VerificationStatus::Verified->value] ?? 0,
            'pending_organisations' => $health->organisationsByVerification[VerificationStatus::Pending->value] ?? 0,
            'verified_sources' => $health->sourcesByVerification[VerificationStatus::Verified->value] ?? 0,
            'pending_sources' => $health->sourcesByVerification[VerificationStatus::Pending->value] ?? 0,
            'fresh_count' => $health->freshCount,
            'stale_count' => $health->staleCount,
            'unverified_articles' => $health->unverifiedCount,
            'issues' => $health->issues,
            'generated_at' => ($health->generatedAt ?? new DateTimeImmutable('now'))->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, array{label: string, count: int, percent: int, verified: int, pending: int, unverified: int}>
     */
    private function emptyTypeBreakdown(): array
    {
        $labels = [
            OrganisationType::Entity->value => 'Municipal Entities',
            OrganisationType::Directorate->value => 'Directorates',
            OrganisationType::Office->value => 'Office',
        ];

        $breakdown = [];
        foreach (OrganisationType::cases() as $type) {
            $breakdown[$type->value] = [
                'type' => $type->value,
                'label' => $labels[$type->value],
                'count' => 0,
                'percent' => 0,
                'verified' => 0,
                'pending' => 0,
                'unverified' => 0,
            ];
        }

        return $breakdown;
    }

    /**
     * Recent "Latest Repository Content" rows. Emptiness is tolerated so the
     * dashboard renders a clean empty state instead of erroring.
     *
     * @param object[] $articles
     *
     * @return array<int, array<string, mixed>>
     */
    private function articleView(array $articles): array
    {
        $names = $this->organisationNames();

        $rows = [];
        foreach ($articles as $article) {
            if (! $article instanceof Article) {
                continue;
            }

            $namesForArticle = [];
            foreach ($article->organisationIds as $organisationId) {
                $namesForArticle[] = $names[$organisationId] ?? $organisationId;
            }

            $rows[] = [
                'id' => $article->id,
                'title' => $article->title,
                'organisation_names' => array_values($namesForArticle),
                'status' => $article->status->value,
                'published_at' => $article->publishedAt,
                'retrieved_at' => $article->retrievedAt,
                'source_name' => $article->sourceName,
                'source_url' => $article->sourceUrl,
                'tier' => $article->tier->value,
            ];
        }

        usort(
            $rows,
            static function (array $a, array $b): int {
                $dateA = self::sortDate($a);
                $dateB = self::sortDate($b);

                return $dateB <=> $dateA;
            },
        );

        return $rows;
    }

    private static function sortDate(array $row): int
    {
        $date = $row['retrieved_at'] instanceof DateTimeImmutable
            ? $row['retrieved_at']
            : ($row['published_at'] instanceof DateTimeImmutable ? $row['published_at'] : null);

        return $date instanceof DateTimeImmutable ? $date->getTimestamp() : -1;
    }

    /**
     * Successful selection decisions grouped by query. With a non-null limit
     * only the most recent decisions are returned (dashboard panel); without
     * a limit the complete persisted history is returned (history page).
     *
     * @return array{groups: array<int, array<string, mixed>>, total: int, has_history: bool}
     */
    public function selectionHistory(?int $limit = null): array
    {
        $selected = array_values(array_filter(
            $this->selectionHistory->all(),
            static fn (SelectionDecision $decision): bool => $decision->selected,
        ));

        usort(
            $selected,
            static fn (SelectionDecision $a, SelectionDecision $b): int => $b->selectedAt <=> $a->selectedAt,
        );

        $groups = [];
        $count = 0;
        foreach ($selected as $decision) {
            if ($limit !== null && $count >= $limit) {
                break;
            }

            $query = $decision->query !== '' ? $decision->query : '(unspecified query)';
            $groups[$query] ??= [
                'query' => $query,
                'selected_at' => $decision->selectedAt->format(DATE_ATOM),
                'selections' => [],
            ];
            $groups[$query]['selections'][] = $decision->toArray();
            $count++;
        }

        return [
            'groups' => array_values($groups),
            'total' => count($selected),
            'has_history' => $selected !== [],
        ];
    }

    /**
     * Compact top-three explainability demonstration. Reuses the EXACT Phase
     * 5 selection pipeline (search -> selector) through the same contract the
     * API uses, so the dashboard preview can never diverge from the product's
     * selection policy.
     *
     * @return array<string, mixed>
     */
    private function preview(): array
    {
        $outcome = $this->selection->preview(self::PREVIEW_QUERY);

        return [
            'query' => $outcome->originalQuery,
            'success' => $outcome->success,
            'status' => $outcome->status,
            'required' => $outcome->required,
            'available' => $outcome->available,
            'message' => $outcome->message,
            'decisions' => array_map(
                static fn (SelectionDecision $decision): array => $decision->toArray(),
                $outcome->decisions,
            ),
        ];
    }

    /**
     * @return array<string, object>
     */
    private function readOrEmpty(string $kind): array
    {
        try {
            return match ($kind) {
                'organisations' => $this->organisations->all(),
                'articles' => $this->articles->all(),
                'sources' => $this->sources->all(),
                default => [],
            };
        } catch (RepositoryFileNotFoundException | InvalidRepositoryJsonException | Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, string>  organisation id -> organisation name
     */
    private function organisationNames(): array
    {
        $names = [];
        foreach ($this->readOrEmpty('organisations') as $organisation) {
            $names[$organisation->id] = $organisation->name;
        }

        return $names;
    }

    /**
     * @param object[] $sourceTrust
     */
    private function hasPending(array $sourceTrust): bool
    {
        foreach ($sourceTrust as $source) {
            if ($source->verificationStatus === VerificationStatus::Pending) {
                return true;
            }
        }

        return false;
    }
}