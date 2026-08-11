# Project: Xtreme Force Track Club — Full Website Redevelopment

## Status
🟡 Active — Core plugin/theme functional; production deployment and Stripe live keys pending.

Sprints 1 and 2 are complete (see `SPRINT-1.md`/`SPRINT-2.md`). A subsequent completion pass fixed
the plugin's activation-blocking bugs found in review (see plugin `README.md` for the current,
accurate state), finished the Sprint 3 backlog, and extended the platform to the remaining
`PROPOSAL.md` scope: a WooCommerce merchandise store, GDPR-style data export/erase support, and
white-label packaging (first-run setup wizard) for reuse by other clubs.

## Timeline
| Phase | Duration | Status |
|-------|----------|--------|
| Discovery & Design | Weeks 1–2 | ✅ Complete |
| Development Sprint 1 | Weeks 3–6 | ✅ Complete |
| Development Sprint 2 | Weeks 7–10 | ✅ Complete |
| Sprint 3 + full-scope completion pass | — | ✅ Complete (code) — not yet verified on a live WordPress install |
| Testing & Validation | — | 🟡 Automated tests added; needs a real WP/MySQL run and manual QA |
| Deployment & Training | — | ⬜ Pending |

## Repositories
- **GitHub Repo:** dpoly2/AgentHarness
- **Project Path:** projects/ts-redevelopment/
- **Plugin Path:** `plugin/ts-membership/`
- **Theme Path:** `theme/ts-theme/`

## Key Docs
- `PROJECT.md` — This file
- `PROPOSAL.md` — Full redevelopment proposal (also serves as the functional-requirements reference — no separate REQUIREMENTS.md exists)
- `ARCHITECTURE.md` — Technical architecture and DB schema
- `SPRINT-1.md` / `SPRINT-2.md` — Sprint task breakdowns
- `plugin/ts-membership/README.md` — Plugin architecture, shortcodes, AJAX/REST reference
- `theme/ts-theme/README.md` — Theme structure and page templates

## Live Site
- **URL:** https://xtremeforcetrackclub.org
- **Alt URL:** https://xtremeforcetc.org
- **WP Admin:** https://xtremeforcetrackclub.org/wp-admin
- **API Base:** https://xtremeforcetrackclub.org/wp-json/wp/v2

## Notes
- The theme is a standalone custom theme (`ts-theme`), not a Grace Themes child theme.
- Must retain and migrate all existing content.
- Plugin includes a first-run setup wizard so it can be reused by other AAU clubs without code changes; a fully hosted multi-tenant SaaS platform (one install serving many clubs) remains a future initiative, not part of the current codebase.
- Mobile-first. WooCommerce is an optional integration — the plugin degrades gracefully if it's not installed.

