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
| PDF | Dompdf |
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
│   ├── resources/views/    Blade views (dashboard, intelligence, newsletter studio)
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

### Municipal intelligence search

A deterministic, explainable natural-language search runs over the curated
repository (no AI, no API keys):

```bash
cd backend
php artisan serve     # then open http://localhost:8000/intelligence
```

- **UI:** `/intelligence` — professional search screen with verification
  badges, relevance scores and "why this matched" explanations.
- **API:** `POST /api/v1/intelligence/search` with
  `{"query": "Which organisations are involved in waste management?"}`.
- Scoring is presence-based (configurable weights in `config/cojmip.php`
  under `ranking_weights`) and verification-aware: verified, pending and
  unverified records stay visibly distinct and are never conflated.
- Same repository + same query always yields the same ranking (ties broken by
  organisation name, then id).

### Explainable top-three selection

The same deterministic ranking powers a selection policy that recommends
**exactly three** municipal organisations:

```bash
cd backend
php artisan serve     # then open http://localhost:8000/intelligence
```

- **UI:** the "Select Top 3" button on `/intelligence` shows the TOP 3
  block with rank, original + adjusted relevance scores, verification status,
  repetition adjustment and "why selected" reasons built only from real
  matches.
- **API:** `POST /api/v1/intelligence/select` with the same query shape.
  A successful selection returns `{success: true, data: {original_query,
  selected_count, selections}}`; when fewer than three candidates reach the
  relevance floor it returns `{success: false, status:
  "insufficient_results", required, available, results}`.
- **Selection policy (`config/cojmip.php` → `selection`):** candidates must
  score ≥ 20; the top three are chosen in ranking order; verification status
  is preserved and exposed (a strongly relevant pending organisation can be
  selected, with the status always shown).
- **Repetition control:** each selection decision is persisted as history
  (`SelectionDecisionRepositoryContract` → JSON). A prior selection inside
  the 30-day window lowers a candidate's score by 4 points per occurrence,
  capped at 25 — no history means no penalty, and an irrelevant organisation
  is never added just to avoid repetition (strong relevance wins).
- Deterministic: same repository + query + history = same result, no
  randomness. The PHPUnit suite now has 159 tests.

### Municipal intelligence dashboard

A professional presentation layer over the existing search, selection and
repository services — no hard-coded figures, everything is computed live:

```bash
cd backend
php artisan serve     # then open http://localhost:8000/dashboard
```

- **Landing page:** `/` redirects to `/dashboard` — metric cards (organisations,
  entities, directorates, offices, verified/pending, articles, trusted
  sources), repository health, organisation type breakdown, verification
  overview, source provenance and latest repository content.
- **Top-3 explainability preview:** the dashboard runs the real selection
  pipeline for the featured query **without persisting history** (read-only
  preview reusing the exact Phase 5 scoring, repetition adjustment and
  reasons).
- **Recent Selection Decisions:** last selections grouped by query, linking to
  `/selection-history` (full auditable history with empty state).
- **Intelligence search entry:** "What municipal information are you looking
  for?" with example query chips, SEARCH and SELECT TOP 3 actions on
  `/intelligence`.
- Pending records stay visibly distinct everywhere (badges + notes); the
  dashboard renders fully without any AI provider or API key.

### Newsletter studio

A professional studio that composes a municipal newsletter for any natural
language topic and renders an **exact 3-page PDF**, entirely on the
deterministic Phase 4/5 pipeline (no AI, no API keys):

```bash
cd backend
php artisan serve     # then open http://localhost:8000/newsletter
```

- **Compose:** type any topic and the studio reuses the real selection
  pipeline as a read-only preview (history never mutated) to pick exactly the
  three most relevant organisations, then matches each to curated repository
  articles deterministically (title/subject/summary relevance; new-item then
  older-item preference). Fewer than three eligible results are reported
  honestly as "Insufficient results" and the topic form stays ready to retry —
  the newsletter never invents organisations or articles.
- **Publication-style pages, one organisation per page:** page 1 opens with
  the masthead (CoJ logo, eyebrow, edition title, seal), the “this edition”
  introduction and the top organisation; pages 2 and 3 carry a running
  header, the next organisation feature and a professional footer. Every
  feature page shows a real logo (or clean monogram), the matched article
  with headline/summary/source/date plus a large photo area (or honest neutral
  panel), and the organisation’s mandate/description when the repository has
  it. The web preview marks the page boundaries (“PAGE N OF 3”) so what you
  see matches the downloaded document exactly.
- **One composition, four layouts:** `executive-brief`, `news-insights`,
  `municipal-spotlight` and `magazine` share the same composed content; the
  layout pills switch the live web preview and the PDF without re-selecting.
- **Media honesty:** organisations resolve to a real logo when one exists,
  otherwise to a clean monogram; articles resolve to a real image or an
  honest neutral panel. Branding colours and margins are config in
  `config/cojmip.php` → `newsletter`.
- **Exact 3-page PDF:** Dompdf renders the same sheets and the result is
  validated page-by-page (`pdf.page_target = 3`, `pdf.regenerate_attempts`);
  over/under-filled layouts are regenerated at a more/less dense profile so
  the delivered PDF is always exactly three pages, one organisation per page.
  Page 3 also carries the colophon, generation stamp and edition id. The
  studio presents a prominent **"Generate & Download PDF"** button plus a
  **Regenerate Newsletter** reset that returns it to the topic step.
- **History:** downloading the PDF writes an auditable
  `newsletter_generations` record (query, layout, edition, timestamps) into
  the JSON volume; creating/styling never writes history.
- The studio renders fully without AI; the only environment requirement for
  real image embedding is the PHP GD extension (enabled in XAMPP's
  `php.ini`).

## Testing

```bash
cd backend
php artisan test
```

## Current Phase

**Phase 7 — Newsletter studio.** On top of the Phase 4/5 selection engine and
the Phase 6 dashboard, `/newsletter` is a professional studio that composes a
municipal intelligence newsletter for any topic. It reuses the real selection
pipeline read-only to pick three organisations, matches them to curated
articles deterministically, and renders one composition across four layouts
(`executive-brief`, `news-insights`, `municipal-spotlight`, `magazine`) for
both the live web preview and Dompdf. The rendered newsletter is a three-page
publication — masthead and edition introduction plus one organisation feature
per page, with folios, running headers and professional footers. PDF
generation validates the page count and regenerates at a different density
until the result is exactly three pages; downloading the PDF writes a
`newsletter_generations` history record
(query, layout, edition, timestamps) into the JSON volume. Organisations and
articles use honest media (real logo/image when available, otherwise clean
monogram/panel fallbacks), and the whole studio works with zero AI and no API
keys. The PHPUnit suite now has 163 tests. See
`docs/architecture/README.md` for the full implementation status register.