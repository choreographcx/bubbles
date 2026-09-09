---
name: deploy-check
description: Diagnose why a change to the Bubbles Pets Rescue site is not visible on bubblespetsrescue.ae, separating "not pushed", "not deployed" and "cached". Use when the user says the site is not reflecting changes, the update did not work, the old version is still showing, or a fix they asked for appears to have been ignored.
---

# Is it not pushed, not deployed, or cached?

Do not re-edit the code first. The change is usually correct and stuck in one
of three layers. Identify the layer, fix that, and tell the user which it was.

## 1. Confirm the commit is on the remote

```bash
git log --oneline origin/main -3
```

The user's cPanel screen shows the deployed SHA — if theirs is older than
`origin/main`, they simply have not pulled. Note that a fresh container may be
on the stale default branch; see the branch note in CLAUDE.md.

## 2. Confirm the files actually reached the server

Fetch the deployed assets directly, not the page:

```bash
base=https://www.bubblespetsrescue.ae/wp-content/themes/bubbles-pet-rescue
curl -sS --max-time 20 "$base/style.css" | grep -c '<new-class-name>'
curl -sS --max-time 20 -o /dev/null -w "%{http_code} %{size_download}\n" \
  "$base/assets/img/<changed-asset>"
```

If the new CSS rules and assets are present, the deploy worked — skip to
step 3. If they are missing, the user needs
**Update from Remote → Deploy HEAD Commit** in cPanel Git Version Control.

## 3. Check whether the page HTML is cached

```bash
curl -sS -L "https://www.bubblespetsrescue.ae/" -o /tmp/live.html
grep -o 'ver=1\.[0-9.]*' /tmp/live.html | sort -u   # theme version served
grep -o 'WP Rocket[^"]*' /tmp/live.html | head -1   # cache plugin marker
```

Files current but HTML stale — old `ver=`, old markup, missing new sections —
means **WP Rocket is serving a cached page**. The fix is on the user's side:
wp-admin → WP Rocket → **Clear and preload cache**, then hard-refresh.

This is the most common cause, and files-current-but-HTML-old is its signature.

## 4. Rule out the "correct but overridden" case

Some changes deploy and un-cache correctly yet still look absent because
WordPress data outranks the theme:

- A **Site Icon** in the Customizer overrides theme favicon tags.
- A page built from a **block pattern** keeps its original content; editing the
  pattern file changes nothing on existing pages.
- **Menus** assigned in Appearance → Menus override the theme's fallback menu
  functions.
- **Customizer theme mods** override hardcoded template fallbacks.

Say plainly which of these is in play and what the user must change in
wp-admin.

## Reporting

State the layer that was stale, what you verified (with the evidence), and the
exact remaining steps for the user. Do not claim a deploy succeeded without
having fetched something from the live server.
