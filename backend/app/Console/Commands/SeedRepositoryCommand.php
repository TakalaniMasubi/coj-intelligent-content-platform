<?php

namespace App\Console\Commands;

use App\Application\Repository\RepositorySeeder;
use Illuminate\Console\Command;

class SeedRepositoryCommand extends Command
{
    protected $signature = 'cojmip:repository-seed
        {--path= : Directory of seed JSON files (defaults to database/seed-data)}';

    protected $description = 'Seed the CoJ-MIP repository from JSON source files';

    public function handle(RepositorySeeder $seeder): int
    {
        $sourcePath = $this->option('path') ?: database_path('seed-data');

        if (! is_dir($sourcePath)) {
            $this->error("Seed path does not exist: {$sourcePath}");

            return self::FAILURE;
        }

        $this->comment("Seeding repository from: {$sourcePath}");

        $counts = $seeder->seedFrom($sourcePath);

        $this->table(
            ['Entity', 'Seeded'],
            [
                ['Organisations', $counts['organisations']],
                ['Articles', $counts['articles']],
                ['Source trust', $counts['sources']],
            ],
        );

        $this->info('Repository seeded successfully.');

        if (str_contains($sourcePath, database_path('seed-data'))) {
            $this->comment('NOTE: database/seed-data currently contains placeholder fixtures, to be replaced by the curated CoJ dataset in Phase 3.');
        }

        return self::SUCCESS;
    }
}