# WordPress theme and plugin rules

Applies to `themes/bubbles-pet-rescue/` and
`plugins/bubbles-pet-rescue-core/`.

## Block patterns are one-time inserts

Editing a file in `patterns/` only changes what gets inserted into **new**
pages. Pages already built from a pattern keep their old content forever.
Whenever a pattern's copy, links, or headings change, name the live pages the
user must edit in wp-admin to match — otherwise the site and the repo silently
diverge.

## Images and alt text

`wp_get_attachment_image()` emits `alt=""` when the attachment has no alt meta,
silently shipping inaccessible images. Always pass an explicit `alt` built from
the content — for pets, the name plus age, gender and breed. Add
`loading="lazy"` to below-the-fold images and leave above-the-fold ones eager.

## Menus

- Every `wp_nav_menu()` call needs a `fallback_cb`, so the site is never
  menu-less before a location is assigned.
- Registered locations: `primary`, `footer_get_involved`, `footer_about`.
- The primary menu's **last item** renders as the CTA pill. Changing which page
  should be the call to action means reordering the menu, not restyling.
- When menu contents change, update the fallback function *and* tell the user
  to mirror the change in Appearance → Menus; the fallback only shows when no
  menu is assigned.

## Templates

- `page-{slug}.php` applies automatically to a page with that slug. Prefer this
  over a `Template Name:` header so the user only has to create the page — no
  template picker step to forget.
- Every template's `<main>` carries `id="bpr-main"` for the skip link.

## Customizer and site identity

- A Site Icon set in the Customizer always wins over theme-printed favicon
  tags. Gate any theme favicon on `!has_site_icon()`, and check the live HTML
  for existing `rel="icon"` tags before building favicon plumbing at all.
- Read contact and social URLs as `get_theme_mod('key') ?: '<known fallback>'`
  so a saved-but-empty value still falls back to something real.

## Redirects

Post-type archive redirects belong on `template_redirect` and must no-op while
their destination page does not exist, so the site keeps working in the window
between deploying code and the user creating the page.
