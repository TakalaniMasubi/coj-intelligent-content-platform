<?php

namespace Tests\Feature\Console;

use App\Infrastructure\Persistence\Json\JsonOrganisationRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use Tests\TestCase;
use Tests\Unit\Repositories\CreatesTempVolumes;

class RepositoryConsoleCommandTest extends TestCase
{
    use CreatesTempVolumes;

    public function test_seed_command_seeds_the_repository_from_seed_data(): void
    {
        $volume = $this->tempVolume();
        config(['cojmip.repository.volume' => $volume]);

        $this->artisan('cojmip:repository-seed', ['--path' => database_path('seed-data')])
            ->expectsOutputToContain('Repository seeded successfully.')
            ->assertExitCode(0);

        $this->assertFileExists($volume.'/organisations.json');
        $this->assertFileExists($volume.'/articles.json');
        $this->assertFileExists($volume.'/sources.json');

        $repository = new JsonOrganisationRepository(new RepositoryJsonGateway($volume));
        $this->assertSame(3, $repository->count());

        $this->deleteDirectory($volume);
    }

    public function test_seed_command_fails_when_the_seed_path_is_missing(): void
    {
        $this->artisan('cojmip:repository-seed', ['--path' => storage_path('app/nope-seed')])
            ->assertExitCode(1);
    }

    public function test_health_command_reports_degraded_over_fixture_data(): void
    {
        config(['cojmip.repository.volume' => dirname(__DIR__, 2).'/Fixtures/data']);

        $this->artisan('cojmip:repository-health')
            ->expectsOutputToContain('degraded')
            ->expectsOutputToContain('Organisations')
            ->assertExitCode(1);
    }
}