<?php

namespace Tests\Unit\Health;

use App\Application\Health\RepositoryHealthService;
use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Repository\RepositoryHealth;
use App\Domain\Source\SourceTrust;
use App\Infrastructure\Persistence\Json\JsonArticleRepository;
use App\Infrastructure\Persistence\Json\JsonOrganisationRepository;
use App\Infrastructure\Persistence\Json\JsonSourceTrustRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use Tests\TestCase;

class RepositoryHealthServiceTest extends TestCase
{
    public function test_it_aggregates_fixture_health_as_degraded(): void
    {
        $service = $this->serviceFor(__DIR__.'/../../Fixtures/data');

        $report = $service->evaluate();

        $this->assertSame(RepositoryHealth::STATUS_DEGRADED, $report->status);
        $this->assertFalse($report->isHealthy());
        $this->assertSame(3, $report->organisationCount);
        $this->assertSame(3, $report->articleCount);
        $this->assertSame(3, $report->sourceTrustCount);
        $this->assertSame(1, $report->unverifiedCount);
        $this->assertSame(1, $report->rejectedCount);
        $this->assertSame(1, $report->archivedCount);
        $this->assertSame(0, $report->expiredCount);
        $this->assertSame([], $report->issues);
        $this->assertSame(['verified' => 1, 'unverified' => 1, 'rejected' => 1], $report->byStatus);
    }

    public function test_it_reports_healthy_for_a_fresh_verified_volume(): void
    {
        $volume = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cojmip-health-'.uniqid('', true);
        $gateway = new RepositoryJsonGateway($volume);

        $gateway->save('organisations', 'organisations', [
            CoJOrganisation::fromArray([
                'id' => 'coj-org-h1',
                'name' => 'Healthy Entity',
                'organisation_type' => 'entity',
            ])->toArray(),
        ]);

        $gateway->save('articles', 'articles', [
            Article::fromArray([
                'id' => 'coj-art-h1',
                'source_name' => 'healthy.example',
                'source_url' => 'https://healthy.example/one',
                'title' => 'Healthy Article',
                'status' => ArticleStatus::Verified->value,
                'retrieved_at' => 'now',
            ])->toArray(),
        ]);

        $gateway->save('sources', 'source_trust', [
            (new SourceTrust('healthy.example', \App\Domain\Source\SourceTier::Tier1))->toArray(),
        ]);

        $report = $this->serviceFor($volume)->evaluate();

        $this->assertSame(RepositoryHealth::STATUS_HEALTHY, $report->status);
        $this->assertTrue($report->isHealthy());
        $this->assertSame(1, $report->freshCount);

        $this->deleteDirectory($volume);
    }

    public function test_it_reports_error_when_a_data_file_is_missing(): void
    {
        $volume = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cojmip-health-'.uniqid('', true);
        (new RepositoryJsonGateway($volume))->save('organisations', 'organisations', []);

        $report = $this->serviceFor($volume)->evaluate();

        $this->assertSame(RepositoryHealth::STATUS_ERROR, $report->status);
        $this->assertFalse($report->isHealthy());
        $this->assertNotEmpty($report->issues);

        $this->deleteDirectory($volume);
    }

    private function serviceFor(string $volume): RepositoryHealthService
    {
        $gateway = new RepositoryJsonGateway($volume);

        return new RepositoryHealthService(
            new JsonOrganisationRepository($gateway),
            new JsonArticleRepository($gateway),
            new JsonSourceTrustRepository($gateway),
        );
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