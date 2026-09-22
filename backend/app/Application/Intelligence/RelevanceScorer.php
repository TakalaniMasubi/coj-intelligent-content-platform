<?php

namespace App\Application\Intelligence;

use App\Domain\Article\Article;
use App\Domain\Intelligence\SearchMatch;
use App\Domain\Organisation\CoJOrganisation;

/**
 * Transparent deterministic relevance scoring.
 *
 * Presence-based signal weights: a repository field contributes its weight
 * once per organisation when any query term is found in it. The base score
 * is then scaled by a documented verification factor. No randomness, no
 * time-based inputs, no ML.
 */
final readonly class RelevanceScorer
{
    /**
     * @param array<string, int>  $fieldWeights
     * @param array<string, float> $verificationFactors
     */
    public function __construct(
        array $fieldWeights = [],
        array $verificationFactors = [],
        int $cap = 100,
    ) {
        $this->fieldWeights = $fieldWeights ?: [
            'name' => 30,
            'abbreviation' => 22,
            'alias' => 18,
            'type' => 6,
            'description' => 22,
            'article_title' => 16,
            'article_summary' => 12,
        ];
        $this->verificationFactors = $verificationFactors ?: [
            'verified' => 1.0,
            'pending' => 0.85,
            'unverified' => 0.7,
        ];
        $this->cap = $cap;
    }

    /** @var array<string, int> */
    private array $fieldWeights;

    /** @var array<string, float> */
    private array $verificationFactors;

    private int $cap;

    /**
     * Evaluate an organisation against the query terms and its articles.
     * Returns null when no repository field matched any term.
     *
     * @param string[]  $terms
     * @param Article[] $articles
     */
    public function evaluate(CoJOrganisation $organisation, array $terms, array $articles): ?SearchMatch
    {
        $fieldTerms = [];

        $haystacks = [
            'name' => $organisation->name,
            'abbreviation' => (string) ($organisation->abbreviation ?? ''),
            'alias' => implode(' ', $organisation->aliases),
            'type' => $organisation->organisationType->value,
            'description' => (string) ($organisation->description ?? ''),
            'article_title' => implode(' ', array_map(static fn (Article $a) => $a->title, $articles)),
            'article_summary' => implode(' ', array_map(
                static fn (Article $a) => ($a->summary ?? '').' '.$a->subject,
                $articles,
            )),
        ];

        foreach ($haystacks as $field => $haystack) {
            $haystack = mb_strtolower($haystack);
            foreach ($terms as $term) {
                if ($haystack !== '' && $this->contains($haystack, $term)) {
                    $fieldTerms[$field] ??= [];
                    if (! in_array($term, $fieldTerms[$field], true)) {
                        $fieldTerms[$field][] = $term;
                    }
                }
            }
        }

        if ($fieldTerms === []) {
            return null;
        }

        $baseScore = 0;
        $matchingTerms = [];
        foreach ($this->fieldWeights as $field => $weight) {
            if (! isset($fieldTerms[$field])) {
                continue;
            }
            $baseScore += $weight;
            foreach ($fieldTerms[$field] as $term) {
                if (! in_array($term, $matchingTerms, true)) {
                    $matchingTerms[] = $term;
                }
            }
        }

        $factor = $this->verificationFactors[$organisation->verificationStatus->value] ?? 1.0;
        $score = (int) round($baseScore * $factor);
        $score = min($this->cap, $score);

        return new SearchMatch(
            organisationId: $organisation->id,
            organisationName: $organisation->name,
            organisationType: $organisation->organisationType->value,
            fieldTerms: $fieldTerms,
            matchingTerms: $matchingTerms,
            score: $score,
        );
    }

    private function contains(string $haystack, string $term): bool
    {
        return str_contains($haystack, $term);
    }
}