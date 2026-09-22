<?php

namespace Tests\Feature\Intelligence;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntelligenceSearchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cojmip.repository.volume' => database_path('seed-data')]);
    }

    public function test_valid_query_returns_a_success_envelope_with_results(): void
    {
        $response = $this->postJson('/api/v1/intelligence/search', [
            'query' => 'Which organisations are involved in waste management?',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.original_query', 'Which organisations are involved in waste management?');

        $data = $response->json('data');

        $this->assertArrayHasKey('results', $data);
        $this->assertGreaterThan(0, $data['result_count']);

        $names = array_column($data['results'], 'organisation_name');
        $this->assertContains('Pikitup', $names);

        $top = $data['results'][0];
        $this->assertSame(1, $top['rank']);
        $this->assertNotEmpty($top['reasons']);
        $this->assertNotEmpty($top['matching_terms']);
        $this->assertNotEmpty($top['sources']);
        $this->assertContains($top['verification_status'], ['verified', 'pending', 'unverified']);
    }

    public function test_waste_service_grouping_ranks_return_objects_at_one(): void
    {
        $response = $this->postJson('/api/v1/intelligence/search', [
            'query' => 'waste management',
        ]);

        $response->assertOk();
        $ranks = array_column($response->json('data.results'), 'rank');
        $this->assertSame(range(1, count($ranks)), $ranks);
    }

    public function test_unknown_query_matches_no_information(): void
    {
        $response = $this->postJson('/api/v1/intelligence/search', [
            'query' => 'warehouse of interstellar unicorns',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.result_count', 0)
            ->assertJsonPath('data.message', 'No sufficiently relevant information was found in the repository for this query.');
    }

    public function test_missing_query_is_rejected(): void
    {
        $this->postJson('/api/v1/intelligence/search', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    public function test_whitespace_only_query_is_rejected(): void
    {
        $this->postJson('/api/v1/intelligence/search', ['query' => '    '])
            ->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    public function test_query_longer_than_max_length_is_rejected(): void
    {
        $this->postJson('/api/v1/intelligence/search', ['query' => str_repeat('a', 501)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    public function test_same_query_returns_the_same_ranking_twice(): void
    {
        $query = ['query' => 'water services'];

        $first = $this->postJson('/api/v1/intelligence/search', $query);
        $second = $this->postJson('/api/v1/intelligence/search', $query);

        $this->assertSame(
            $first->json('data'),
            $second->json('data'),
        );
    }
}