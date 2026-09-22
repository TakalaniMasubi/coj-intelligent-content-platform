<?php

namespace App\Domain\Intelligence;

final readonly class SearchResponse
{
    /**
     * @param string[]          $queryTerms
     * @param SearchResult[]    $results
     */
    public function __construct(
        public string $originalQuery,
        public string $normalisedQuery,
        public array $queryTerms,
        public array $results = [],
        public string $message = '',
    ) {
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function toArray(): array
    {
        return [
            'original_query' => $this->originalQuery,
            'normalised_query' => $this->normalisedQuery,
            'query_terms' => array_values($this->queryTerms),
            'result_count' => $this->count(),
            'results' => array_map(static fn (SearchResult $result) => $result->toArray(), $this->results),
            'message' => $this->message,
        ];
    }
}