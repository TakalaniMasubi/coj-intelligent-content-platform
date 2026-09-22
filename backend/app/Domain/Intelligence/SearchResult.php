<?php

namespace App\Domain\Intelligence;

use App\Domain\Shared\VerificationStatus;

final readonly class SearchResult
{
    /**
     * @param string[] $matchingTerms
     * @param string[] $reasons
     * @param string[] $sources
     */
    public function __construct(
        public string $organisationId,
        public string $organisationName,
        public string $organisationType,
        public int $score,
        public int $rank,
        public array $matchingTerms,
        public array $reasons,
        public VerificationStatus $verificationStatus,
        public array $sources,
    ) {
    }

    public function toArray(): array
    {
        return [
            'organisation_id' => $this->organisationId,
            'organisation_name' => $this->organisationName,
            'organisation_type' => $this->organisationType,
            'score' => $this->score,
            'rank' => $this->rank,
            'matching_terms' => array_values($this->matchingTerms),
            'reasons' => array_values($this->reasons),
            'verification_status' => $this->verificationStatus->value,
            'sources' => array_values($this->sources),
        ];
    }
}