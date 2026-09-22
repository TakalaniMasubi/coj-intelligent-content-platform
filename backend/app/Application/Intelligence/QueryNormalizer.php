<?php

namespace App\Application\Intelligence;

/**
 * Deterministic, rule-based query normalisation and term extraction.
 *
 * No NLP libraries and no AI. Lowercasing, punctuation removal, whitespace
 * collapsing, tokenisation and stop-word removal are the only operations.
 */
final readonly class QueryNormalizer
{
    /**
     * Common English function/question words removed during term extraction.
     * Domain vocabulary (e.g. "municipal", "intelligence") is deliberately
     * NOT in this list so it remains searchable.
     */
    public const STOP_WORDS = [
        'a', 'all', 'an', 'and', 'any', 'are', 'as', 'at', 'be', 'by', 'can',
        'could', 'currently', 'did', 'do', 'does', 'for', 'from', 'get',
        'give', 'has', 'have', 'how', 'i', 'in', 'involved', 'is', 'it',
        'its', 'list', 'me', 'of', 'on', 'or', 'organisations', 'please',
        'related', 'responsible', 'show', 'tell', 'that', 'the', 'their',
        'there', 'these', 'them', 'then', 'they', 'to', 'us', 'want', 'we',
        'what', 'when', 'where', 'which', 'who', 'why', 'will', 'with',
        'within', 'would', 'you', 'your',
    ];

    public function normalize(string $query): string
    {
        $query = mb_strtolower(trim($query));
        $query = preg_replace('/[^a-z0-9\s]+/u', ' ', $query) ?? '';
        $query = preg_replace('/\s+/u', ' ', trim($query)) ?? '';

        return trim($query);
    }

    /**
     * Tokens from a query with stop words removed, deduplicated and kept in
     * first-seen order (deterministic).
     *
     * @return string[]
     */
    public function terms(string $query): array
    {
        $normalised = $this->normalize($query);

        if ($normalised === '') {
            return [];
        }

        $terms = [];
        $seen = [];
        foreach (explode(' ', $normalised) as $token) {
            if (mb_strlen($token) < 2) {
                continue;
            }
            if (in_array($token, self::STOP_WORDS, true)) {
                continue;
            }
            if (isset($seen[$token])) {
                continue;
            }
            $seen[$token] = true;
            $terms[] = $token;
        }

        return $terms;
    }
}