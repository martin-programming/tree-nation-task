# Tree Nation — X Visits = 1 Tree

A Laravel web service that tracks customer shop visits and plants a virtual tree for every configurable number of entries. Includes a real-time public dashboard showing visit activity per hour.

---

## Architecture overview

```
Physical device  ──POST /api/visits──▶  Laravel API  ──▶  PostgreSQL
Browser          ──GET /──────────────▶  Laravel Web  ──▶  Vue 3 dashboard
```

**Stack:** PHP 8.4 · Laravel 13 · Vue 3 + TypeScript · Inertia.js · Tailwind CSS v4 · PostgreSQL 17

---

## Requirements

- [Docker](https://docs.docker.com/get-docker/) with the Compose plugin
- `make`

Everything else (PHP, Composer, Node, npm) runs inside containers.

---

## Getting started

### 1. Clone and configure

```bash
git clone <repo-url> tree-nation
cd tree-nation
cp .env.example .env
```

Edit `.env` and set at minimum:

| Variable | Description | Default |
|---|---|---|
| `DB_DATABASE` | PostgreSQL database name | `tree_nation` |
| `DB_USERNAME` | PostgreSQL user | `tree_nation` |
| `DB_PASSWORD` | PostgreSQL password | _(empty)_ |
| `VISITS_PER_TREE` | Visits required to plant one tree | `10` |

> `DB_HOST` is automatically overridden to `postgres` (the Docker service name) by `docker-compose.yml`. Leave it as `127.0.0.1` in `.env` for local non-Docker use.

### 2. Install git hooks

```bash
make hooks
```

This copies the hooks from `.hooks/` into `.git/hooks/`. The pre-commit hook runs Laravel Pint on every staged PHP file before a commit goes through.

### 3. First-time setup

```bash
make setup
```

This runs in sequence:
1. Builds the PHP image and starts all containers in detached mode
2. Generates `APP_KEY` in `.env`
3. Runs all database migrations

Three services will be running:

| Service | Description | Port |
|---|---|---|
| `app` | Laravel (php artisan serve) | 8000 |
| `vite` | Vite dev server with HMR | 5173 |
| `postgres` | PostgreSQL 17 | 5432 |

> After the first setup, use `make up` and `make migrate` independently. Migrations are not run automatically on container start — this keeps schema changes explicit and prevents accidental migrations against shared databases.

### 4. Start the Vite dev server

In a **separate terminal**, run:

```bash
make dev
```

This compiles the frontend assets and enables hot module replacement. The dashboard will not load correctly without it.

### 5. Open the dashboard

```
http://localhost:8000
```

The dashboard shows visits per hour (last 24 h), total visits today, trees planted, and number of customers.

---

## Makefile reference

| Command | Description |
|---|---|
| `make setup` | First-time setup: start containers, generate app key, run migrations |
| `make up` | Build images and start all containers |
| `make down` | Stop and remove containers |
| `make restart` | Restart all containers |
| `make logs` | Tail logs for all services |
| `make logs service=app` | Tail logs for a specific service |
| `make migrate` | Run pending database migrations |
| `make test` | Run the PHPUnit test suite |
| `make pint` | Run Laravel Pint (auto-fix mode) |
| `make dev` | Start the Vite dev server locally (outside Docker) |
| `make hooks` | Install git hooks from `.hooks/` |

---

## API reference

### Record a visit

The physical device calls this endpoint each time a customer is detected entering the shop.

```
POST /api/visits
Content-Type: application/json
```

**Request body**

| Field | Type | Required | Description |
|---|---|---|---|
| `external_id` | string | Yes | Stable unique identifier for the customer (e.g. card number, UUID) |
| `visited_at` | ISO 8601 datetime | No | Timestamp of detection. Defaults to current server time if omitted |
| `name` | string | No | Display name, stored only on first encounter |

**Example request**

```bash
curl -X POST http://localhost:8000/api/visits \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-api-key" \
  -d '{
    "external_id": "card-0042",
    "visited_at": "2026-06-11T14:30:00Z",
    "name": "Jane Doe"
  }'
```

**Success response — `201 Created`**

```json
{
  "visit_id": 1,
  "customer_id": 1,
  "visited_at": "2026-06-11T14:30:00+00:00"
}
```

**Validation error response — `422 Unprocessable Entity`**

```json
{
  "message": "The external id field is required.",
  "errors": {
    "external_id": ["The external id field is required."]
  }
}
```

---

## How the visit-to-tree logic works

1. A visit event arrives with an `external_id`.
2. The matching customer row is fetched (or created on first encounter).
3. The customer row is locked for the duration of the transaction to prevent counter drift under concurrent requests from the same device.
4. A `visits` row is inserted.
5. `total_visits` and `last_visited_at` are updated on the customer.
6. If `total_visits % VISITS_PER_TREE === 0`, `trees_planted` is incremented.

The `VISITS_PER_TREE` value is read from `config('app.visits_per_tree')` and can be changed without a code deployment — update `.env` and restart the app container.

---

## Data model

```
customers
  id              bigint PK
  external_id     varchar  UNIQUE   — identifier sent by the device
  name            varchar  nullable — optional display name
  last_visited_at timestamp nullable
  total_visits    int      default 0
  trees_planted   int      default 0
  created_at / updated_at

visits
  id          bigint PK
  customer_id bigint FK → customers.id (cascade delete)
  visited_at  timestamp              — indexed for hourly aggregation
  created_at  timestamp
```

---

## Assumptions

- **Customer identity is owned by the device.** The service treats `external_id` as an opaque string. Whatever the physical device sends (card number, UUID, barcode) is accepted without further validation of its format or meaning.
- **The dashboard is public.** No authentication is required to view visit statistics. The assignment does not specify a restricted audience for the frontend.
- **The API is authenticated via a shared secret.** `POST /api/visits` requires an `Authorization: Bearer <key>` header matching the `API_KEY` environment variable. Requests without a valid key receive a `401`. The endpoint is also rate-limited to 60 requests per minute per key (IP fallback).
- **`visited_at` is optional.** If the device omits it, the server timestamp at the time the request is received is used. This handles devices that do not maintain an accurate clock.
- **A customer's name can only be set on first encounter.** Subsequent visits for the same `external_id` do not overwrite `name`. A dedicated update endpoint would be needed to change it later.
- **Sessions, cache, and queue use the file/sync drivers.** No Redis or database-backed infrastructure is required to run the project. This simplifies the setup for a local/demo context.
- **Visits are never deleted or amended.** The `visits` table is append-only by design. Correcting a mistaken visit would require a separate admin operation outside the scope of this assignment.
