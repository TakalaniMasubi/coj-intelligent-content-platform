<?php

namespace App\Infrastructure\Persistence\Json;

use App\Contracts\Repositories\SelectionDecisionRepositoryContract;
use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;

/**
 * JSON driver for the selection decision history. The history rows are
 * stored under the same repository volume as the curated data, in a file
 * separate from the immutable curated dataset.
 */
final readonly class JsonSelectionDecisionRepository implements SelectionDecisionRepositoryContract
{
    private const ENTITY = 'selections';

    private const KEY = 'selection_decisions';

    public function __construct(
        private RepositoryJsonGateway $gateway,
    ) {
    }

    public function all(): array
    {
        $rows = [];

        try {
            $rows = $this->gateway->load(self::ENTITY, self::KEY);
        } catch (RepositoryFileNotFoundException) {
            // An append-only history legitimately starts with no file at all;
            // that is an empty history, not a repository error.
            return [];
        }

        return array_map(
            fn (array $row): SelectionDecision => SelectionDecision::fromArray($row),
            $rows,
        );
    }

    public function saves(array $decisions): void
    {
        if ($decisions === []) {
            return;
        }

        $rows = [];

        foreach ($this->existing() as $existing) {
            $rows[] = $existing->toArray();
        }

        foreach ($decisions as $decision) {
            $rows[] = $decision->toArray();
        }

        $this->gateway->save(self::ENTITY, self::KEY, $rows);
    }

    public function forOrganisation(string $organisationId): array
    {
        $decisions = [];

        foreach ($this->existing() as $decision) {
            if ($decision->organisationId === $organisationId) {
                $decisions[] = $decision;
            }
        }

        return $decisions;
    }

    public function count(): int
    {
        return count($this->existing());
    }

    /** @return SelectionDecision[] */
    private function existing(): array
    {
        return $this->all();
    }
}