# CoJ-MIP API

API documentation for the CoJ Intelligent Content & Municipal Intelligence
Platform.

**Status: PARTIAL — Phase 1 foundation only.**

Current endpoints under the versioned base `/api/v1`:

| Method   | Endpoint        | Purpose                              | Status    |
|----------|-----------------|--------------------------------------|-----------|
| `GET`    | `/api/v1/ping`  | Foundation readiness probe           | IMPLEMENTED |
| `POST`   | `/api/v1/intelligence/search` | Prompt → ranked organisations | PLANNED (Phases 4-5) |
| `POST`   | `/api/v1/newsletter/generate`  | Executive newsletter pipeline  | PLANNED (Phases 9-10) |
| `POST`   | `/api/v1/discovery/run`       | Trigger content discovery      | PLANNED (Phase 6) |
| `GET`    | `/api/v1/repository/health`   | Repository health/freshness    | PLANNED (Phases 7-8) |
| `GET`    | `/api/v1/organisations`       | CoJ organisations list         | PLANNED (Phases 2-3) |

Planned endpoints will be documented here as they are implemented (OpenAPI
specification to be included).