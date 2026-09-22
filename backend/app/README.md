# CoJ-MIP Application Structure

This document defines the architectural boundaries of the CoJ-MIP backend.

All feature logic belongs to a clearly named layer. Boundaries below are
established in **Phase 1**; most are PLANNED (empty) and filled in during
later phases.

## Layered Architecture

```
Presentation      Blade views + Tailwind (resources/views)
API               HTTP layer: routes/api.php + app/Http/Controllers/Api/V1
Application       Use-case orchestration services (one module per capability)
Domain            Pure domain models/enums/value objects (no framework dependencies)
Contracts         Interfaces (Ports):
                    Contracts\Repositories   repository port (JSON now, Eloquent/MySQL later)
                    Contracts\AI             AI provider port (pluggable providers)
                    Contracts\Discovery       discovery source-adapter port
Infrastructure    Concrete adapters:
                    Infrastructure\Persistence\Json   JSON repository drivers
                    Infrastructure\AI                 AI provider implementations
                    Infrastructure\Discovery          discovery source adapters
```

## Layer Responsibilities

### `Domain/`
Pure domain concepts for each bounded context:
- `Organisation` – CoJ organisations (entities, directorates, offices; extensible to
  departments, programmes, initiatives). Core concept: `CoJOrganisation` (never "business").
- `Article` – articles/announcements with content metadata
- `Source` – source records with provenance and trust tier
- `Newsletter` – newsletter generation and layout concepts
- `Intelligence` – ranking/selection/explainability concepts
- `Verification` – verification-lifecycle concepts
- `Shared` – widely reused primitives (enums, value objects)

**PLANNED (Phases 2–5).** No domain classes exist yet.

### `Application/`
Application services orchestrate use cases, depending on **contracts**, never on
concrete drivers:
- `Intelligence` – intent analysis, ranking, selection, explainability
- `Discovery` – content discovery orchestration
- `Verification` – source verification and provenance
- `Newsletter` – newsletter composition
- `Repository` – knowledge-repository administration
- `Health` – repository health / freshness reporting

**PLANNED (Phases 2–10).** No application services exist yet.

### `Contracts/`
Ports/interfaces only:
- `Contracts\Repositories` – e.g. `CoJOrganisationRepository`, `ArticleRepository`,
  `SourceRepository`, `NewsletterRepository`
- `Contracts\AI` – provider-agnostic AI interface
- `Contracts\Discovery` – source adapter interface

**Boundary declared in Phase 1; interfaces implemented Phase 2.**

### `Infrastructure/`
Concrete implementations behind contracts:
- `Infrastructure\Persistence\Json` – JSON file repository drivers (Phase 2).
  A future Eloquent/MySQL implementation will satisfy the same contracts.
- `Infrastructure\AI` – `LocalProvider` (deterministic, no API key), plus
  `AzureOpenAIProvider` / `OpenAIProvider` (Phase 12).
- `Infrastructure\Discovery` – source adapters (Phase 6).

## Data Storage

JSON data files live under `storage/app/data/` (per-entity files such as
`organisations.json`, `articles.json`). **PLANNED (Phases 2–3).** The
directory exists to anchor the JSON-first persistence strategy.

## Dependency Rule

- `Domain` depends on nothing.
- `Application` depends on `Contracts` and `Domain`.
- `Infrastructure` implements `Contracts`.
- No layer reaches directly into another layer's internals.