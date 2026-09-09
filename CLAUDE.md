# Bubbles Pets Rescue — WordPress site

Volunteer-run dog and cat rescue in the UAE. Live site
**https://www.bubblespetsrescue.ae** — note the `s` in "pets"; the older
`bubblespetrescue.ae` domain is being redirected to it.

## Repository layout and branch

- The repository root **is** the site's `wp-content` directory (`themes/`,
  `plugins/`, `forminator-forms/`).
- **All work happens on `main`.** GitHub's default branch is still the old
  `claude/website-files-setup-tzypht`, so a fresh clone lands on a stale branch
  with none of the current work. Start every session with
  `git fetch origin main && git checkout main`.
- Only two directories are ours: `themes/bubbles-pet-rescue` and
  `plugins/bubbles-pet-rescue-core`. Everything else under `plugins/`
  (Forminator, WP Rocket, Yoast, Instagram Feed, WP Mail SMTP, …) is
  third-party — never edit it.

## Deploying

Claude commits and pushes; the user deploys. After pushing, tell them to:

1. cPanel → Git Version Control → **Update from Remote** → **Deploy HEAD
   Commit**. `.cpanel.yml` copies only the theme and core plugin into
   `$HOME/public_html/wp-content`, so uploads and third-party plugins are safe.
2. **WP Rocket → Clear and preload cache.** Required for every front-end
   change — without it the site serves stale HTML and the change looks like it
   failed.
3. Hard-refresh, and confirm the loaded CSS carries the new `ver=`.

## Conventions

- Bump `BPR_THEME_VERSION` in `themes/bubbles-pet-rescue/functions.php` for
  every front-end change; it cache-busts enqueued CSS/JS and versioned asset
  URLs.
- Run `php -l` on every PHP file changed before committing.
- **Never write 4-byte emoji** into anything that reaches the database
  (patterns, form JSON, post content, options). The database is `utf8mb3` and
  emoji corrupt serialised Forminator data.
- Do not open pull requests. The user pulls `main` and deploys.

## Brand

- Colors: `--bpr-blue #1767a1`, `--bpr-deep-blue #0f486f`, `--bpr-sky #d8eef7`,
  `--bpr-aqua #8fd4dd`, `--bpr-sand #fff7ef`, `--bpr-ink #17324d`.
- Type: Nunito Sans with Caveat for script accents; Bootstrap 5.3 and Bootstrap
  Icons from jsDelivr.
- Logo: the official heart-and-paw artwork, `assets/img/bubbles-mark.svg`
  (brand blue) and `bubbles-logo-white.svg`. Header and footer use the same
  lockup — mark on the left, two-line "BUBBLES.PETS / RESCUE" text wordmark on
  the right. Keep the two consistent when either changes.

## Information architecture

- **Meet the Animals** (`/meet-the-animals/`) is the single adoption directory
  for dogs and cats, with species filter chips. `/dogs/` and `/cats/`
  301-redirect to it. Link here rather than to separate "Meet the Dogs" /
  "Meet the Cats" destinations.
- **Ways to Help** (`/ways-to-help/`) is the volunteer / donate / partner page,
  renamed from "Get Involved". It is the last primary-menu item and renders as
  the CTA pill.
- Animals come from the Dogs and Cats admin menus registered by
  `bubbles-pet-rescue-core`. The `pet_status` taxonomy controls placement:
  `adopted` hides an animal; `coming-soon`, `medical-care` and
  `adoption-pending` move it to "Available Soon".
- Contact: WhatsApp +971 50 808 3083 is the fastest channel; Instagram and
  Facebook are @Bubbles.PetsRescue. Customizer values under "Bubbles Rescue
  Settings" override the hardcoded fallbacks in the templates.

A prioritized UX/content/SEO review of the live site (Aug 2026) is published as
an artifact; several of its remaining items are wp-admin tasks for the user
rather than code changes.
