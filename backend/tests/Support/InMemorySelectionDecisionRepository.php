<?php

namespace Tests\Support;

use App\Contracts\Repositories\SelectionDecisionRepositoryContract;
use App\Domain\Intelligence\SelectionDecision;

/**
 * In-memory selection history for selector unit tests. Mirrors the append-
 * only semantics of the JSON driver without any filesystem dependency.
 */
final class InMemorySelectionDecisionRepository implements SelectionDecisionRepositoryContract
{
    /** @var SelectionDecision[] */
    private array $decisions = [];

    /**
     * @param SelectionDecision[] $initial
     */
    public function __construct(array $initial = [])
    {
        $this->decisions = $initial;
    }

    public function all(): array
    {
        return $this->decisions;
    }

    public function saves(array $decisions): void
    {
        foreach ($decisions as $decision) {
            $this->decisions[] = $decision;
        }
    }

    public function forOrganisation(string $organisationId): array
    {
        return array_values(array_filter(
            $this->decisions,
            static fn (SelectionDecision $decision): bool => $decision->organisationId === $organisationId,
        ));
    }

    public function count(): int
    {
        return count($this->decisions);
    }
}