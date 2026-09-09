# CSS and layout rules

Applies to `themes/bubbles-pet-rescue/style.css` and the theme's CSS assets.

## Containing blocks break fixed positioning

`backdrop-filter`, `filter`, `transform`, `perspective`, `contain` and
`will-change` on an ancestor make that element the containing block for
`position: fixed` descendants. The header's frosted-glass `backdrop-filter`
once clipped the mobile slide-in menu inside the header bar, which looked like
a broken off-canvas but was a containing-block problem.

If a fixed overlay renders clipped, cut off, or positioned relative to the
wrong box, check ancestors for these properties first, then scope the effect to
the breakpoint where no fixed descendant exists.

## Bootstrap responsive off-canvas

With `offcanvas-lg`, the element is a fixed panel *below* the breakpoint and an
ordinary inline flex child *above* it. **All** panel styling — width,
background, shadow, radius, height — must live inside the mobile media query.
An unscoped `width` on `.offcanvas-lg` squeezes the desktop nav into a narrow
box and pushes its links outside the page grid.

## Before changing widths

Grep for every `max-width` in the chain — page container class, section
wrapper, inner grid — and change them together. This layout has carried caps at
several levels simultaneously, so fixing only the outer one leaves the content
visibly narrow.

## Pattern-based styling

Alternating treatments (`:nth-child(3n + 2)`, `:nth-child(even)`) read as
"random" to users, and usually have a second copy inside a media query that
re-alternates at another breakpoint. When removing one, remove every
breakpoint's copy.

## Mobile rules live in two files

`assets/css/mobile-layout-v1.2.4.css` is enqueued after `style.css` and carries
its own responsive overrides. The `v1.2.4` in the filename is historical, not a
staleness marker — the file is current and is versioned by
`BPR_THEME_VERSION` like everything else. When hunting or changing a responsive
rule, grep both files, or a later override will silently undo the fix.

## Housekeeping

- Append new rules to the end of `style.css` in labelled blocks; the file is
  long and frequently edited by script.
- The `Version:` in the `style.css` header is cosmetic. The real cache-busting
  version is `BPR_THEME_VERSION` in `functions.php`.
- Respect `prefers-reduced-motion` for any transition or transform added.
- Keep decorative images and icons out of the accessibility tree
  (`alt=""` plus `aria-hidden="true"`), and give interactive controls a visible
  `:focus` state.
