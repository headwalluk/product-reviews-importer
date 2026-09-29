# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Product Reviews Importer is a WooCommerce plugin that imports product reviews from CSV and exports approved reviews as a Walmart review-syndication CSV. Everything happens on one admin page, **WooCommerce → Import Reviews**, with Import, Export, Settings and Help tabs.

- **Namespace:** `Product_Reviews_Importer` for all classes and helper functions
- **Text Domain:** `product-reviews-importer`
- **PHP:** 8.0+ (do NOT use `declare(strict_types=1)` — breaks WordPress interop). Nothing here needs more than 8.0; check any newer type syntax (a `true` return type is 8.2) against the floor before using it
- **WordPress:** 6.0+. **WooCommerce:** 7.0+, declared with `Requires Plugins: woocommerce`
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS/JS. `phpcs` and `phpcbf` are installed globally; never add Composer

This plugin follows the maintainer's **reference plugin**, `quick-2fa`. Where this file is silent,
take structure, conventions and patterns from there. This file and the code comments must match
what the code actually does.

This plugin is published publicly on GitHub. Tracked files must contain no client names,
client URLs or client data of any kind — in code, comments, docs, fixtures or commit messages.
Real import CSVs contain customer names and emails: they live in `sample-data/`, which is
untracked, and never anywhere else.

`dev-notes/` is **private and untracked** (`.gitignore`), backed up with the dev site, and
blocked from the web by `dev-notes/.htaccess`. It is the right home for client-identifying
material. Never copy content from `dev-notes/` into a tracked file without scrubbing it, and
never reference a `dev-notes/` path from a file that ships in the release zip.

## Commands

```bash
phpcs                               # Check WordPress Coding Standards (configured in phpcs.xml)
phpcbf                              # Auto-fix coding standards violations
phpcs includes/class-plugin.php     # Check a specific file
```

```bash
wp-translate . --check-instructions   # Is the block at the end of this file still current?
wp-translate . --sync-instructions    # Update it; review the diff afterwards
wp-translate . --dry-run              # Preview; no DeepL calls, no writes
```

Run `--check-instructions` after a `wp-translate` upgrade; if it reports drift, run
`--sync-instructions` and review the diff. Never hand-edit inside the
`wp-translate:begin`/`end` markers — the block is hash-validated and edits break it.

## Testing

There is no unit-test framework and none is wanted. Behaviour is exercised against the live
dev site through WP-CLI:

```bash
# Render the admin page as an administrator
wp eval 'wp_set_current_user( 1 ); Product_Reviews_Importer\get_plugin_instance()->get_admin_hooks()->render_admin_page();'

# Read a CSV batch without importing it
wp eval '$csv = new Product_Reviews_Importer\CSV_Importer( "/path/to/file.csv" ); print_r( $csv->get_batch( 0, 50 ) );'

# Check a translation
wp eval 'switch_to_locale( "de_DE" ); load_plugin_textdomain( "product-reviews-importer", false, "product-reviews-importer/languages" ); echo __( "Import complete!", "product-reviews-importer" );'
```

Two rules: reset the state you touch afterwards, and test the **defensive** path as well as the
happy one (a blank row, a missing column, an unknown SKU). An import that ran for real leaves
reviews, users and `pri-temp/` files behind. Reviews have `comment_type` `review`, which
`get_comments()` leaves out by default — find and delete them by ID with `wp comment delete`.

## Architecture

### Design Principles

1. **Source-agnostic import** — `Review_Importer` works on normalised review arrays; adapters such as `CSV_Importer` handle source-specific parsing. A new import source is a new adapter, not a change to the engine
2. **Batch everything** — imports run in batches of `BATCH_SIZE` rows, one AJAX request each, so no single request risks a PHP timeout
3. **No stored exports** — exports are streamed straight to the browser from an `admin_post_` handler; no temp files

### Entry Point & Initialization

`product-reviews-importer.php` → `product_reviews_importer_init()` on `plugins_loaded` → checks WooCommerce is active → instantiates `Plugin`, stored in the `$product_reviews_importer` global and returned by `get_plugin_instance()`. Classes are loaded manually (no autoloader). `Github_Updater` loads only on admin, cron and WP-CLI requests.

### Request Flow

**Import:**
1. `pri_upload_csv` AJAX → `wp_handle_upload()` stores the file in `uploads/pri-temp/` (`Admin_Hooks::override_upload_dir()`) → headers validated → file path and row count stored in a transient keyed by a random upload ID
2. `assets/admin/admin.js` calls `pri_import_batch` repeatedly with an offset → `CSV_Importer::get_batch()` reads `BATCH_SIZE` rows → `Review_Importer::import_reviews()` → progress accumulated in a second transient
3. A call past the last row returns the summary, deletes the file and both transients

**Export:** the Export tab links to `admin-post.php?action=pri_export_walmart_csv` → `Review_Exporter::export_walmart_csv()` → queries approved reviews → streams the CSV with a UTF-8 BOM → `exit`.

### Key Files

| File | Purpose |
|------|---------|
| `product-reviews-importer.php` | Main plugin file: constants, class loading, init |
| `constants.php` | All constants: option keys (`OPT_`), defaults (`DEF_`), nonces, transient keys, updater settings |
| `functions-private.php` | Internal namespaced helpers: WooCommerce check, SKU lookup, CSV field definitions, server IP lookup, sanitisers. Private — integrations use the filters in `docs/developers/hooks-and-filters.md` |
| `includes/class-plugin.php` | Orchestrator: hook registration, admin menu, lazy-loads the other classes |
| `includes/class-settings.php` | Settings API registration, sanitisation callbacks, typed getters |
| `includes/class-admin-hooks.php` | Asset enqueueing, admin page render, the two import AJAX endpoints |
| `includes/class-csv-importer.php` | Streaming CSV reader (`fgetcsv`): UTF-8 BOM, header validation, batch reads, row normalisation |
| `includes/class-review-importer.php` | Source-agnostic engine: validate, match product by SKU, deduplicate, create/update review, optionally create user |
| `includes/class-review-exporter.php` | Walmart export: column definitions, row mapping, CSV streaming |
| `includes/class-github-updater.php` | In-plugin updater: checks GitHub Releases and feeds the WordPress update transient. Holds the `log()` / `log_error()` split described under **Logging** |
| `views/` | The admin page and its four tabs |

### Data Storage

- **Reviews are WooCommerce's.** Comments of type `review`, with `rating` and `verified` comment meta — WooCommerce's keys, not ours. Duplicates are matched on product ID + author email
- **Options** hold the settings, all prefixed `pri_`
- **Transients** hold the upload session and import progress (one hour), the updater's release cache, and the server's public IP (`pri_server_public_ip`)
- **Users** may be created in the Customer role, depending on `OPT_CREATE_USER_ACCOUNTS`
- There is no `uninstall.php`: options remain after the plugin is deleted

### Where the Plugin Runs

- **Request context.** Hook callbacks can run under WP-CLI, cron, AJAX and REST, where there may be no current user or admin screen. Check for what the code needs
- **Install layout.** Build URLs and paths with `admin_url()`, `wp_upload_dir()`, `PRODUCT_REVIEWS_IMPORTER_URL` and `PRODUCT_REVIEWS_IMPORTER_DIR`, never by joining strings onto the domain
- **Multisite is untested.** Settings are per-site options; created users are network-wide

## Public Contracts

Code outside the plugin depends on more than `docs/developers/hooks-and-filters.md` lists. Treat
everything in this table as a contract, whether or not it is documented as public:

| Contract | Examples | Breaks when |
|----------|----------|-------------|
| Filters | `product_reviews_importer_csv_field_definitions`, `product_reviews_importer_updater_enabled` | renamed or removed, or an argument is removed or reordered |
| Stored key strings | `pri_create_user_accounts`, `pri_min_review_length` | a constant's **value** changes. Settings stored under the old string are ignored |
| CSV column names | `SKU`, `Author Email`, `Review Stars` | renamed. Every CSV users have prepared stops validating |
| Walmart export format | `Walmart Item ID`, `MM/DD/YYYY` | a header, the column order or a value format changes. Walmart rejects the file |
| Script and style handles | `product-reviews-importer-admin` | renamed |
| AJAX and admin-post actions | `pri_upload_csv`, `pri_import_batch`, `pri_export_walmart_csv` | renamed. Bookmarked export links and any scripted use break |

- **Expose behaviour, not storage.** Give integrations functions and hooks rather than option names to read
- **Add, don't change.** New filter arguments go at the end. New behaviour gets a new filter, not a new meaning for an existing one
- **Deprecate, don't rename.** Fire the old name through `apply_filters_deprecated()` and pass its result into the new filter. Remove the old name no earlier than the next major version
- **Filters are prefixed `product_reviews_importer_`.** Constants, options, nonces, transients and AJAX actions use the short `pri_` prefix
- **Stored data has no migration path.** Changing a stored key or value format is a breaking change: stop and ask

Before changing anything in the table:

1. Name the contract you are touching
2. Assume there are consumers you can't see
3. Take the additive path if one exists
4. Record it in `CHANGELOG.md`, under **Deprecated** or as a breaking change with an upgrade notice in `readme.txt`
5. If you can't tell whether anything outside the plugin depends on it, stop and ask

## Code Conventions

### PHP Style

- **Namespace:** `Product_Reviews_Importer` for all classes. Global functions are prefixed `product_reviews_importer_`
- **No `declare(strict_types=1)`** — breaks WordPress interop
- **Single-Entry Single-Exit (SESE):** Functions should generally have one return at the end. Top-of-function guard clauses (capability checks, nonce checks, missing-input early-exits) are acceptable when they keep the rest of the function flat and readable. What is **not** acceptable: `return` statements scattered mid-function, inside loops, or nested several `if` blocks deep. Older code (`Review_Importer::import_review()`, `process_author_ip()`) predates the rule; bring it into line when you are working in it anyway
- **An `if` with one or more `elseif` branches ends in a plain `else`**, never an `elseif`, so every case is handled on purpose instead of falling through. A branch that does nothing is still written out, with a short comment. A lone `if` needs no `else`. `phpcs.xml` excludes the `if`/`elseif`/`else` codes of `Generic.CodeAnalysis.EmptyStatement` so these comment-only branches pass; empty `catch`, loop and `switch` bodies are still errors
- **No assignment inside a condition** — WPCS flags it (`AssignmentInCondition`). Assign on the line before; for a read loop, read once before the loop and again at the end of its body, as `CSV_Importer::get_batch()` does
- **No unreachable `return`.** A `return` after a call that always `exit()`s (`wp_send_json()`, `wp_die()`) is dead code, and so is a bare `return;` as the last statement of a `void` function
- **Constants for all magic strings/numbers** in `constants.php` — option keys use `OPT_`, defaults use `DEF_`. A key constant's **value** is stored data and never changes; see **Public Contracts**. Translatable strings use `__()` directly
- **Type hints and return types** on all functions, and on class properties too
- **Callbacks on hooks the plugin doesn't own take `mixed`.** Any callback earlier in the chain can hand over the wrong type, and a typed parameter turns that into a `TypeError` on a page the plugin doesn't control. Declare parameters `mixed`, check each value before use, and give a filter callback a `mixed` return that passes a value it can't use through unchanged. `Github_Updater::check_for_update()` is the pattern
- **Check what a filter returns.** Read a boolean result with `filter_var()` as under **Boolean Options**, and fall back to the default for a malformed array
- **Guard object lookups with `instanceof`, not truthiness.** `wc_get_product()`, `get_user_by()` and friends are documented as returning `Object|false`, and the user lookups are pluggable. `if ( ! $product instanceof \WC_Product )` accepts only the contract the code relies on
- **Cast at the boundary.** `get_option()`, `get_comment_meta()` and CSV cells are strings. Cast on the way in, at the point of read
- **Return `WP_Error` on failure**, not exceptions or `false`
- **Dates the plugin stores are Unix timestamps** (`time()`), formatted for display only, with `wp_date()`. Review dates are the exception: they go into WordPress's `comment_date` column in its own `Y-m-d H:i:s` format. `DATE_FORMAT` is the format the CSV's `Review Date` column is read in, not a storage format

### Template Pattern (Code-First)

Templates in `views/` use `printf()`/`echo` exclusively — no inline HTML mixed with PHP snippets. This prevents whitespace bleeding into attributes/values.

```php
// Correct
printf(
    '<button>%s</button>',
    esc_html__( 'Click', 'product-reviews-importer' )
);

// Wrong — no inline HTML
<button><?php esc_html_e( 'Click', 'product-reviews-importer' ); ?></button>
```

phpcs treats a template's scope as global, so variables a template defines itself are prefixed
`pri_` (`WordPress.NamingConventions.PrefixAllGlobals`).

### CSS and JavaScript

- **Logical properties for anything with a left or right.** Use `margin-inline-start`, `padding-inline-end` and `text-align: start`, never `margin-left` or `text-align: left`, so right-to-left locales style the correct side. Top and bottom stay physical
- **User-visible JS strings come from PHP.** Fixed strings go in the `i18n` object passed by `wp_localize_script()` in `Admin_Hooks::enqueue_assets()`. A message containing a count is built in PHP with `_n()` and returned in the AJAX response, because a localised string can't choose a plural form
- `assets/admin/admin.js` uses jQuery; new scripts should be plain JavaScript

### Logging

Two methods, deliberately split — see `Github_Updater::log()` / `log_error()`:

- `log_error()` — genuine failures (HTTP errors, malformed responses, missing assets). Logs
  **unconditionally**, so a sysadmin diagnosing a broken install sees it without touching config
- `log()` — routine flow tracing (cache hits, version comparisons, "up to date"). Logs only when `WP_DEBUG` is on

An error that only appears under `WP_DEBUG` is a silent failure in production. Never hide an
error behind a debug flag, and never leave a `catch` that records nothing. There is no logging
dependency; `error_log()` with a `phpcs:ignore` is correct.

### Comments

- One-line docblock summary per function, saying what it does. `@param`, `@return` and `@since` lines don't count
- Inline comments only where the mechanism isn't obvious: a load-order trap, an API behaving unexpectedly, a guard whose absence would be silently wrong
- Don't restate what the names already say, and don't label the obvious (`// Loop over rows`)
- Plain words: say "check", "guard" or "only when", not "gate" or "gating"
- Match the wrap width of the surrounding file; there is no fixed column
- Reasoning and history go in `docs/`, not in comments; see **Reference Files**

### Boolean Options

Use `filter_var()` with `FILTER_VALIDATE_BOOLEAN` to handle all WordPress boolean formats (`'1'`, `'yes'`, `'on'`, `true`) — never compare against a specific string:

```php
$enabled = (bool) filter_var( get_option( OPT_CREATE_USER_ACCOUNTS, DEF_CREATE_USER_ACCOUNTS ), FILTER_VALIDATE_BOOLEAN );
```

### Commit Messages

Format: `type: brief description` where type is one of: `feat:`, `fix:`, `chore:`, `refactor:`, `docs:`, `style:`, `test:`, `release:`

### Pre-Commit Workflow

1. `phpcs` — check violations
2. `phpcbf` — auto-fix
3. `phpcs` — verify clean: no errors **and no warnings**
4. If user-facing strings changed, run `wp-translate .`
5. Stage and commit

Every `phpcs:ignore` and `phpcs:disable` names the exact sniff and ends with `-- reason`.

## Release Workflow

1. Update the version in `product-reviews-importer.php` — **both** the `Version:` header and the `PRODUCT_REVIEWS_IMPORTER_VERSION` constant
2. Update `CHANGELOG.md`: move the `[Unreleased]` entries under the new version. If the version differs from the one in new `@since` tags, update those too
3. Update the `readme.txt` stable tag and changelog
4. Run `phpcs` to verify compliance
5. Tag the release in git as `v*.*.*`

The version lives in **three** places that must agree with the git tag: the header `Version:`
field, `PRODUCT_REVIEWS_IMPORTER_VERSION`, and the `readme.txt` stable tag.
`.github/workflows/release.yml` refuses to build on a mismatch, then builds the zip excluding
everything in `.distignore` and creates the GitHub Release.

**The `Version` must always correspond to a real GitHub Release tag.** The updater compares it
against the latest release, so a version that doesn't exist on GitHub degrades the update
experience on every site.

## Reference Files

`docs/` is the maintained entry point and is tracked/public. **One audience per document** — do
not mix administrator and developer material in the same file:

- `docs/importing.md`, `docs/exporting.md`, `docs/configuration.md`, `docs/troubleshooting.md` — store administrators
- `docs/developers/hooks-and-filters.md` — the public extension surface

Rationale, evidence and history belong in `docs/`, not in code comments. Where a mechanism
needs more than a sentence to justify, write it up in `docs/` and reference the file from the
code.

Supporting material (private, untracked):

- `dev-notes/00-project-tracker.md` — milestones and deferred features
- `dev-notes/01-requirements.md`, `dev-notes/architecture.md`, `dev-notes/import-logic.md` — original design notes; may be out of date, so check them against the code
- `dev-notes/archive/` — superseded material kept for reference, including the retired
  `copilot-instructions.md` and the old `patterns/` and `workflows/` guides. Archived, not
  authoritative: **this file is the standard**, not anything under `archive/`
- `CHANGELOG.md` — per-version release notes (tracked)

<!-- wp-translate:begin v=1.2.0 hash=b80494816d4de57703cc39be00d74b1701c1a1eddf86716c9dfa1fa84a2976c1 -->
## Translating this plugin (wp-translate conventions)

This plugin's `.po`/`.mo` files are generated from source by
[wp-translate](https://github.com/headwalluk/wp-translate-tool), which
machine-translates strings with DeepL. Machine translation is only as good as
the strings you give it — follow these conventions when adding or editing
user-facing text.

### 1. Disambiguate short or ambiguous strings with `_x()`

DeepL handles full sentences well but guesses badly on short, context-free
labels. Give it context with `_x()` (or `esc_html_x()`, `_ex()`):

```php
// Ambiguous out of context — DeepL may read "Sent" as "late", "Folder" as "leaflet"
__( 'Sent', 'product-reviews-importer' );

// Disambiguated — the context is passed to the translator and to DeepL
_x( 'Sent', 'email delivery status', 'product-reviews-importer' );
_x( 'Folder', 'IMAP mailbox', 'product-reviews-importer' );
_x( 'Open', 'verb; button label', 'product-reviews-importer' );
```

The context (2nd argument) is never shown to users. Use it whenever a string is a
single word, a short label, or has more than one plausible meaning.

### 2. Use placeholders, never concatenation

Build dynamic text with `printf`/`sprintf` so the whole sentence translates as a
unit, and add a `translators:` comment to explain each placeholder:

```php
/* translators: %s is the user's display name */
printf( esc_html__( 'Welcome back, %s', 'product-reviews-importer' ), $name );
```

Never split a sentence across multiple translation calls — word order differs
between languages.

### 3. Use `_n()` for anything that can be counted

Never build a count-dependent sentence by hand, and never settle for a single
form that reads correctly only for one number. Languages differ in how many
plural forms they have — English and German have two, French treats 0 as
singular, Polish and Russian have three, Japanese has one, Arabic has six — and
`_n()` is the only way to express that.

```php
// Wrong — "1 reviews", and untranslatable into languages with other forms
printf( esc_html__( '%d reviews', 'product-reviews-importer' ), $count );

// Right — wp-translate fills every form the target locale needs
printf(
    esc_html( _n( '%d review', '%d reviews', $count, 'product-reviews-importer' ) ),
    $count
);
```

Keep the placeholder in **both** forms, even when the singular reads fine
without it (`'%d review'`, not `'One review'`) — some locales use the singular
slot for other numbers too.

For a short or ambiguous countable noun, use `_nx()` — the plural equivalent of
`_x()` — so the context reaches DeepL:

```php
// "Review" alone is ambiguous: critique? opinion? inspection?
_nx( '%d review', '%d reviews', $count, 'customer feedback on a company', 'product-reviews-importer' );
```

**Locales needing more than two forms will have their extra slots left empty for
a human translator.** DeepL supplies a singular and a plural; nobody can invent
Polish's third form from those, and wp-translate deliberately leaves it blank
rather than filling it with a plausible guess. Expect to see empty
`msgstr[2]` entries in `pl_PL` — that is correct behaviour, not a failure.

### 4. Acronyms and technical tokens

wp-translate keeps common acronyms (`TLS`, `API`, `SMTP`, `URL`, `ID`, `UTC`, …)
verbatim automatically. If you introduce an unusual acronym or product name that
must not be translated, keep it as its own standalone string so it is recognised,
or ask the maintainer to add it to the tool's acronym list.

### 5. Don't translate dates — let WordPress localise them

Never add month or day-of-week names (full or abbreviated) as translatable
strings. DeepL frequently mistranslates short forms like `Mon`, `Tue`, `Jan`,
`Feb` even with context hints. WordPress already ships locale-aware names — use
`$wp_locale`:

```php
global $wp_locale;
$wp_locale->get_month( $month_number );        // "January" (1-based)
$wp_locale->get_month_abbrev( $month_name );   // "Jan"
$wp_locale->get_weekday( $weekday_number );     // "Monday" (0 = Sunday)
$wp_locale->get_weekday_abbrev( $weekday_name ); // "Mon"
```

For formatted dates, prefer `wp_date()` / `date_i18n()`, which localise month and
day names automatically.

### 6. English source dialect

Write source strings in standard English. wp-translate handles English targets
locally (no DeepL): `en`/`en_US` use the source as-is, and `en_GB`/`en_AU`/… get
American spellings converted to British automatically (`color` → `colour`).

### Running wp-translate

After changing strings, regenerate translations:

```bash
wp-translate /path/to/this-plugin              # auto-detect locales from languages/
wp-translate /path/to/this-plugin en_GB,fr_FR  # explicit locales
wp-translate /path/to/this-plugin --dry-run    # preview; no API calls, no writes
```

Requires WP-CLI (`wp`) and a DeepL API key at `~/.config/deepl.env`. The tool
regenerates the `.pot` from source, translates new/changed strings for each
locale, and compiles the `.mo` files.
<!-- wp-translate:end -->
