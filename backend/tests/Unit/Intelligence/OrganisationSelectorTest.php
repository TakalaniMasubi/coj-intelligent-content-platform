<?php

namespace Tests\Unit\Intelligence;

use App\Application\Intelligence\OrganisationSelector;
use App\Domain\Intelligence\SearchResult;
use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Shared\VerificationStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemorySelectionDecisionRepository;

class OrganisationSelectorTest extends TestCase
{
    private DateTimeImmutable $asOf;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asOf = new DateTimeImmutable('2026-09-22T12:00:00+00:00');
    }

    public function test_it_selects_exactly_three_in_policy_order(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select(
            'waste',
            [
                $this->searchResult('low', 'Low', 25),
                $this->searchResult('high', 'High', 90),
                $this->searchResult('mid', 'Mid', 50),
            ],
            $this->asOf,
        );

        $this->assertTrue($outcome->success);
        $this->assertSame('success', $outcome->status);
        $this->assertSame(3, $outcome->count());
        $this->assertSame(3, $outcome->required);
        $this->assertSame(3, $outcome->available);

        $selected = $outcome->selected();
        $this->assertSame(['high', 'mid', 'low'], array_map(
            static fn (SelectionDecision $d) => $d->organisationId,
            $selected,
        ));
        $this->assertSame([1, 2, 3], array_map(
            static fn (SelectionDecision $d) => $d->rank,
            $selected,
        ));
        $this->assertTrue($selected[0]->selected);
    }

    public function test_it_preserves_the_phase_four_ranking_order(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('water', [
            $this->searchResult('second', 'Second', 70),
            $this->searchResult('first', 'First', 95),
            $this->searchResult('third', 'Third', 40),
        ], $this->asOf);

        $this->assertSame(['first', 'second', 'third'], array_map(
            static fn (SelectionDecision $d) => $d->organisationId,
            $outcome->selected(),
        ));
    }

    public function test_it_filters_candidates_below_the_minimum_score(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('waste', [
            $this->searchResult('a', 'Alpha', 90),
            $this->searchResult('b', 'Bravo', 60),
            $this->searchResult('c', 'Charlie', 40),
            $this->searchResult('weak', 'Weak', 12), // below min_score 20
            $this->searchResult('weaker', 'Weaker', 5),
        ], $this->asOf);

        $this->assertTrue($outcome->success);
        $ids = array_map(static fn (SelectionDecision $d) => $d->organisationId, $outcome->selected());
        $this->assertSame(['a', 'b', 'c'], $ids);
        $this->assertNotContains('weak', $ids);
    }

    public function test_fewer_than_three_eligible_candidates_is_insufficient(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('housing', [
            $this->searchResult('a', 'Alpha', 80),
            $this->searchResult('b', 'Bravo', 30),
        ], $this->asOf);

        $this->assertFalse($outcome->success);
        $this->assertSame('insufficient_results', $outcome->status);
        $this->assertSame(3, $outcome->required);
        $this->assertSame(2, $outcome->available);
        $this->assertCount(2, $outcome->decisions);
        $this->assertFalse($outcome->decisions[0]->selected);
        $this->assertStringContainsString('not selected', implode(' ', $outcome->decisions[0]->reasons));
    }

    public function test_zero_candidates_is_insufficient(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('nothing', [], $this->asOf);

        $this->assertFalse($outcome->success);
        $this->assertSame('insufficient_results', $outcome->status);
        $this->assertSame(0, $outcome->available);
        $this->assertSame([], $outcome->decisions);
    }

    public function test_all_candidates_below_the_floor_is_insufficient(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('weak', [
            $this->searchResult('a', 'Alpha', 10),
        ], $this->asOf);

        $this->assertFalse($outcome->success);
        $this->assertSame(0, $outcome->available);
    }

    public function test_verification_status_is_preserved_and_exposed(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('public safety', [
            $this->searchResult('pending-org', 'Strong Pending', 68, 'pending'),
            $this->searchResult('verified-org', 'Verified', 50, 'verified'),
            $this->searchResult('third', 'Third', 30),
        ], $this->asOf);

        $this->assertTrue($outcome->success);
        $top = $outcome->selected()[0];

        // A strongly relevant pending organisation can be selected...
        $this->assertSame('pending-org', $top->organisationId);
        // ...but its status is never concealed.
        $this->assertSame(VerificationStatus::Pending, $top->verificationStatus);
        $this->assertSame('pending', $top->toArray()['verification_status']);
    }

    public function test_no_history_means_no_repetition_penalty(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('housing', [
            $this->searchResult('joshco', 'JOSHCO', 80, 'verified'),
            $this->searchResult('b', 'Bravo', 40),
            $this->searchResult('c', 'Charlie', 30),
        ], $this->asOf);

        $top = $outcome->selected()[0];
        $this->assertSame('joshco', $top->organisationId);
        $this->assertSame(0, $top->repetitionPenalty);
        $this->assertSame(80, $top->originalScore);
        $this->assertSame(80, $top->adjustedScore);
        $this->assertContains('No repetition penalty applied', $top->reasons);
    }

    public function test_repetition_penalty_is_drawn_from_history(): void
    {
        $history = new InMemorySelectionDecisionRepository([
            $this->priorDecision('joshco', 'JOSHCO', $this->asOf->modify('-1 day')),
        ]);

        $selector = new OrganisationSelector($history);

        $outcome = $selector->select('housing', [
            $this->searchResult('joshco', 'JOSHCO', 80, 'verified'),
            $this->searchResult('b', 'Bravo', 40),
            $this->searchResult('c', 'Charlie', 30),
        ], $this->asOf);

        $top = $outcome->selected()[0];
        $this->assertSame('joshco', $top->organisationId);
        $this->assertSame(4, $top->repetitionPenalty);
        $this->assertSame(80, $top->originalScore);
        $this->assertSame(76, $top->adjustedScore);
        $this->assertStringContainsString('Repetition penalty of 4 applied', implode(' ', $top->reasons));
    }

    public function test_repeated_selection_increases_the_penalty(): void
    {
        $history = new InMemorySelectionDecisionRepository([
            $this->priorDecision('a', 'Alpha', $this->asOf->modify('-2 days')),
            $this->priorDecision('a', 'Alpha', $this->asOf->modify('-1 day')),
        ]);

        $selector = new OrganisationSelector($history);

        $outcome = $selector->select('x', [
            $this->searchResult('a', 'Alpha', 80),
            $this->searchResult('b', 'Bravo', 40),
            $this->searchResult('c', 'Charlie', 30),
        ], $this->asOf);

        $top = $outcome->selected()[0];
        $this->assertSame('a', $top->organisationId);
        $this->assertSame(8, $top->repetitionPenalty);
        $this->assertSame(72, $top->adjustedScore);
    }

    public function test_strong_relevance_overcomes_repetition_penalty(): void
    {
        // Alpha has been selected many times; its penalty is capped by
        // max_penalty (25). Its adjusted score still beats a weaker candidate
        // that has never been selected, proving relevance is never abandoned
        // merely to avoid repetition.
        $history = new InMemorySelectionDecisionRepository([]);
        foreach ([1, 2, 3, 4, 5, 6, 7] as $daysAgo) {
            $history->saves([
                $this->priorDecision('alpha', 'Alpha', $this->asOf->modify('-'.$daysAgo.' days')),
            ]);
        }

        $selector = new OrganisationSelector($history);

        $outcome = $selector->select('x', [
            $this->searchResult('alpha', 'Alpha', 80),
            $this->searchResult('bravo', 'Bravo', 50),
            $this->searchResult('charlie', 'Charlie', 30),
        ], $this->asOf);

        $top = $outcome->selected()[0];
        $this->assertSame('alpha', $top->organisationId);
        $this->assertSame(25, $top->repetitionPenalty);
        $this->assertSame(55, $top->adjustedScore);
    }

    public function test_history_outside_the_repetition_window_is_ignored(): void
    {
        $history = new InMemorySelectionDecisionRepository([
            $this->priorDecision('a', 'Alpha', $this->asOf->modify('-60 days')),
        ]);

        $selector = new OrganisationSelector($history);

        $outcome = $selector->select('x', [
            $this->searchResult('a', 'Alpha', 80),
            $this->searchResult('b', 'Bravo', 40),
            $this->searchResult('c', 'Charlie', 30),
        ], $this->asOf);

        $top = $outcome->selected()[0];
        $this->assertSame('a', $top->organisationId);
        $this->assertSame(0, $top->repetitionPenalty);
    }

    public function test_a_candidate_dropped_below_the_floor_by_penalty_is_passed_over(): void
    {
        // Alpha matches but has been selected repeatedly; its adjusted score
        // falls below the minimum. It is passed over instead of being
        // selected to fill a slot.
        $history = new InMemorySelectionDecisionRepository([
            $this->priorDecision('alpha', 'Alpha', $this->asOf->modify('-1 day')),
            $this->priorDecision('alpha', 'Alpha', $this->asOf->modify('-2 days')),
            $this->priorDecision('alpha', 'Alpha', $this->asOf->modify('-3 days')),
            $this->priorDecision('alpha', 'Alpha', $this->asOf->modify('-4 days')),
        ]);

        $selector = new OrganisationSelector($history);

        $outcome = $selector->select('x', [
            $this->searchResult('alpha', 'Alpha', 30), // adjusted 30 - 16 = 14 < 20
            $this->searchResult('b', 'Bravo', 40),
            $this->searchResult('c', 'Charlie', 35),
            $this->searchResult('d', 'Delta', 25),
        ], $this->asOf);

        $this->assertTrue($outcome->success);
        $ids = array_map(static fn (SelectionDecision $d) => $d->organisationId, $outcome->selected());
        $this->assertSame(['b', 'c', 'd'], $ids);
        $this->assertNotContains('alpha', $ids);
    }

    public function test_ties_break_by_original_score_then_name_then_id(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        // No history: adjusted == original, so ties are identical. Ordering
        // falls through to original score, then name, then id.
        $outcome = $selector->select('x', [
            $this->searchResult('c2', 'Charlie', 40),
            $this->searchResult('a1', 'Alpha', 40),
            $this->searchResult('b2', 'Bravo', 40),
            $this->searchResult('d1', 'Extra', 30),
        ], $this->asOf);

        $ids = array_map(static fn (SelectionDecision $d) => $d->organisationId, $outcome->selected());
        $this->assertSame(['a1', 'b2', 'c2'], $ids);
    }

    public function test_selection_is_deterministic_across_runs(): void
    {
        $history = new InMemorySelectionDecisionRepository();
        $selector = new OrganisationSelector($history);

        $results = [
            $this->searchResult('a', 'Alpha', 70),
            $this->searchResult('b', 'Bravo', 50),
            $this->searchResult('c', 'Charlie', 30),
        ];

        $first = $selector->select('x', $results, $this->asOf);
        $second = $selector->select('x', $results, $this->asOf);

        $this->assertSame(
            array_map(static fn (SelectionDecision $d) => $d->toArray(), $first->selected()),
            array_map(static fn (SelectionDecision $d) => $d->toArray(), $second->selected()),
        );
    }

    public function test_selection_decision_carries_the_full_audit_fields(): void
    {
        $selector = new OrganisationSelector(new InMemorySelectionDecisionRepository());

        $outcome = $selector->select('waste', [
            $this->searchResult('pikitup', 'Pikitup', 43, 'pending', ['waste', 'management']),
        ], $this->asOf);

        $this->assertFalse($outcome->success);

        // 1 eligible candidate only => insufficient, but the candidate record
        // must still expose every auditable field.
        $record = $outcome->decisions[0]->toArray();
        foreach ([
            'organisation_id',
            'organisation_name',
            'rank',
            'original_score',
            'adjusted_score',
            'verification_status',
            'matching_terms',
            'reasons',
            'repetition_penalty',
            'final_selection',
            'selection_timestamp',
            'query',
        ] as $key) {
            $this->assertArrayHasKey($key, $record, "Missing auditable key: {$key}");
        }
        $this->assertSame(['waste', 'management'], $record['matching_terms']);
        $this->assertSame('waste', $record['query']);
        $this->assertSame($this->asOf->format('c'), $record['selection_timestamp']);
    }

    private function searchResult(
        string $id,
        string $name,
        int $score,
        string $verification = 'verified',
        array $terms = [],
        array $reasons = [],
    ): SearchResult {
        return new SearchResult(
            organisationId: $id,
            organisationName: $name,
            organisationType: 'entity',
            score: $score,
            rank: 1,
            matchingTerms: $terms,
            reasons: $reasons !== [] ? $reasons : ["Matched '{$name}' in the organisation name"],
            verificationStatus: VerificationStatus::fromName($verification),
            sources: [],
        );
    }

    private function priorDecision(string $id, string $name, DateTimeImmutable $at): SelectionDecision
    {
        return new SelectionDecision(
            organisationId: $id,
            organisationName: $name,
            rank: 1,
            originalScore: 80,
            adjustedScore: 80,
            verificationStatus: VerificationStatus::Verified,
            matchingTerms: ['x'],
            reasons: ['prior selection'],
            repetitionPenalty: 0,
            selected: true,
            selectedAt: $at,
            query: 'x',
        );
    }
}