<?php

namespace App\Contracts\Repositories;

use App\Domain\Organisation\CoJOrganisation;

interface OrganisationRepositoryContract
{
    public function find(string $id): ?CoJOrganisation;

    public function findByNormalisedName(string $name): ?CoJOrganisation;

    /** @return CoJOrganisation[] */
    public function all(): array;

    public function save(CoJOrganisation $organisation): void;

    public function delete(string $id): bool;

    public function count(): int;
}