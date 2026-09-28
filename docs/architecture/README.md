# CoJ-MIP Architecture

CoJ Intelligent Content & Municipal Intelligence Platform

## Project Purpose

CoJ-MIP is a municipal intelligence and content platform designed to
**discover, validate, organise, rank and present** information about
authorised City of Johannesburg sources and organisations. The executive
newsletter is **one output** of the platform — the platform itself is an
information-management and intelligence system.

Target long-term pipeline:

```
Authorised Sources
  → Content Discovery
  → Extraction
  → Validation
  → Normalisation
  → Deduplication
  → Knowledge Repository
  → Natural-Language Understanding
  → Relevance Ranking
  → Explainable Selection
  → Content Generation
  → Dashboard
  → Executive Newsletter
  → PDF
  → Audit Trail
```

## Implementation Status

**Current phase: Phase 7 — Newsletter studio (implemented).**

| Item | Status |
|------|--------|
| Laravel 12 scaffold + project identity | IMPLEMENTED (Phase 1) |
| Versioned API foundation (`/api/v1`) | IMPLEMENTED (Phase 1, ping) |
| Application architecture boundaries | IMPLEMENTED (Phase 1) |
| Configuration shell (`config/cojmip.php`) | IMPLEMENTED (Phase 1) |
| Documentation structure | IMPLEMENTED (Phase 1) |
| Domain model (Organisation, Article, Source, Repository, Verification) | IMPLEMENTED (Phase 2/3) |
| Repository contracts + JSON drivers | IMPLEMENTED (Phase 2) |
| Repository health aggregation + artisan commands | IMPLEMENTED (Phase 2/3) |
| PHPUnit regression suite | IMPLEMENTED (Phase 2/3/4/5/6/7, 159 tests) |
| CoJ dataset | IMPLEMENTED (Phase 3) |
| Search + deterministic ranking | IMPLEMENTED (Phase 4) |
| Intelligence search API + UI | IMPLEMENTED (Phase 4) |
| Exactly-three selection + explanations | IMPLEMENTED (Phase 5) |
| Selection decision history + repetition control | IMPLEMENTED (Phase 5) |
| Dashboard (metrics, health, verification, provenance, preview, history) | IMPLEMENTED (Phase 6) |
| Newsletter composition engine (topic → 3 orgs + article matching) | IMPLEMENTED (Phase 7) |
| Newsletter pages (masthead + one organisation per page, folios, footers) | IMPLEMENTED (Phase 7) |
| Newsletter layouts (executive-brief, news-insights, spotlight, magazine) | IMPLEMENTED (Phase 7) |
| Newsletter media service (logo/monogram, image/panel fallbacks) | IMPLEMENTED (Phase 7) |
| Newsletter studio UI + live web preview | IMPLEMENTED (Phase 7) |
| Exact 3-page PDF with density regeneration + validation | IMPLEMENTED (Phase 7) |
| Newsletter generation history | IMPLEMENTED (Phase 7) |
| Content discovery | PLANNED (Phase 8) |
| Verification + provenance | PLANNED (Phase 9) |
| Full testing/security/observability | PLANNED (Phase 10) |
| AI integration | PLANNED (Phase 11) |
| Scheduler + automated sync | PLANNED (Phase 12) |
| Deployment / production readiness | PLANNED (Phase 13) |

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.2 |
| Framework | Laravel 12 |
| Frontend | Blade + Tailwind CSS v4 (Vite) |
| Data (initial) | JSON files under `storage/app/data/` |
| Data (future) | Eloquent/MySQL behind the same repository contracts |
| PDF | Dompdf |
| Tests | PHPUnit 11 |
| AI (future) | Provider-agnostic (local deterministic provider first) |

## Architectural Layers

```
Presentation   Blade views + Tailwind
API            routes/api.php → app/Http/Controllers/Api/V1
Application    use-case services (one module per capability)
Domain         pure models/enums/value objects
Contracts      repository / AI / discovery ports
Infrastructure concrete drivers (JSON persistence, AI providers, adapters)
```

See `backend/app/README.md` for the layer responsibilities and dependency rule.

## Key Architectural Principles

### 1. JSON-First Repository Strategy

Phase 2 introduces repository **contracts** implemented by **JSON file
drivers**. Application services depend only on the contracts. Later, the same
contracts are implemented with Eloquent/MySQL — no business logic changes.
JSON files are modular (one file per entity type), not one giant blob.

### 2. Deterministic-First Intelligence

The core ranking, selection and explainability engine is deterministic and
works with **no external AI**. An AI provider only enhances natural-language
understanding (Phase 12) through a pluggable contract. The platform must
work identically with zero AI configuration.

### 3. Provenance-First Design

Every stored content item carries source provenance: source URL, source name,
source tier, publication/retrieval dates, content hash and verification
status. Organisations and sources additionally carry a `verification_status`
(verified / pending / unverified) with a `last_verified_at` date where
verified. The system answers "where did this come from?" and never presents
unverified information as fact.

### 4. Exactly-Three Explainable Selection

Newsletter generation selects **exactly three** relevant organisations,
based on a reproducible relevance score. Every selection stores its reasons
and is persisted to a selection decision history. If fewer than three
eligible results exist, the system reports insufficiency rather than
silently filling gaps. A repetition preference (configured penalty per
prior selection inside a rolling window, capped) balances fairness across
organisations without ever sacrificing relevance: no history means no
penalty, and an irrelevant organisation is never added just to avoid
repetition.

### 5. Content/Layout/Branding/PDF Separation

Newsletter content, layout templates, branding assets and PDF rendering are
independent layers. New layouts can be added without touching the
composition engine or branding system.

### 6. No Fabricated Information

AI-generated claims must trace back to repository data or an approved source.
Where evidence is insufficient the system says:
"Insufficient verified information available."

### 7. Phase-Gated Development

Development proceeds through approved phases (below). Phases are sequential;
each phase is validated and reported before the next begins.

## Development Phases

| Phase | Focus |
|-------|-------|
| 1 | Architecture + Scaffold (complete) |
| 2 | Domain model + repository contracts + JSON drivers (complete) |
| 3 | CoJ dataset (complete) |
| 4 | Search + deterministic ranking (complete) |
| 5 | Exactly-three selection + explainability (complete) |
| 6 | Municipal intelligence dashboard (complete) |
| 7 | Newsletter studio — newsletter engine + exact 3-page PDF (complete) |
| 8 | Content discovery architecture |
| 9 | Verification + provenance |
| 10 | Testing + security + observability |
| 11 | AI integration |
| 12 | Scheduler + automated sync |
| 13 | Deployment + production readiness |

## Security Posture (Foundation)

- Secrets live in `.env` only; `.env.example` documents required keys; `.env`
  is never committed.
- No API keys, tokens or credentials in source code.
- External webpages are treated as **data**, never as instructions
  (prompt-injection protection applies from Phase 6 onward).
- Input validation and safe content handling are phased in alongside each
  feature rather than bolted on at the end.

## Repository Structure

```
coj-intelligent-content-platform/
├── backend/            Laravel 12 application
│   ├── app/
│   │   ├── Domain/         pure domain concepts
│   │   │   ├── Organisation/  CoJOrganisation, OrganisationType/Status
│   │   │   ├── Article/       Article, ArticleStatus
│   │   │   ├── Source/        SourceTrust, SourceTier
│   │   │   ├── Shared/        VerificationStatus (verified/pending/unverified)
│   │   │   ├── Intelligence/  SearchMatch, SearchResult, SearchResponse, SelectionDecision, SelectionOutcome
│   │   │   ├── Newsletter/    NewsletterComposition, NewsletterOrganisation, NewsletterArticle, NewsletterMediaAsset, NewsletterLayout, NewsletterGenerationRecord
│   │   │   └── Repository/    RepositoryHealth, RepositoryPolicy
│   │   ├── Application/    use-case services (seeding, health, paths, intelligence, selection, newsletter)
│   │   ├── Contracts/      repository ports + intelligence/selection contracts
│   │   ├── Infrastructure/ JSON persistence drivers (gateway + repositories, incl. newsletter history)
│   │   └── Http/           API controllers + form requests
│   ├── config/cojmip.php   CoJ-MIP configuration (volume + freshness policies)
│   ├── routes/api.php      versioned API foundation
│   ├── database/seed-data/ curated CoJ dataset (entities/sources/articles)
│   ├── storage/app/data/   JSON repository volume (gitignored runtime data)
│   └── tests/              PHPUnit suites (unit + feature)
├── docs/               Documentation (architecture, requirements, api, user-guide)
└── README.md           Project overview
```