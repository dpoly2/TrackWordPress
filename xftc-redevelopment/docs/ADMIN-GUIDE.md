# Admin Guide

Everything a club administrator needs for day-to-day operation. This assumes the plugin and theme are
already installed — if not, start with [GETTING-STARTED.md](GETTING-STARTED.md).

All of this lives under the **Xtreme Force** menu in your WordPress admin sidebar (look for the
group icon). The submenu items match the section headers below.

---

## Dashboard

**Xtreme Force > Dashboard** — your at-a-glance view: total members and the current active season.

Your regular WordPress **Dashboard** (Dashboard > Home) also gets four extra widgets once the plugin is
active:

- **Upcoming Meets** — next five meets, with a link into the Meets screen.
- **Recent Payments** — last five payments across all families and gateways.
- **Payroll Due** — how many payroll entries are pending and their total, with a link to Payroll.
- **New Registrations** — most recent meet sign-ups.

---

## Members

**Xtreme Force > Members** — a searchable list of every athlete profile in the system (name, DOB,
gender, team level, school, emergency contact, registration date). Use the search box to find a
specific athlete by name.

Athletes are created by parents through the registration form (see the
[Parent Guide](PARENT-GUIDE.md)) — there's no separate "add athlete" button here, since every athlete
needs to be linked to a parent account first.

---

## Seasons

**Xtreme Force > Seasons** — create and manage your club's seasons (e.g. "2026 Outdoor", "2026 Summer").

Each season has:

- **Type** — Indoor, Outdoor, Summer, or Fall
- **Dates** — season start/end, and registration open/close windows
- **Standard Fee** and **Premium Fee** — the two membership tiers a parent can choose from at
  registration
- **Status** — only one season can be the *active* season at a time; that's the one shown by default on
  the registration form

Create your season here before opening registration for it.

---

## Meets

**Xtreme Force > Meets** — create and manage track meets: name, date, time, location, type
(practice/competitive/invitational), and event categories (sprints, hurdles, distance, field, etc.).

Meets move through a status workflow: **Upcoming → Active → Completed → Cancelled**.

From this screen you can also view a meet's **roster** — every athlete registered for that meet, by
event.

---

## Results

**Xtreme Force > Results** — enter meet results (placement, time/distance/points) per athlete and event.

The system automatically:
- Flags a result as a **Personal Best** if it beats that athlete's prior best in the same event
- Flags a result as a **Club Record** if it beats the best result across the whole club, all-time

Both show up as badges throughout the site (including on the parent's results chart). This screen also
shows your club's current **Club Records** table for reference.

---

## Travel

**Xtreme Force > Travel** — manage bus and hotel logistics per meet: assign a bus seat or hotel room to
each booked athlete, and see who's paid vs. unpaid.

**Travel Fee Settings** on this screen sets the default bus and hotel fees (used to calculate what a
family owes when they book travel for a meet).

---

## Payroll

**Xtreme Force > Payroll** — manage coach/staff pay:

1. Add staff (link them to a WordPress user account, set their role and hourly wage).
2. Enter a payroll period (start/end date, hours worked) — gross pay, deductions, and net pay calculate
   automatically from the staff member's hourly wage.
3. Mark entries **Paid** once processed, or **Voided** if an entry needs to be cancelled without
   deleting the record.

Staff can see their own pay history without needing WP Admin access at all — see the
[Coach & Staff Guide](COACH-STAFF-GUIDE.md).

---

## Payments & Stripe

**Xtreme Force > Payments** is where you connect Stripe and view all payment activity — membership fees,
travel fees, and (if you're using the store) merchandise orders all show up in one list here.

### Connecting Stripe

1. Create a [Stripe account](https://dashboard.stripe.com/register) if you don't have one.
2. Get your API keys from your [Stripe dashboard](https://dashboard.stripe.com/apikeys).
3. On the Payments screen, leave **Test Mode** checked and paste in your **Test Publishable Key** and
   **Test Secret Key**. Test mode lets you run through the entire registration → payment flow with fake
   card numbers, so nothing real gets charged.
4. Copy the **Webhook Endpoint URL** shown on this screen and add it in your
   [Stripe Webhooks dashboard](https://dashboard.stripe.com/webhooks) as a new endpoint, listening for
   `checkout.session.completed` and `payment_intent.payment_failed`.
5. Stripe will give you a **Webhook Signing Secret** — paste that into the **Webhook Signing Secret**
   field here and save.
6. When you're ready to accept real payments, add your **Live** keys in the fields below the test keys,
   then uncheck **Test Mode**.

The publishable key is safe to appear in your site's front-end code — the secret key and webhook secret
never are, and this screen only shows them to logged-in admins.

### Recording a manual payment

If a family pays by cash or check instead of online, use **Record Manual Payment**: pick the parent, the
payment type (Membership Fee or Travel Fee), the specific membership or travel booking ID it applies to
(you can find the ID on the Members/Travel screens), and the amount. This updates that family's balance
exactly the same way an online payment would.

---

## Merchandise Store (WooCommerce)

If [WooCommerce](https://woocommerce.com) is installed and active, the plugin automatically:

- Creates an **XFTC Uniforms** product category — put customizable gear (jerseys, singlets) in it.
- Adds a **name/number for jersey** field to any product in that category — no configuration needed.
- Adds a **Store Orders** tab to the parent portal.
- Logs every completed order into the same Payments list as memberships/travel, so you see all club
  revenue in one place.

Sizes are handled by WooCommerce's normal product variations (Size: S/M/L/XL, etc.) — nothing special
to configure there.

If WooCommerce isn't installed, none of this appears — the rest of the plugin works exactly the same
either way.

---

## Reports

**Xtreme Force > Reports** has three tabs:

- **Registration** — registrations broken down by season and tier, with pending/active/cancelled counts.
- **Financial** — membership and travel revenue collected vs. outstanding, store revenue (if
  WooCommerce is active), payroll paid out, and a breakdown of payments by gateway/status.
- **Performance** — your top athletes by personal-best count, current club records, and participation
  numbers by meet.

---

## Settings

**Xtreme Force > Settings** covers:

- **Club Name** and **Admin Email** — shown across the site and in emails.
- **Data Retention** — how many years of inactivity before an athlete record is flagged for review (see
  [Privacy & Data below](#privacy--data)). Set to 0 to disable.
- **On Uninstall** — check this box only if you want *all* club data permanently deleted if this plugin
  is ever removed from the site. Leave it unchecked (the default) to keep your data safe even if the
  plugin is temporarily deactivated or removed.
- A link to re-run the **Setup Wizard** if you need to change your club's name/colors/first season again.

Stripe keys live on the **Payments** screen, not here — see above.

---

## Privacy & Data

This system collects information about minors (names, dates of birth, schools, emergency contacts), so
it's built on WordPress's standard privacy tooling rather than anything custom:

- **Tools > Export Personal Data** — when a parent requests a copy of their data, this pulls their
  athlete profiles, payment history, and travel bookings automatically.
- **Tools > Erase Personal Data** — redacts an athlete's personal fields (name, DOB, school, emergency
  contact) while keeping anonymized results/participation history for club records.
- **Settings > Privacy** — the plugin suggests privacy-policy language describing what data is
  collected; review and adjust it for your club before publishing.
- Parents check a consent box when registering an athlete, and that consent is timestamped.

**Important:** this gives you the standard technical tools WordPress provides for handling personal
data — it is not a substitute for your own legal/privacy-policy review, especially given the data
involves minors. Talk to your club's board or legal counsel about what your actual privacy policy should
say.

---

## Coach/staff without WP Admin access

You don't need to give every coach a WordPress Admin login. Point them at your site's staff portal page
(the `[TRACKSUITE_staff_portal]` shortcode / "Staff/Coach Portal" template) — see the
[Coach & Staff Guide](COACH-STAFF-GUIDE.md). They just need the `Coach` or `Staff` role on their user
account, which you can set under **Users** in WP Admin.
