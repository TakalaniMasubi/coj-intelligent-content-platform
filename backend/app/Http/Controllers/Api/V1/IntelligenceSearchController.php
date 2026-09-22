<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Intelligence\IntelligenceSearchContract;
use App\Http\Requests\IntelligenceSearchRequest;

class IntelligenceSearchController
{
    /**
     * Natural-language municipal intelligence search.
     *
     * Runs the deterministic search pipeline (normalisation, term
     * extraction, repository search, scoring, ranking, explanation) and
     * returns a stable, explained result envelope.
     */
    public function search(IntelligenceSearchRequest $request, IntelligenceSearchContract $search): array
    {
        $response = $search->search($request->input('query', ''));

        return [
            'success' => true,
            'data' => $response->toArray(),
        ];
    }
}