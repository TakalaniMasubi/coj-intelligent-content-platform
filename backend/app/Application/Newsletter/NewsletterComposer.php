<?php

namespace App\Application\Newsletter;

use App\Application\Intelligence\IntelligenceSelectionService;
use App\Contracts\Repositories\OrganisationRepositoryContract;
use App\Domain\Article\Article;
use App\Domain\Intelligence\SelectionDecision;
use App\Domain\Intelligence\SelectionOutcome;
use App\Domain\Newsletter\NewsletterArticle;
use App\Domain\Newsletter\NewsletterComposition;
use App\Domain\Newsletter\NewsletterLayout;
use App\Domain\Newsletter\NewsletterOrganisation;
use App\Domain\Organisation\CoJOrganisation;
use DateTimeImmutable;
use Illuminate\Support\Str;

/**
 * Builds the shared newsletter composition for a topic.
 *
 * The composition is derived EXCLUSIVELY from the existing platform
 * pipeline — no new ranking or selection algorithm exists here:
 *
 *   1. the Phase 4 search + Phase 5 top-three selection pipeline is reused
 *      through IntelligenceSelectionService::preview() (a non-persisting
 *      run, so merely composing a draft never writes to the selection
 *      history — the same preview path the dashboard explainability uses)
 *   2. each selected organisation is read back through the shared
 *      OrganisationRepositoryContract
 *   3. curated articles are matched deterministically (NewsletterArticleMatcher)
 *   4. logos and article media resolve through the asset service
 *
 * Every editorial string is written from repository facts or the config
 * disclaimer; no AI is involved and no content is fabricated.
 */
final readonly class NewsletterComposer
{
    public function __construct(
        private IntelligenceSelectionService $selection,
        private OrganisationRepositoryContract $organisations,
        private NewsletterArticleMatcher $articles,
        private NewsletterAssetService $assets,
        private array $newsletterConfig,
        private int $required,
    ) {}

    public function compose(string $topic, string $layoutSlug = NewsletterLayout::ExecutiveBrief->value): NewsletterComposition
    {
        $topic = trim($topic);
        $outcome = $this->selection->preview($topic);
        $layout = NewsletterLayout::fromSlug($layoutSlug);

        if (! $outcome->success) {
            return $this->insufficient($outcome, $topic, $layout);
        }

        $organisations = [];
        foreach ($outcome->selected() as $decision) {
            $organisation = $this->organisations->find($decision->organisationId);
            if ($organisation === null) {
                continue;
            }

            $organisations[] = $this->presentOrganisation($organisation, $decision, $topic);
        }

        if ($organisations === []) {
            return $this->insufficient($outcome, $topic, $layout);
        }

        usort(
            $organisations,
            static fn (NewsletterOrganisation $a, NewsletterOrganisation $b): int => $a->rank <=> $b->rank,
        );

        $now = new DateTimeImmutable('now');

        return new NewsletterComposition(
            id: $this->editionId($topic, $now),
            topic: $topic,
            status: NewsletterComposition::STATUS_SUCCESS,
            message: 'Selected the top '.count($organisations).' of '.$outcome->available
                .' eligible organisations for this topic.',
            layout: $layout,
            generatedAt: $now,
            title: (string) ($this->newsletterConfig['brand']['publication'] ?? 'CoJ Municipal Intelligence Newsletter'),
            subtitle: 'Municipal Spotlight — '.$this->titleCase($topic),
            date: $now->format('F j, Y'),
            introduction: $this->introduction($topic, count($organisations)),
            organisations: $organisations,
            footnote: (string) ($this->newsletterConfig['footnote'] ?? ''),
            required: $outcome->required,
            available: $outcome->available,
        );
    }

    private function presentOrganisation(
        CoJOrganisation $organisation,
        SelectionDecision $decision,
        string $topic,
    ): NewsletterOrganisation {
        $matches = $this->articles->forOrganisation(
            $organisation->id,
            $topic,
            (int) ($this->newsletterConfig['composition']['organisation_articles_limit'] ?? 2),
        );

        $articles = array_map(
            function (array $match) use ($topic): NewsletterArticle {
                /** @var Article $article */
                $article = $match['article'];

                return new NewsletterArticle(
                    articleId: $article->id,
                    title: $article->title,
                    summary: $article->summary,
                    subject: $article->subject,
                    sourceName: $article->sourceName,
                    sourceUrl: $article->sourceUrl,
                    status: $article->status->value,
                    retrievedAt: $article->retrievedAt ?? $article->publishedAt,
                    relevanceScore: (int) $match['score'],
                    image: $this->assets->articleImage($article, $article->subject !== '' ? $article->subject : $topic),
                );
            },
            $matches,
        );

        return new NewsletterOrganisation(
            organisationId: $organisation->id,
            name: $organisation->name,
            rank: $decision->rank,
            score: $decision->adjustedScore,
            type: $organisation->organisationType->value,
            verification: $organisation->verificationStatus->value,
            abbreviation: (string) ($organisation->abbreviation ?? ''),
            description: $organisation->description,
            mandate: $organisation->mandate,
            officialWebsite: $organisation->officialWebsite,
            reasons: array_values($decision->reasons),
            matchingTerms: array_values($decision->matchingTerms),
            logo: $this->assets->organisationLogo($organisation),
            articles: $articles,
        );
    }

    private function insufficient(SelectionOutcome $outcome, string $topic, NewsletterLayout $layout): NewsletterComposition
    {
        $now = new DateTimeImmutable('now');

        return new NewsletterComposition(
            id: $this->editionId($topic, $now),
            topic: $topic,
            status: NewsletterComposition::STATUS_INSUFFICIENT,
            message: $outcome->message,
            layout: $layout,
            generatedAt: $now,
            title: (string) ($this->newsletterConfig['brand']['publication'] ?? 'CoJ Municipal Intelligence Newsletter'),
            subtitle: 'Municipal Spotlight — '.$this->titleCase($topic),
            date: $now->format('F j, Y'),
            introduction: $this->introduction($topic, 0),
            organisations: [],
            footnote: (string) ($this->newsletterConfig['footnote'] ?? ''),
            required: $outcome->required,
            available: $outcome->available,
        );
    }

    private function introduction(string $topic, int $count): string
    {
        $prefix = (string) ($this->newsletterConfig['composition']['introduction_prefix'] ?? 'Generated');

        if ($count < 1) {
            return 'This edition could not identify enough relevant City of Johannesburg '
                .'organisations for the topic “'.$this->titleCase($topic).'” in the curated repository. '
                .'The platform never invents or duplicates organisations — it reports honestly '
                .'when the repository does not yet cover a topic.';
        }

        return $prefix
            .' “'.$this->titleCase($topic).'”, this edition highlights the top '.$count
            .' most relevant City of Johannesburg organisations, ordered by the platform\'s '
            .'deterministic relevance scoring and selected through the standard top-'.$this->required
            .' policy. Every organisation, article, source and verification status shown '
            .'is real curated repository data.';
    }

    private function editionId(string $topic, DateTimeImmutable $now): string
    {
        return Str::slug($topic) ?: 'edition';
    }

    private function titleCase(string $text): string
    {
        $lower = strtolower(trim($text));

        return mb_strtoupper(mb_substr($lower, 0, 1)).mb_substr($lower, 1);
    }
}
