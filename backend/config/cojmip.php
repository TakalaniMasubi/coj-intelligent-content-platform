<?php

/*
|--------------------------------------------------------------------------
| CoJ-MIP Configuration Shell
|--------------------------------------------------------------------------
|
| Central configuration namespace for the CoJ Intelligent Content &
| Municipal Intelligence Platform.
|
| PHASE 1 STATUS:     This file establishes only the structural shell.
|                     Values shown are empty/feature-flag defaults and
|                     are NOT production tuning parameters.
|
| PLANNED SECTIONS (populated in later phases):
|   - source_tiers      -> verified mapping of SourceTier enum to rankings
|   - ranking_weights   -> deterministic scoring weights (Phase 4)
|   - freshness         -> freshness band thresholds (Phase 4/7)
|   - layouts           -> newsletter layout registry (Phase 9)
|   - feature_flags     -> runtime capability switches
|
| No seeding weights, thresholds or trust scores are invented here.
| Everything below is a documented placeholder for phased development.
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
    | Ranking Weights
    |--------------------------------------------------------------------------
    |
    | PLANNED (Phase 4): deterministic relevance-scoring weights.
    | Deliberately empty in Phase 1 to avoid inventing tuning values.
    |
    */
    'ranking_weights' => [],

    /*
    |--------------------------------------------------------------------------
    | Freshness Thresholds
    |--------------------------------------------------------------------------
    |
    | PLANNED (Phase 4/7): content freshness band thresholds (e.g. "fresh",
    | "needs verification", "outdated"). Deliberately empty in Phase 1.
    |
    */
    'freshness' => [],

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