<?php

namespace App\Application\Intelligence;

use App\Contracts\Repositories\SelectionDecisionRepositoryContract;
use App\Domain\Intelligence\SearchResult;
use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Intelligence\SelectionOutcome;
use DateTimeImmutable;

/**
 * The top-three organisation selection policy.
 *
 * Eligibility is determined purely by a minimum relevance score against the
 * Phase 4 ranking (never by random or array-order shortcuts). Selections are
 * ordered by adjusted score (relevance minus a repetition penalty drawn from
 * the persisted selection history), with stable tie-breaking. Fewer than
 * three eligible candidates produce an explicit insufficient-results state.
 *
 * Determinism contract: the same repository + query + selection history
 * always yields the same selection.
 */
final readonly class OrganisationSelector
{
    public function __construct(
        private SelectionDecisionRepositoryContract $history,
        private int $required = 3,
        private int $minimumScore = 20,
        private int $perSelectionPenalty = 4,
        private int $maxPenalty = 25,
        private ?int $windowDays = 30,
    ) {
    }

    /**
     * @param SearchResult[] $results  Phase 4 ranked search results
     */
    public function select(string $query, array $results, ?DateTimeImmutable $asOf = null): SelectionOutcome
    {
        $asOf ??= new DateTimeImmutable();
        $since = $this->windowDays !== null
            ? $asOf->modify('-'.$this->windowDays.' days')
            : null;

        $candidates = [];
        foreach ($results as $result) {
            if ($result->score < $this->minimumScore) {
                continue;
            }
            $candidates[] = $result;
        }

        $available = count($candidates);

        if ($available === 0) {
            return new SelectionOutcome(
                originalQuery: $query,
                success: false,
                status: SelectionOutcome::INSUFFICIENT,
                required: $this->required,
                available: 0,
                decisions: [],
                message: 'No sufficiently relevant organisations were available for selection.',
            );
        }

        $knowledge = [];
        foreach ($candidates as $result) {
            $penalty = $this->repetitionPenalty($result->organisationId, $since, $asOf);
            $knowledge[] = [
                'result' => $result,
                'penalty' => $penalty,
                'adjusted' => max(0, $result->score - $penalty),
            ];
        }

        usort(
            $knowledge,
            static function (array $a, array $b): int {
                return ($b['adjusted'] <=> $a['adjusted'])
                    ?: ($b['result']->score <=> $a['result']->score)
                    ?: strcmp($a['result']->organisationName, $b['result']->organisationName)
                    ?: strcmp($a['result']->organisationId, $b['result']->organisationId);
            },
        );

        // Respect the minimum-relevance rule even after penalty adjustments:
        // a candidate whose adjusted score drops below the floor is passed
        // over rather than selected irresponsibly to fill a slot.
        $eligible = [];
        foreach ($knowledge as $entry) {
            if ($entry['adjusted'] < $this->minimumScore) {
                continue;
            }
            $eligible[] = $entry;
        }

        if (count($eligible) < $this->required) {
            $decisions = [];
            foreach ($eligible as $index => $entry) {
                $decisions[] = $this->candidateDecision(
                    $entry,
                    $query,
                    $index,
                    $asOf,
                    $this->priorCount($entry['result']->organisationId, $since, $asOf),
                );
            }

            return new SelectionOutcome(
                originalQuery: $query,
                success: false,
                status: SelectionOutcome::INSUFFICIENT,
                required: $this->required,
                available: $available,
                decisions: $decisions,
                message: 'Insufficient relevant results for selection: '.count($eligible).' of '.$this->required.' required organisations available.',
            );
        }

        $decisions = [];
        foreach (array_slice($eligible, 0, $this->required) as $index => $entry) {
            $decisions[] = $this->selectionDecision(
                $entry,
                $query,
                $index,
                $asOf,
                $this->priorCount($entry['result']->organisationId, $since, $asOf),
            );
        }

        return new SelectionOutcome(
            originalQuery: $query,
            success: true,
            status: SelectionOutcome::SUCCESS,
            required: $this->required,
            available: $available,
            decisions: $decisions,
            message: 'Selected '.$this->required.' organisations.',
        );
    }

    private function repetitionPenalty(string $organisationId, ?DateTimeImmutable $since, DateTimeImmutable $asOf): int
    {
        return min($this->maxPenalty, $this->priorCount($organisationId, $since, $asOf) * $this->perSelectionPenalty);
    }

    private function priorCount(string $organisationId, ?DateTimeImmutable $since, DateTimeImmutable $asOf): int
    {
        $count = 0;

        foreach ($this->history->forOrganisation($organisationId) as $decision) {
            if ($since !== null && $decision->selectedAt < $since) {
                continue;
            }
            if ($decision->selectedAt < $asOf) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * A committed decision in a successful top-three outcome.
     *
     * @param array{result: SearchResult, penalty: int, adjusted: int} $entry
     */
    private function selectionDecision(
        array $entry,
        string $query,
        int $index,
        DateTimeImmutable $asOf,
        int $priorCount,
    ): SelectionDecision {
        /** @var SearchResult $result */
        $result = $entry['result'];

        $reasons = $result->reasons;
        $reasons[] = 'Selected as top '.($index + 1).' for this request (original relevance score '.$result->score.')';

        if ($entry['penalty'] > 0) {
            $reasons[] = 'Repetition penalty of '.$entry['penalty'].' applied (selected '.$priorCount.
                ' time'.($priorCount === 1 ? '' : 's').' within the repetition window)';
        } else {
            $reasons[] = 'No repetition penalty applied';
        }

        return new SelectionDecision(
            organisationId: $result->organisationId,
            organisationName: $result->organisationName,
            rank: $index + 1,
            originalScore: $result->score,
            adjustedScore: $entry['adjusted'],
            verificationStatus: $result->verificationStatus,
            matchingTerms: $result->matchingTerms,
            reasons: $reasons,
            repetitionPenalty: $entry['penalty'],
            selected: true,
            selectedAt: $asOf,
            query: $query,
        );
    }

    /**
     * A candidate presented in an insufficient-results state. It carries the
     * same audit fields but is recorded as NOT selected.
     *
     * @param array{result: SearchResult, penalty: int, adjusted: int} $entry
     */
    private function candidateDecision(
        array $entry,
        string $query,
        int $index,
        DateTimeImmutable $asOf,
        int $priorCount,
    ): SelectionDecision {
        /** @var SearchResult $result */
        $result = $entry['result'];

        $reasons = $result->reasons;
        $reasons[] = 'Candidate ranked '.($index + 1).' — not selected because fewer than '.$this->required.
            ' eligible organisations are available';

        return new SelectionDecision(
            organisationId: $result->organisationId,
            organisationName: $result->organisationName,
            rank: $index + 1,
            originalScore: $result->score,
            adjustedScore: $entry['adjusted'],
            verificationStatus: $result->verificationStatus,
            matchingTerms: $result->matchingTerms,
            reasons: $reasons,
            repetitionPenalty: $entry['penalty'],
            selected: false,
            selectedAt: $asOf,
            query: $query,
        );
    }
}