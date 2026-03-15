# Analytics Reporting System

A full-stack web analytics pipeline built for CSE 135. The system collects browser events from a frontend site, processes and stores them server-side, and presents the data through an authenticated single-page dashboard with charts, tables, reports, and decision-support tools.

## Links

- **Reporting Dashboard:** https://reporting.arpitap.site/dashboard.html
- **Login Page:** https://reporting.arpitap.site/login.php
- **Frontend (Collector Source):** https://test.arpitap.site

## Architecture

```
Phase 1: Collection     Phase 2: Server       Phase 3: Storage
┌────────────────┐      ┌──────────────┐      ┌──────────────┐
│ collector.js   │─────>│ PHP (PDO)    │─────>│   MySQL      │
│ pageviews,     │      │ validate,    │      │ 7+ tables    │
│ performance,   │      │ enrich,      │      │ events,      │
│ errors, events │      │ sessionize   │      │ users, etc.  │
└────────────────┘      └──────────────┘      └──────────────┘
                                                    │
Phase 6: Decisions      Phase 5: Dashboard    Phase 4: API
┌────────────────┐      ┌──────────────┐      ┌──────────────┐
│ perf budgets,  │<─────│ SPA (vanilla │<─────│ JWT auth,    │
│ error triage,  │      │ JS), canvas  │      │ role-based,  │
│ alerting       │      │ charts       │      │ JSON endpts  │
└────────────────┘      └──────────────┘      └──────────────┘
```

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend Collector | Vanilla JavaScript (no dependencies) |
| Server Processing | PHP 8.x with PDO |
| Storage | MySQL |
| Authentication | JWT (hand-rolled, no Composer) |
| Dashboard | Vanilla JS SPA, HTML5 Canvas charts |
| PDF Export | html2pdf.js (client-side, CDN) |
| Styling | Custom CSS (no framework on SPA) |
| Server | Apache on Ubuntu (DigitalOcean) |

## Pipeline Phases

### Phase 1 — Collection
A JavaScript collector on `test.arpitap.site` captures pageviews, scroll depth, clicks, keyboard activity, idle time, performance timing, and JavaScript errors. Events are sent via `fetch` POST to the reporting server's `/api/static.php` endpoint.

### Phase 2 — Server Processing
The PHP ingestion endpoint (`api/static.php`) validates incoming JSON payloads, extracts fields (session ID, event type, page, timestamp), and stores the raw event data as JSON in the `events` table.

### Phase 3 — Storage
MySQL schema with 7+ tables: `users`, `sections`, `analyst_sections`, `report_categories`, `reports`, `report_viewers`, `exports`, and `events`. Composite indexes on analyst assignments and reports. Role-based data partitioning via `analyst_sections`.

### Phase 4 — Reporting API
REST API endpoints secured with JWT Bearer tokens:
- `POST /api/login.php` — authenticate, returns JWT with role and sections
- `GET /api/overview.php` — summary cards, daily pageviews, top pages
- `GET /api/performance.php` — load times by page, daily trends
- `GET /api/errors.php` — error trends, grouped errors
- `GET /api/decisions.php` — performance budgets, error triage, actionable metrics
- `GET/POST/PUT/DELETE /api/reports.php` — report CRUD
- `GET/POST/PUT/DELETE /api/users.php` — user management (admin only)

All data endpoints accept `?start=YYYY-MM-DD&end=YYYY-MM-DD` for date filtering.

### Phase 5 — Dashboard
A single-page application (`dashboard.html`) with hash-based routing:
- **Overview** — 4 summary cards, daily pageviews line chart, top pages table
- **Performance** — per-page load time bar chart, daily trend line chart, breakdown table
- **Errors** — daily error trend chart, grouped error table with expandable stack traces
- **Reports** — create/edit/view reports with analyst commentary, PDF export
- **Decisions** — performance budgets, error triage with priority ranking, actionable vs vanity metrics
- **Admin** — user CRUD, role management

Charts are drawn on HTML5 `<canvas>` using vanilla JavaScript (no Chart.js). DOM rendering uses `textContent` for XSS prevention. Layout is responsive with a collapsible sidebar at 768px.

### Phase 6 — Decisions
The Decisions view provides:
- **Actionable Metrics** — period-over-period comparison (sessions, pageviews/session, errors per 1k PV) with percentage change indicators
- **Performance Budgets** — 3000ms budget per page, visual status (under/over budget), bar chart comparison
- **Error Triage** — priority ranking (Critical/High/Medium/Low) based on frequency x impact, with affected sessions and browser info
- **Alerting** — `alert-check.sh` cron script that monitors error rate and load time, sends notifications when thresholds are exceeded

## Authentication & Authorization

Three user roles with section-based access control:

| Role | Access |
|---|---|
| **Super Admin** | All views, user management, all reports |
| **Analyst** | Assigned sections only, create/edit own reports |
| **Viewer** | Public reports + explicitly shared private reports |

Analysts are assigned specific dashboard sections (overview, performance, errors, reports, decisions) via the `analyst_sections` table. The JWT token includes the user's allowed sections, and both the SPA router and API endpoints enforce access.

## AI Usage

This project made use of Claude (Anthropic) as a coding assistant throughout development. Claude was used for:
- **Architecture design** — planning the SPA structure, API endpoints, and JWT auth flow
- **Debugging** — diagnosing Apache Authorization header stripping, PHP permission errors, MySQL query issues

**Observations on AI value:**
- Good at diagnosing common server configuration issues (Apache mod_rewrite, file permissions)
- Overall accelerated development, especially for the large SPA file 

## Future Roadmap

Given more time, the following improvements would be prioritized:

2. **Server-side PDF generation** — Currently using client-side html2pdf.js. A server-side solution (Dompdf or Puppeteer) would enable email delivery of reports.
3. **Real-time alerting** — The cron script exists but could be enhanced with Slack/email webhook integration and more granular thresholds.
4. **A/B testing support** — The events table structure supports it; the collector would need variant assignment and the dashboard would need a comparison view.
5. **Data retention & partitioning** — Implementing MySQL partitioning by date and automatic purging of old events.
6. **Proper error tracking** — Enriching error events with stack traces, source maps, and browser/OS metadata.
7. **Test coverage** — No automated tests exist currently. API endpoint tests and SPA integration tests would improve reliability.
8. **OpenTelemetry migration** — Replacing the custom collector with OTel Browser SDK for industry-standard telemetry.
