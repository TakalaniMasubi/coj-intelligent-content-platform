<?php

namespace App\Domain\Intelligence;

/**
 * The outcome of a selection run: either a successful top-three selection
 * or an explicit insufficient-results state. Selection outcomes never invent
 * or duplicate organisations, and never silently fill missing positions.
 */
final readonly class SelectionOutcome
{
    public const SUCCESS = 'success';

    public const INSUFFICIENT = 'insufficient_results';

    /**
     * @param SelectionDecision[] $decisions
     */
    public function __construct(
        public string $originalQuery,
        public bool $success,
        public string $status,
        public int $required,
        public int $available,
        public array $decisions,
        public string $message,
    ) {
    }

    /**
     * @return SelectionDecision[]
     */
    public function selected(): array
    {
        return array_values(array_filter(
            $this->decisions,
            static fn (SelectionDecision $decision): bool => $decision->selected,
        ));
    }

    public function count(): int
    {
        return count($this->selected());
    }
}