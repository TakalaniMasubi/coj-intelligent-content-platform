<?php

namespace Tests\Unit\Intelligence;

use App\Application\Intelligence\RelevanceScorer;
use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationType;
use PHPUnit\Framework\TestCase;

class RelevanceScorerTest extends TestCase
{
    private RelevanceScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new RelevanceScorer();
    }

    public function test_exact_name_match_scores_higher_than_description_only(): void
    {
        $byName = $this->organisation([
            'id' => 'waste-management',
            'name' => 'Waste Management Utility',
            'description' => 'the utility responsible for waste collection and recycling',
        ], 'verified');
        $byDescription = $this->organisation([
            'id' => 'recycling-hub',
            'name' => 'Recycling Hub',
            'description' => 'the utility responsible for waste collection and recycling',
        ], 'verified');

        $nameMatch = $this->scorer->evaluate($byName, ['waste'], []);
        $descriptionMatch = $this->scorer->evaluate($byDescription, ['waste'], []);

        $this->assertNotNull($nameMatch);
        $this->assertNotNull($descriptionMatch);
        $this->assertGreaterThan($descriptionMatch->score, $nameMatch->score);
    }

    public function test_a_verified_organisation_outranks_a_pending_one_for_the_same_evidence(): void
    {
        $verified = $this->scorer->evaluate($this->organisation([
            'id' => 'a',
            'name' => 'Alpha',
            'description' => 'waste collection',
        ], 'verified'), ['waste'], []);
        $pending = $this->scorer->evaluate($this->organisation([
            'id' => 'b',
            'name' => 'Beta',
            'description' => 'waste collection',
        ], 'pending'), ['waste'], []);

        $this->assertNotNull($verified);
        $this->assertNotNull($pending);
        $this->assertGreaterThan($pending->score, $verified->score);
    }

    public function test_article_title_and_summary_matches_add_to_the_score(): void
    {
        $organisation = $this->organisation([
            'id' => 'metrobus',
            'name' => 'Metrobus',
        ], 'verified');

        $article = Article::fromArray([
            'id' => 'art-1',
            'source_name' => 'mbus.joburg.org.za',
            'source_url' => 'https://mbus.joburg.org.za',
            'title' => 'Municipal bus services in Joburg',
            'summary' => 'Scheduled public bus transport across the metro',
            'subject' => 'public transport',
            'status' => ArticleStatus::Verified->value,
            'organisation_ids' => ['metrobus'],
        ]);

        $noArticle = $this->scorer->evaluate($organisation, ['bus'], []);
        $withArticle = $this->scorer->evaluate($organisation, ['bus'], [$article]);

        $this->assertNotNull($noArticle);
        $this->assertNotNull($withArticle);
        $this->assertGreaterThan($noArticle->score, $withArticle->score);
    }

    public function test_score_is_capped_at_one_hundred(): void
    {
        $organisation = $this->organisation([
            'id' => 'omni',
            'name' => 'Omni x',
            'abbreviation' => 'OX',
            'aliases' => ['x Corp'],
            'description' => 'x activities cover everything with the letter x',
        ], 'verified');

        $article = Article::fromArray([
            'id' => 'art-2',
            'source_name' => 'omni.example',
            'source_url' => 'https://omni.example',
            'title' => 'x overview',
            'summary' => 'x summary',
            'subject' => 'x',
            'status' => ArticleStatus::Verified->value,
            'organisation_ids' => ['omni'],
        ]);

        $match = $this->scorer->evaluate($organisation, ['x'], [$article]);

        $this->assertNotNull($match);
        $this->assertSame(100, $match->score);
    }

    public function test_it_returns_null_when_nothing_matches(): void
    {
        $organisation = $this->organisation([
            'id' => 'unrelated',
            'name' => 'Unrelated',
            'description' => 'books and libraries',
        ], 'verified');

        $this->assertNull($this->scorer->evaluate($organisation, ['pneumonoultramicroscopic'], []));
    }

    public function test_it_is_deterministic_across_calls(): void
    {
        $organisation = $this->organisation([
            'id' => 'pikitup',
            'name' => 'Pikitup',
        ], 'pending');

        $first = $this->scorer->evaluate($organisation, ['pikitup'], []);
        $second = $this->scorer->evaluate($organisation, ['pikitup'], []);

        $this->assertSame($first?->score, $second?->score);
        $this->assertSame($first?->matchingTerms, $second?->matchingTerms);
    }

    private function organisation(array $data, string $verificationStatus): CoJOrganisation
    {
        return CoJOrganisation::fromArray([
            'id' => $data['id'],
            'name' => $data['name'],
            'organisation_type' => OrganisationType::Entity->value,
            'description' => $data['description'] ?? null,
            'abbreviation' => $data['abbreviation'] ?? null,
            'aliases' => $data['aliases'] ?? [],
            'verification_status' => $verificationStatus,
            'official_status' => 'active',
        ]);
    }
}