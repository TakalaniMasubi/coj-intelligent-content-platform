<?php

namespace Tests\Unit\Domain;

use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Shared\VerificationStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SelectionDecisionTest extends TestCase
{
    public function test_from_array_builds_a_domain_object(): void
    {
        $decision = SelectionDecision::fromArray([
            'organisation_id' => 'joshco',
            'organisation_name' => 'Johannesburg Social Housing Company',
            'rank' => 1,
            'original_score' => 80,
            'adjusted_score' => 76,
            'verification_status' => 'verified',
            'matching_terms' => ['housing'],
            'reasons' => ['Selected as top 1 for this request'],
            'repetition_penalty' => 4,
            'final_selection' => true,
            'selection_timestamp' => '2026-09-22T12:00:00+00:00',
            'query' => 'housing',
        ]);

        $this->assertSame('joshco', $decision->organisationId);
        $this->assertSame(1, $decision->rank);
        $this->assertSame(80, $decision->originalScore);
        $this->assertSame(76, $decision->adjustedScore);
        $this->assertSame(4, $decision->repetitionPenalty);
        $this->assertTrue($decision->selected);
        $this->assertSame(VerificationStatus::Verified, $decision->verificationStatus);
        $this->assertSame('housing', $decision->query);
        $this->assertSame('2026-09-22T12:00:00+00:00', $decision->selectedAt->format(DATE_ATOM));
    }

    public function test_to_array_round_trips(): void
    {
        $original = new SelectionDecision(
            organisationId: 'pikitup',
            organisationName: 'Pikitup',
            rank: 1,
            originalScore: 43,
            adjustedScore: 39,
            verificationStatus: VerificationStatus::Pending,
            matchingTerms: ['waste', 'management'],
            reasons: ['Matched waste in the description', 'Verification status is PENDING — confirm against an official source before relying on this result'],
            repetitionPenalty: 4,
            selected: true,
            selectedAt: new DateTimeImmutable('2026-09-22T08:30:00+00:00'),
            query: 'waste management',
        );

        $restored = SelectionDecision::fromArray($original->toArray());

        $this->assertSame($original->toArray(), $restored->toArray());
        $this->assertSame('2026-09-22T08:30:00+00:00', $restored->selectedAt->format(DATE_ATOM));
    }

    public function test_from_array_requires_organisation_id_and_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SelectionDecision::fromArray(['rank' => 1]);
    }

    public function test_from_array_applies_documented_defaults(): void
    {
        $decision = SelectionDecision::fromArray([
            'organisation_id' => 'a',
            'organisation_name' => 'Alpha',
        ]);

        $this->assertSame(0, $decision->rank);
        $this->assertSame(0, $decision->originalScore);
        $this->assertSame(0, $decision->adjustedScore);
        $this->assertSame(0, $decision->repetitionPenalty);
        $this->assertFalse($decision->selected);
        $this->assertSame(VerificationStatus::Pending, $decision->verificationStatus);
        $this->assertSame([], $decision->matchingTerms);
        $this->assertSame([], $decision->reasons);
        $this->assertSame('', $decision->query);
    }

    public function test_snake_case_envelope_keys_match_project_conventions(): void
    {
        $decision = SelectionDecision::fromArray([
            'organisation_id' => 'a',
            'organisation_name' => 'Alpha',
            'final_selection' => false,
        ]);

        $this->assertArrayHasKey('final_selection', $decision->toArray());
        $this->assertArrayHasKey('selection_timestamp', $decision->toArray());
        $this->assertArrayHasKey('repetition_penalty', $decision->toArray());
        $this->assertFalse($decision->toArray()['final_selection']);
    }
}