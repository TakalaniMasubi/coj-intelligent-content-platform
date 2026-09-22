<?php

namespace App\Contracts\Repositories;

use App\Domain\Article\Article;

interface ArticleRepositoryContract
{
    public function find(string $id): ?Article;

    /** @return Article[] */
    public function all(): array;

    public function save(Article $article): void;

    public function delete(string $id): bool;

    /** @return Article[] */
    public function forOrganisation(string $organisationId): array;

    public function count(): int;
}