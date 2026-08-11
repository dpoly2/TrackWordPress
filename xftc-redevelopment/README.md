# XFTC Redevelopment Project

**Status:** Core plugin + theme functional and code-complete through the full `PROPOSAL.md` scope.
Not yet deployed or verified against a live WordPress/MySQL environment.

> **Correction:** this file and others in this repo previously stated the plugin "activates without
> fatal errors" and was verified end-to-end on staging. That was not accurate as committed — both
> plugin bootstrap files required include files that didn't exist anywhere in the repo, so the plugin
> fataled immediately on activation. See [Plugin README](plugin/ts-membership/README.md) for what was
> actually wrong and what's been fixed since.

---

## Project Summary

Full redevelopment of the Xtreme Force Track Club web presence using a custom WordPress plugin + standalone theme architecture. The system replaces the legacy Gravity Forms-based workflow with a purpose-built membership management platform, reusable by other clubs.

**Live Site:** https://xtremeforcetrackclub.org (redevelopment not yet deployed here)

---

## Components

| Component | Version | Status |
|-----------|---------|--------|
| `ts-membership` plugin | v2.0.0 | Code-complete (Sprints 1–3 + full-scope pass); needs live-environment verification |
| `ts-theme` | v1.0.0 | Code-complete; needs live-environment verification |

→ See individual READMEs:
- [Plugin README](plugin/ts-membership/README.md)
- [Theme README](theme/ts-theme/README.md)

---

## Sprint Overview

### Sprint 1 ✅ — Core Foundation
**Delivered:** Plugin scaffold, 10-table DB schema, 5 user roles, member + season CRUD

### Sprint 2 ✅ — Feature Modules
**Delivered:**
- Meet management + athlete enrollment
- Results tracking
- Travel manifests
- Payroll system
- Stripe payment placeholder
- Multi-step registration form (4 steps)
- Tabbed parent portal dashboard
- Plugin/theme decoupled via 13 shortcodes
- 4 AJAX endpoints
- Bug fix: `send_parent_welcome()` activation error resolved

**"Verified on staging" claim retracted** — see the correction note at the top of this file. This has not
been re-verified against a live install as part of the fix.

### Sprint 3 + full-scope completion pass ✅ (code) — 🔜 live verification
**Delivered:**
- Fixed the plugin-load-blocking bug and REST API IDOR issues described in the correction note
- Stripe checkout/webhook implementation (code-complete; needs real API keys to exercise)
- Coach/staff front-end portal (`[TRACKSUITE_staff_portal]`)
- Reports engine (registration/financial/performance) + admin Reports screen
- Real admin dashboard widgets (previously placeholders)
- GDPR-style data export/erase support, privacy policy content, consent capture, retention cron
- Self-hosted fonts + Chart.js (previously loaded from Google/jsdelivr CDNs)
- Optional WooCommerce merchandise store integration
- First-run setup wizard for reuse by other clubs
- PHPUnit suite that loads the real plugin bootstrap (not a placeholder test)

**Still pending:**
- [ ] Stripe live keys entered in WP Admin settings (deployment step, not a code task)
- [ ] Full end-to-end registration + payment test against a real WordPress/MySQL install
- [ ] Permalink flush on production
- [ ] Plugin + theme install on xtremeforcetrackclub.org
- [ ] Load testing and QA

---

## Documents

| Document | Description |
|----------|-------------|
| [PROPOSAL.md](PROPOSAL.md) | 12-week project proposal |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Technical architecture overview |
| [SPRINT-1.md](SPRINT-1.md) | Sprint 1 task log |
| [SPRINT-2.md](SPRINT-2.md) | Sprint 2 task log |
| [PROJECT.md](PROJECT.md) | Ongoing project notes |

