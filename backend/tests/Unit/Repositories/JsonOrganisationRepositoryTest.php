<?php

namespace Tests\Unit\Repositories;

use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationType;
use App\Domain\Shared\Exceptions\InvalidRepositoryJsonException;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;
use App\Infrastructure\Persistence\Json\JsonOrganisationRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use PHPUnit\Framework\TestCase;

class JsonOrganisationRepositoryTest extends TestCase
{
    use CreatesTempVolumes;

    public function test_it_reads_organisations_from_a_json_volume(): void
    {
        $repository = new JsonOrganisationRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $all = $repository->all();
        $this->assertCount(3, $all);
        $this->assertSame('Fixture Example Entity', $all[0]->name);
        $this->assertSame(OrganisationType::Entity, $all[0]->organisationType);
    }

    public function test_it_finds_organisations_by_id(): void
    {
        $repository = new JsonOrganisationRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $this->assertSame('Fixture Example Directorate', $repository->find('coj-org-fix-0002')?->name);
        $this->assertNull($repository->find('does-not-exist'));
    }

    public function test_it_finds_organisations_by_normalised_name_and_aliases(): void
    {
        $repository = new JsonOrganisationRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $this->assertSame('coj-org-fix-0001', $repository->findByNormalisedName('fixture example ENTITY')?->id);
        $this->assertSame('coj-org-fix-0003', $repository->findByNormalisedName('Fixture Office')?->id);
        $this->assertNull($repository->findByNormalisedName('Not A Real Entity'));
    }

    public function test_it_throws_when_the_organisations_file_is_missing(): void
    {
        $repository = new JsonOrganisationRepository(new RepositoryJsonGateway($this->tempVolume()));

        $this->expectException(RepositoryFileNotFoundException::class);
        $repository->all();
    }

    public function test_it_throws_when_the_organisations_file_is_invalid_json(): void
    {
        $volume = $this->tempVolume();
        mkdir($volume, 0755, true);
        file_put_contents($volume.'/organisations.json', '{"organisations": [');

        $repository = new JsonOrganisationRepository(new RepositoryJsonGateway($volume));

        $this->expectException(InvalidRepositoryJsonException::class);
        $repository->all();
    }

    public function test_save_creates_and_updates_and_delete_removes(): void
    {
        $volume = $this->tempVolume();
        $repository = new JsonOrganisationRepository(new RepositoryJsonGateway($volume));

        $organisation = CoJOrganisation::fromArray([
            'id' => 'coj-org-tmp-1',
            'name' => 'Temp Entity',
            'organisation_type' => 'entity',
        ]);

        $repository->save($organisation);
        $repository->save($organisation);
        $this->assertSame(1, $repository->count());

        $repository->save(CoJOrganisation::fromArray([
            'id' => 'coj-org-tmp-2',
            'name' => 'Temp Directorate',
            'organisation_type' => 'directorate',
        ]));
        $this->assertSame(2, $repository->count());

        $this->assertTrue($repository->delete('coj-org-tmp-1'));
        $this->assertSame(1, $repository->count());
        $this->assertFalse($repository->delete('coj-org-tmp-1'));

        $this->deleteDirectory($volume);
    }
}