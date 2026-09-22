<?php

namespace Tests\Unit\Repositories;

use App\Domain\Article\Article;
use App\Domain\Shared\Exceptions\InvalidRepositoryJsonException;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;
use App\Infrastructure\Persistence\Json\JsonArticleRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use PHPUnit\Framework\TestCase;

class JsonArticleRepositoryTest extends TestCase
{
    use CreatesTempVolumes;

    public function test_it_reads_articles_from_a_json_volume(): void
    {
        $repository = new JsonArticleRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $this->assertSame(3, $repository->count());
        $this->assertSame('Fixture Article One', $repository->find('coj-art-fix-0001')?->title);
        $this->assertNull($repository->find('does-not-exist'));
    }

    public function test_it_filters_articles_for_an_organisation(): void
    {
        $repository = new JsonArticleRepository(new RepositoryJsonGateway($this->fixtureVolume()));

        $articles = $repository->forOrganisation('coj-org-fix-0001');

        $this->assertCount(2, $articles);
        $this->assertCount(1, $repository->forOrganisation('coj-org-fix-0002'));
        $this->assertCount(1, $repository->forOrganisation('coj-org-fix-0003'));
        $this->assertSame([], $repository->forOrganisation('coj-org-fix-9999'));
    }

    public function test_it_throws_when_the_articles_file_is_missing(): void
    {
        $repository = new JsonArticleRepository(new RepositoryJsonGateway($this->tempVolume()));

        $this->expectException(RepositoryFileNotFoundException::class);
        $repository->all();
    }

    public function test_it_throws_when_the_articles_file_has_an_invalid_structure(): void
    {
        $volume = $this->tempVolume();
        mkdir($volume, 0755, true);
        file_put_contents($volume.'/articles.json', json_encode(['wrong_key' => []]));

        $repository = new JsonArticleRepository(new RepositoryJsonGateway($volume));

        $this->expectException(InvalidRepositoryJsonException::class);
        $repository->all();
    }

    public function test_save_creates_and_delete_removes(): void
    {
        $volume = $this->tempVolume();
        $repository = new JsonArticleRepository(new RepositoryJsonGateway($volume));

        $article = Article::fromArray([
            'id' => 'coj-art-tmp-1',
            'source_name' => 'example.source',
            'source_url' => 'https://example.source/one',
            'title' => 'Temp Article',
        ]);

        $repository->save($article);
        $this->assertSame(1, $repository->count());
        $this->assertTrue($repository->delete('coj-art-tmp-1'));
        $this->assertSame(0, $repository->count());

        $this->deleteDirectory($volume);
    }
}