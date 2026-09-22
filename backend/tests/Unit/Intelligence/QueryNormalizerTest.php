<?php

namespace Tests\Unit\Intelligence;

use App\Application\Intelligence\QueryNormalizer;
use PHPUnit\Framework\TestCase;

class QueryNormalizerTest extends TestCase
{
    private QueryNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new QueryNormalizer();
    }

    public function test_it_lowercases_input(): void
    {
        $this->assertSame('waste management', $this->normalizer->normalize('WASTE MANAGEMENT'));
    }

    public function test_it_removes_punctuation(): void
    {
        $this->assertSame('waste management', $this->normalizer->normalize('Waste, management!?'));
    }

    public function test_it_collapses_repeated_whitespace(): void
    {
        $this->assertSame('water services', $this->normalizer->normalize("   water   services\n"));
    }

    public function test_it_extracts_useful_terms_from_a_full_question(): void
    {
        $terms = $this->normalizer->terms('Which Organisations are involved in Waste Management?');

        $this->assertSame(['waste', 'management'], $terms);
    }

    public function test_it_extracts_terms_from_a_short_query(): void
    {
        $this->assertSame(['housing', 'support'], $this->normalizer->terms('housing support'));
        $this->assertSame(['waste'], $this->normalizer->terms('waste'));
    }

    public function test_it_deduplicates_terms_keeping_first_seen_order(): void
    {
        $this->assertSame(['waste', 'management'], $this->normalizer->terms('waste waste management waste management management'));
    }

    public function test_it_returns_no_terms_for_stop_words_only(): void
    {
        $this->assertSame([], $this->normalizer->terms('Which organisations are involved in?'));
    }

    public function test_it_returns_no_terms_for_empty_input(): void
    {
        $this->assertSame([], $this->normalizer->terms(''));
        $this->assertSame([], $this->normalizer->terms('   '));
    }

    public function test_it_keeps_domain_vocabulary_searchable(): void
    {
        $terms = $this->normalizer->terms('municipal intelligence');

        $this->assertSame(['municipal', 'intelligence'], $terms);
    }

    public function test_normalisation_is_deterministic(): void
    {
        $a = $this->normalizer->normalize('Which Organisations handle Waste, Management?');
        $b = $this->normalizer->normalize('Which Organisations handle Waste, Management?');

        $this->assertSame($a, $b);
    }
}