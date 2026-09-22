<?php

namespace Tests\Unit\Domain;

use App\Domain\Shared\VerificationStatus;
use PHPUnit\Framework\TestCase;

class VerificationStatusTest extends TestCase
{
    public function test_from_name_maps_known_statuses(): void
    {
        $this->assertSame(VerificationStatus::Verified, VerificationStatus::fromName('verified'));
        $this->assertSame(VerificationStatus::Pending, VerificationStatus::fromName('pending'));
        $this->assertSame(VerificationStatus::Unverified, VerificationStatus::fromName('unverified'));
    }

    public function test_from_name_is_case_insensitive(): void
    {
        $this->assertSame(VerificationStatus::Verified, VerificationStatus::fromName('VERIFIED'));
        $this->assertSame(VerificationStatus::Pending, VerificationStatus::fromName('  Pending '));
    }

    public function test_from_name_rejects_unknown_statuses(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VerificationStatus::fromName('confirmed-by-spirit');
    }
}