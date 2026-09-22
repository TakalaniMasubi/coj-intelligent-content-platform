<?php

namespace App\Application\Repository;

final class RepositoryPaths
{
    public static function basePath(): string
    {
        $configured = config('cojmip.repository.volume');

        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '\\/');
        }

        return storage_path('app/data');
    }

    public function volume(): string
    {
        return self::basePath();
    }

    public function path(string $entity): string
    {
        return self::basePath().DIRECTORY_SEPARATOR.$entity.'.json';
    }

    public function ensureVolume(): void
    {
        $dir = self::basePath();

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}