# Troubleshooting & FAQ

## Installation & setup

**A page shows `[TRACKSUITE_...]` as literal text instead of real content.**
This means the `ts-membership` plugin isn't active, or the shortcode name was typed wrong on the page.
Check **Plugins > Installed Plugins** and confirm "XFTC Membership" is active. If it is, edit the page
and re-check the shortcode spelling against [ADMIN-GUIDE.md](ADMIN-GUIDE.md) or the plugin README.

**Pages give a 404 error after installing the plugin/theme.**
Go to **Settings > Permalinks** and click **Save Changes** — WordPress needs to regenerate its URL
rules after new page templates are added. See [GETTING-STARTED.md](GETTING-STARTED.md#step-4--flush-permalinks).

**The Setup Wizard didn't appear after activating the plugin.**
It only auto-appears once, right after activation (and not if you activated several plugins at the same
time). You can open it anytime from **Xtreme Force > Settings**, under the "Setup Wizard" link.

**The plugin won't activate / shows a fatal error.**
Confirm you're running PHP 8.1+ and WordPress 6.4+. If you're using Stripe payments, make sure
`composer install` was run inside the plugin folder (see
[GETTING-STARTED.md](GETTING-STARTED.md#step-1--install-the-plugin)) — the plugin itself doesn't
require this to activate, but the Payments screen will show a clear "Stripe SDK missing" message instead
of working until it's done.

## Payments

**"Stripe is not yet configured" when a family tries to pay.**
Go to **Xtreme Force > Payments** and enter your Stripe keys (test keys are fine to start). See
[ADMIN-GUIDE.md — Payments & Stripe](ADMIN-GUIDE.md#payments--stripe).

**A payment went through in Stripe but the family's balance didn't update.**
This is almost always the webhook. Double check the **Webhook Endpoint URL** shown on the Payments
screen is registered in your Stripe dashboard, listening for `checkout.session.completed`, and that the
**Webhook Signing Secret** on the Payments screen matches what Stripe shows for that endpoint.

**A manual payment isn't showing up correctly.**
Manual payments need a specific **Membership or Travel ID**, not just a parent name — find the right ID
on the Members or Travel screen before recording the payment. See
[ADMIN-GUIDE.md — Recording a manual payment](ADMIN-GUIDE.md#recording-a-manual-payment).

## Registration

**A parent registered but no season membership was created.**
This happens if no season was selected on Step 3 of the registration form — usually because no season
is marked **Active** yet. Create/activate a season under **Xtreme Force > Seasons**, and have the family
re-register their athlete for that season from their portal.

**"You do not have permission" errors when a parent tries to view/edit an athlete.**
This is intentional — a parent can only see and manage their own athletes, not another family's. If a
parent believes this is wrong for their own athlete, double-check the athlete is actually linked to
their account (Members screen, in WP Admin).

## Store / WooCommerce

**No "Store Orders" tab or uniform customization field.**
These only appear when WooCommerce is installed *and active*. If you don't sell merchandise, this is
expected — everything else works without it.

## Staff / coaches

**A coach can't see the result-entry form.**
Their WordPress user needs the **Coach** (or **Admin**) role — check under **Users** in WP Admin.

**A staff member sees no pay history.**
Their staff record needs to be linked to their WordPress user account under **Xtreme Force > Payroll**
first.

## Emails

**Welcome/receipt emails aren't arriving.**
This plugin sends email through WordPress's standard `wp_mail()` — the same system used for password
resets, etc. If those don't work either, it's a hosting/server mail configuration issue, not specific to
this plugin. Many hosts recommend an SMTP plugin (e.g. WP Mail SMTP) for reliable delivery.

## Still stuck?

Check the plugin and theme README files for a more technical view of what each piece does:
- `plugin/ts-membership/README.md`
- `theme/ts-theme/README.md`
- `ARCHITECTURE.md` — full database schema and REST API reference
