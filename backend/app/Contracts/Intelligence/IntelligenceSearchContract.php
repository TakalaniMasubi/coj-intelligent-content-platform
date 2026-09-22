<?php

namespace App\Contracts\Intelligence;

use App\Domain\Intelligence\SearchResponse;

interface IntelligenceSearchContract
{
    /**
     * Run a natural-language query against the knowledge repository and
     * return deterministically ranked, explained results.
     */
    public function search(string $query): SearchResponse;
}