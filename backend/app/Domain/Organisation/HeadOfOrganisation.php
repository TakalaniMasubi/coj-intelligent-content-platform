<?php

namespace App\Domain\Organisation;

final readonly class HeadOfOrganisation
{
    public function __construct(
        public string $name,
        public string $title,
        public ?string $roleSummary = null,
    ) {
    }

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        if (! isset($data['name'], $data['title']) || ! is_string($data['name']) || ! is_string($data['title'])) {
            throw new \InvalidArgumentException('Organisation head entry must include a name and title.');
        }

        return new self(
            name: $data['name'],
            title: $data['title'],
            roleSummary: isset($data['role_summary']) && is_string($data['role_summary']) ? $data['role_summary'] : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'title' => $this->title,
        ];

        if ($this->roleSummary !== null) {
            $data['role_summary'] = $this->roleSummary;
        }

        return $data;
    }
}