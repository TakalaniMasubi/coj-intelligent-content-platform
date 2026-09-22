<?php

namespace App\Application\Intelligence;

use App\Domain\Article\Article;
use App\Domain\Intelligence\SearchMatch;
use App\Domain\Organisation\CoJOrganisation;

/**
 * Builds human-readable reasons and source lists from actual matching
 * signals. Every reason corresponds to a field that genuinely matched a
 * query term in the repository; no fabricated explanations.
 */
final readonly class SearchExplainer
{
    /**
     * @param Article[] $articles
     *
     * @return string[]
     */
    public function reasons(SearchMatch $match, CoJOrganisation $organisation, array $articles): array
    {
        $reasons = [];

        foreach ($match->fieldTerms as $field => $terms) {
            $quoted = "'".implode("', '", $terms)."'";
            $reasons[] = match ($field) {
                'name' => "Matched {$quoted} in the organisation name",
                'abbreviation' => "Matched {$quoted} in the organisation abbreviation",
                'alias' => "Matched {$quoted} in an organisation alias",
                'type' => "Matched {$quoted} in the organisation type",
                'description' => "Matched {$quoted} in the organisation description",
                'article_title' => "Matched {$quoted} in an article title",
                'article_summary' => "Matched {$quoted} in an article summary",
                default => "Matched {$quoted} in {$field}",
            };
        }

        $reasons[] = match ($organisation->verificationStatus->value) {
            'verified' => $organisation->lastVerifiedAt !== null
                ? 'Organisation is VERIFIED against an official source (last verified '.$organisation->lastVerifiedAt->format('Y-m-d').')'
                : 'Organisation is VERIFIED against an official source',
            'pending' => 'Verification status is PENDING — confirm against an official source before relying on this result',
            default => 'Verification status is UNVERIFIED — no confirming source has been reviewed yet',
        };

        if ($articles !== [] || $organisation->sourceLinks !== []) {
            $count = count($this->sources($organisation, $articles));
            $reasons[] = "Information is available from {$count} repository source".($count === 1 ? '' : 's');
        }

        return $reasons;
    }

    /**
     * @param Article[] $articles
     *
     * @return string[]
     */
    public function sources(CoJOrganisation $organisation, array $articles): array
    {
        $sources = [];

        foreach ($organisation->sourceLinks as $link) {
            if (! in_array($link, $sources, true)) {
                $sources[] = $link;
            }
        }

        foreach ($articles as $article) {
            if ($article->sourceUrl !== '' && ! in_array($article->sourceUrl, $sources, true)) {
                $sources[] = $article->sourceUrl;
            }
        }

        return $sources;
    }
}