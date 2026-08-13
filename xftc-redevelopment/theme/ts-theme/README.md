# XFTC Theme

**Version:** 1.0.0
**Status:** Code-complete; not independently verified on a live WordPress install as part of this pass.

> Note: earlier docs in this repo claimed the paired `ts-membership` plugin was "staging verified,"
> which was not accurate — the plugin fataled on activation until a subsequent fix (see the plugin
> README). This theme is structurally independent and degrades gracefully without the plugin, but any
> page relying on plugin shortcodes (portal, register, schedule, results, roster, staff portal) would
> have rendered without real data until that fix landed.

---

## Overview

`ts-theme` is a standalone WordPress theme, reusable by any track/field club — not tied to Xtreme Force
specifically (see the plugin's Setup Wizard for club branding). It handles all front-end presentation
and is fully decoupled from the data layer — all dynamic content is rendered via shortcodes provided by
the `ts-membership` plugin.

The theme degrades gracefully if the plugin is inactive: unregistered shortcodes render as inert text
rather than breaking the page.

---

## File Structure

```
ts-theme/
├── style.css              # Theme header + base styles
├── theme.json             # Block editor / FSE settings
├── functions.php          # Theme setup, script/style enqueuing
├── index.php              # Fallback template
├── front-page.php         # Homepage template
├── header.php             # Site header
├── footer.php             # Site footer
├── page.php               # Default page template
├── single.php              # Single post template
├── 404.php                # Custom 404 ("FALSE START.")
├── inc/
│   ├── nav-walker.php     # Custom Bootstrap-compatible nav walker
│   └── template-tags.php  # Reusable template helpers
├── assets/
│   ├── css/fonts.css      # Self-hosted @font-face declarations (Bebas Neue, Inter)
│   ├── fonts/              # Self-hosted woff2 font files
│   └── js/theme.js        # Theme JS (nav, tabs, filters — chart rendering lives in the plugin)
└── templates/
    ├── register.php        # Registration page → [TRACKSUITE_register_form]
    ├── portal.php          # Parent portal → [TRACKSUITE_portal]
    ├── staff-portal.php    # Coach/staff portal → [TRACKSUITE_staff_portal]
    ├── schedule.php        # Meet schedule → [TRACKSUITE_meets]
    ├── results.php         # Results board → [TRACKSUITE_results]
    ├── roster.php          # Team roster → [TRACKSUITE_roster]
    └── parts/
        ├── header.php      # Reusable header partial
        └── footer.php      # Reusable footer partial
```

---

## Page Templates

| Template | Shortcode Used | Suggested URL |
|----------|---------------|-----|
| `register.php` | `[TRACKSUITE_register_form]` | `/register/` |
| `portal.php` | `[TRACKSUITE_portal]` | `/portal/` |
| `staff-portal.php` | `[TRACKSUITE_staff_portal]` | `/staff-portal/` |
| `schedule.php` | `[TRACKSUITE_meets]` | `/schedule/` |
| `results.php` | `[TRACKSUITE_results]` | `/results/` |
| `roster.php` | `[TRACKSUITE_roster]` | `/roster/` |

---

## Design System

- **Primary color:** `#0D1B2A` (navy) — customizable per club via Appearance > Customize
- **Accent color:** `#F5A623` (gold) — customizable per club
- **Fonts:** Bebas Neue (display) + Inter (body), self-hosted (see `assets/fonts/`) — not loaded from
  Google Fonts, so no visitor data is sent to a third party before any consent notice is shown.
- **Custom 404:** Athletic theme with "FALSE START." message

---

## Dependencies

- WordPress 6.0+
- `ts-membership` plugin (for all dynamic content)
- PHP 8.1+

---

## Deployment

To install: upload the `ts-theme/` folder to `/wp-content/themes/` and activate via WP Admin > Appearance > Themes.

After activation:
1. Flush permalinks: Settings > Permalinks > Save Changes
2. Assign template pages: Register, Portal, Staff Portal, Schedule, Results, Roster
3. Configure menus in WP Admin > Appearance > Menus
4. Run the `ts-membership` plugin's Setup Wizard (triggered automatically on plugin activation) to set
   your club's name, colors, and first season.
