<?php

namespace App\Application\Health;

use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Contracts\Repositories\OrganisationRepositoryContract;
use App\Contracts\Repositories\RepositoryHealthContract;
use App\Contracts\Repositories\SourceTrustRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;
use App\Domain\Repository\RepositoryHealth;
use App\Domain\Repository\RepositoryPolicy;
use App\Domain\Shared\Exceptions\InvalidRepositoryJsonException;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;
use DateTimeImmutable;

final readonly class RepositoryHealthService implements RepositoryHealthContract
{
    public function __construct(
        private OrganisationRepositoryContract $organisations,
        private ArticleRepositoryContract $articles,
        private SourceTrustRepositoryContract $sources,
    ) {
    }

    public function evaluate(): RepositoryHealth
    {
        $issues = [];

        $organisationRows = $this->readAll($this->organisations, 'organisations', $issues);
        $articleRows = $this->readAll($this->articles, 'articles', $issues);
        $sourceRows = $this->readAll($this->sources, 'sources', $issues);

        $fresh = $stale = $expired = $archived = $unverified = $rejected = 0;
        $byStatus = [ArticleStatus::Verified->value => 0, ArticleStatus::Unverified->value => 0, ArticleStatus::Rejected->value => 0];
        $dates = [];

        /** @var Article $article */
        foreach ($articleRows as $article) {
            $byStatus[$article->status->value] = ($byStatus[$article->status->value] ?? 0) + 1;

            if ($article->status === ArticleStatus::Unverified) {
                $unverified++;
            }
            if ($article->status === ArticleStatus::Rejected) {
                $rejected++;
            }

            $effective = $article->retrievedAt ?? $article->publishedAt;
            $dates[] = $effective;

            $band = RepositoryPolicy::classify($effective);
            if ($band === 'fresh') {
                $fresh++;
            } elseif ($band === 'stale') {
                $stale++;
            } elseif ($band === 'expired') {
                $expired++;
            } elseif ($band === 'archived') {
                $archived++;
            }
        }

        $newest = $dates === [] ? null : max($dates);
        $oldest = $dates === [] ? null : min($dates);

        $organisationsByType = [];
        $organisationsByVerification = [];
        foreach ($organisationRows as $organisation) {
            $organisationsByType[$organisation->organisationType->value] ??= 0;
            $organisationsByType[$organisation->organisationType->value]++;
            $organisationsByVerification[$organisation->verificationStatus->value] ??= 0;
            $organisationsByVerification[$organisation->verificationStatus->value]++;
        }

        $sourcesByVerification = [];
        foreach ($sourceRows as $source) {
            $sourcesByVerification[$source->verificationStatus->value] ??= 0;
            $sourcesByVerification[$source->verificationStatus->value]++;
        }

        if ($issues !== []) {
            $status = RepositoryHealth::STATUS_ERROR;
        } elseif (
            $organisationRows === []
            || $articleRows === []
            || $sourceRows === []
            || $fresh === 0
            || $stale > 0
            || $expired > 0
            || $archived > 0
            || $unverified > 0
        ) {
            $status = RepositoryHealth::STATUS_DEGRADED;
        } else {
            $status = RepositoryHealth::STATUS_HEALTHY;
        }

        return new RepositoryHealth(
            organisationCount: count($organisationRows),
            articleCount: count($articleRows),
            sourceTrustCount: count($sourceRows),
            byStatus: $byStatus,
            freshCount: $fresh,
            staleCount: $stale,
            expiredCount: $expired,
            archivedCount: $archived,
            unverifiedCount: $unverified,
            rejectedCount: $rejected,
            organisationsByType: $organisationsByType,
            organisationsByVerification: $organisationsByVerification,
            sourcesByVerification: $sourcesByVerification,
            newestRetrievedAt: $newest instanceof DateTimeImmutable ? $newest : null,
            oldestRetrievedAt: $oldest instanceof DateTimeImmutable ? $oldest : null,
            status: $status,
            issues: $issues,
            generatedAt: new DateTimeImmutable('now'),
        );
    }

    /**
     * @param string[] $issues
     * @return mixed[]
     */
    private function readAll(object $repository, string $label, array &$issues): array
    {
        try {
            return $repository->all();
        } catch (RepositoryFileNotFoundException) {
            $issues[] = "{$label}.json file is missing.";

            return [];
        } catch (InvalidRepositoryJsonException $e) {
            $issues[] = "{$label}.json is not valid: {$e->getMessage()}.";

            return [];
        } catch (\InvalidArgumentException $e) {
            $issues[] = "{$label}.json contains invalid data: {$e->getMessage()}.";

            return [];
        }
    }
}