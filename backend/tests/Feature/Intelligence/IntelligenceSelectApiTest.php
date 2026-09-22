<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Repositories\CreatesTempVolumes;

class IntelligenceSelectApiTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTempVolumes;

    private string $runtimeVolume;

    protected function setUp(): void
    {
        parent::setUp();

        // Search reads the curated dataset; selection additionally persists
        // decision history. Both must live outside the committed seed-data
        // directory, so the dataset is copied into a temporary runtime volume.
        $this->runtimeVolume = $this->tempVolume();
        mkdir($this->runtimeVolume, 0755, true);

        foreach (['organisations', 'articles', 'sources'] as $entity) {
            copy(
                database_path('seed-data').DIRECTORY_SEPARATOR.$entity.'.json',
                $this->runtimeVolume.DIRECTORY_SEPARATOR.$entity.'.json',
            );
        }

        config(['cojmip.repository.volume' => $this->runtimeVolume]);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->runtimeVolume);
        parent::tearDown();
    }

    public function test_a_successful_query_selects_exactly_three_organisations(): void
    {
        $response = $this->postJson('/api/v1/intelligence/select', [
            'query' => 'waste management',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.original_query', 'waste management')
            ->assertJsonPath('data.selected_count', 3);

        $selections = $response->json('data.selections');

        $this->assertCount(3, $selections);
        $this->assertSame([1, 2, 3], array_column($selections, 'rank'));

        $ids = array_column($selections, 'organisation_id');
        $this->assertSame(['pikitup', 'jpc', 'joburg-tourism'], $ids);

        // The strongly relevant PENDING organisation is selectable, and its
        // verification status is always exposed alongside the score.
        $top = $selections[0];
        $this->assertSame('Pikitup', $top['organisation_name']);
        $this->assertSame('pending', $top['verification_status']);
        $this->assertTrue($top['final_selection']);
        $this->assertNotEmpty($top['matching_terms']);
        $this->assertNotEmpty($top['reasons']);

        foreach ($selections as $selection) {
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
                $this->assertArrayHasKey($key, $selection, "Missing selection key: {$key}");
            }
        }
    }

    public function test_fewer_than_three_eligible_candidates_returns_insufficient_results(): void
    {
        $response = $this->postJson('/api/v1/intelligence/select', [
            'query' => 'transport',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 'insufficient_results')
            ->assertJsonPath('required', 3);

        $body = $response->json();

        $this->assertSame(2, $body['available']);
        $this->assertCount(2, $body['results']);

        $ids = array_column($body['results'], 'organisation_id');
        $this->assertSame(['transport', 'metrobus'], $ids);
        $this->assertFalse($body['results'][0]['final_selection']);
    }

    public function test_a_query_with_no_candidates_returns_insufficient_results(): void
    {
        $response = $this->postJson('/api/v1/intelligence/select', [
            'query' => 'warehouse of interstellar unicorns',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 'insufficient_results')
            ->assertJsonPath('required', 3)
            ->assertJsonPath('available', 0);
    }

    public function test_missing_query_is_rejected(): void
    {
        $this->postJson('/api/v1/intelligence/select', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    public function test_whitespace_only_query_is_rejected(): void
    {
        $this->postJson('/api/v1/intelligence/select', ['query' => '  '])
            ->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    public function test_query_longer_than_max_length_is_rejected(): void
    {
        $this->postJson('/api/v1/intelligence/select', ['query' => str_repeat('a', 501)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    public function test_repeated_selection_applies_and_persists_a_repetition_penalty(): void
    {
        // Using the full "housing support" phrasing yields 4 eligible
        // candidates (JOSHCO, Human Settlements, Economic Development,
        // Office of the City Manager), which is what makes a success state
        // with an exactly-three selection possible.
        $query = 'Which organisations support housing?';

        $first = $this->postJson('/api/v1/intelligence/select', ['query' => $query]);
        $first->assertJsonPath('success', true);

        $firstJoshco = collect($first->json('data.selections'))->first(
            fn (array $selection): bool => $selection['organisation_id'] === 'joshco',
        );

        $this->assertSame('joshco', $firstJoshco['organisation_id']);
        $this->assertSame(0, $firstJoshco['repetition_penalty']);
        $this->assertSame($firstJoshco['original_score'], $firstJoshco['adjusted_score']);

        // The decision was persisted as history.
        $this->assertFileExists($this->runtimeVolume.'/selections.json');

        $second = $this->postJson('/api/v1/intelligence/select', ['query' => $query]);
        $second->assertJsonPath('success', true);

        $secondJoshco = collect($second->json('data.selections'))->first(
            fn (array $selection): bool => $selection['organisation_id'] === 'joshco',
        );

        // JOSHCO remains top because its strong relevance survives the
        // repetition adjustment — the system never swaps in an irrelevant
        // organisation just to avoid repetition.
        $this->assertSame('joshco', $secondJoshco['organisation_id']);
        $this->assertSame(1, $secondJoshco['rank']);
        $this->assertSame(4, $secondJoshco['repetition_penalty']);
        $this->assertSame($secondJoshco['original_score'] - 4, $secondJoshco['adjusted_score']);
        $this->assertStringContainsString('Repetition penalty of 4 applied', implode(' ', $secondJoshco['reasons']));
    }
}