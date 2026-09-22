<?php

namespace App\Contracts\Intelligence;

use App\Domain\Intelligence\SelectionOutcome;

interface SelectionContract
{
    /**
     * Run the Phase 4 search pipeline and then apply the top-three selection
     * policy, returning a deterministic, explainable selection outcome.
     */
    public function select(string $query): SelectionOutcome;
}