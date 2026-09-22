<?php

namespace App\Domain\Source;

final readonly class SourceTrust
{
    public function __construct(
        public string $sourceName,
        public SourceTier $tier,
        public ?string $displayName = null,
        public ?string $category = null,
        public string $notes = '',
        public bool $active = true,
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
        );
    }

    public function toArray(): array
    {
        $data = [
            'source_name' => $this->sourceName,
            'tier' => $this->tier->value,
            'display_name' => $this->displayName,
            'category' => $this->category,
            'notes' => $this->notes,
            'active' => $this->active,
        ];

        return $data;
    }
}