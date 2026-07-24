# Bubbles Pet Rescue — WordPress site files

This repository version-controls the **`wp-content`** part of the Bubbles
WordPress site (themes, plugins, and uploads). WordPress core files and
`wp-config.php` are intentionally **not** tracked — see `.gitignore`.

## The two locations in cPanel

| Location | Path | What it is |
|---|---|---|
| **This git repo** | `/home/bubblespetrescue/gitrepo` | Version history of your files |
| **Live website** | `/home/bubblespetrescue/public_html` | What visitors actually see |

Putting files in the repo does **not** change the live site until you *deploy*
(see step 5). Deployment copies `wp-content` from the repo into `public_html`.

## How to get your files in (one-time setup)

1. **Upload your zip.** On the Git Version Control page, click the
   **File Manager** button (it opens right inside the repo folder). Use
   *Upload* to add your `wp-content` zip.
2. **Extract it** in File Manager so you end up with a `wp-content/` folder
   at the top level of the repo (i.e. `gitrepo/wp-content/themes`, etc.).
   Delete the leftover `.zip` afterward.
3. **Open Terminal** in cPanel (Tools → Terminal), then run:

   ```bash
   cd ~/gitrepo
   git add -A
   git commit -m "Add wp-content for Bubbles site"
   ```

   > cPanel's web UI can pull and deploy, but **committing must be done from
   > the Terminal** (or over SSH). That's normal.

4. Refresh the Git Version Control page — the "No checked-out branch"
   message is now gone and you'll see your branch and history.
5. **Deploy to the live site** by clicking **Manage → Deploy HEAD Commit**.
   That runs `.cpanel.yml`, copying `wp-content` into `public_html`.

## Everyday workflow after that

Edit files (in File Manager or locally) → `git add -A && git commit -m "..."`
→ **Deploy HEAD Commit**. Each commit is a restore point you can roll back to.
