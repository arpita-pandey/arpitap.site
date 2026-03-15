# Grader Guide

## Credentials

| Role | Username | Password | Access |
|---|---|---|---|
| **Super Admin** | admin | admin123 | All views + user management |
| **Analyst** | analyst | analyst123 | Overview, Performance, Errors, Reports |
| **Analyst 2** | analyst2 | analyst123 | Overview, Decisions only |
| **Viewer** | viewer | viewer123 | Public/shared reports only |

## URLs

- **Login:** https://reporting.arpitap.site/login.php
- **Dashboard SPA:** https://reporting.arpitap.site/dashboard.html
- **Frontend (data source):** https://test.arpitap.site

## Guided Walkthrough

### Step 1: Login as Super Admin
1. Go to https://reporting.arpitap.site/login.php
2. Enter `admin` / `admin123`
3. You will be redirected to the dashboard SPA at `dashboard.html#/overview`

### Step 2: Explore the Overview
1. You should see 4 summary cards (Sessions, Pageviews, Avg Load, Errors)
2. A daily pageviews line chart drawn on canvas
3. A top pages table
4. Try changing the date range using the From/To inputs in the header and clicking Apply

### Step 3: Check Performance
1. Click "Performance" in the sidebar
2. You'll see per-page load time bar charts and a daily trend line chart
3. The breakdown table shows samples, min/avg/max load per page

### Step 4: Check Errors
1. Click "Errors" in the sidebar
2. Daily error trend chart and grouped error table
3. Click any error row to expand and see the raw event data

### Step 5: Decisions Dashboard
1. Click "Decisions" in the sidebar
2. **Actionable Metrics** — period comparison with % change (green = good, red = bad)
3. **Performance Budgets** — pages compared against 3000ms budget, bar chart with red/green status
4. **Error Triage** — priority-ranked errors (Critical/High/Medium/Low)
5. **Decision Framework** — the Measure > Analyze > Decide > Act > Verify cycle

### Step 6: Create a Report
1. Click "Reports" in the sidebar
2. Click "New Report"
3. Fill in a title, select a category, write some content and analyst comments
4. Toggle "Public" if you want viewers to see it
5. Set status to "Published" and click "Create Report"

### Step 7: View and Export a Report
1. From the reports list, click "View" on any report
2. Review the report content and analyst insights
3. Click "Export PDF" — a PDF will download in your browser

### Step 8: Test Section-Based Access
1. Click Logout
2. Login as `analyst` / `analyst123`
3. Notice the sidebar only shows: Overview, Performance, Errors, Reports (no Decisions, no Admin)
4. Logout again
5. Login as `analyst2` / `analyst123`
6. Notice the sidebar only shows: Overview, Decisions

### Step 9: Admin Panel
1. Logout and login as `admin` / `admin123`
2. Click "Admin" in the sidebar
3. You can create new users, change roles, and delete non-admin users
4. Try creating a test user and assigning them a role

### Step 10: Viewer Experience
1. Logout and login as `viewer` / `viewer123`
2. The viewer can only see reports that are marked as public or explicitly shared with them

## Report Categories

The system has three report category groups tied to sections:

| Section | Categories |
|---|---|
| **Performance** | Sales Performance, Monthly Revenue |
| **Behavioral** | Customer Engagement, User Behavior |
| **Financial** | Budget vs Actual, Financial Health |

Each category supports charts, data tables, and analyst commentary text.

## Known Issues & Concerns

### Bugs

1. **Performance data shows zeros** — The JavaScript collector records `totalLoadTime: 0` for all performance events because it fires before `performance.timing` is fully populated. The performance charts show seeded/synthetic data to demonstrate functionality. A production fix would use `PerformanceObserver` or the `web-vitals` library.

2. **Error event data is sparse** — Only 4 error events exist in the database. The error triage and error charts work correctly but have limited data to display. Generating more traffic on `test.arpitap.site` with deliberate errors would populate this.

3. **Date range edge cases** — If the selected date range has no data at all, the canvas charts render empty axes. The charts handle this gracefully (no crash) but the visual could be improved with a "no data" overlay on the chart itself.

### Architecture Concerns

1. **JWT without Composer** — JWT is implemented manually (HMAC-SHA256 signing/verification) rather than using a vetted library like `firebase/php-jwt`. The implementation is correct but hasn't been audited for edge cases (e.g., algorithm confusion attacks). In production, a library would be preferred.

2. **No HTTPS-only token storage** — JWT is stored in `localStorage`, which is accessible to any JavaScript on the page. `httpOnly` cookies would be more secure but complicate the SPA architecture. For this project scope, localStorage is acceptable.

3. **Client-side PDF only** — PDF export uses html2pdf.js in the browser, which means PDFs can only be downloaded, not emailed. Server-side PDF generation (Dompdf/Puppeteer) would require Composer or Node.js dependencies.



### What Works Well

- Section-based analyst access control is fully functional and enforced at both SPA and API levels
- Canvas charts render correctly with real data and handle empty states
- Date range filtering works across all views and is bookmarkable via URL hash
- Report CRUD with role-based access (analysts own their reports, viewers see public/shared only)
- The decisions view provides genuine analytical value: period comparison, budget tracking, and error triage with priority ranking
