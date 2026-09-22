<?php

namespace App\Infrastructure\Persistence\Json;

use App\Contracts\Repositories\SourceTrustRepositoryContract;
use App\Domain\Source\SourceTier;
use App\Domain\Source\SourceTrust;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;

final readonly class JsonSourceTrustRepository implements SourceTrustRepositoryContract
{
    private const ENTITY = 'sources';

    private const KEY = 'source_trust';

    public function __construct(
        private RepositoryJsonGateway $gateway,
    ) {
    }

    public function all(): array
    {
        return array_map(
            fn (array $row): SourceTrust => SourceTrust::fromArray($row),
            $this->gateway->load(self::ENTITY, self::KEY),
        );
    }

    public function tierFor(string $sourceName): ?SourceTier
    {
        $needle = mb_strtolower(trim($sourceName));

        foreach ($this->all() as $trust) {
            if (mb_strtolower(trim($trust->sourceName)) === $needle) {
                return $trust->tier;
            }
        }

        return null;
    }

    public function contains(string $sourceName): bool
    {
        return $this->tierFor($sourceName) !== null;
    }

    public function save(SourceTrust $sourceTrust): void
    {
        $rows = [];

        foreach ($this->existing() as $existing) {
            if (mb_strtolower(trim($existing->sourceName)) !== mb_strtolower(trim($sourceTrust->sourceName))) {
                $rows[] = $existing->toArray();
            }
        }

        $rows[] = $sourceTrust->toArray();

        $this->gateway->save(self::ENTITY, self::KEY, $rows);
    }

    public function delete(string $sourceName): bool
    {
        $kept = [];
        $deleted = false;

        foreach ($this->existing() as $existing) {
            if (mb_strtolower(trim($existing->sourceName)) === mb_strtolower(trim($sourceName))) {
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

    public function count(): int
    {
        return count($this->all());
    }

    /** @return SourceTrust[] */
    private function existing(): array
    {
        try {
            return $this->all();
        } catch (RepositoryFileNotFoundException) {
            return [];
        }
    }
}