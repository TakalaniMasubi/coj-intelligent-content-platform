<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Intelligence\SelectionContract;
use App\Domain\Intelligence\SelectionOutcome;
use App\Http\Requests\IntelligenceSelectRequest;

class IntelligenceSelectController
{
    /**
     * Explainable top-three organisation selection.
     *
     * Runs the Phase 4 deterministic search pipeline and then applies the
     * Phase 5 selection policy (eligibility, repetition control, exactly
     * three) returning either a successful selection envelope or an explicit
     * insufficient-results state. Decisions are persisted as history for the
     * repetition policy and future auditability.
     */
    public function select(IntelligenceSelectRequest $request, SelectionContract $selection): array
    {
        $outcome = $selection->select($request->input('query', ''));

        return $outcome->success
            ? $this->successEnvelope($outcome)
            : $this->insufficientEnvelope($outcome);
    }

    private function successEnvelope(SelectionOutcome $outcome): array
    {
        return [
            'success' => true,
            'data' => [
                'original_query' => $outcome->originalQuery,
                'selected_count' => $outcome->count(),
                'selections' => array_map(
                    static fn ($decision) => $decision->toArray(),
                    $outcome->selected(),
                ),
            ],
        ];
    }

    private function insufficientEnvelope(SelectionOutcome $outcome): array
    {
        return [
            'success' => false,
            'status' => $outcome->status,
            'required' => $outcome->required,
            'available' => $outcome->available,
            'message' => $outcome->message,
            'results' => array_map(
                static fn ($decision) => $decision->toArray(),
                $outcome->decisions,
            ),
        ];
    }
}