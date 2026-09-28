<?php

namespace Tests\Unit\Dataset;

use App\Domain\Article\Article;
use App\Domain\Article\ArticleStatus;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationType;
use App\Domain\Shared\VerificationStatus;
use App\Domain\Source\SourceTrust;
use App\Infrastructure\Persistence\Json\JsonArticleRepository;
use App\Infrastructure\Persistence\Json\JsonOrganisationRepository;
use App\Infrastructure\Persistence\Json\JsonSourceTrustRepository;
use App\Infrastructure\Persistence\Json\RepositoryJsonGateway;
use Tests\TestCase;

class CuratedDatasetTest extends TestCase
{
    private RepositoryJsonGateway $gateway;

    private JsonOrganisationRepository $organisations;

    private JsonArticleRepository $articles;

    private JsonSourceTrustRepository $sources;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new RepositoryJsonGateway(database_path('seed-data'));
        $this->organisations = new JsonOrganisationRepository($this->gateway);
        $this->articles = new JsonArticleRepository($this->gateway);
        $this->sources = new JsonSourceTrustRepository($this->gateway);
    }

    public function test_the_curated_dataset_loads_through_the_phase_2_repositories(): void
    {
        $this->assertCount(31, $this->organisations->all());
        $this->assertCount(14, $this->sources->all());
        $this->assertCount(17, $this->articles->all());
    }

    public function test_organisation_dataset_is_complete_and_well_typed(): void
    {
        $organisations = $this->organisations->all();

        $byType = [];
        $ids = [];
        foreach ($organisations as $organisation) {
            $this->assertNotEmpty($organisation->id);
            $this->assertNotEmpty($organisation->name);
            $this->assertNotEmpty($organisation->description, "Missing description: {$organisation->id}");
            $this->assertNotEmpty($organisation->sourceReference, "Missing source_reference: {$organisation->id}");
            $this->assertContains($organisation->organisationType, [
                OrganisationType::Entity,
                OrganisationType::Directorate,
                OrganisationType::Office,
            ]);
            $this->assertContains($organisation->verificationStatus, [
                VerificationStatus::Verified,
                VerificationStatus::Pending,
                VerificationStatus::Unverified,
            ]);
            $ids[] = $organisation->id;
            $byType[$organisation->organisationType->value] ??= 0;
            $byType[$organisation->organisationType->value]++;
        }

        $this->assertCount(31, array_unique($ids));
        $this->assertSame(13, $byType['entity']);
        $this->assertSame(17, $byType['directorate']);
        $this->assertSame(1, $byType['office']);
    }

    public function test_verified_organisations_carry_evidence_of_verification(): void
    {
        foreach ($this->organisations->all() as $organisation) {
            if ($organisation->verificationStatus === VerificationStatus::Verified) {
                $this->assertNotNull($organisation->lastVerifiedAt, "Missing last_verified_at: {$organisation->id}");
                $this->assertNotNull($organisation->officialWebsite, "Verified org without url: {$organisation->id}");
            } else {
                $this->assertNull($organisation->lastVerifiedAt, "Pending org should have no last_verified_at: {$organisation->id}");
            }
        }
    }

    public function test_no_synthetic_leaders_are_present(): void
    {
        foreach ($this->organisations->all() as $organisation) {
            $this->assertNull($organisation->head);
        }
    }

    public function test_article_dataset_references_known_sources_and_organisations(): void
    {
        $orgIds = array_map(static fn (CoJOrganisation $o) => $o->id, $this->organisations->all());
        $sourceNames = array_map(static fn (SourceTrust $s) => $s->sourceName, $this->sources->all());

        foreach ($this->articles->all() as $article) {
            $this->assertContains($article->sourceName, $sourceNames, "Unknown source: {$article->sourceName}");
            foreach ($article->organisationIds as $oid) {
                $this->assertContains($oid, $orgIds, "Unknown organisation: {$oid}");
            }
            $this->assertContains($article->status, [
                ArticleStatus::Verified,
                ArticleStatus::Unverified,
                ArticleStatus::Rejected,
            ]);
        }
    }

    public function test_article_content_hashes_are_real_sha256_values(): void
    {
        $hashes = [];
        foreach ($this->articles->all() as $article) {
            $this->assertNotNull($article->contentHash, "Missing content_hash: {$article->id}");
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $article->contentHash);

            $recomputed = hash('sha256', json_encode([
                'title' => $article->title,
                'summary' => $article->summary,
                'organisations' => $article->organisationIds,
            ]));
            $this->assertSame($recomputed, $article->contentHash, "Content hash mismatch: {$article->id}");
            $hashes[] = $article->contentHash;
        }

        $this->assertCount(17, array_unique($hashes));
    }

    public function test_verified_sources_carry_verification_evidence(): void
    {
        foreach ($this->sources->all() as $source) {
            $this->assertNotEmpty($source->url);
            if ($source->verificationStatus === VerificationStatus::Verified) {
                $this->assertNotNull($source->verifiedAt, "Verified source without date: {$source->sourceName}");
            } else {
                $this->assertNull($source->verifiedAt);
            }
        }
    }

    public function test_dataset_round_trips_through_the_repositories(): void
    {
        $volume = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cojmip-dataset-'.uniqid('', true);
        $gateway = new RepositoryJsonGateway($volume);

        $organisation = $this->organisations->find('joshco');
        $this->assertNotNull($organisation);
        (new JsonOrganisationRepository($gateway))->save($organisation);
        (new JsonOrganisationRepository($gateway))->save($organisation);

        $persisted = (new JsonOrganisationRepository($gateway))->find('joshco');

        $this->assertSame($organisation->toArray(), $persisted?->toArray());

        $this->deleteDirectory($volume);
    }

    public function test_a_malformed_record_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CoJOrganisation::fromArray([
            'id' => 'broken-org',
            'organisation_type' => 'entity',
        ]);
    }

    public function test_a_record_with_an_unknown_type_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CoJOrganisation::fromArray([
            'id' => 'broken-org',
            'name' => 'Broken',
            'organisation_type' => 'department',
        ]);
    }

    public function test_an_article_with_a_missing_required_field_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Article::fromArray([
            'id' => 'broken-article',
            'source_name' => 'joshco.co.za',
            'title' => 'No source url',
        ]);
    }

    private function deleteDirectory(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
