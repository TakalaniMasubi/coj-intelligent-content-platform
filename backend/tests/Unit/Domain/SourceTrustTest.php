<?php

namespace Tests\Unit\Domain;

use App\Domain\Shared\VerificationStatus;
use App\Domain\Source\SourceTier;
use App\Domain\Source\SourceTrust;
use PHPUnit\Framework\TestCase;

class SourceTrustTest extends TestCase
{
    public function test_from_array_builds_a_source_trust_object(): void
    {
        $source = SourceTrust::fromArray([
            'source_name' => 'joshco.co.za',
            'tier' => 'tier1',
            'display_name' => 'JOSHCO (Official Website)',
            'category' => 'official',
            'source_type' => 'municipal-entity-website',
            'url' => 'https://www.joshco.co.za',
            'canonical_url' => 'https://www.joshco.co.za',
            'notes' => 'Verified in Phase 3.',
            'active' => true,
            'verification_status' => 'verified',
            'verified_at' => '2026-09-22T00:00:00+00:00',
        ]);

        $this->assertSame('joshco.co.za', $source->sourceName);
        $this->assertSame(SourceTier::Tier1, $source->tier);
        $this->assertSame('municipal-entity-website', $source->sourceType);
        $this->assertSame('https://www.joshco.co.za', $source->canonicalUrl);
        $this->assertSame(VerificationStatus::Verified, $source->verificationStatus);
        $this->assertNotNull($source->verifiedAt);
    }

    public function test_from_array_applies_documented_defaults(): void
    {
        $source = SourceTrust::fromArray([
            'source_name' => 'minimal.source',
            'tier' => 'tier2',
        ]);

        $this->assertSame(VerificationStatus::Pending, $source->verificationStatus);
        $this->assertNull($source->verifiedAt);
        $this->assertNull($source->url);
        $this->assertNull($source->sourceType);
        $this->assertTrue($source->active);
    }

    public function test_to_array_round_trips(): void
    {
        $source = [
            'source_name' => 'example.source',
            'tier' => 'tier1',
            'display_name' => 'Example',
            'category' => 'official',
            'source_type' => 'municipal-portal',
            'url' => 'https://example.source',
            'canonical_url' => null,
            'notes' => 'notes',
            'active' => true,
            'verification_status' => 'pending',
            'verified_at' => null,
        ];

        $roundTripped = SourceTrust::fromArray(SourceTrust::fromArray($source)->toArray())->toArray();

        $this->assertSame($source, $roundTripped);
    }
}