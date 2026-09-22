<?php

namespace Tests\Unit\Intelligence;

use App\Application\Intelligence\IntelligenceSearchService;
use App\Application\Intelligence\QueryNormalizer;
use App\Application\Intelligence\RelevanceScorer;
use App\Application\Intelligence\SearchExplainer;
use App\Application\Intelligence\SearchRanker;
use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;
use App\Domain\Intelligence\SearchResponse;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationType;
use App\Infrastructure\Persistence\Json\JsonArticleRepository;
use App\Infrastructure\Persistence\Json\JsonOrganisationRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use PHPUnit\Framework\TestCase;

class IntelligenceSearchServiceTest extends TestCase
{
    private string $volume;

    private IntelligenceSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->volume = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cojmip-search-'.uniqid('', true);
        $gateway = new RepositoryJsonGateway($this->volume);

        $organisations = new JsonOrganisationRepository($gateway);

        $organisations->save($this->organisation('waste-services', 'Waste Services', 'verified', 'waste collection and recycling services'));
        $organisations->save($this->organisation('water-works', 'Water Works', 'pending', 'water supply and sanitation services'));
        $organisations->save($this->organisation('roads-agency', 'Roads Agency', 'unverified', 'road construction and maintenance'));
        $organisations->save($this->organisation('recycling-partner', 'Recycling Partner', 'pending', 'waste processing and recycling'));

        (new JsonArticleRepository($gateway))->save(Article::fromArray([
            'id' => 'art-waste',
            'source_name' => 'waste-services.example',
            'source_url' => 'https://waste-services.example/waste-overview',
            'title' => 'Waste management overview',
            'summary' => 'waste collection and recycling across the city',
            'subject' => 'waste management',
            'status' => ArticleStatus::Verified->value,
            'organisation_ids' => ['waste-services'],
        ]));

        $this->service = new IntelligenceSearchService(
            $organisations,
            new JsonArticleRepository($gateway),
            new QueryNormalizer(),
            new RelevanceScorer(),
            new SearchRanker(),
            new SearchExplainer(),
        );
    }

    protected function tearDown(): void
    {
        if (is_dir($this->volume)) {
            $this->deleteDirectory($this->volume);
        }
        parent::tearDown();
    }

    public function test_it_finds_and_ranks_organisations_for_a_short_query(): void
    {
        $response = $this->service->search('waste');

        $this->assertSame('waste', $response->normalisedQuery);
        $this->assertSame(['waste'], $response->queryTerms);
        $this->assertSame(2, $response->count());

        $top = $response->results[0];
        $this->assertSame('waste-services', $top->organisationId);
        $this->assertSame(1, $top->rank);
        $this->assertContains('waste', $top->matchingTerms);
        $this->assertNotEmpty($top->reasons);
        $this->assertContains('https://waste-services.example/waste-overview', $top->sources);
    }

    public function test_it_finds_a_pending_organisation_and_flags_it(): void
    {
        $response = $this->service->search('water');

        $this->assertSame(1, $response->count());
        $this->assertSame('water-works', $response->results[0]->organisationId);
        $this->assertSame('pending', $response->results[0]->verificationStatus->value);
        $this->assertStringContainsString('PENDING', implode(' ', $response->results[0]->reasons));
    }

    public function test_full_question_with_stop_words_is_normalised(): void
    {
        $question = $this->service->search('Which organisations are involved in Waste Management?');
        $short = $this->service->search('waste management');

        $this->assertSame(['waste', 'management'], $question->queryTerms);
        $this->assertSame(
            array_map(static fn ($r) => $r->toArray(), $question->results),
            array_map(static fn ($r) => $r->toArray(), $short->results),
        );
    }

    public function test_queries_are_case_insensitive(): void
    {
        $upper = $this->service->search('THE ROADS AGENCY');
        $lower = $this->service->search('the roads agency');

        $this->assertSame(1, $upper->count());
        $this->assertSame('roads-agency', $upper->results[0]->organisationId);
        $this->assertSame($upper->results[0]->score, $lower->results[0]->score);
    }

    public function test_punctuation_and_duplicate_terms_do_not_affect_results(): void
    {
        $clean = $this->service->search('water');
        $noisy = $this->service->search('water! water?? water...');

        // original_query is echoed back (intentionally), but the normalised
        // terms and the resulting ranking must be identical.
        $this->assertSame($clean->queryTerms, $noisy->queryTerms);
        $this->assertSame(['water'], $noisy->queryTerms);
        $this->assertSame(self::payloads($clean), self::payloads($noisy));
    }

    public function test_identifier_matches_outrank_description_only_matches(): void
    {
        // 'recycling-partner' matches on name + description; 'waste-services'
        // matches 'recycling' only through its description.
        $response = $this->service->search('recycling');

        $this->assertSame('recycling-partner', $response->results[0]->organisationId);
        $this->assertSame('waste-services', $response->results[1]->organisationId);
    }

    public function test_it_returns_no_matches_with_a_clear_message(): void
    {
        $response = $this->service->search('quantum cloud farming');

        $this->assertSame(0, $response->count());
        $this->assertSame('No sufficiently relevant information was found in the repository for this query.', $response->message);
    }

    public function test_empty_query_yields_no_terms_and_a_message(): void
    {
        $response = $this->service->search('');

        $this->assertSame(0, $response->count());
        $this->assertSame('No searchable terms could be extracted from the query.', $response->message);
    }

    public function test_results_are_deterministic_across_repeated_searches(): void
    {
        $first = $this->service->search('waste management');
        $second = $this->service->search('waste management');

        $this->assertSame($first->toArray(), $second->toArray());
    }

    public function test_each_result_includes_the_full_contract_fields(): void
    {
        $response = $this->service->search('waste');
        $result = $response->results[0]->toArray();

        foreach (['organisation_id', 'organisation_name', 'organisation_type', 'score', 'rank', 'matching_terms', 'reasons', 'verification_status', 'sources'] as $key) {
            $this->assertArrayHasKey($key, $result, "Missing contract key: {$key}");
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function payloads(SearchResponse $response): array
    {
        return array_map(static fn ($result) => $result->toArray(), $response->results);
    }

    private function organisation(string $id, string $name, string $verification, string $description): CoJOrganisation
    {
        return CoJOrganisation::fromArray([
            'id' => $id,
            'name' => $name,
            'organisation_type' => OrganisationType::Entity->value,
            'description' => $description,
            'verification_status' => $verification,
            'official_status' => 'active',
        ]);
    }

    private function deleteDirectory(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}