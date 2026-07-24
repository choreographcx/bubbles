# Forminator forms

Ready-to-import Forminator Pro form exports for the Bubbles Pets Rescue site.
Both files reproduce the source Word documents exactly (every section,
question, agreement, and consent block).

| File | Form | Notifications to |
|---|---|---|
| `adoption-application-form.json` | Adoption Application - Bubbles Pets Rescue | `LittleAngelsUAE@outlook.com` |
| `foster-application-form.json` | Foster Application - Bubbles Pets Rescue | `LittleAngelsUAE@outlook.com` |

## How to load them into the site (creates the form in the database)

1. WordPress admin → **Forminator → Forms**.
2. Click **Import** (top of the page).
3. Upload one `.json` file (or open it, copy all, paste it in), then **Import**.
4. Repeat for the second file.
5. Note each form's **ID** (shown under its name).

## Put each form on its own page

1. **Pages → Add New**, give it a title (e.g. "Adopt a Pet" / "Foster a Pet").
2. Add the **Forminator Forms** block and pick the imported form
   (or use a Shortcode block: `[forminator_form id="NNN"]`).
3. **Publish**, then add the page to your menu under **Appearance → Menus**.

> Note: importing is the safe, supported way to write a Forminator form into
> the database. Do not hand-write SQL for these — Forminator stores forms as
> serialized objects and manual SQL corrupts them.
