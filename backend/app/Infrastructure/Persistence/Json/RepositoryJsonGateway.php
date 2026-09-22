<?php

namespace App\Infrastructure\Persistence\Json;

use App\Domain\Shared\Exceptions\InvalidRepositoryJsonException;
use App\Domain\Shared\Exceptions\RepositoryFileNotFoundException;

final readonly class RepositoryJsonGateway
{
    public function __construct(
        private string $volume,
    ) {
    }

    public function volume(): string
    {
        return $this->volume;
    }

    public function path(string $entity): string
    {
        return $this->volume.DIRECTORY_SEPARATOR.$entity.'.json';
    }

    public function exists(string $entity): bool
    {
        return is_file($this->path($entity));
    }

    /**
     * @return mixed[]
     */
    public function load(string $entity, string $dataKey): array
    {
        $path = $this->path($entity);

        if (! is_file($path)) {
            throw new RepositoryFileNotFoundException("Repository file not found: {$path}");
        }

        $raw = @file_get_contents($path);

        if ($raw === false) {
            throw new RepositoryFileNotFoundException("Unable to read repository file: {$path}");
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw new InvalidRepositoryJsonException(sprintf(
                '%s is not valid JSON: %s',
                $path,
                json_last_error_msg(),
            ));
        }

        if (! isset($decoded[$dataKey]) || ! is_array($decoded[$dataKey])) {
            throw new InvalidRepositoryJsonException(sprintf(
                '%s is missing the "%s" data key.',
                $path,
                $dataKey,
            ));
        }

        return $decoded[$dataKey];
    }

    public function save(string $entity, string $dataKey, array $rows): void
    {
        $path = $this->path($entity);
        $dir = dirname($path);

        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new InvalidRepositoryJsonException("Unable to create repository directory: {$dir}");
        }

        $payload = [$dataKey => array_values($rows)];

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new InvalidRepositoryJsonException('Unable to encode repository payload: '.json_last_error_msg());
        }

        if (@file_put_contents($path, $encoded, LOCK_EX) === false) {
            throw new InvalidRepositoryJsonException("Unable to write repository file: {$path}");
        }
    }
}