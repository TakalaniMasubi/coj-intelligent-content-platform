<?php

namespace Tests\Feature\Dashboard;

use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Shared\VerificationStatus;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Repositories\CreatesTempVolumes;

class DashboardTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTempVolumes;

    private string $runtimeVolume;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_the_dashboard_route_returns_ok(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('COJ MUNICIPAL INTELLIGENCE PLATFORM')
            ->assertSee('Intelligence Search')
            ->assertSee('Selection History');
    }

    public function test_the_root_path_redirects_to_the_dashboard(): void
    {
        $this->get('/')
            ->assertRedirect('/dashboard');
    }

    public function test_dashboard_loads_repository_metrics_dynamically(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Total Organisations')
            ->assertSee('Municipal Entities')
            ->assertSee('Directorates')
            ->assertSee('Verified Organisations')
            ->assertSee('Pending Organisations')
            ->assertSee('Total Articles')
            ->assertSee('Trusted Sources');
    }

    public function test_organisation_counts_match_the_repository_dataset(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('31', false)
            ->assertSee('13', false)
            ->assertSee('17', false)
            ->assertSee('Municipal Entities')
            ->assertSee('Directorates')
            ->assertSee('Office');
    }

    public function test_verification_counts_are_calculated_from_repository_data(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Verified')
            ->assertSee('Pending')
            ->assertSee('Unverified')
            ->assertSee('Information has supporting verification evidence.')
            ->assertSee('exists in the curated repository but requires live verification.')
            ->assertSee('has not yet been sufficiently verified.');
    }

    public function test_repository_health_is_displayed(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Repository Health')
            ->assertSee('Degraded')
            ->assertSee('Organisations')
            ->assertSee('Articles')
            ->assertSee('Sources');
    }

    public function test_pending_information_is_visible_and_preserved(): void
    {
        $response = $this->get('/dashboard');

        $response->assertOk()
            ->assertSee('Repository verification status')
            ->assertSee('pending verification');

        // The top-three preview surfaces a strongly relevant PENDING
        // organisation (Pikitup) with its status shown, never converted.
        $response->assertSee('Pikitup')
            ->assertSee('PENDING');
    }

    public function test_empty_selection_history_is_handled(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('No selection decisions have been recorded yet.');
    }

    public function test_dashboard_does_not_write_selection_history_on_render(): void
    {
        // Rendering the dashboard (including its selection preview) must not
        // persist anything to the selection history.
        $this->get('/dashboard')->assertOk();
        $this->get('/dashboard')->assertOk();

        $this->assertFileDoesNotExist($this->runtimeVolume.'/selections.json');
    }

    public function test_recent_selection_history_is_displayed(): void
    {
        $this->seedHistory();

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Recent Selection Decisions')
            ->assertSee('waste management')
            ->assertSee('Pikitup')
            ->assertSee('Joburg Property Company')
            ->assertSee('2 successful decisions recorded');
    }

    public function test_recent_articles_are_displayed(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Latest Repository Content')
            ->assertSee('JOSHCO: social and affordable housing in Johannesburg')
            ->assertSee('The Joburg Property Company: managing the city property estate')
            ->assertSee('Retrieved 22 Sep 2026');
    }

    public function test_selection_history_page_shows_empty_state(): void
    {
        $this->get('/selection-history')
            ->assertOk()
            ->assertSee('Selection History')
            ->assertSee('No selection decisions have been recorded yet.');
    }

    public function test_selection_history_page_shows_recorded_decisions(): void
    {
        $this->seedHistory();

        $this->get('/selection-history')
            ->assertOk()
            ->assertSee('waste management')
            ->assertSee('Pikitup')
            ->assertSee('PENDING')
            ->assertSee('VERIFIED')
            ->assertSee('2 decisions recorded');
    }

    public function test_dashboard_does_not_require_an_ai_api_key(): void
    {
        // The deterministic intelligence stack needs no provider key.
        $this->assertSame('local', config('cojmip.ai.provider'));
        $this->assertEmpty(config('cojmip.ai.openai.api_key'));

        $this->get('/dashboard')->assertOk();
    }

    private function seedHistory(): void
    {
        $asOf = new DateTimeImmutable('2026-09-23 10:00:00');

        $decisions = [
            new SelectionDecision(
                organisationId: 'pikitup',
                organisationName: 'Pikitup',
                rank: 1,
                originalScore: 43,
                adjustedScore: 43,
                verificationStatus: VerificationStatus::Pending,
                matchingTerms: ['waste'],
                reasons: ['Matched \'waste\' in the organisation description', 'Selected as top 1 for this request'],
                repetitionPenalty: 0,
                selected: true,
                selectedAt: $asOf,
                query: 'waste management',
            ),
            new SelectionDecision(
                organisationId: 'jpc',
                organisationName: 'Joburg Property Company',
                rank: 2,
                originalScore: 34,
                adjustedScore: 34,
                verificationStatus: VerificationStatus::Verified,
                matchingTerms: ['management'],
                reasons: ['Selected as top 2 for this request'],
                repetitionPenalty: 0,
                selected: true,
                selectedAt: $asOf,
                query: 'waste management',
            ),
        ];

        $payload = ['selection_decisions' => array_map(
            static fn (SelectionDecision $decision): array => $decision->toArray(),
            $decisions,
        )];

        file_put_contents(
            $this->runtimeVolume.DIRECTORY_SEPARATOR.'selections.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }
}