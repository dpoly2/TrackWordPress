# Getting Started

This guide walks a club administrator through installing the `ts-membership` plugin and the `ts-theme`
theme on a WordPress site, and getting a club fully configured for the first time. No coding knowledge
is required.

If you're setting this up for a brand-new club (not Xtreme Force), everything here still applies — the
Setup Wizard in Step 3 is exactly how you tell the system your club's name, colors, and first season.

---

## What you need before you start

- A WordPress site (WordPress 6.4 or newer), with admin access.
- The ability to upload plugin/theme files (via WP Admin, or FTP/hosting file manager).
- A [Stripe](https://dashboard.stripe.com/register) account, if you'll be collecting payments online.
  You can install everything and explore the system without one — you just won't be able to take real
  payments until it's connected (see [ADMIN-GUIDE.md](ADMIN-GUIDE.md#payments--stripe)).
- (Optional) A WooCommerce installation, only if you plan to sell uniforms/merchandise through the site.

---

## Step 1 — Install the plugin

1. Upload the `plugin/ts-membership` folder to `/wp-content/plugins/ts-membership/` on your server.
2. This plugin needs one extra library (the Stripe payment SDK) installed via Composer. If your host
   gives you terminal/SSH access, run this once inside the plugin folder:
   ```bash
   composer install
   ```
   If you don't have terminal access, ask your host or developer to run this for you before you take
   real payments — everything else in the plugin works without it.
3. In WP Admin, go to **Plugins > Installed Plugins** and click **Activate** under "XFTC Membership".

Activating creates all the database tables the plugin needs automatically — you don't need to do
anything in your database.

## Step 2 — Install the theme

1. Upload the `theme/ts-theme` folder to `/wp-content/themes/ts-theme/`.
2. In WP Admin, go to **Appearance > Themes**, find "XFTC Track & Field", and click **Activate**.

The theme works with the plugin but doesn't require it to load — if the plugin is ever deactivated, the
theme's pages simply show no data instead of breaking.

## Step 3 — Run the Setup Wizard

The very first time you activate the plugin, you'll be sent automatically to a **Setup Wizard**. (If you
ever need to run it again — say, to reconfigure things — go to **Xtreme Force > Settings** and click the
"Setup Wizard" link near the bottom of the page.)

The wizard asks for:

| Field | What it's for |
|---|---|
| **Club Name** | Shown in the site footer, emails, and anywhere the theme doesn't have a custom logo. |
| **Club Admin Email** | Where system notifications (new registrations, etc.) get sent. |
| **Accent Color / Dark Background / Secondary Accent** | Your club's brand colors — used across the site. You can fine-tune these later with a full color picker under **Appearance > Customize**. |
| **First Season** (optional) | Give it a name (e.g. "2026 Outdoor"), a type, start/end dates, and Standard/Premium membership fees. You can skip this and create seasons later under **Xtreme Force > Seasons**. |

Click **Save & Finish Setup**. That's it — the system now knows who you are.

## Step 4 — Flush permalinks

WordPress needs to regenerate its URL rules after a new plugin/theme is activated.

Go to **Settings > Permalinks** and click **Save Changes** (you don't need to change anything on that
page — just opening and saving it is enough).

## Step 5 — Set up your pages

The theme ships with page templates for each major area of the site. For each one, create a WordPress
page (**Pages > Add New**), give it a title, and under **Page Attributes > Template** pick the matching
template:

| Page | Template to select | Suggested URL slug |
|---|---|---|
| Registration | Registration | `register` |
| Parent Portal | Parent Portal | `portal` |
| Coach/Staff Portal | Staff/Coach Portal | `staff-portal` |
| Meet Schedule | Meet Schedule | `schedule` |
| Results | Results & Records | `results` |
| Team Roster | Athlete Roster | `roster` |

Then, in **Appearance > Menus**, add these pages to your site's main navigation menu so visitors can
find them.

## Step 6 — Configure payments

Go to **Xtreme Force > Payments** and enter your Stripe API keys. Start with **Test Mode** checked and
your Stripe *test* keys (from your Stripe dashboard) — this lets you run through a full registration and
payment without moving real money. Full details: [ADMIN-GUIDE.md — Payments & Stripe](ADMIN-GUIDE.md#payments--stripe).

## Step 7 — (Optional) Set up the merchandise store

If you're selling uniforms or gear, install and activate **WooCommerce** (a separate, free WordPress
plugin) — the system detects it automatically and adds a "Store" category, a jersey name/number field on
customizable products, and a "Store Orders" tab to the parent portal. No extra plugin configuration
needed on our side. See [ADMIN-GUIDE.md — Merchandise Store](ADMIN-GUIDE.md#merchandise-store-woocommerce).

---

## You're done — what now?

- Read the [Admin Guide](ADMIN-GUIDE.md) for day-to-day operation: managing members, seasons, meets,
  results, travel, payroll, and reports.
- Point families to the [Parent Guide](PARENT-GUIDE.md) so they know how to register and use the portal.
- Point coaches/staff to the [Coach & Staff Guide](COACH-STAFF-GUIDE.md).
- If something doesn't look right, check [Troubleshooting](TROUBLESHOOTING.md) before assuming it's broken.
