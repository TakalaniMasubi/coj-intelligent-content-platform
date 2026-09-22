<?php

namespace Tests\Unit\Intelligence;

use App\Application\Intelligence\SearchRanker;
use App\Domain\Intelligence\SearchMatch;
use PHPUnit\Framework\TestCase;

class SearchRankerTest extends TestCase
{
    private SearchRanker $ranker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ranker = new SearchRanker();
    }

    public function test_it_orders_by_score_descending(): void
    {
        $matches = [
            $this->match('low', 'Low', 10),
            $this->match('high', 'High', 80),
            $this->match('mid', 'Mid', 50),
        ];

        $ordered = $this->ranker->rank($matches);

        $this->assertSame(['high', 'mid', 'low'], array_column(
            array_map(static fn (SearchMatch $m) => ['id' => $m->organisationId], $ordered),
            'id',
        ));
    }

    public function test_it_breaks_ties_deterministically_by_name_then_id(): void
    {
        $matches = [
            $this->match('c-id', 'Charlie', 40),
            $this->match('a-id', 'Alpha', 40),
            $this->match('b-id', 'Bravo', 40),
        ];

        $ordered = $this->ranker->rank($matches);

        $this->assertSame(['a-id', 'b-id', 'c-id'], array_column(
            array_map(static fn (SearchMatch $m) => ['id' => $m->organisationId], $ordered),
            'id',
        ));
    }

    public function test_identical_input_produces_identical_output(): void
    {
        $matches = [
            $this->match('b', 'Beta', 40),
            $this->match('a', 'Alpha', 40),
        ];

        $this->assertSame(
            array_map(static fn (SearchMatch $m) => $m->organisationId, $this->ranker->rank($matches)),
            array_map(static fn (SearchMatch $m) => $m->organisationId, $this->ranker->rank($matches)),
        );
    }

    private function match(string $id, string $name, int $score): SearchMatch
    {
        return new SearchMatch(
            organisationId: $id,
            organisationName: $name,
            organisationType: 'entity',
            fieldTerms: [],
            matchingTerms: [],
            score: $score,
        );
    }
}