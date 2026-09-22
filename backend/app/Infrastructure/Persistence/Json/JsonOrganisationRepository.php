<?php

namespace App\Infrastructure\Persistence\Json;

use App\Contracts\Repositories\OrganisationRepositoryContract;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;

final readonly class JsonOrganisationRepository implements OrganisationRepositoryContract
{
    private const ENTITY = 'organisations';

    private const KEY = 'organisations';

    public function __construct(
        private RepositoryJsonGateway $gateway,
    ) {
    }

    public function find(string $id): ?CoJOrganisation
    {
        foreach ($this->all() as $organisation) {
            if ($organisation->id === $id) {
                return $organisation;
            }
        }

        return null;
    }

    public function findByNormalisedName(string $name): ?CoJOrganisation
    {
        $needle = mb_strtolower(trim($name));

        foreach ($this->all() as $organisation) {
            if (mb_strtolower($organisation->name) === $needle) {
                return $organisation;
            }
            foreach ($organisation->aliases as $alias) {
                if (mb_strtolower(trim($alias)) === $needle) {
                    return $organisation;
                }
            }
        }

        return null;
    }

    public function all(): array
    {
        return array_map(
            fn (array $row): CoJOrganisation => CoJOrganisation::fromArray($row),
            $this->gateway->load(self::ENTITY, self::KEY),
        );
    }

    public function save(CoJOrganisation $organisation): void
    {
        $rows = [];

        foreach ($this->existing() as $existing) {
            if ($existing->id !== $organisation->id) {
                $rows[] = $existing->toArray();
            }
        }

        $rows[] = $organisation->toArray();

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

    /** @return CoJOrganisation[] */
    private function existing(): array
    {
        try {
            return $this->all();
        } catch (RepositoryFileNotFoundException) {
            return [];
        }
    }

    public function count(): int
    {
        return count($this->all());
    }
}