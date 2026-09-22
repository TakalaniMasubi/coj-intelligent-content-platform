<?php

namespace App\Application\Intelligence;

use App\Contracts\Intelligence\SelectionContract;
use App\Contracts\Repositories\SelectionDecisionRepositoryContract;
use App\Domain\Intelligence\SelectionOutcome;
use DateTimeImmutable;

/**
 * Orchestrates an explainable top-three selection request:
 *
 *   Phase 4 search -> ranking -> top-three selection policy -> decision
 *   record -> persisted history.
 *
 * The ranking algorithm is not duplicated here; this service reuses the
 * Phase 4 search pipeline and only applies the selection policy on top.
 */
final readonly class IntelligenceSelectionService implements SelectionContract
{
    public function __construct(
        private IntelligenceSearchService $search,
        private OrganisationSelector $selector,
        private SelectionDecisionRepositoryContract $history,
    ) {
    }

    public function select(string $query, ?DateTimeImmutable $asOf = null): SelectionOutcome
    {
        $outcome = $this->selector->select(
            $query,
            $this->search->search($query)->results,
            $asOf,
        );

        if ($outcome->success) {
            $this->history->saves($outcome->selected());
        }

        return $outcome;
    }
}