# Dynamic Reporting System

Dynamic Reporting System is a full-stack reporting platform that lets teams ingest CSV data, index it in Solr, explore it in a React dashboard, save report configurations, receive realtime refreshes, and schedule report emails.

This README is written as an onboarding guide for junior developers. The goal is not only to show how to run the project, but also to explain how the main workflows and features fit together.

## What This Project Does

At a high level, the system supports these business flows:

1. A CSV file is uploaded or ingested.
2. The data is indexed into Solr.
3. The frontend queries Solr through a PHP API.
4. Users filter, sort, chart, export, and save report views.
5. Realtime events notify the UI when new data is indexed.
6. Admins can schedule report emails that are generated and delivered by a background worker.

## Main Workflows

### 1. Data ingestion workflow

There are two supported ways data enters the system:

1. Kafka pipeline:
   - A CSV is sent to Kafka by the producer.
   - The Kafka consumer reads the records.
   - The consumer indexes them into Solr.

2. Admin upload from the UI:
   - An admin uploads a CSV from the frontend.
   - The PHP backend parses the file.
   - The backend indexes the data directly into Solr.
   - A realtime event is broadcast so connected users see fresh data.

### 2. Report viewing workflow

1. The frontend loads the schema from the backend.
2. The user builds filters, picks visible columns, and optionally chooses compare mode.
3. The frontend sends a report payload to `/api/reports/query`.
4. The PHP backend converts the payload into a Solr query.
5. Solr returns matching documents and facet counts.
6. The frontend renders:
   - a table
   - charts
   - comparison data
   - filter dropdown values

### 3. Saved views workflow

1. Admin users can save the current report configuration.
2. The configuration is stored in MySQL.
3. Viewers can only apply saved views, not create or delete them.
4. Applying a saved view restores filters, sorting, date range, and selected columns.

### 4. Export workflow

1. The user clicks CSV export.
2. The backend runs a full Solr export using cursor-based pagination.
3. The CSV is streamed back to the browser.

### 5. Realtime update workflow

1. The frontend opens a WebSocket connection to the realtime service.
2. When new data is indexed, the backend publishes a realtime event.
3. Connected clients receive the event.
4. The frontend invalidates report-related queries and refreshes the data.

### 6. Scheduled report workflow

1. An admin creates a scheduled report from the current report state.
2. The schedule is stored in MySQL.
3. A background worker checks schedules every minute.
4. If a schedule is due, the worker generates a CSV export.
5. The worker emails the CSV through SMTP.
6. In local development, MailHog captures those emails for testing.

## Architecture Overview

```text
CSV
  -> Kafka Producer (optional path)
  -> Kafka
  -> Kafka Consumer
  -> Solr
  -> PHP Backend API
  -> React Frontend

Admin CSV Upload
  -> PHP Backend API
  -> Solr
  -> Realtime WebSocket Service
  -> React Frontend refresh

Scheduled Report
  -> MySQL scheduled_reports table
  -> Scheduled Reports Worker
  -> Solr export
  -> SMTP
  -> MailHog (local dev)
```

## Services In Docker

The project uses Docker Compose. Each service has a clear role:

| Service | Purpose |
|---|---|
| `react-frontend` | React UI |
| `php-backend` | REST API, auth, filters, export, upload |
| `solr` | Search and faceting engine |
| `mysql` | Users, saved views, scheduled reports |
| `redis` | Cache for Solr responses |
| `kafka` | Message broker for ingestion pipeline |
| `kafka-producer` | Sends CSV rows into Kafka |
| `kafka-consumer` | Reads Kafka records and indexes Solr |
| `realtime-server` | WebSocket relay for live refresh |
| `scheduled-reports-worker` | Cron-style loop for due schedules |
| `mailhog` | Local email inbox for testing scheduled emails |

## Local URLs

| Service | URL |
|---|---|
| Frontend | http://localhost:3000 |
| PHP API | http://localhost:8080/api |
| Solr Admin | http://localhost:8983/solr/#/report_data |
| MailHog | http://localhost:8025 |
| Realtime WebSocket | ws://localhost:3001 |

## Quick Start

### Prerequisites

- Docker Desktop
- Docker Compose v2

### Setup

```bash
git clone <your-repo-url>
cd reporting-system
copy .env.example .env
docker compose up -d
```

Open:

- Frontend: `http://localhost:3000`
- MailHog: `http://localhost:8025`

### Demo credentials

- Admin: `admin@demo.com` / `password`
- Viewer: `viewer@demo.com` / `password`

## Environment Variables

Important variables from `.env.example`:

### Core services

- `MYSQL_*` for database connection
- `SOLR_*` for Solr collection connection
- `REDIS_*` for cache
- `KAFKA_*` for ingestion pipeline
- `JWT_*` for login token signing

### Frontend and realtime

- `VITE_API_URL` points the frontend to the PHP API
- `VITE_WS_URL` points the frontend to the WebSocket server
- `REALTIME_BROADCAST_URL` is used by the backend to push realtime events

### Scheduled reports

- `SMTP_HOST`
- `SMTP_PORT`
- `SMTP_FROM`
- `SCHEDULE_WORKER_INTERVAL_SECONDS`
- `SCHEDULE_ATTACHMENT_MAX_BYTES`

In local development, `SMTP_HOST=mailhog` and `SMTP_PORT=1025` are correct.

## Important Features Explained

### Authentication and roles

Users authenticate through JWT.

- `admin`
  - can upload CSV files
  - can save and manage views
  - can create scheduled reports

- `viewer`
  - can apply saved views
  - cannot create or delete saved views
  - cannot manage schedules

### Dynamic schema

The frontend does not hardcode all report fields. It asks the backend for a schema, and the backend reads Solr field information to build that schema dynamically.

This means:

- new Solr fields can appear in the UI
- filter controls can adapt to field types
- columns can be chosen dynamically

### Filters

There are two filter areas:

1. Toolbar filters:
   - date range
   - compare mode

2. Filter builder:
   - grouped rules
   - AND/OR logic
   - text, dropdown, range, date, boolean types

The backend converts these filters into Solr query parameters.

### Charts

Charts are generated from the same filtered dataset. The user can choose grouping and metric fields. Clicking on a chart element adds a drill-down filter.

### Saved views

A saved view stores:

- selected columns
- column order
- column widths
- filters
- sorting
- date range
- compare mode

This is useful when the same report needs to be reopened frequently.

### Realtime updates

Realtime updates are used when data changes after indexing.

Example:

1. Admin uploads a CSV.
2. Backend indexes it into Solr.
3. Backend sends a `report_data_updated` event to the realtime service.
4. Open dashboards refresh automatically.

### Scheduled reports

Scheduled reports are designed for recurring email delivery.

An admin can create a schedule with:

- report name
- recipient email
- frequency: daily / weekly / monthly
- send time
- timezone
- current report payload

The worker then generates the CSV and sends it by email.

In local development, these emails appear in MailHog, not in Gmail.

## API Overview

All API responses follow this shape:

```json
{
  "success": true,
  "data": {},
  "error": null,
  "meta": {}
}
```

### Main endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/api/auth/login` | Login |
| `GET` | `/api/schema` | Load frontend field schema |
| `POST` | `/api/reports/query` | Query table data |
| `POST` | `/api/reports/chart` | Query chart data |
| `GET` | `/api/reports/export` | Export CSV |
| `GET` | `/api/facets/{field}` | Load dropdown values |
| `GET` | `/api/saved-views` | List saved views |
| `POST` | `/api/saved-views` | Create saved view |
| `PUT` | `/api/saved-views/{id}` | Update saved view |
| `DELETE` | `/api/saved-views/{id}` | Delete saved view |
| `POST` | `/api/ingestion/upload` | Admin CSV upload |
| `GET` | `/api/scheduled-reports` | List schedules |
| `POST` | `/api/scheduled-reports` | Create schedule |
| `DELETE` | `/api/scheduled-reports/{id}` | Delete schedule |

## Folder Guide For Juniors

### `backend/`

Pure PHP MVC-style API.

- `app/Controllers`
  - receives HTTP requests
  - validates input
  - returns JSON or CSV

- `app/Models`
  - talks to MySQL
  - stores users, views, schedules

- `app/Services`
  - contains reusable business logic
  - examples: Solr query building, CSV import, SMTP mail, realtime notify

- `app/Middleware`
  - auth and CORS

- `app/Config`
  - reads DB, Solr, Kafka config from env

- `public/index.php`
  - entry point

- `routes/api.php`
  - route definitions

### `frontend/`

React application.

- `src/pages`
  - page-level containers

- `src/components`
  - reusable UI blocks like filters, table, charts, schedules

- `src/store`
  - Zustand state management

- `src/hooks`
  - custom hooks like report loading and realtime updates

- `src/services/api.js`
  - all HTTP requests

### `kafka/`

- `producer/`
  - reads CSV and publishes rows

- `consumer/`
  - consumes Kafka messages and indexes Solr

### `mysql/`

- `init.sql`
  - creates tables
  - seeds demo users

### `solr/`

- schema and configset files for the Solr collection

### `realtime/`

- minimal Node WebSocket relay server

## Common Developer Tasks

### Rebuild changed services

```bash
docker compose up -d --build php-backend
docker compose up -d --build react-frontend
docker compose up -d --build realtime-server
docker compose up -d --build scheduled-reports-worker
```

### Watch logs

```bash
docker compose logs -f php-backend
docker compose logs -f react-frontend
docker compose logs -f kafka-consumer
docker compose logs -f realtime-server
docker compose logs -f scheduled-reports-worker
```

### Load sample data through Kafka

```bash
docker exec kafka-producer php KafkaProducer.php --file=sample_data.csv
```

### Run scheduled worker once manually

```bash
docker exec -it scheduled-reports-worker php bin/scheduled_reports_worker.php --once
```

### Open MySQL shell

```bash
docker exec -it mysql mysql -u root -p
```

## Troubleshooting

### Frontend loads but data does not appear

Check:

- `docker compose logs -f php-backend`
- `docker compose logs -f solr`

### Realtime status stays offline

Check:

- `docker compose logs -f realtime-server`
- `.env` has the correct `VITE_WS_URL`

### CSV upload fails

Check:

- `docker compose logs -f php-backend`
- PHP upload limits
- file format and extension

### Scheduled report does not send

Check:

- `docker compose logs -f scheduled-reports-worker`
- `http://localhost:8025` for MailHog inbox
- schedule time and timezone
- `SCHEDULE_ATTACHMENT_MAX_BYTES` if the report is very large

### New MySQL tables do not appear

If MySQL volume already existed before the table was added, `init.sql` will not rerun automatically.

Either:

- create the missing tables manually in MySQL
- or reset the MySQL volume with `docker compose down -v`

## Suggested Learning Path For Juniors

If you are new to this codebase, read it in this order:

1. `docker-compose.yml`
2. `backend/public/index.php`
3. `backend/routes/api.php`
4. `backend/app/Controllers/ReportController.php`
5. `backend/app/Services/QueryBuilderService.php`
6. `frontend/src/pages/ReportPage.jsx`
7. `frontend/src/hooks/useReport.js`
8. `frontend/src/store/filterStore.js`
9. `frontend/src/store/reportStore.js`

That path helps you understand request flow from browser to backend and back.

## Cleanup

```bash
docker compose down
docker compose down -v
```

Use `down -v` carefully because it removes local database, Solr, Redis, and Kafka data volumes.
