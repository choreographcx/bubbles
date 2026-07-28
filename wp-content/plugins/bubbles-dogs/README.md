# Bubbles Dogs

Adoptable dog listings for the Bubbles Pet Rescue website, plus tooling to
bulk-load dogs from an Instagram or Facebook export.

## What it gives you

- A **Dogs** section in wp-admin with proper fields — sex, age, weight, health,
  temperament — instead of everything living in one blob of text.
- A **filterable listing** at `/adopt-a-dog/`, filterable by size, age group
  and breed. Each filter is a real shareable URL.
- **Status handling.** A dog marked *Adopted* drops off the listing but keeps
  their page, so links people shared months ago still work.
- An **apply button** that carries the dog's name into your Forminator
  adoption form, so every application says which dog it is for.
- A **CSV importer** for loading many dogs at once, and a parser that drafts
  that CSV from a Meta data export.

## Installing

The plugin lives in this git repo, so it deploys the same way as everything
else:

```bash
cd ~/gitrepo
git pull origin claude/automate-adoption-posts-wbv55k
git add -A && git commit -m "Add Bubbles Dogs plugin"
```

Then click **Manage → Deploy HEAD Commit** on the cPanel Git Version Control
page, and activate **Bubbles Dogs** under Plugins in wp-admin.

Activating creates the default Size and Age group terms and flushes the
permalinks, so `/adopt-a-dog/` works immediately.

## Setting up the apply button

1. In wp-admin go to **Dogs → Settings**.
2. Put the URL of your adoption application page in **Adoption form page**.
3. Leave **Query parameter** as `dog`.
4. Open your Forminator adoption form (the existing form id 32), add a
   **Hidden** field, and set its default value to **Query parameter** with the
   name `dog`.

Every application now records which dog it was for, and the field fills in by
itself when someone arrives from a dog's page.

## Getting your current dogs in

The bottleneck here is not creating the posts — it is getting clean, structured
information out of captions that were written as prose. So the workflow puts a
spreadsheet in the middle, where correcting thirty rows takes minutes rather
than thirty trips through the admin screens.

### 1. Export your posts from Meta

In the Instagram app or Meta Accounts Centre: **Your information and
permissions → Download your information**. Choose **JSON** (not HTML), include
**Posts** and media, and pick a date range covering your current dogs. Do the
same for the Facebook Page if its captions differ.

Unzip it somewhere you can find.

### 2. Draft the CSV

```bash
php wp-content/plugins/bubbles-dogs/tools/parse-social-export.php \
  --export=/path/to/unzipped-export \
  --out=dogs-draft.csv
```

Options:

| Option | Default | What it does |
|---|---|---|
| `--export` | *(required)* | The unzipped export folder |
| `--out` | `dogs-draft.csv` | Where to write the CSV |
| `--since` | 18 months ago | Ignore posts older than `YYYY-MM` |
| `--min-words` | `12` | Skip captions shorter than this — usually reshares |

The script groups posts it thinks belong to the same dog, keeps the longest
caption as the bio, and guesses sex, age, weight, size, health flags and
"good with" from the wording.

### 3. Check the CSV — this is the part that matters

Open it in Excel or Google Sheets and work down the **needs_review** column.
It flags what the script could not work out. Expect to correct a fair number of
rows; the guesses are genuinely guesses.

Watch for the three things it gets wrong most:

- **A row that isn't a dog.** Fundraising and thank-you posts occasionally slip
  through. These are flagged `check-name-may-not-be-a-dog`. Delete them.
- **The same dog twice** under different spellings, or under a nickname.
  Merge the rows and combine the `photos` column with `|` between paths.
- **One row that is really a litter.** Split it into one row per puppy.

Then **clear the needs_review column**. The importer refuses any row that still
has something in it, which is the safety net against importing unchecked data.

Columns are documented in `tools/dogs-template.csv`, which has two filled-in
example rows.

### 4. Import

From cPanel **Terminal**, in your site directory:

```bash
# See what would happen, without writing anything
wp bubbles-dogs import dogs.csv --dry-run

# Import as drafts so you can look before they go live
wp bubbles-dogs import dogs.csv \
  --media-root=/path/to/unzipped-export \
  --post-status=draft
```

| Flag | What it does |
|---|---|
| `--dry-run` | Report only, write nothing |
| `--media-root` | Folder that relative photo paths resolve against |
| `--skip-photos` | Create listings without images — a fast first pass |
| `--post-status` | `draft` or `publish` for new dogs (default `publish`) |

Re-running is safe. Rows are matched on the dog's name, so a second run updates
the existing listings instead of duplicating them, and photos already imported
are not uploaded twice.

If `wp` is not available on your hosting, install it into your home directory:

```bash
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar && mkdir -p ~/bin && mv wp-cli.phar ~/bin/wp
export PATH="$HOME/bin:$PATH"
```

### A note on photo quality

Images pulled back out of Instagram have been recompressed and often
square-cropped. Where you still have the originals on a phone or in Drive, use
those instead — the website is where photo quality actually converts, and you
can swap the main photo per dog afterwards in wp-admin.

## Shortcodes

Drop these into any page:

| Shortcode | What it shows |
|---|---|
| `[bubbles_dogs]` | Every dog currently looking for a home |
| `[bubbles_dogs filters="yes"]` | The same, with a size and age filter bar |
| `[bubbles_dogs status="adopted"]` | A Happy endings page |
| `[bubbles_dogs size="small" limit="6"]` | Filtered and capped |

`size`, `age` and `breed` take term slugs, comma-separated.

## Notes for whoever maintains this

**Fields are defined in one place.** `BPR_Dogs_Fields::schema()` drives the
admin meta boxes, the sanitising on save, the CSV importer and the front-end
detail list. Add a field there and it appears in all four. There is deliberately
nowhere else to define one.

**Output is filters, not templates.** The dog details, apply button and gallery
are appended via `the_content`, which works with classic themes, block themes
and page builders alike. If you want full control, add `single-dog.php` to the
theme — WordPress picks it up and you can unhook
`BPR_Dogs_Display::append_dog_details`.

**Dogs with no status meta count as available.** Anything created before the
status field existed still shows on the listing rather than silently vanishing.

**`travel_ready`** covers rehoming overseas with a flight volunteer. If you only
rehome inside the UAE, delete that entry from the schema.

## Not included yet

Automatic posting out to Instagram and Facebook. The groundwork is here — the
Instagram account is a Business account linked to the Page, and the media
library gives photos the public URLs the Content Publishing API needs. Two
routes when you want it:

- **Jetpack Social** — connect both accounts and publishing a dog auto-shares.
  No code, but no control over the caption.
- **Meta Graph API** in this plugin — templated captions, watermarks, an
  automatic reshare of long-stay dogs. Needs a Meta app and a long-lived Page
  token, and someone to own that token when it expires.
