<?php

namespace Tests\Unit\Domain;

use App\Domain\Organisation\CoJOrganisation;
use App\Domain\Organisation\OrganisationStatus;
use App\Domain\Organisation\OrganisationType;
use App\Domain\Source\SourceTier;
use PHPUnit\Framework\TestCase;

class CoJOrganisationTest extends TestCase
{
    public function test_from_array_builds_a_domain_object(): void
    {
        $organisation = CoJOrganisation::fromArray([
            'id' => 'coj-org-0001',
            'name' => 'Example Entity',
            'organisation_type' => 'entity',
            'aliases' => ['Example'],
            'head' => ['name' => 'A. Person', 'title' => 'MD', 'role_summary' => 'runs things'],
            'tier' => 'tier1',
            'official_status' => 'active',
            'last_updated' => '2026-09-01T00:00:00+00:00',
        ]);

        $this->assertSame('coj-org-0001', $organisation->id);
        $this->assertSame(OrganisationType::Entity, $organisation->organisationType);
        $this->assertSame(SourceTier::Tier1, $organisation->tier);
        $this->assertSame(OrganisationStatus::Active, $organisation->officialStatus);
        $this->assertSame('A. Person', $organisation->head?->name);
        $this->assertSame(['Example'], $organisation->aliases);
    }

    public function test_from_array_applies_documented_defaults(): void
    {
        $organisation = CoJOrganisation::fromArray([
            'id' => 'coj-org-0002',
            'name' => 'Minimal Entity',
            'organisation_type' => 'office',
        ]);

        $this->assertSame('City of Johannesburg', $organisation->jurisdiction);
        $this->assertSame(SourceTier::Tier2, $organisation->tier);
        $this->assertSame(OrganisationStatus::Active, $organisation->officialStatus);
        $this->assertNull($organisation->head);
        $this->assertSame([], $organisation->aliases);
        $this->assertSame(\App\Domain\Shared\VerificationStatus::Pending, $organisation->verificationStatus);
        $this->assertNull($organisation->abbreviation);
        $this->assertNull($organisation->description);
        $this->assertNull($organisation->sourceReference);
        $this->assertNull($organisation->lastVerifiedAt);
    }

    public function test_from_array_requires_id_and_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CoJOrganisation::fromArray([
            'name' => 'Missing Id',
            'organisation_type' => 'entity',
        ]);
    }

    public function test_from_array_requires_organisation_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CoJOrganisation::fromArray([
            'id' => 'coj-org-0003',
            'name' => 'Missing Type',
        ]);
    }

    public function test_from_array_rejects_unknown_organisation_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CoJOrganisation::fromArray([
            'id' => 'coj-org-0004',
            'name' => 'Bad Type',
            'organisation_type' => 'department',
        ]);
    }

    public function test_to_array_round_trips(): void
    {
        $source = [
            'id' => 'coj-org-0005',
            'name' => 'Round Trip Entity',
            'organisation_type' => 'entity',
            'aliases' => ['RT'],
            'head' => ['name' => 'B. Person', 'title' => 'CEO'],
            'jurisdiction' => 'City of Johannesburg',
            'official_url' => 'https://example.org',
            'mandate' => 'Mandate text',
            'source_links' => ['https://example.org/mandate'],
            'tier' => 'tier2',
            'official_status' => 'active',
            'last_updated' => '2026-09-01T00:00:00+00:00',
            'abbreviation' => 'RT',
            'description' => 'Round trip description.',
            'source_reference' => 'https://example.org',
            'verification_status' => 'verified',
            'last_verified_at' => '2026-09-22T00:00:00+00:00',
        ];

        $roundTripped = CoJOrganisation::fromArray(CoJOrganisation::fromArray($source)->toArray())->toArray();

        $this->assertSame($source, $roundTripped);
    }

    public function test_from_array_accepts_legacy_official_website_key(): void
    {
        $organisation = CoJOrganisation::fromArray([
            'id' => 'coj-org-0006',
            'name' => 'Legacy Key Entity',
            'organisation_type' => 'entity',
            'official_website' => 'https://legacy.example',
        ]);

        $this->assertSame('https://legacy.example', $organisation->officialWebsite);
        $this->assertSame('https://legacy.example', $organisation->toArray()['official_url']);
    }
}