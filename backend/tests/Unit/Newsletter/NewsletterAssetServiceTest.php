<?php

namespace Tests\Unit\Newsletter;

use App\Application\Newsletter\NewsletterAssetService;
use App\Domain\Article\Article;
use App\Domain\Newsletter\NewsletterMediaAsset;
use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationStatus;
use App\Domain\Organisation\OrganisationType;
use App\Domain\Shared\VerificationStatus;
use Tests\TestCase;

class NewsletterAssetServiceTest extends TestCase
{
    private const CONFIG = [
        'brand' => [
            'logo' => 'images/coj-logo.png',
            'seal' => 'images/coj-initials.jpg',
        ],
        'media' => [
            'organisation_logos_dir' => 'images/organisations',
            'article_images_dir' => 'images/articles',
        ],
    ];

    public function test_organisation_without_a_logo_file_gets_a_deterministic_monogram(): void
    {
        $service = $this->service();
        $organisation = $this->organisation('pikitup', 'Pikitup', 'PKT', OrganisationType::Entity);

        $asset = $service->organisationLogo($organisation);

        $this->assertTrue($asset->isFallback());
        $this->assertSame(NewsletterMediaAsset::SOURCE_MONOGRAM, $asset->source);
        $this->assertSame('PKT', $asset->initials);
        $this->assertSame('#0f5c3a', $asset->fill);
        $this->assertSame(0, $asset->width);
        $this->assertSame(0.0, $asset->aspectRatio());
        $this->assertSame('', $asset->webUrl);
    }

    public function test_monogram_colour_is_keyed_by_organisation_type(): void
    {
        $service = $this->service();

        $entity = $service->organisationLogo(
            $this->organisation('entity-org', 'Some Entity', 'SE', OrganisationType::Entity),
        );
        $directorate = $service->organisationLogo(
            $this->organisation('dir-org', 'Some Directorate', 'SD', OrganisationType::Directorate),
        );
        $office = $service->organisationLogo(
            $this->organisation('office-org', 'Some Office', 'SO', OrganisationType::Office),
        );

        $this->assertSame('#0f5c3a', $entity->fill);
        $this->assertSame('#1d4e89', $directorate->fill);
        $this->assertSame('#7a3f92', $office->fill);
    }

    public function test_article_without_an_image_gets_a_subject_labelled_panel(): void
    {
        $service = $this->service();
        $article = $this->article('coj-art-1', 'Waste management strategy');

        $asset = $service->articleImage($article, 'waste management');

        $this->assertTrue($asset->isFallback());
        $this->assertSame(NewsletterMediaAsset::SOURCE_PANEL, $asset->source);
        $this->assertSame('Waste management', $asset->label);
        $this->assertNotSame('', $asset->fill);
    }

    public function test_subject_panel_colour_is_deterministic(): void
    {
        $service = $this->service();

        $first = $service->articleImage($this->article('a', 'Title'), 'waste management');
        $second = $service->articleImage($this->article('b', 'Title'), 'waste management');

        $this->assertSame($first->fill, $second->fill);
        $this->assertNotSame(
            $first->fill,
            $service->articleImage($this->article('c', 'Title'), 'water services')->fill,
        );
    }

    public function test_the_brand_assets_resolve_to_real_files_with_dimensions(): void
    {
        if (! is_file(public_path('images/coj-logo.png'))) {
            $this->markTestSkipped('Phase 6 brand logo is not available.');
        }

        $masthead = $this->service()->masthead();

        $this->assertArrayHasKey('brand', $masthead);
        $this->assertArrayHasKey('seal', $masthead);

        $brand = $masthead['brand'];
        $this->assertTrue($brand->isImage());
        $this->assertSame(NewsletterMediaAsset::KIND_LOGO, $brand->kind);
        $this->assertGreaterThan(0, $brand->width);
        $this->assertGreaterThan(0, $brand->height);
        $this->assertGreaterThan(0.0, $brand->aspectRatio());
        $this->assertStringContainsString('coj-logo.png', $brand->webUrl);

        // The PDF path must be an absolute, slash-normalised on-disk path
        // (Dompdf runs inside a public-directory chroot).
        $this->assertNotSame('', $brand->pdfPath);
        $this->assertStringNotContainsString('\\', $brand->pdfPath);
        $this->assertSame(
            str_replace('\\', '/', realpath(public_path('images/coj-logo.png'))),
            $brand->pdfPath,
        );
    }

    private function service(): NewsletterAssetService
    {
        return new NewsletterAssetService(self::CONFIG);
    }

    private function organisation(
        string $id,
        string $name,
        ?string $abbreviation,
        OrganisationType $type,
    ): CoJOrganisation {
        return new CoJOrganisation(
            id: $id,
            name: $name,
            organisationType: $type,
            abbreviation: $abbreviation,
            officialStatus: OrganisationStatus::Active,
            verificationStatus: VerificationStatus::Pending,
        );
    }

    private function article(string $id, string $title): Article
    {
        return new Article(
            id: $id,
            sourceName: 'Example Source',
            sourceUrl: 'https://example.test/',
            title: $title,
            subject: 'waste management',
        );
    }
}
