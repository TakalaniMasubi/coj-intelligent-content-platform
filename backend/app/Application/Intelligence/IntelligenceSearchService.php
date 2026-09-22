<?php

namespace App\Application\Intelligence;

use App\Contracts\Intelligence\IntelligenceSearchContract;
use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Contracts\Repositories\OrganisationRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Intelligence\SearchResponse;
use App\Domain\Intelligence\SearchResult;
use App\Domain\Organisation\CoJOrganisation;

/**
 * Orchestrates the deterministic intelligence pipeline:
 * query -> normalise -> extract terms -> repository search -> score ->
 * rank -> explain -> results.
 */
final readonly class IntelligenceSearchService implements IntelligenceSearchContract
{
    public function __construct(
        private OrganisationRepositoryContract $organisations,
        private ArticleRepositoryContract $articles,
        private QueryNormalizer $normalizer,
        private RelevanceScorer $scorer,
        private SearchRanker $ranker,
        private SearchExplainer $explainer,
    ) {
    }

    public function search(string $query): SearchResponse
    {
        $original = $query;
        $normalised = $this->normalizer->normalize($query);
        $terms = $this->normalizer->terms($query);

        if ($terms === []) {
            return new SearchResponse(
                originalQuery: $original,
                normalisedQuery: $normalised,
                queryTerms: $terms,
                message: 'No searchable terms could be extracted from the query.',
            );
        }

        try {
            $organisationRows = $this->organisations->all();
            $articleRows = $this->articles->all();
        } catch (\Throwable) {
            return new SearchResponse(
                originalQuery: $original,
                normalisedQuery: $normalised,
                queryTerms: $terms,
                message: 'The intelligence repository is not currently available.',
            );
        }

        if ($organisationRows === []) {
            return new SearchResponse(
                originalQuery: $original,
                normalisedQuery: $normalised,
                queryTerms: $terms,
                message: 'No organisations are present in the intelligence repository.',
            );
        }

        $articlesByOrganisation = [];
        /** @var Article $article */
        foreach ($articleRows as $article) {
            foreach ($article->organisationIds as $organisationId) {
                $articlesByOrganisation[$organisationId] ??= [];
                $articlesByOrganisation[$organisationId][] = $article;
            }
        }

        $matches = [];
        /** @var CoJOrganisation $organisation */
        foreach ($organisationRows as $organisation) {
            $match = $this->scorer->evaluate(
                $organisation,
                $terms,
                $articlesByOrganisation[$organisation->id] ?? [],
            );

            if ($match !== null) {
                $matches[] = $match;
            }
        }

        $ordered = $this->ranker->rank($matches);

        $results = [];
        foreach ($ordered as $position => $match) {
            $organisation = $this->findOrganisation($organisationRows, $match->organisationId);
            if ($organisation === null) {
                continue;
            }

            $articles = $articlesByOrganisation[$match->organisationId] ?? [];
            $results[] = new SearchResult(
                organisationId: $match->organisationId,
                organisationName: $match->organisationName,
                organisationType: $match->organisationType,
                score: $match->score,
                rank: $position + 1,
                matchingTerms: $match->matchingTerms,
                reasons: $this->explainer->reasons($match, $organisation, $articles),
                verificationStatus: $organisation->verificationStatus,
                sources: $this->explainer->sources($organisation, $articles),
            );
        }

        return new SearchResponse(
            originalQuery: $original,
            normalisedQuery: $normalised,
            queryTerms: $terms,
            results: $results,
            message: $results === []
                ? 'No sufficiently relevant information was found in the repository for this query.'
                : '',
        );
    }

    /**
     * @param CoJOrganisation[] $organisations
     */
    private function findOrganisation(array $organisations, string $id): ?CoJOrganisation
    {
        foreach ($organisations as $organisation) {
            if ($organisation->id === $id) {
                return $organisation;
            }
        }

        return null;
    }
}