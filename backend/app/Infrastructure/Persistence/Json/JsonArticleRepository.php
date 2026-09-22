<?php

namespace App\Infrastructure\Persistence\Json;

use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;

final readonly class JsonArticleRepository implements ArticleRepositoryContract
{
    private const ENTITY = 'articles';

    private const KEY = 'articles';

    public function __construct(
        private RepositoryJsonGateway $gateway,
    ) {
    }

    public function find(string $id): ?Article
    {
        foreach ($this->all() as $article) {
            if ($article->id === $id) {
                return $article;
            }
        }

        return null;
    }

    public function all(): array
    {
        return array_map(
            fn (array $row): Article => Article::fromArray($row),
            $this->gateway->load(self::ENTITY, self::KEY),
        );
    }

    public function save(Article $article): void
    {
        $rows = [];

        foreach ($this->existing() as $existing) {
            if ($existing->id !== $article->id) {
                $rows[] = $existing->toArray();
            }
        }

        $rows[] = $article->toArray();

        $this->gateway->save(self::ENTITY, self::KEY, $rows);
    }

    public function delete(string $id): bool
    {
        $kept = [];
        $deleted = false;

        foreach ($this->existing() as $existing) {
            if ($existing->id === $id) {
                $deleted = true;
                continue;
            }
            $kept[] = $existing->toArray();
        }

        if ($deleted) {
            $this->gateway->save(self::ENTITY, self::KEY, $kept);
        }

        return $deleted;
    }

    public function forOrganisation(string $organisationId): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (Article $article): bool => in_array($organisationId, $article->organisationIds, true),
        ));
    }

    public function count(): int
    {
        return count($this->all());
    }

    /** @return Article[] */
    private function existing(): array
    {
        try {
            return $this->all();
        } catch (RepositoryFileNotFoundException) {
            return [];
        }
    }
}