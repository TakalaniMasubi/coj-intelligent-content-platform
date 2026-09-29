# CoJ-MIP Requirements — Baseline

Requirements baseline for the CoJ Intelligent Content & Municipal Intelligence
Platform (CoJ-MIP).

**Status: BASELINED (Phase 7).** This document records the requirements
established and validated through Phase 7 (newsletter studio). Every claim
below is grounded in the implemented system — repository, ranking, selection,
dashboard and newsletter — and is verified by the PHPUnit suite
(163 tests / 1066 assertions). Requirements for later phases (content
discovery, verification workflow automation, AI, deployment) are listed as
future and intentionally not implied to be implemented.

---

## 1. Problem Statement and Objectives

### 1.1 Problem statement

Decision makers in the City of Johannesburg need a reliable, auditable way to
find which municipal organisation is responsible for a topic, why that
organisation is the right answer, and to turn the answer into a professional
publication. The information is scattered across many official sources, the
relevance of any single answer is hard to justify, and generated publications
often contain invented or unverified content.

### 1.2 Objectives

1. Provide a single curated knowledge repository of City of Johannesburg
   organisations, their mandates, their official sources and relevant
   articles — built exclusively from authorised sources, never from
   fabricated content.
2. Answer natural-language questions deterministically: any user can see which
   organisations matched, why they matched, and how much they score.
3. Support an exactly-three explainable organisational selection with a full
   decision history and built-in repetition control.
4. Surface repository health, provenance and verification status prominently
   so unverified information is never presented as fact.
5. Compose a professional municipal newsletter for any topic, render it as an
   **exact 3-page PDF** in one of four layouts, and record an audit trail of
   every PDF generation.
6. Work entirely without external AI or API keys in the baseline; any later AI
   enhancement must be optional and pluggable.

### 1.3 Scope

**In scope (Phases 1–7):** domain model, JSON-first repository with contracts,
curated CoJ dataset, deterministic relevance ranking and explainable top-three
selection with history, municipal intelligence dashboard, newsletter studio
(composition, four layouts, media honesty, exact 3-page PDF, generation
history).

**Out of scope / future (Phases 8–13):** automated content discovery and
extraction, mechanised verification workflow, production testing/security/
observability hardening, AI integration, scheduled synchronisation, and
deployment. Requirements for these are named in Section 9 as future
requirements and are not yet binding.

---

## 2. Functional Requirements

Identifier scheme: `FR-<n>`.

### 2.1 Repository and data

| ID | Requirement |
|----|-------------|
| FR-01 | The system SHALL store organisations, articles and source-trust records in a JSON-first repository behind repository contracts, so the storage implementation can be replaced (e.g. by Eloquent/MySQL) without changing business logic. |
| FR-02 | Each organisation SHALL carry an id, name, type, aliases, head, jurisdiction, official website, mandate, source links, tier, official status, last-updated date, abbreviation, description, source reference, verification status and last-verified date. |
| FR-03 | Each organisation type SHALL be exactly one of `entity`, `directorate` or `office`. |
| FR-04 | Each article SHALL carry an id, source name, source URL, title, summary, subject, linked organisation ids, tier, status, published date, retrieved date, content hash and archived date. |
| FR-05 | Each source-trust record SHALL carry a source name, tier, display name, category, notes, active flag, source type, URL, canonical URL, verification status and verified date. |
| FR-06 | The system SHALL provide a seed process that loads the curated dataset from `database/seed-data` into the repository volume, exposing an organisations/articles/sources count. |
| FR-07 | The system SHALL reject missing files, malformed JSON and missing data keys with explicit typed exceptions rather than silent partial loads. |
| FR-08 | The system SHALL provide a repository-health aggregate (counts, status distribution, freshness bands, verification breakdown, issues) computed live from the repository at query time. |

### 2.2 Intelligence search and ranking

| ID | Requirement |
|----|-------------|
| FR-09 | A natural-language query SHALL be normalised (stop-word removal, case folding, tokenisation) and matched against organisations and their articles. |
| FR-10 | Relevance SHALL be scored deterministically from configurable field weights (name, abbreviation, alias, type, description, article title, article summary), scaled by a verification factor and capped at a maximum score. |
| FR-11 | The same repository and query SHALL always produce the same ranking, with deterministic tie-breaking (organisation name, then id). |
| FR-12 | Every result SHALL expose the verification status, the matched terms, and human-readable reasons for the score. |
| FR-13 | Results SHALL list their contributing organisation sources (official websites and linked articles). |

### 2.3 Exactly-three selection

| ID | Requirement |
|----|-------------|
| FR-14 | A successful selection SHALL return exactly three relevant organisations, ranked. |
| FR-15 | A candidate SHALL be eligible only when its adjusted relevance score reaches a configurable minimum; if fewer than the required number of candidates are eligible, the system SHALL report an `insufficient_results` outcome and SHALL NOT invent, pad or duplicate organisations. |
| FR-16 | Selection SHALL apply a repetition penalty to organisations selected repeatedly within a rolling window, capped so strong relevance can always overcome repetition; with no history the penalty SHALL be zero. |
| FR-17 | Every decision SHALL record the organisation, rank, original and adjusted score, verification status, matched terms, reasons, repetition penalty and timestamps. |
| FR-18 | A selection SHALL be persistable to history, and a non-persisting preview SHALL be available for read-only contexts (e.g. dashboard). |

### 2.4 Dashboard

| ID | Requirement |
|----|-------------|
| FR-19 | The landing page SHALL be the dashboard, rendering live (never hard-coded) metrics: organisation/entity/directorate/office counts, verified/pending counts, article count, trusted-source count and repository health. |
| FR-20 | The dashboard SHALL show a top-three explainability preview for a featured query, running the real selection pipeline without persisting history. |
| FR-21 | The dashboard SHALL surface recent selection decisions grouped by query and link to the full auditable selection history. |

### 2.5 Newsletter studio

| ID | Requirement |
|----|-------------|
| FR-22 | A topic SHALL be composed by reusing the selection pipeline as a read-only preview to pick exactly the three most relevant organisations; composition SHALL derive all editorial content exclusively from the curated repository. |
| FR-23 | Fewer than three eligible organisations SHALL be reported honestly as insufficient results, leaving the topic form ready to retry. |
| FR-24 | Each selected organisation SHALL be matched to repository articles deterministically (title/subject/summary term overlap, then verification, then recency, then id), up to a configurable article limit. |
| FR-25 | One composition SHALL be renderable across four layouts — `executive-brief`, `news-insights`, `municipal-spotlight`, `magazine` — sharing the same content, one organisation per page, exactly three pages. |
| FR-26 | Organisations SHALL render a real logo when a file exists and a deterministic monogram fallback otherwise; articles SHALL render a real image or an honest neutral panel fallback. |
| FR-27 | The rendered PDF SHALL be exactly the configured page target (3 pages) and SHALL be validated page-by-page, regenerating at a tighter/looser density up to a configured attempt cap when the count does not match. |
| FR-28 | Downloading the PDF SHALL write an auditable generation record (record id, topic, layout, organisations, article count, PDF status, validated page count, message, generation timestamp); composing and styling SHALL NOT write history. |

### 2.6 Accessibility and administration

| ID | Requirement |
|----|-------------|
| FR-29 | Dashboards and studio screens SHALL expose selection history with a clear empty state when no decisions exist. |
| FR-30 | The system SHALL expose versioned API endpoints for ping, intelligence search and intelligence selection. |
| FR-31 | Console commands SHALL expose repository seeding and repository-health inspection. |

---

## 3. Non-Functional Requirements

Identifier scheme: `NFR-<n>`.

| ID | Category | Requirement |
|----|----------|-------------|
| NFR-01 | Determinism | Ranking, selection, article matching and PDF generation SHALL be deterministic: identical input (repository, query, history) yields identical output. |
| NFR-02 | No fabricated content | The system SHALL never invent organisations, articles, facts or claims; where evidence is insufficient it SHALL say so explicitly. |
| NFR-03 | Provenance | Every stored content item SHALL carry source provenance: source URL, source name, source tier, publication/retrieval dates, content hash and verification status. |
| NFR-04 | Verification transparency | Verified, pending and unverified records SHALL remain visibly distinct in results and SHALL never be conflated or presented as fact. |
| NFR-05 | Security | Secrets SHALL live in `.env` only; no API keys, tokens or credentials SHALL appear in source code. |
| NFR-06 | Data-as-data | External webpages SHALL be treated as data, never as instructions (prompt-injection protection). |
| NFR-07 | Performance | Dashboard and intelligence pages SHALL render fully without any AI provider or API key and SHALL return promptly for the curated dataset size. |
| NFR-08 | Portability | Repository contracts SHALL allow swapping the JSON drivers for another persistence implementation without business-logic changes. |
| NFR-09 | Auditability | Selection decisions and newsletter generations SHALL be persisted and inspectable. |
| NFR-10 | Media | PDF rendering SHALL use local and official image assets only (remote resource loading disabled), with honest fallbacks. |
| NFR-11 | Configurability | Ranking weights, selection policy, freshness thresholds, layouts, branding and PDF settings SHALL be configuration-driven. |
| NFR-12 | Testability | The system SHALL maintain a PHPUnit regression suite covering unit and feature behaviour (currently 163 tests / 1066 assertions, all passing). |

---

## 4. Use Cases

### UC-1: Search municipal responsibility
**Actor:** Municipal officer, researcher.
**Trigger:** The user asks which organisation handles a topic.
**Flow:** Enter a natural-language query → system normalises tokens → scores and
ranks organisations → returns ranked results with score, matched terms,
verification status and reasons.
**Success:** Ordered, explained results whose provenance is visible.

### UC-2: Select the top three organisations
**Actor:** Newsletter editor.
**Flow:** Run a selection for a topic → system applies the eligibility minimum
and repetition penalty → returns exactly three decisions with reasons → editor
saves the selection (persisted to history).
**Alternate:** fewer than three eligible → honest `insufficient_results`
outcome, nothing invented.

### UC-3: Inspect selection history
**Actor:** Decision maker, auditor.
**Flow:** Open the selection history page → decisions grouped by query with
empty-state handling.

### UC-4: Compose a newsletter for a topic
**Actor:** Newsletter editor.
**Flow:** Enter a topic in the studio → system selects three organisations via
the preview pipeline and matches repository articles → editor reviews the
web preview and switches layouts.

### UC-5: Generate an exact 3-page PDF
**Actor:** Newsletter editor.
**Flow:** Choose a layout → request PDF → system renders one organisation per
page → validates the page count → regenerates at a different density if needed
→ returns a validated 3-page PDF and writes a generation record.

### UC-6: Monitor repository health
**Actor:** Administrator.
**Flow:** Open the dashboard or run `cojmip:repository-health` → system
computes live counts, freshness and verification breakdown → surfaces any
issues with a healthy/degraded status.

### UC-7: Unrestricted zero-AI operation
**Actor:** Any user.
**Flow:** Use search, selection, dashboard and newsletter with no AI provider
configured.
**Success:** Full functionality; no API keys required.

---

## 5. User Stories

| ID | As a... | I want to... | So that... |
|----|---------|--------------|------------|
| US-01 | decision maker | ask which organisation answers for a topic | I get a defensible answer, not a guess. |
| US-02 | decision maker | see why an organisation matched | I can trust the ranking. |
| US-03 | decision maker | see whether a record is verified | I never mistake unverified content for fact. |
| US-04 | editor | get exactly three relevant organisations with reasons | my newsletter features the right players. |
| US-05 | editor | rely on honest insufficient-results handling | the system never invents an organisation. |
| US-06 | auditor | review past selections and penalties | I can justify and trace every choice. |
| US-07 | editor | compose a newsletter from a topic | I avoid manually assembling sources. |
| US-08 | editor | switch newsletter layouts without re-selecting | I can restyle without re-doing the analysis. |
| US-09 | editor | download an exactly-three-page PDF | the printed document matches what I saw. |
| US-10 | administrator | see repository health at a glance | stale, expired or unverified content is visible. |
| US-11 | user | use the platform with no API keys | the tool works offline and without vendor cost. |

---

## 6. Business Rules

Identifier scheme: `BR-<n>`.

| ID | Rule |
|----|------|
| BR-01 | A newsletter composition SHALL contain exactly three organisations when successful. |
| BR-02 | Organisations scoring below the selection minimum (20 by default) SHALL NOT be eligible for selection. |
| BR-03 | The system SHALL NOT add an organisation just to reach three; insufficiency SHALL be reported instead. |
| BR-04 | Repetition penalties (4 points per prior selection, 30-day window, max 25) SHALL apply only when prior selection history exists. |
| BR-05 | Strong relevance SHALL always be able to overcome the repetition cap. |
| BR-06 | The PDF page target SHALL be exactly 3 pages with up to 4 regeneration attempts at different densities. |
| BR-07 | Composition and layout switching SHALL NOT write history; only PDF download SHALL write a generation record. |
| BR-08 | All editorial text in a newsletter SHALL be derived from the curated repository. |
| BR-09 | Verification status SHALL be displayed alongside every organisation, decision and selection. |
| BR-10 | Deterministic tie-breaking SHALL order results by score, then organisation name, then id. |
| BR-11 | Freshness classification SHALL follow configured bands: recent ≤7 days, stale ≤30, expired ≤180, archived ≤365. |
| BR-12 | The landing route SHALL redirect to the dashboard. |

---

## 7. Source Trust and Data Governance Requirements

| ID | Requirement |
|----|-------------|
| DG-01 | Every source SHALL be classified into a tier (Tier 1 = official City of Johannesburg sources; higher tiers = approved external sources), recorded in the source-trust table. |
| DG-02 | The curated dataset SHALL reference only authorised domains as source URLs; deep paths SHALL NOT be fabricated. |
| DG-03 | Every organisation and article SHALL carry a `source_reference` and verification status instead of unsourced claims. |
| DG-04 | Article `content_hash` values SHALL be real sha256 hashes of the article content, enabling change detection. |
| DG-05 | Official websites SHALL point at verified domain roots; unverified records SHALL remain visibly labelled unverified. |
| DG-06 | Verification statuses (verified/pending/unverified) SHALL be distinct and never conflated in ranking or display. |
| DG-07 | Stale, expired and archived content SHALL be classifiable via freshness policy thresholds. |
| DG-08 | Repository health SHALL report counts by source verification and freshness so governance gaps are visible. |

### Current governance state (Phase 3–7 dataset)

- 31 organisations: 13 entities, 17 directorates, 1 office; 11 verified, 20 pending.
- 17 articles (all Tier 1 source references): 9 verified, 8 unverified; content hashes are real sha256 values.
- 14 trusted sources, all Tier 1 and active: 10 verified, 4 pending.
- Leadership (`head`) is intentionally null until records can be verified; no fabricated leadership claims.

---

## 8. POPIA / Privacy Considerations

| ID | Requirement |
|----|-------------|
| PP-01 | CoJ-MIP SHALL publish only public, official City of Johannesburg information; it SHALL NOT collect or store personal information of individuals. |
| PP-02 | In the event of later personal-data processing, the system SHALL comply with the Protection of Personal Information Act 4 of 2013 (POPIA): lawful purpose, minimality, purpose specification, retention limits and security safeguards. |
| PP-03 | The platform SHALL treat external webpages as data, not instructions, protecting against prompt-injection of fabricated claims. |
| PP-04 | API keys and provider credentials SHALL be held only in `.env`, never in code or committed configuration. |
| PP-05 | Access to any future administrative data SHALL use authenticated, role-scoped endpoints. |

---

## 9. Future Requirements (Phases 8–13)

The following are named for traceability but are **not** yet binding or
implemented:

- **Phase 8 — Content discovery:** automated discovery/extraction/normalisation/
  deduplication of authorised sources into the repository.
- **Phase 9 — Verification + provenance:** mechanised verification workflow with
  stronger provenance evidence (verification evidence fields, review state).
- **Phase 10 — Testing/security/observability:** hardening, penetration
  testing, logging/metrics.
- **Phase 11 — AI integration:** provider-agnostic natural-language
  understanding via the `Contracts\AI` port (local deterministic provider
  first); zero-AI operation SHALL remain fully functional.
- **Phase 12 — Scheduler + automated sync:** scheduled discovery and freshness
  synchronisation.
- **Phase 13 — Deployment:** production readiness, infrastructure, backup and
  disaster recovery.

Natural-language discovery workflow and end-user/administrator guides are
maintained in `docs/user-guide/`; implemented API endpoints are documented in
`docs/api/`.