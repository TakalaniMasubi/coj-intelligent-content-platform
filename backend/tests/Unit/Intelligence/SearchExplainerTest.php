<?php

namespace Tests\Unit\Intelligence;

use App\Application\Intelligence\SearchExplainer;
use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;
use App\Domain\Intelligence\SearchMatch;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationType;
use PHPUnit\Framework\TestCase;

class SearchExplainerTest extends TestCase
{
    private SearchExplainer $explainer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->explainer = new SearchExplainer();
    }

    public function test_reasons_are_built_from_actual_matched_fields(): void
    {
        $organisation = $this->organisation('verified', '2026-09-22T00:00:00+00:00');
        $match = new SearchMatch(
            organisationId: 'pikitup',
            organisationName: 'Pikitup',
            organisationType: 'entity',
            fieldTerms: [
                'description' => ['waste', 'collection'],
                'article_title' => ['waste'],
            ],
            matchingTerms: ['waste', 'collection'],
            score: 40,
        );

        $reasons = $this->explainer->reasons($match, $organisation, [$this->article()]);

        $this->assertContains("Matched 'waste', 'collection' in the organisation description", $reasons);
        $this->assertContains("Matched 'waste' in an article title", $reasons);
        $this->assertContains('Organisation is VERIFIED against an official source (last verified 2026-09-22)', $reasons);
    }

    public function test_pending_organisations_get_an_explicit_caution_reason(): void
    {
        $organisation = $this->organisation('pending', null);
        $match = new SearchMatch(
            organisationId: 'pikitup',
            organisationName: 'Pikitup',
            organisationType: 'entity',
            fieldTerms: ['description' => ['waste']],
            matchingTerms: ['waste'],
            score: 19,
        );

        $reasons = $this->explainer->reasons($match, $organisation, []);

        $this->assertContains('Verification status is PENDING — confirm against an official source before relying on this result', $reasons);
    }

    public function test_sources_combine_organisation_links_and_article_urls(): void
    {
        $organisation = $this->organisation('verified', null);
        $organisation = CoJOrganisation::fromArray(
            $organisation->toArray() + ['source_links' => ['https://pikitup.example']],
        );

        if ($organisation->sourceLinks === []) {
            $organisation = CoJOrganisation::fromArray(
                array_merge($organisation->toArray(), ['source_links' => ['https://pikitup.example']]),
            );
        }
        $this->assertNotEmpty($organisation->sourceLinks);

        $sources = $this->explainer->sources($organisation, [$this->article()]);

        $this->assertContains('https://pikitup.example', $sources);
        $this->assertContains('https://pikitup.example/waste-overview', $sources);
        $this->assertSame($sources, array_values(array_unique($sources)));
    }

    private function organisation(string $verificationStatus, ?string $lastVerifiedAt): CoJOrganisation
    {
        return CoJOrganisation::fromArray([
            'id' => 'pikitup',
            'name' => 'Pikitup',
            'organisation_type' => OrganisationType::Entity->value,
            'description' => 'waste management utility offering waste collection',
            'verification_status' => $verificationStatus,
            'last_verified_at' => $lastVerifiedAt,
            'official_status' => 'active',
        ]);
    }

    private function article(): Article
    {
        return Article::fromArray([
            'id' => 'art-1',
            'source_name' => 'pikitup.example',
            'source_url' => 'https://pikitup.example/waste-overview',
            'title' => 'Waste overview',
            'summary' => 'waste collection services',
            'subject' => 'waste management',
            'status' => ArticleStatus::Verified->value,
            'organisation_ids' => ['pikitup'],
        ]);
    }
}