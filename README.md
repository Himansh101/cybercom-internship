# Dynamic Reporting System

A full-stack reporting platform: CSV → Kafka → Solr → PHP API → React UI.

---

## Architecture

```
CSV file
  └─► Kafka Producer (PHP)
        └─► Kafka Topic: report_data_topic
              └─► Kafka Consumer (PHP)
                    └─► Solr 9 (indexed, faceted)
                          └─► PHP Backend API (MVC, pure PHP)
                                └─► Redis Cache
                                └─► MySQL (saved views, auth)
                                      └─► React Frontend (Vite + Tailwind)
```

---

## Prerequisites

- **Docker Desktop 4.x+** (includes Docker Compose v2)
- Port availability: 3000, 8080, 8983, 9092, 3306, 6379, 2181

---

## Quick Start

```bash
# 1. Clone the repo
git clone <your-repo-url>
cd reporting-system

# 2. Copy environment file
cp .env.example .env
# Edit .env if you need custom passwords or ports

# 3. Start all services
docker compose up -d

# 4. Wait ~30 seconds for all services to initialise
# Watch readiness:
docker compose logs -f solr mysql kafka

# 5. Open the app
open http://localhost:3000
```

**Demo credentials:** `admin@demo.com` / `password`

---

## Load Sample Data

Once all services are running, ingest the 200-row sample CSV:

```bash
docker exec kafka-producer php KafkaProducer.php --file=sample_data.csv
```

Watch the consumer index the data into Solr:

```bash
docker logs -f kafka-consumer
```

Verify data in Solr Admin:

```
http://localhost:8983/solr/report_data/select?q=*:*&rows=5&wt=json
```

---

## Service URLs

| Service         | URL                                    | Notes                        |
|-----------------|----------------------------------------|------------------------------|
| React Frontend  | http://localhost:3000                  | Main UI                      |
| PHP API         | http://localhost:8080/api              | REST API                     |
| Solr Admin      | http://localhost:8983/solr/#/report_data | Query browser, schema view |
| Kafka           | localhost:9092                         | Internal broker              |
| MySQL           | localhost:3306                         | DB: reporting_db             |
| Redis           | localhost:6379                         | Cache                        |

---

## API Reference

All responses follow the shape:
```json
{ "success": true, "data": {}, "error": null, "meta": {} }
```

| Method   | Endpoint                  | Auth | Description                              |
|----------|---------------------------|------|------------------------------------------|
| `POST`   | `/api/auth/login`         | No   | Login, returns JWT                       |
| `GET`    | `/api/schema`             | No   | Report field schema                      |
| `POST`   | `/api/reports/query`      | Yes  | Query with filters, pagination, sorting  |
| `GET`    | `/api/reports/export`     | Yes  | Stream full result as CSV download       |
| `GET`    | `/api/facets/{field}`     | Yes  | Distinct values for dropdown filters     |
| `GET`    | `/api/saved-views`        | Yes  | List saved views for authenticated user  |
| `POST`   | `/api/saved-views`        | Yes  | Save current view configuration          |
| `PUT`    | `/api/saved-views/{id}`   | Yes  | Update a saved view                      |
| `DELETE` | `/api/saved-views/{id}`   | Yes  | Delete a saved view                      |

### Example: Query request body
```json
{
  "q": "oak",
  "filters": [
    {
      "type": "group",
      "operator": "AND",
      "rules": [
        { "field": "category",   "type": "dropdown", "value": ["Furniture"] },
        { "field": "price",      "type": "range",    "from": 100, "to": 500 },
        { "field": "is_active",  "type": "boolean",  "value": true },
        { "field": "created_at", "type": "date",     "from": "2023-01-01", "to": "2024-12-31" }
      ]
    }
  ],
  "sort":     { "field": "price", "direction": "desc" },
  "page":     1,
  "per_page": 50,
  "columns":  ["id", "name", "category", "price", "region", "created_at"]
}
```

---

## Features

### Column Selector
Click **⊞ Columns** in the toolbar to show/hide any field. Drag column headers to reorder. Drag the right edge of any header to resize it.

### Advanced Filters
Click **⚙ Filters** to open the filter panel. Add a filter group (AND/OR), then add individual rules per field. Each field type renders the appropriate control:
- String fields → text input or multi-select (for faceted fields like category, region)
- Numeric fields → from/to range inputs
- Date fields → date range pickers
- Boolean fields → yes/no dropdown

### Charts
Charts appear automatically once data loads. Select chart type (Bar / Line / Pie), X-axis field, and Y-axis field. **Click a bar or pie segment** to drill down — it adds that value as a filter to the table instantly.

### Date Comparison
Set a date range in the toolbar, then click **Prev period** or **Last year**. The table renders comparison columns alongside current values with colour-coded percentage change indicators (green = positive, red = negative).

### Saved Views
Click **+ Save** in the sidebar to persist your current column selection, filters, sort order, and column widths. Click the ★ icon to mark a view as default — it loads automatically on next login.

### Export
Click **⬇ CSV** to stream the full result set as a downloadable CSV file using cursor-based pagination (no row limit).

---

## Adding a New Field to the Schema

1. **Solr** — add the field to `solr/configsets/report_schema/schema.xml`:
   ```xml
   <field name="my_field" type="string" indexed="true" stored="true"/>
   ```

2. **Recreate the Solr collection** (drops existing data):
   ```bash
   docker exec solr bin/solr delete -c report_data
   docker exec solr bin/solr create -c report_data -d /opt/solr/server/solr/configsets/report_schema
   ```

3. **PHP schema array** — add an entry to the `$schemaFields` array in `backend/app/Controllers/ReportController.php`:
   ```php
   ['name' => 'my_field', 'type' => 'string', 'label' => 'My Field', 'faceted' => false],
   ```

4. **Re-ingest data** — run the Kafka producer again with your updated CSV.

---

## Folder Structure

```
reporting-system/
├── docker-compose.yml
├── .env
├── sample_data.csv
├── backend/              PHP MVC API
│   ├── public/           Apache document root (index.php)
│   └── app/
│       ├── Controllers/
│       ├── Models/
│       ├── Services/     QueryBuilder, Solr, Redis
│       ├── Middleware/   Auth (JWT), CORS
│       └── Config/       DB, Solr, Kafka
├── kafka/
│   ├── producer/         CsvParser + KafkaProducer
│   └── consumer/         KafkaConsumer + SolrIndexer
├── frontend/             React 18 + Vite + Tailwind
│   └── src/
│       ├── components/   DataTable, FilterBuilder, ChartRenderer, SavedViews
│       ├── pages/        ReportPage
│       ├── store/        Zustand (reportStore, filterStore)
│       ├── services/     api.js
│       └── hooks/        useReport, useDebounce
├── solr/                 schema.xml + solrconfig.xml
└── mysql/                init.sql (schema + seed data)
```

---

## Stopping & Cleanup

```bash
# Stop all services (preserves volumes)
docker compose down

# Stop and remove all data volumes
docker compose down -v

# Rebuild a single service after code changes
docker compose up -d --build php-backend
docker compose up -d --build react-frontend
```

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| Solr collection not created | Run: `docker exec solr bin/solr create -c report_data -d /opt/solr/server/solr/configsets/report_schema` |
| No data in table after CSV load | Check: `docker logs kafka-consumer` and `docker logs kafka-producer` |
| PHP API returns 500 | Check: `docker logs php-backend` |
| Frontend can't reach API | Ensure `VITE_API_URL=http://localhost:8080/api` in `.env` |
| MySQL connection refused | Wait 15–20s for MySQL to fully initialise, then restart php-backend |
