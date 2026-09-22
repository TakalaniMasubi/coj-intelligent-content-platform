<?php

namespace App\Domain\Organisation;

use App\Domain\Shared\VerificationStatus;
use App\Domain\Source\SourceTier;
use DateTimeImmutable;

final readonly class CoJOrganisation
{
    public function __construct(
        public string $id,
        public string $name,
        public OrganisationType $organisationType,
        /** @var string[] */
        public array $aliases = [],
        public ?HeadOfOrganisation $head = null,
        public string $jurisdiction = 'City of Johannesburg',
        public ?string $officialWebsite = null,
        public ?string $mandate = null,
        /** @var string[] */
        public array $sourceLinks = [],
        public SourceTier $tier = SourceTier::Tier2,
        public OrganisationStatus $officialStatus = OrganisationStatus::Active,
        public ?DateTimeImmutable $lastUpdated = null,
        public ?string $abbreviation = null,
        public ?string $description = null,
        public ?string $sourceReference = null,
        public VerificationStatus $verificationStatus = VerificationStatus::Pending,
        public ?DateTimeImmutable $lastVerifiedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! isset($data['id'], $data['name']) || ! is_string($data['id']) || ! is_string($data['name'])) {
            throw new \InvalidArgumentException('Organisation entries must include a string id and name.');
        }

        if (! isset($data['organisation_type']) || ! is_string($data['organisation_type'])) {
            throw new \InvalidArgumentException("Organisation '{$data['id']}' is missing organisation_type.");
        }

        return new self(
            id: $data['id'],
            name: $data['name'],
            organisationType: OrganisationType::fromName($data['organisation_type']),
            aliases: self::strings($data['aliases'] ?? []),
            head: HeadOfOrganisation::fromArray($data['head'] ?? null),
            jurisdiction: isset($data['jurisdiction']) && is_string($data['jurisdiction'])
                ? $data['jurisdiction']
                : 'City of Johannesburg',
            officialWebsite: isset($data['official_url']) && is_string($data['official_url'])
                ? $data['official_url']
                : (isset($data['official_website']) && is_string($data['official_website'])
                    ? $data['official_website']
                    : null),
            mandate: isset($data['mandate']) && is_string($data['mandate']) ? $data['mandate'] : null,
            sourceLinks: self::strings($data['source_links'] ?? []),
            tier: isset($data['tier']) && is_string($data['tier']) ? SourceTier::fromName($data['tier']) : SourceTier::Tier2,
            officialStatus: isset($data['official_status']) && is_string($data['official_status'])
                ? OrganisationStatus::fromName($data['official_status'])
                : OrganisationStatus::Active,
            lastUpdated: isset($data['last_updated']) && is_string($data['last_updated'])
                ? new DateTimeImmutable($data['last_updated'])
                : null,
            abbreviation: isset($data['abbreviation']) && is_string($data['abbreviation']) ? $data['abbreviation'] : null,
            description: isset($data['description']) && is_string($data['description']) ? $data['description'] : null,
            sourceReference: isset($data['source_reference']) && is_string($data['source_reference'])
                ? $data['source_reference']
                : null,
            verificationStatus: isset($data['verification_status']) && is_string($data['verification_status'])
                ? VerificationStatus::fromName($data['verification_status'])
                : VerificationStatus::Pending,
            lastVerifiedAt: isset($data['last_verified_at']) && is_string($data['last_verified_at'])
                ? new DateTimeImmutable($data['last_verified_at'])
                : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'organisation_type' => $this->organisationType->value,
            'aliases' => array_values($this->aliases),
            'head' => $this->head?->toArray(),
            'jurisdiction' => $this->jurisdiction,
            'official_url' => $this->officialWebsite,
            'mandate' => $this->mandate,
            'source_links' => array_values($this->sourceLinks),
            'tier' => $this->tier->value,
            'official_status' => $this->officialStatus->value,
            'last_updated' => $this->lastUpdated?->format(DATE_ATOM),
            'abbreviation' => $this->abbreviation,
            'description' => $this->description,
            'source_reference' => $this->sourceReference,
            'verification_status' => $this->verificationStatus->value,
            'last_verified_at' => $this->lastVerifiedAt?->format(DATE_ATOM),
        ];

        return $data;
    }

    /** @return string[] */
    private static function strings(mixed $value): array
    {
        if (! is_array($value)) {
            throw new \InvalidArgumentException('Expected an array of strings.');
        }

        $result = [];
        foreach ($value as $item) {
            if (! is_string($item)) {
                throw new \InvalidArgumentException('Expected a list of strings.');
            }
            $result[] = $item;
        }

        return $result;
    }
}