<?php

namespace App\Application\Repository;

use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Contracts\Repositories\OrganisationRepositoryContract;
use App\Contracts\Repositories\SourceTrustRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Source\SourceTrust;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;

final readonly class RepositorySeeder
{
    public function __construct(
        private RepositoryPaths $paths,
        private OrganisationRepositoryContract $organisations,
        private ArticleRepositoryContract $articles,
        private SourceTrustRepositoryContract $sources,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function seedFrom(string $sourcePath): array
    {
        if (! is_dir($sourcePath)) {
            throw new \InvalidArgumentException("Seed path does not exist: {$sourcePath}");
        }

        $this->paths->ensureVolume();

        $gateway = new RepositoryJsonGateway(rtrim($sourcePath, '\\/'));

        $counts = ['organisations' => 0, 'articles' => 0, 'sources' => 0];

        foreach ($gateway->load('organisations', 'organisations') as $row) {
            $this->organisations->save(CoJOrganisation::fromArray($row));
            $counts['organisations']++;
        }

        foreach ($gateway->load('articles', 'articles') as $row) {
            $this->articles->save(Article::fromArray($row));
            $counts['articles']++;
        }

        foreach ($gateway->load('sources', 'source_trust') as $row) {
            $this->sources->save(SourceTrust::fromArray($row));
            $counts['sources']++;
        }

        return $counts;
    }
}