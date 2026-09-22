<?php

namespace App\Contracts\Repositories;

use App\Domain\Source\SourceTier;
use App\Domain\Source\SourceTrust;

interface SourceTrustRepositoryContract
{
    /** @return SourceTrust[] */
    public function all(): array;

    public function tierFor(string $sourceName): ?SourceTier;

    public function contains(string $sourceName): bool;

    public function save(SourceTrust $sourceTrust): void;

    public function delete(string $sourceName): bool;

    public function count(): int;
}