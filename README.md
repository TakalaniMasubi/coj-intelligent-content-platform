# CoJ Intelligent Content & Municipal Intelligence Platform (CoJ-MIP)

A municipal intelligence and content platform designed to **discover,
validate, organise, rank and present** information about authorised City of
Johannesburg sources and organisations.

The executive newsletter is **one output** of the platform — the platform
itself is an intelligent municipal information-management system with
content discovery, source verification, provenance tracking, relevance
ranking, explainable selection, newsletter generation and repository health
monitoring.

## Overview

```
Authorised Sources → Content Discovery → Extraction → Validation →
Normalisation → Deduplication → Knowledge Repository → Natural-Language
Understanding → Relevance Ranking → Explainable Selection → Content
Generation → Dashboard → Executive Newsletter → PDF → Audit Trail
```

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | Blade, Tailwind CSS v4, Vite |
| Data (initial) | JSON under `backend/storage/app/data/` |
| Data (future) | Eloquent/MySQL behind repository contracts |
| PDF (future) | Dompdf |
| Tests | PHPUnit 11 |

## Project Structure

```
coj-intelligent-content-platform/
├── backend/            Laravel 12 application
│   ├── app/
│   │   ├── Domain/         pure domain concepts (per bounded context)
│   │   ├── Application/    use-case services
│   │   ├── Contracts/      repository / AI / discovery ports
│   │   ├── Infrastructure/ concrete drivers (JSON persistence, etc.)
│   │   └── Http/           API controllers
│   ├── config/cojmip.php   CoJ-MIP configuration (volume + freshness policies)
│   ├── routes/api.php      versioned API foundation (/api/v1)
│   ├── database/seed-data/ curated CoJ dataset (entities/sources/articles)
│   └── storage/app/data/   JSON repository volume (gitignored runtime data)
├── docs/
│   ├── architecture/    architecture documentation
│   ├── requirements/    requirements baseline (planned)
│   ├── api/             API documentation
│   └── user-guide/      user documentation (planned)
└── README.md
```

## Architectural Principles

- **JSON-first, contract-backed persistence** — repository contracts now,
  Eloquent/MySQL later, without rewriting business logic.
- **Deterministic-first intelligence** — ranking/selection works with zero
  AI; AI (Phase 12) is a pluggable enhancement, not a dependency.
- **Provenance-first** — every content item tracks its source, trust tier,
  retrieval/source dates and verification status.
- **Exactly-three explainable selection** — newsletter generation selects
  exactly three relevant organisations and records the reasons, or reports
  insufficient evidence.
- **No fabricated information** — generated claims trace to repository
  data; where evidence is missing the system says "Insufficient verified
  information available."
- **Phase-gated development** — each phase is implemented, validated and
  reported before the next begins.

## Development Phases

| Phase | Focus |
|-------|-------|
| 1 | Architecture + Scaffold (complete) |
| 2 | Domain model + repository contracts + JSON drivers (complete) |
| 3 | CoJ dataset (complete) |
| 4 | Search + deterministic ranking |
| 5 | Exactly-three selection + explainability |
| 6 | Content discovery architecture |
| 7 | Verification + provenance |
| 8 | Professional dashboard |
| 9 | Newsletter engine |
| 10 | Exact 3-page PDF |
| 11 | Testing + security + observability |
| 12 | AI integration |
| 13 | Scheduler + automated sync |
| 14 | Deployment + production readiness |

Features are only claimed as implemented when truly delivered; the
architecture (`docs/architecture/README.md`) maintains an explicit
IMPLEMENTED vs PLANNED register.

## Setup (Local Development)

Requirements: PHP 8.2, Composer 2.x, Node.js (for frontend assets).

```bash
cd backend

# Install backend dependencies
composer install

# Configure environment
copy .env.example .env        # (Windows)   /   cp .env.example .env  (macOS/Linux)
php artisan key:generate

# Install frontend dependencies and build assets (when needed)
npm install
npm run build

# Run the development server
php artisan serve             # http://localhost:8000
```

The SQLite database is created automatically by the scaffold. No migration
changes are required in Phases 1-2.

### Repository commands

```bash
cd backend

# Seed the JSON repository from the curated database/seed-data dataset
php artisan cojmip:repository-seed

# Show aggregated repository health
php artisan cojmip:repository-health
```

The repository volume defaults to `backend/storage/app/data`; point
`COJMIP_REPOSITORY_VOLUME` elsewhere to use a different data directory.

## Testing

```bash
cd backend
php artisan test
```

## Current Phase

**Phase 3 — Curated CoJ dataset.** The platform domain model now also
includes `VerificationStatus` (verified / pending / unverified) with
verification metadata on organisations and source-trust records.
`database/seed-data/` holds the curated City of Johannesburg dataset:
13 municipal entities, 17 directorates, the Office of the City Manager,
14 official source records and 16 foundation articles. Entities and sources
verified against official domains are marked verified with `last_verified_at`;
directorates and not-yet-re-verified entities are marked pending. Repository
health (`cojmip:repository-health`) reports organisations by type and by
verification status. The PHPUnit suite has grown to 47 tests. See
`docs/architecture/README.md` for the full implementation status register.