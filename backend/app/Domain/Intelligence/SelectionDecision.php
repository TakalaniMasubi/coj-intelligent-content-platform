<?php

namespace App\Domain\Intelligence;

use App\Domain\Shared\VerificationStatus;
use DateTimeImmutable;

/**
 * A single recorded selection within a top-three selection outcome.
 *
 * Carries everything needed for later auditability: what was chosen, when,
 * why (reasons from actual signals), the relevance score before and after
 * any repetition adjustment, and whether a repetition penalty applied.
 */
final readonly class SelectionDecision
{
    /**
     * @param string[]            $matchingTerms
     * @param string[]            $reasons
     */
    public function __construct(
        public string $organisationId,
        public string $organisationName,
        public int $rank,
        public int $originalScore,
        public int $adjustedScore,
        public VerificationStatus $verificationStatus,
        public array $matchingTerms,
        public array $reasons,
        public int $repetitionPenalty,
        public bool $selected,
        public DateTimeImmutable $selectedAt,
        public string $query,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! isset($data['organisation_id'], $data['organisation_name']) || ! is_string($data['organisation_id']) || ! is_string($data['organisation_name'])) {
            throw new \InvalidArgumentException('Selection decisions must include a string organisation_id and organisation_name.');
        }

        return new self(
            organisationId: $data['organisation_id'],
            organisationName: $data['organisation_name'],
            rank: isset($data['rank']) && is_int($data['rank']) ? $data['rank'] : 0,
            originalScore: isset($data['original_score']) && is_int($data['original_score']) ? $data['original_score'] : 0,
            adjustedScore: isset($data['adjusted_score']) && is_int($data['adjusted_score']) ? $data['adjusted_score'] : 0,
            verificationStatus: isset($data['verification_status']) && is_string($data['verification_status'])
                ? VerificationStatus::fromName($data['verification_status'])
                : VerificationStatus::Pending,
            matchingTerms: self::strings($data['matching_terms'] ?? []),
            reasons: self::strings($data['reasons'] ?? []),
            repetitionPenalty: isset($data['repetition_penalty']) && is_int($data['repetition_penalty'])
                ? $data['repetition_penalty']
                : 0,
            selected: (bool) ($data['final_selection'] ?? false),
            selectedAt: isset($data['selection_timestamp']) && is_string($data['selection_timestamp'])
                ? new DateTimeImmutable($data['selection_timestamp'])
                : new DateTimeImmutable(),
            query: isset($data['query']) && is_string($data['query']) ? $data['query'] : '',
        );
    }

    public function toArray(): array
    {
        return [
            'organisation_id' => $this->organisationId,
            'organisation_name' => $this->organisationName,
            'rank' => $this->rank,
            'original_score' => $this->originalScore,
            'adjusted_score' => $this->adjustedScore,
            'verification_status' => $this->verificationStatus->value,
            'matching_terms' => array_values($this->matchingTerms),
            'reasons' => array_values($this->reasons),
            'repetition_penalty' => $this->repetitionPenalty,
            'final_selection' => $this->selected,
            'selection_timestamp' => $this->selectedAt->format(DATE_ATOM),
            'query' => $this->query,
        ];
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