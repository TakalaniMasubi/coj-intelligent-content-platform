# CoJ-MIP Seed Data

Curated City of Johannesburg knowledge dataset used by the
`cojmip:repository-seed` artisan command to populate the JSON-first
repository (Phase 3).

- `organisations.json` — `CoJOrganisation` rows for 13 municipal entities,
  17 directorates and the Office of the City Manager
- `sources.json` — `SourceTrust` classifications for the 14 official
  source domains referenced by the dataset
- `articles.json` — 16 foundation `Article` rows mapping entities and
  directorates to their official sources

## Provenance policy

No facts are invented. Every record carries a `source_reference` and a
`verification_status`:

- **verified** — content confirmed against an official CoJ source on the
  `last_verified_at` date
- **pending** — canonical source identified but not re-verified in the
  compilation session (e.g. City Power, Johannesburg Water, Pikitup, the
  Johannesburg Roads Agency and the municipal directorates)
- **unverified** — no confirming source yet

Directorates are individually unverified; their entries rely on the City
of Johannesburg official portal (`https://www.joburg.org.za`) as the
source reference. Official URLs point at verified domain roots; no deep
paths are fabricated. Article `content_hash` values are real sha256
hashes of the article content. Leadership (`head`) is intentionally null
until records can be verified.