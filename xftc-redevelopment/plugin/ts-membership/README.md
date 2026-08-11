# XFTC Membership Plugin

**Version:** 2.0.0
**Status:** Core plugin functional and covered by an automated test suite. Not yet verified on a real
WordPress/MySQL install (this codebase was developed and reviewed without one available) — see
Deployment below for the manual verification steps to run before going live.

> A previous version of this file (and `PROJECT.md`/`SPRINT-2.md`) claimed this plugin was
> "staging verified" and passing end-to-end tests. That was not accurate as committed: both plugin
> bootstrap files required include files that didn't exist anywhere in the repo (a partially-finished
> `xftc- → ts-` rename), so the plugin fataled immediately on activation and could never have run on
> any environment. That bug, along with several REST API correctness/authorization bugs (IDOR — a
> parent could read or edit another family's athlete record by guessing an ID), are fixed as of this
> version. See git history for the full list of fixes.

---

## Overview

The `ts-membership` WordPress plugin is the data and business logic engine for a track/field club's
web presence. It is fully decoupled from the display layer — the companion `ts-theme` theme handles
all front-end rendering via shortcodes provided by this plugin. It's built to be reused by any AAU-style
club, not just Xtreme Force Track Club — see the Setup Wizard note below.

---

## Architecture

```
ts-membership/
├── ts-membership.php          # Bootstrap — the ONLY plugin entry point; registers hooks, loads classes
├── uninstall.php               # Runs on plugin deletion — removes roles/options, optionally data
├── includes/
│   ├── class-ts-activator.php     # DB table creation on activation + schema upgrades
│   ├── class-ts-deactivator.php   # Cleanup on deactivation
│   ├── class-ts-roles.php         # Custom WP user roles
│   ├── class-ts-members.php       # Athlete/member CRUD
│   ├── class-ts-registration.php  # Parent + athlete registration logic, membership creation
│   ├── class-ts-seasons.php       # Season management
│   ├── class-ts-meets.php         # Meet scheduling
│   ├── class-ts-results.php       # Athlete results tracking
│   ├── class-ts-travel.php        # Travel manifest management
│   ├── class-ts-payroll.php       # Coach/staff payroll
│   ├── class-ts-payments.php      # Stripe checkout/webhook + manual payment entry
│   ├── class-ts-reports.php       # Registration/financial/performance reports
│   ├── class-ts-privacy.php       # GDPR-style export/erase hooks, retention cron, SSL notice
│   ├── class-ts-woocommerce.php   # Optional store integration — only loaded if WooCommerce is active
│   └── class-ts-emails.php        # Transactional email system
├── admin/
│   ├── class-ts-admin.php         # WP Admin panel registration + setup-wizard redirect
│   ├── class-ts-dashboard-widgets.php # WP Admin dashboard widgets
│   ├── assets/admin.css             # Admin styles
│   └── views/                       # WP Admin view templates
│       ├── dashboard.php, members.php, seasons.php, meets.php, results.php,
│       ├── payments.php, payroll.php, travel.php, reports.php,
│       └── settings.php, setup-wizard.php
├── public/
│   ├── class-ts-public.php        # Shortcode registration + AJAX handlers
│   ├── assets/
│   │   ├── public.css               # Front-end styles
│   │   ├── public.js                # AJAX, multi-step form, portal JS
│   │   └── vendor/chart.umd.min.js  # Self-hosted Chart.js (loaded on demand)
│   └── views/                       # Shortcode output templates
│       ├── register.php, portal.php, meets.php, results.php,
│       ├── checkout.php, receipts.php, staff-portal.php
├── api/
│   └── class-ts-rest-api.php      # WP REST API endpoints (see ARCHITECTURE.md for the full table)
└── tests/                          # PHPUnit suite — loads the real plugin bootstrap, not a mock
```

---

## Database Tables

All tables use the `wp_TRACKSUITE_` prefix (e.g. `wp_TRACKSUITE_athletes`):

| Table | Purpose |
|-------|---------|
| `athletes` | Athlete records linked to parent WP users |
| `seasons` | Season definitions, dates, and pricing tiers |
| `memberships` | Athlete-to-season enrollment + payment status (`UNIQUE(athlete_id, season_id)`) |
| `meets` | Track meet events |
| `meet_entries` | Athlete-to-meet enrollment (`UNIQUE(meet_id, athlete_id, event_category)`) |
| `results` | Athlete performance results |
| `travel` | Travel manifests (`UNIQUE(meet_id, athlete_id)`) |
| `staff` / `payroll` | Coach/staff records and payroll entries |
| `payments` | Payment log — Stripe, manual, and (if WooCommerce is active) store orders |

See `ARCHITECTURE.md` for full column definitions.

---

## User Roles

| Role | Capabilities |
|------|-------------|
| `TRACKSUITE_parent` | Register athletes, view portal, manage own athletes |
| `TRACKSUITE_athlete` | View own results and schedule |
| `TRACKSUITE_coach` | Manage meets, enter results (via front-end staff portal or WP Admin) |
| `TRACKSUITE_staff` | View own hours/payroll (via front-end staff portal) |
| `TRACKSUITE_admin` | Full access to all XFTC admin panels |

---

## Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[TRACKSUITE_register_form]` | Multi-step parent + athlete registration (4 steps) |
| `[TRACKSUITE_portal]` | Tabbed parent dashboard |
| `[TRACKSUITE_my_athletes]` | Logged-in parent's athlete list |
| `[TRACKSUITE_my_results]` | Athlete results for logged-in parent |
| `[TRACKSUITE_my_payments]` | Payment history for logged-in parent |
| `[TRACKSUITE_my_travel]` | Travel manifest for logged-in parent |
| `[TRACKSUITE_my_orders]` | WooCommerce store order history for logged-in parent (requires WooCommerce) |
| `[TRACKSUITE_staff_portal]` | Coach/staff front-end home — result entry, pay history |
| `[TRACKSUITE_meets]` / `[TRACKSUITE_schedule]` | Public meet schedule |
| `[TRACKSUITE_results]` / `[TRACKSUITE_club_records]` / `[TRACKSUITE_leaderboard]` | Public results/records |
| `[TRACKSUITE_roster]` | Public team roster |
| `[TRACKSUITE_login_form]` | Parent login form |

---

## AJAX Endpoints

| Action | Handler | Description |
|--------|---------|-------------|
| `TRACKSUITE_register_athlete` | `ajax_register_athlete()` | Full registration — creates WP user + athlete record + season membership |
| `TRACKSUITE_login` | `ajax_login()` | Parent portal login |
| `TRACKSUITE_register_for_meet` | `ajax_register_for_meet()` | Enroll athlete in a meet (ownership-checked) |
| `TRACKSUITE_get_chart_data` | `ajax_get_chart_data()` | Portal performance chart data (login + ownership required) |
| `TRACKSUITE_staff_add_result` | `ajax_staff_add_result()` | Coach/admin enters a meet result from the front-end staff portal |

REST endpoints (with authorization details) are documented in `ARCHITECTURE.md`.

---

## GDPR / Privacy

`class-ts-privacy.php` wires athlete/payment/travel data into WordPress's native Tools > Export/Erase
Personal Data flows, suggests privacy-policy language via `wp_add_privacy_policy_content()`, and runs a
daily data-retention pass (configurable under Settings). This implements the standard technical
mechanisms WordPress provides — it is not a substitute for the club's own legal/privacy-policy review,
particularly given the data collected concerns minors.

---

## Testing

```bash
composer install
vendor/bin/phpunit
```

`tests/bootstrap.php` stubs enough of the WordPress API to actually `require` the plugin's real
bootstrap file and fire `plugins_loaded`/`init`/`rest_api_init` — a missing include or a call to a
method that doesn't exist on another class will fail the test run, which is exactly the bug class that
shipped previously. `RestRoutesSmokeTest.php`, `OwnershipCheckTest.php`, and
`MeetRegistrationDedupeTest.php` specifically regression-test the bugs found in review.

This suite has not been executed against a real PHP interpreter as part of this change (none was
available in the environment it was written in) — treat the first CI run as the first real signal, and
watch it closely.

---

## Deployment

To install: upload the plugin folder to `/wp-content/plugins/ts-membership/`, run `composer install`
inside it (installs the Stripe SDK), and activate via WP Admin > Plugins.

After activation, you'll be redirected to a **Setup Wizard** to configure the club name, brand colors,
and first season — no code changes needed. Then:
1. Go to **Xtreme Force > Payments** and enter Stripe API keys (test keys first).
2. Flush permalinks: Settings > Permalinks > Save Changes.
3. Assign the `[TRACKSUITE_register_form]` shortcode to `/register/`, `[TRACKSUITE_portal]` to
   `/portal/`, and (if using the theme) `[TRACKSUITE_staff_portal]` to `/staff-portal/`.
4. If selling merchandise, install and activate WooCommerce — the store integration in
   `class-ts-woocommerce.php` picks it up automatically.

**Before treating this as production-ready:** run the automated tests, then manually verify
activation, registration, and payment flows against a real WordPress/MySQL environment — this pass
fixed everything found in static review, but nothing here has been click-tested end-to-end in a browser.
