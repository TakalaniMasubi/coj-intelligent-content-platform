<?php

namespace Tests\Unit\Repositories;

use App\Domain\Source\SourceTier;
use App\Domain\Source\SourceTrust;
use App\Infrastructure\Persistence\Json\JsonSourceTrustRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use PHPUnit\Framework\TestCase;

class JsonSourceTrustRepositoryTest extends TestCase
{
    use CreatesTempVolumes;

    public function test_it_reads_source_trust_classifications(): void
    {
        $repository = new JsonSourceTrustRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $all = $repository->all();
        $this->assertCount(3, $all);
        $this->assertSame('coj.example.gov.za', $all[0]->sourceName);
        $this->assertSame(SourceTier::Tier1, $all[0]->tier);
    }

    public function test_tier_lookup_is_case_insensitive_and_normalised(): void
    {
        $repository = new JsonSourceTrustRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $this->assertSame(SourceTier::Tier1, $repository->tierFor('COJ.EXAMPLE.GOV.ZA'));
        $this->assertSame(SourceTier::Tier2, $repository->tierFor('joburg-municipal-news.example'));
        $this->assertSame(SourceTier::Tier3, $repository->tierFor('citizen-blog.example'));
        $this->assertNull($repository->tierFor('unclassified.source'));
    }

    public function test_contains_returns_whether_a_source_is_classified(): void
    {
        $repository = new JsonSourceTrustRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $this->assertTrue($repository->contains('coj.example.gov.za'));
        $this->assertFalse($repository->contains('not-a-known-source'));
    }

    public function test_save_upserts_by_source_name(): void
    {
        $volume = $this->tempVolume();
        $repository = new JsonSourceTrustRepository(new RepositoryJsonGateway($volume));

        $repository->save(new SourceTrust('example.source', SourceTier::Tier1));
        $repository->save(new SourceTrust('EXAMPLE.SOURCE', SourceTier::Tier2));

        $this->assertSame(1, $repository->count());
        $this->assertSame(SourceTier::Tier2, $repository->tierFor('example.source'));
        $this->assertTrue($repository->delete('example.source'));
        $this->assertSame(0, $repository->count());

        $this->deleteDirectory($volume);
    }
}