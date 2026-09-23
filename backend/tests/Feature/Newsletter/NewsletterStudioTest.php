<?php

namespace Tests\Feature\Newsletter;

use App\Contracts\Repositories\NewsletterHistoryRepositoryContract;
use App\Http\Controllers\NewsletterController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Repositories\CreatesTempVolumes;

class NewsletterStudioTest extends TestCase
{
    use CreatesTempVolumes;
    use RefreshDatabase;

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

    public function test_the_studio_route_renders_the_compose_form_when_empty(): void
    {
        $this->get('/newsletter')
            ->assertOk()
            ->assertSee('Newsletter Studio')
            ->assertSee('Compose a newsletter');
    }

    public function test_the_studio_route_shows_the_step_registry(): void
    {
        $this->get('/newsletter')
            ->assertOk()
            ->assertSee('Choose a topic')
            ->assertSee('Top-3 organisations')
            ->assertSee('Layout & PDF');
    }

    public function test_composing_a_topic_honestly_reuses_the_selection_pipeline(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'waste management'])
            ->assertRedirect('/newsletter');

        // The standard top-3 selection results appear in the studio — the
        // same organisations and ordering the selection API would return.
        $this->get('/newsletter')
            ->assertOk()
            ->assertSee('Pikitup')
            ->assertSee('Joburg Property Company')
            ->assertSee('Joburg Tourism Company')
            ->assertSee('Executive Brief')
            ->assertSee('News & Insights')
            ->assertSee('Municipal Spotlight')
            ->assertSee('Magazine');
    }

    public function test_an_insufficient_topic_is_surfaced_honestly(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'transport'])
            ->assertRedirect('/newsletter');

        $this->get('/newsletter')
            ->assertOk()
            ->assertSee('Insufficient results');
    }

    public function test_the_topic_field_is_required_and_bounded(): void
    {
        $this->post('/newsletter/compose', [])
            ->assertSessionHasErrors('topic');

        $this->post('/newsletter/compose', ['topic' => '   '])
            ->assertSessionHasErrors('topic');

        $this->post('/newsletter/compose', ['topic' => str_repeat('a', 121)])
            ->assertSessionHasErrors('topic');
    }

    public function test_switching_layout_never_recomposes_or_reselects(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'waste management']);

        $composition = $this->app['session']->get(NewsletterController::SESSION_COMPOSITION);
        $this->assertIsArray($composition);
        $this->assertSame('waste management', $composition['topic']);
        $this->assertSame('executive-brief', $composition['layout']);
        $organisationIds = array_column($composition['organisations'], 'organisation_id');

        $this->get('/newsletter/layout/news-insights')
            ->assertRedirect('/newsletter');

        $switched = $this->app['session']->get(NewsletterController::SESSION_COMPOSITION);
        $this->assertSame('news-insights', $switched['layout']);
        $this->assertSame('waste management', $switched['topic']);
        $this->assertSame($organisationIds, array_column($switched['organisations'], 'organisation_id'));

        // The studio still renders the same organisations under the new layout.
        $this->get('/newsletter')
            ->assertOk()
            ->assertSee('Pikitup')
            ->assertSee('News & Insights');
    }

    public function test_unknown_layouts_fall_back_to_the_configured_default(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'waste management']);

        $this->get('/newsletter/layout/not-a-layout')
            ->assertRedirect('/newsletter');

        $composition = $this->app['session']->get(NewsletterController::SESSION_COMPOSITION);
        $this->assertSame('executive-brief', $composition['layout']);
    }

    public function test_composing_a_draft_never_writes_newsletter_history(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'waste management']);

        $this->assertFileDoesNotExist($this->runtimeVolume.'/newsletters.json');
        $this->assertSame(0, app(NewsletterHistoryRepositoryContract::class)->count());
    }

    public function test_the_pdf_endpoint_requires_a_composition_first(): void
    {
        $this->get('/newsletter/pdf')
            ->assertRedirect('/newsletter');
    }

    public function test_each_layout_generates_a_validated_exactly_three_page_pdf(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'waste management']);

        foreach (['executive-brief', 'news-insights', 'municipal-spotlight', 'magazine'] as $layout) {
            $this->get('/newsletter/layout/'.$layout);
            $this->get('/newsletter/pdf')
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertHeader('X-Pdf-Pages', '3')
                ->assertHeader('X-Pdf-Valid', 'true');
        }
    }

    public function test_generating_a_pdf_writes_a_newsletter_history_record(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'waste management']);
        $this->get('/newsletter/pdf');

        $this->assertFileExists($this->runtimeVolume.'/newsletters.json');

        $payload = json_decode((string) file_get_contents($this->runtimeVolume.'/newsletters.json'), true);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('newsletter_generations', $payload);

        $rows = $payload['newsletter_generations'];
        $this->assertCount(1, $rows);

        $record = $rows[0];
        $this->assertSame('waste-management', $record['id']);
        $this->assertSame('waste management', $record['topic']);
        $this->assertSame('executive-brief', $record['layout']);
        $this->assertSame('success', $record['pdf_status']);
        $this->assertSame(3, $record['page_count']);
        $this->assertNotEmpty($record['organisations']);
        $this->assertSame(['pikitup', 'jpc', 'joburg-tourism'], array_column(
            $record['organisations'],
            'id',
        ));
    }

    public function test_downloading_the_pdf_twice_appends_two_history_records(): void
    {
        $this->post('/newsletter/compose', ['topic' => 'waste management']);
        $this->get('/newsletter/pdf');
        $this->get('/newsletter/pdf');

        $payload = json_decode((string) file_get_contents($this->runtimeVolume.'/newsletters.json'), true);

        $this->assertCount(2, $payload['newsletter_generations']);
    }
}
