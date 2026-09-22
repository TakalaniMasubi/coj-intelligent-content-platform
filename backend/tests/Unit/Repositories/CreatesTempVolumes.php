<?php

namespace Tests\Unit\Repositories;

trait CreatesTempVolumes
{
    private function tempVolume(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'cojmip-tests-'.uniqid('', true);
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }

    private function fixtureVolume(): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'data';
    }
}