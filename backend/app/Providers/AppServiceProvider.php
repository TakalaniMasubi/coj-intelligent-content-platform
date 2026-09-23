<?php

namespace App\Providers;

use App\Application\Health\RepositoryHealthService;
use App\Application\Intelligence\IntelligenceSearchService;
use App\Application\Intelligence\IntelligenceSelectionService;
use App\Application\Intelligence\OrganisationSelector;
use App\Application\Intelligence\RelevanceScorer;
use App\Application\Newsletter\NewsletterArticleMatcher;
use App\Application\Newsletter\NewsletterAssetService;
use App\Application\Newsletter\NewsletterComposer;
use App\Application\Newsletter\NewsletterLayouts;
use App\Application\Newsletter\NewsletterPdfService;
use App\Application\Repository\RepositoryPaths;
use App\Contracts\Intelligence\IntelligenceSearchContract;
use App\Contracts\Intelligence\SelectionContract;
use App\Contracts\Repositories\ArticleRepositoryContract;
use App\Contracts\Repositories\NewsletterHistoryRepositoryContract;
use App\Contracts\Repositories\OrganisationRepositoryContract;
use App\Contracts\Repositories\RepositoryHealthContract;
use App\Contracts\Repositories\SelectionDecisionRepositoryContract;
use App\Contracts\Repositories\SourceTrustRepositoryContract;
use App\Infrastructure\Persistence\Json\JsonArticleRepository;
use App\Infrastructure\Persistence\Json\JsonNewsletterHistoryRepository;
use App\Infrastructure\Persistence\Json\JsonOrganisationRepository;
use App\Infrastructure\Persistence\Json\JsonSelectionDecisionRepository;
use App\Infrastructure\Persistence\Json\JsonSourceTrustRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerCoJMipBindings();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * CoJ-MIP service bindings (JSON-first repository contracts; Eloquent
     * drivers swap in later behind the same contracts without service changes).
     */
    private function registerCoJMipBindings(): void
    {
        $this->app->singleton(RepositoryPaths::class, fn () => new RepositoryPaths);

        $this->app->bind(
            RepositoryJsonGateway::class,
            fn () => new RepositoryJsonGateway(RepositoryPaths::basePath()),
        );

        $this->app->bind(OrganisationRepositoryContract::class, JsonOrganisationRepository::class);
        $this->app->bind(ArticleRepositoryContract::class, JsonArticleRepository::class);
        $this->app->bind(SourceTrustRepositoryContract::class, JsonSourceTrustRepository::class);
        $this->app->bind(RepositoryHealthContract::class, RepositoryHealthService::class);
        $this->app->bind(IntelligenceSearchContract::class, IntelligenceSearchService::class);
        $this->app->bind(SelectionDecisionRepositoryContract::class, JsonSelectionDecisionRepository::class);
        $this->app->bind(
            OrganisationSelector::class,
            fn () => new OrganisationSelector(
                app(SelectionDecisionRepositoryContract::class),
                (int) config('cojmip.selection.required', 3),
                (int) config('cojmip.selection.min_score', 20),
                (int) config('cojmip.selection.repetition.per_selection', 4),
                (int) config('cojmip.selection.repetition.max_penalty', 25),
                config('cojmip.selection.repetition.window_days') !== null
                    ? (int) config('cojmip.selection.repetition.window_days')
                    : null,
            ),
        );
        $this->app->bind(SelectionContract::class, IntelligenceSelectionService::class);
        $this->app->bind(
            RelevanceScorer::class,
            fn () => new RelevanceScorer(
                config('cojmip.ranking_weights.fields', []),
                config('cojmip.ranking_weights.verification', []),
                (int) config('cojmip.ranking_weights.cap', 100),
            ),
        );

        $this->registerNewsletterBindings();
    }

    /**
     * Newsletter studio bindings (Phase 7). The composer reuses the existing
     * selection pipeline for top-three selection; layouts, assets, PDF
     * rendering and the newsletter generation history are wired here so the
     * presentation layer never constructs them directly.
     */
    private function registerNewsletterBindings(): void
    {
        $this->app->bind(NewsletterLayouts::class, fn () => new NewsletterLayouts(config('cojmip.layouts', [])));
        $this->app->bind(
            NewsletterArticleMatcher::class,
            fn () => new NewsletterArticleMatcher(app(ArticleRepositoryContract::class)),
        );
        $this->app->singleton(
            NewsletterAssetService::class,
            fn () => new NewsletterAssetService(config('cojmip.newsletter', [])),
        );
        $this->app->bind(
            NewsletterComposer::class,
            fn () => new NewsletterComposer(
                app(IntelligenceSelectionService::class),
                app(OrganisationRepositoryContract::class),
                app(NewsletterArticleMatcher::class),
                app(NewsletterAssetService::class),
                config('cojmip.newsletter', []),
                (int) config('cojmip.selection.required', 3),
            ),
        );
        $this->app->bind(
            NewsletterPdfService::class,
            fn () => new NewsletterPdfService(
                app(NewsletterAssetService::class),
                config('cojmip.newsletter', []),
            ),
        );
        $this->app->bind(NewsletterHistoryRepositoryContract::class, JsonNewsletterHistoryRepository::class);
    }
}
