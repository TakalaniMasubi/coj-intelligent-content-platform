<?php

namespace App\Domain\Intelligence;

final readonly class SearchMatch
{
    /**
     * @param array<string, string[]> $fieldTerms  matched field => terms that matched it
     * @param string[]                $matchingTerms
     */
    public function __construct(
        public string $organisationId,
        public string $organisationName,
        public string $organisationType,
        public array $fieldTerms,
        public array $matchingTerms,
        public int $score,
    ) {
    }
}