<?php

namespace App\Contracts\Repositories;

use App\Domain\Intelligence\SelectionDecision;

/**
 * Persists selection decisions produced by the top-three selector.
 *
 * The stored decisions form the selection history used by the repetition
 * policy: recent selections of the same organisation attract a configurable
 * penalty so one entity does not dominate repeated selections. History is
 * queried through this contract only; no second storage mechanism exists.
 */
interface SelectionDecisionRepositoryContract
{
    /** @return SelectionDecision[] */
    public function all(): array;

    /**
     * Append one or more selection decisions to the history. Decisions are
     * cumulative — repeated selections of the same organisation accumulate.
     *
     * @param SelectionDecision[] $decisions
     */
    public function saves(array $decisions): void;

    /**
     * All stored decisions for a single organisation (oldest first).
     *
     * @return SelectionDecision[]
     */
    public function forOrganisation(string $organisationId): array;

    public function count(): int;
}