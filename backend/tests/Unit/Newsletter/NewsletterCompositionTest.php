<?php

namespace Tests\Unit\Newsletter;

use App\Domain\Newsletter\NewsletterArticle;
use App\Domain\Newsletter\NewsletterComposition;
use App\Domain\Newsletter\NewsletterLayout;
use App\Domain\Newsletter\NewsletterMediaAsset;
use App\Domain\Newsletter\NewsletterOrganisation;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class NewsletterCompositionTest extends TestCase
{
    public function test_with_layout_never_touches_the_selected_content(): void
    {
        $composition = $this->composition(
            $this->organisation('pikitup', 1),
        );

        $switched = $composition->withLayout(NewsletterLayout::Magazine);

        $this->assertSame(NewsletterLayout::Magazine, $switched->layout);
        $this->assertNotSame($composition->layout, $switched->layout);

        // Every piece of content survives the layout switch untouched.
        $this->assertSame($composition->id, $switched->id);
        $this->assertSame($composition->topic, $switched->topic);
        $this->assertSame($composition->status, $switched->status);
        $this->assertSame($composition->message, $switched->message);
        $this->assertSame($composition->generatedAt, $switched->generatedAt);
        $this->assertSame($composition->introduction, $switched->introduction);
        $this->assertSame($composition->footnote, $switched->footnote);
        $this->assertSame($composition->organisations(), $switched->organisations());
        $this->assertSame($composition->organisations()[0]->articles, $switched->organisations()[0]->articles);
    }

    public function test_to_array_from_array_round_trip_is_lossless(): void
    {
        $organisation = $this->organisation('pikitup', 1);
        $composition = $this->composition($organisation, NewsletterLayout::NewsInsights);

        $rebuilt = NewsletterComposition::fromArray($composition->toArray());

        $this->assertSame($composition->id, $rebuilt->id);
        $this->assertSame($composition->topic, $rebuilt->topic);
        $this->assertSame($composition->status, $rebuilt->status);
        $this->assertSame($composition->message, $rebuilt->message);
        $this->assertSame(NewsletterLayout::NewsInsights, $rebuilt->layout);
        $this->assertSame(
            $composition->generatedAt->format(DATE_ATOM),
            $rebuilt->generatedAt->format(DATE_ATOM),
        );
        $this->assertSame($composition->title, $rebuilt->title);
        $this->assertSame($composition->subtitle, $rebuilt->subtitle);
        $this->assertSame($composition->date, $rebuilt->date);
        $this->assertSame($composition->introduction, $rebuilt->introduction);
        $this->assertSame($composition->footnote, $rebuilt->footnote);
        $this->assertSame($composition->required, $rebuilt->required);
        $this->assertSame($composition->available, $rebuilt->available);
        $this->assertCount(1, $rebuilt->organisations());

        $originalOrganisation = $composition->organisations()[0];
        $rebuiltOrganisation = $rebuilt->organisations()[0];

        $this->assertSame($originalOrganisation->organisationId, $rebuiltOrganisation->organisationId);
        $this->assertSame($originalOrganisation->name, $rebuiltOrganisation->name);
        $this->assertSame($originalOrganisation->score, $rebuiltOrganisation->score);
        $this->assertSame($originalOrganisation->verification, $rebuiltOrganisation->verification);
        $this->assertSame($originalOrganisation->mandate, $rebuiltOrganisation->mandate);
        $this->assertSame($originalOrganisation->description, $rebuiltOrganisation->description);
        $this->assertSame($originalOrganisation->logo->source, $rebuiltOrganisation->logo->source);
        $this->assertSame($originalOrganisation->logo->initials, $rebuiltOrganisation->logo->initials);
        $this->assertCount(1, $rebuiltOrganisation->articles);
        $this->assertSame(
            $originalOrganisation->articles[0]->title,
            $rebuiltOrganisation->articles[0]->title,
        );
    }

    public function test_is_success_reflects_the_status(): void
    {
        $success = $this->composition();
        $insufficient = NewsletterComposition::fromArray(
            array_replace($success->toArray(), [
                'status' => NewsletterComposition::STATUS_INSUFFICIENT,
            ]),
        );

        $this->assertTrue($success->isSuccess());
        $this->assertFalse($insufficient->isSuccess());
    }

    private function composition(
        ?NewsletterOrganisation $organisation = null,
        NewsletterLayout $layout = NewsletterLayout::ExecutiveBrief,
    ): NewsletterComposition {
        return new NewsletterComposition(
            id: 'waste-management',
            topic: 'waste management',
            status: NewsletterComposition::STATUS_SUCCESS,
            message: 'Selected the top 3 of 5 eligible organisations for this topic.',
            layout: $layout,
            generatedAt: new DateTimeImmutable('2026-09-22T10:00:00+00:00'),
            title: 'CoJ Municipal Intelligence Newsletter',
            subtitle: 'Municipal Spotlight — Waste management',
            date: 'September 22, 2026',
            introduction: 'Curated repository summary.',
            organisations: $organisation === null ? [] : [$organisation],
            footnote: 'Automatically generated from the curated CoJ repository.',
            required: 3,
            available: 5,
        );
    }

    private function organisation(string $id, int $rank): NewsletterOrganisation
    {
        return new NewsletterOrganisation(
            organisationId: $id,
            name: 'Pikitup',
            rank: $rank,
            score: 43,
            type: 'entity',
            verification: 'pending',
            abbreviation: 'PKT',
            description: 'City waste management entity.',
            mandate: 'Keeps Johannesburg clean and waste-free.',
            officialWebsite: 'https://www.pikitup.co.za',
            reasons: ['Matches the topic terms'],
            matchingTerms: ['waste'],
            logo: new NewsletterMediaAsset(
                kind: NewsletterMediaAsset::KIND_LOGO,
                source: NewsletterMediaAsset::SOURCE_MONOGRAM,
                label: 'Pikitup',
                initials: 'PKT',
                fill: '#0f5c3a',
            ),
            articles: [
                new NewsletterArticle(
                    articleId: 'coj-art-1',
                    title: 'Waste management strategy',
                    summary: 'How the City keeps streets clean.',
                    subject: 'waste management',
                    sourceName: 'pikitup.co.za',
                    sourceUrl: 'https://www.pikitup.co.za',
                    status: 'verified',
                    retrievedAt: new DateTimeImmutable('2026-09-22T08:00:00+00:00'),
                    relevanceScore: 7,
                    image: new NewsletterMediaAsset(
                        kind: NewsletterMediaAsset::KIND_FEATURED_IMAGE,
                        source: NewsletterMediaAsset::SOURCE_PANEL,
                        label: 'Waste management',
                        fill: '#0f5c3a',
                    ),
                ),
            ],
        );
    }
}
