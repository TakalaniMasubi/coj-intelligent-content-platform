<?php

/*
|--------------------------------------------------------------------------
| CoJ-MIP Configuration Shell
|--------------------------------------------------------------------------
|
| Central configuration namespace for the CoJ Intelligent Content &
| Municipal Intelligence Platform.
|
| PHASE 2 STATUS:     This file establishes the structural shell plus the
|                     repository volume and freshness policy defaults used by
|                     the JSON-first repository layer (Phase 2).
|
| PLANNED SECTIONS (populated in later phases):
|   - source_tiers      -> verified mapping of SourceTier enum to rankings
|   - ranking_weights   -> deterministic scoring weights (Phase 4)
|   - freshness         -> freshness band thresholds (Phase 4/7)
|   - layouts           -> newsletter layout registry (Phase 9)
|   - feature_flags     -> runtime capability switches
|
| No seeding weights or trust scores are invented here. The freshness policy
| defaults are documented, configurable operational thresholds (Phase 2).
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider Settings
    |--------------------------------------------------------------------------
    |
    | The AI layer is provider-agnostic (Phase 12). These keys are declared
    | now so the environment contract is explicit. The provider defaults to
    | the deterministic "local" implementation, which requires no API key,
    | so the platform remains fully operational without any AI service.
    |
    */
    'ai' => [
        'provider' => env('COJMIP_AI_PROVIDER', 'local'),
        'azure' => [
            'endpoint' => env('AZURE_OPENAI_ENDPOINT', ''),
            'api_version' => env('AZURE_OPENAI_API_VERSION', ''),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Source Tiers
    |--------------------------------------------------------------------------
    |
    | PLANNED (Phase 7): authoritative source hierarchy used by verification
    | and ranking. T1 = official City of Johannesburg sources; T4 = explicitly
    | approved external sources. Mapping definitions arrive with Phase 7.
    |
    */
    'source_tiers' => [],

    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    |
    | JSON-first repository volume. Override with COJMIP_REPOSITORY_VOLUME so
    | environment-specific data directories can be used without code changes
    | (e.g. tests point at tests/Fixtures/data).
    |
    */
    'repository' => [
        'volume' => env('COJMIP_REPOSITORY_VOLUME', storage_path('app/data')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ranking Weights
    |--------------------------------------------------------------------------
    |
    | Deterministic relevance-scoring weights for the intelligence search
    | engine (Phase 4). Presence-based: a repository field contributes its
    | weight ONCE per organisation when any query term occurs in it. The base
    | score is then scaled by the documented verification factor and capped at
    | `cap`.
    |
    |   name / abbreviation / alias   identifier matches (strongest)
    |   description                   stored organisation description
    |   type                          entity / directorate / office
    |   article_title / article_summary  stored article content
    |
    | Verification factors lower the score of records that are not yet
    | verified; the status itself is always surfaced separately in results.
    |
    */
    'ranking_weights' => [
        'fields' => [
            'name' => 30,
            'abbreviation' => 22,
            'alias' => 18,
            'type' => 6,
            'description' => 22,
            'article_title' => 16,
            'article_summary' => 12,
        ],
        'verification' => [
            'verified' => 1.0,
            'pending' => 0.85,
            'unverified' => 0.7,
        ],
        'cap' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Selection Policy
    |--------------------------------------------------------------------------
    |
    | Top-three selection policy (Phase 5).
    |
    |   required        exactly how many organisations a successful selection
    |                   must contain
    |   min_score       a candidate must reach this adjusted relevance score
    |                   to be eligible; fewer than `required` eligible
    |                   candidates produce an insufficient-results state and
    |                   the system never invents or duplicates organisations
    |
    | Repetition control: the persisted selection history applies a
    | configurable penalty to organisations selected recently, so the same
    | organisation does not dominate repeated requests. With no history the
    | penalty is zero — nothing is fabricated. `max_penalty` caps the
    | adjustment so strong relevance can always overcome repetition.
    |
    | Verification is handled by the Phase 4 ranking already: verified,
    | pending and unverified records remain visibly distinct and are never
    | treated as identical, while a strongly relevant pending organisation
    | remains selectable.
    |
    */
    'selection' => [
        'required' => 3,
        'min_score' => 20,
        'repetition' => [
            'enabled' => true,
            'per_selection' => 4,
            'window_days' => 30,
            'max_penalty' => 25,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Freshness Thresholds
    |--------------------------------------------------------------------------
    |
    | Operational policy defaults consumed by Domain\Repository\RepositoryPolicy
    | (Phase 2). They drive age-band classification of repository content:
    |   recent_window_days  -> "fresh" window (most recent band)
    |   stale_after_days    -> content older than this is "stale"
    |   expired_after_days  -> content older than this is "expired"
    |   archived_after_days -> content older than this is "archived"
    |
    | These are configurable defaults, not production tuning claims; they can
    | be adjusted in later phases as requirements firm up.
    |
    */
    'freshness' => [
        'recent_window_days' => 7,
        'stale_after_days' => 30,
        'expired_after_days' => 180,
        'archived_after_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Newsletter Layouts
    |--------------------------------------------------------------------------
    |
    | PLANNED (Phase 9): newsletter layout registry. The platform will
    | support multiple layouts independent of content and branding.
    |
    */
    'layouts' => [],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Runtime capability switches. Defaults safe for a local development
    | prototype; features are enabled as phases are implemented.
    |
    */
    'features' => [
        'discovery_enabled' => (bool) env('COJMIP_DISCOVERY_ENABLED', false),
        'ai_enabled' => (bool) env('COJMIP_AI_ENABLED', false),
    ],

];