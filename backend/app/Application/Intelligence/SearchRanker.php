<?php

namespace App\Application\Intelligence;

use App\Domain\Intelligence\SearchMatch;

/**
 * Deterministic ranking with a stable tie-breaker.
 *
 * Results are ordered by score (descending); equal scores are broken by
 * organisation name (ascending) and then organisation id (ascending) so the
 * same repository + same query always yields the same order.
 */
final readonly class SearchRanker
{
    /**
     * @param SearchMatch[] $matches
     *
     * @return SearchMatch[]
     */
    public function rank(array $matches): array
    {
        $ordered = array_values($matches);

        usort(
            $ordered,
            static function (SearchMatch $a, SearchMatch $b): int {
                return ($b->score <=> $a->score)
                    ?: strcmp($a->organisationName, $b->organisationName)
                    ?: strcmp($a->organisationId, $b->organisationId);
            },
        );

        return $ordered;
    }
}