<?php

namespace Tests\Unit\Repositories;

use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Shared\VerificationStatus;
use App\Infrastructure\Persistence\Json\JsonSelectionDecisionRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class JsonSelectionDecisionRepositoryTest extends TestCase
{
    use CreatesTempVolumes;

    public function test_it_returns_empty_history_when_no_file_exists(): void
    {
        $repository = new JsonSelectionDecisionRepository(new RepositoryJsonGateway($this->tempVolume()));

        $this->assertSame([], $repository->all());
        $this->assertSame(0, $repository->count());
        $this->assertSame([], $repository->forOrganisation('anything'));
    }

    public function test_saves_appends_decision_history(): void
    {
        $volume = $this->tempVolume();
        $repository = new JsonSelectionDecisionRepository(new RepositoryJsonGateway($volume));

        $repository->saves([
            $this->decision('joshco', new DateTimeImmutable('2026-09-22T08:00:00+00:00')),
        ]);
        $repository->saves([
            $this->decision('metrobus', new DateTimeImmutable('2026-09-22T09:00:00+00:00')),
        ]);

        $this->assertSame(2, $repository->count());
        $this->assertSame(['joshco', 'metrobus'], array_map(
            static fn (SelectionDecision $d) => $d->organisationId,
            $repository->all(),
        ));

        $this->deleteDirectory($volume);
    }

    public function test_for_organisation_filters_the_history(): void
    {
        $volume = $this->tempVolume();
        $repository = new JsonSelectionDecisionRepository(new RepositoryJsonGateway($volume));

        $repository->saves([
            $this->decision('joshco', new DateTimeImmutable('2026-09-22T08:00:00+00:00')),
            $this->decision('pikitup', new DateTimeImmutable('2026-09-22T08:30:00+00:00')),
            $this->decision('joshco', new DateTimeImmutable('2026-09-22T09:00:00+00:00')),
        ]);

        $joshco = $repository->forOrganisation('joshco');
        $this->assertCount(2, $joshco);
        $this->assertSame(['joshco', 'joshco'], array_map(
            static fn (SelectionDecision $d) => $d->organisationId,
            $joshco,
        ));

        $this->assertSame([], $repository->forOrganisation('unknown'));
        $this->assertSame(3, $repository->count());

        $this->deleteDirectory($volume);
    }

    public function test_saved_decisions_round_trip_through_the_repository(): void
    {
        $volume = $this->tempVolume();
        $repository = new JsonSelectionDecisionRepository(new RepositoryJsonGateway($volume));

        $decision = $this->decision('joshco', new DateTimeImmutable('2026-09-22T08:00:00+00:00'));
        $repository->saves([$decision]);

        $stored = $repository->all()[0];
        $this->assertSame($decision->toArray(), $stored->toArray());

        $this->deleteDirectory($volume);
    }

    private function decision(string $id, DateTimeImmutable $at): SelectionDecision
    {
        return new SelectionDecision(
            organisationId: $id,
            organisationName: ucfirst($id),
            rank: 1,
            originalScore: 80,
            adjustedScore: 76,
            verificationStatus: VerificationStatus::Verified,
            matchingTerms: ['x'],
            reasons: ['Selected as top 1 for this request'],
            repetitionPenalty: 4,
            selected: true,
            selectedAt: $at,
            query: 'x',
        );
    }
}