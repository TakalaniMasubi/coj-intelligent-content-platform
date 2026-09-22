<?php

namespace App\Domain\Source;

use App\Domain\Shared\VerificationStatus;
use DateTimeImmutable;

final readonly class SourceTrust
{
    public function __construct(
        public string $sourceName,
        public SourceTier $tier,
        public ?string $displayName = null,
        public ?string $category = null,
        public string $notes = '',
        public bool $active = true,
        public ?string $sourceType = null,
        public ?string $url = null,
        public ?string $canonicalUrl = null,
        public VerificationStatus $verificationStatus = VerificationStatus::Pending,
        public ?DateTimeImmutable $verifiedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! isset($data['source_name']) || ! is_string($data['source_name'])) {
            throw new \InvalidArgumentException('Source trust entries must include a source_name.');
        }

        if (! isset($data['tier']) || ! is_string($data['tier'])) {
            throw new \InvalidArgumentException("Source '{$data['source_name']}' is missing a tier.");
        }

        return new self(
            sourceName: $data['source_name'],
            tier: SourceTier::fromName($data['tier']),
            displayName: isset($data['display_name']) && is_string($data['display_name']) ? $data['display_name'] : null,
            category: isset($data['category']) && is_string($data['category']) ? $data['category'] : null,
            notes: isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : '',
            active: $data['active'] ?? true,
            sourceType: isset($data['source_type']) && is_string($data['source_type']) ? $data['source_type'] : null,
            url: isset($data['url']) && is_string($data['url']) ? $data['url'] : null,
            canonicalUrl: isset($data['canonical_url']) && is_string($data['canonical_url']) ? $data['canonical_url'] : null,
            verificationStatus: isset($data['verification_status']) && is_string($data['verification_status'])
                ? VerificationStatus::fromName($data['verification_status'])
                : VerificationStatus::Pending,
            verifiedAt: isset($data['verified_at']) && is_string($data['verified_at'])
                ? new DateTimeImmutable($data['verified_at'])
                : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'source_name' => $this->sourceName,
            'tier' => $this->tier->value,
            'display_name' => $this->displayName,
            'category' => $this->category,
            'source_type' => $this->sourceType,
            'url' => $this->url,
            'canonical_url' => $this->canonicalUrl,
            'notes' => $this->notes,
            'active' => $this->active,
            'verification_status' => $this->verificationStatus->value,
            'verified_at' => $this->verifiedAt?->format(DATE_ATOM),
        ];

        return $data;
    }
}