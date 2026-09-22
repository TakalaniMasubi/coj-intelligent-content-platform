<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\RepositoryHealthContract;
use Illuminate\Console\Command;

class RepositoryHealthCommand extends Command
{
    protected $signature = 'cojmip:repository-health';

    protected $description = 'Show the aggregated health of the CoJ-MIP repository';

    public function handle(RepositoryHealthContract $health): int
    {
        $report = $health->evaluate();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Status', $report->status],
                ['Organisations', $report->organisationCount],
                ['Articles', $report->articleCount],
                ['Source trust entries', $report->sourceTrustCount],
                ['By status', implode(' / ', array_map(
                    fn ($key, $count) => "{$key}: {$count}",
                    array_keys($report->byStatus),
                    array_values($report->byStatus),
                ))],
                ['Fresh', $report->freshCount],
                ['Stale', $report->staleCount],
                ['Expired', $report->expiredCount],
                ['Archived', $report->archivedCount],
                ['Unverified', $report->unverifiedCount],
                ['Newest retrieved', $report->newestRetrievedAt?->format(DATE_ATOM) ?? 'n/a'],
                ['Oldest retrieved', $report->oldestRetrievedAt?->format(DATE_ATOM) ?? 'n/a'],
                ['Issues', $report->issues === [] ? 'none' : implode(' | ', $report->issues)],
            ],
        );

        if ($report->isHealthy()) {
            $this->info('Repository is healthy.');

            return self::SUCCESS;
        }

        $this->warn('Repository is not healthy. See status and issues above.');

        return self::FAILURE;
    }
}