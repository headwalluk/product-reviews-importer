# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress/WooCommerce plugin for importing and exporting product reviews. CSV import with batch processing, and export for Walmart review syndication. Requires WordPress 6.0+, WooCommerce 7.0+, PHP 8.0+. Text domain: `product-reviews-importer`.

## Commands

```bash
# Check WordPress coding standards (globally installed, no composer)
phpcs

# Auto-fix coding standards issues
phpcbf
```

PHPCS is configured in `phpcs.xml` with the full WordPress ruleset. No automated test suite exists.

## Architecture

The plugin follows a source-agnostic import design. The core import engine (`Review_Importer`) works with normalized review data arrays, while adapters (`CSV_Importer`) handle source-specific parsing. This allows adding new import sources without modifying the core engine.

**Entry point:** `product-reviews-importer.php` → `product_reviews_importer_init()` on `plugins_loaded` → checks WooCommerce is active → instantiates `Plugin` class stored in `$product_reviews_importer` global.

**Core classes in `includes/`:**
- **Plugin** — Orchestrator. Registers all WordPress hooks, lazy-loads Settings, Admin_Hooks, and Review_Exporter. Settings must be instantiated early (before `admin_init`).
- **Settings** — WordPress Settings API registration, sanitization callbacks, typed getter methods.
- **Admin_Hooks** — Admin UI rendering, asset enqueueing, AJAX endpoints (`pri_upload_csv`, `pri_import_batch`). Uses `wp_handle_upload()` with `override_upload_dir()` filter to store CSV uploads in `pri-temp/`.
- **Review_Importer** — Source-agnostic import engine. Validates, deduplicates (by product_id + author_email), creates/updates reviews and optionally creates user accounts.
- **CSV_Importer** — Streaming CSV parser using `fgetcsv`. Handles UTF-8 BOM, processes in batches of 50 rows (constant `BATCH_SIZE`).
- **Review_Exporter** — Export engine. Queries approved WooCommerce reviews, maps to target format (currently Walmart syndication CSV), and streams download. Uses `admin_post_` action hook for direct file download — no AJAX or temp files needed.

**Import flow:** Upload CSV via AJAX → validate structure → store file path + metadata in transient → client-side JS orchestrates sequential batch AJAX calls → each batch: CSV_Importer reads 50 rows → Review_Importer processes them → progress updated in transient → cleanup on final batch.

**Export flow:** User clicks export link on Export tab → `admin_post_{action}` fires → Review_Exporter queries all approved reviews → streams CSV with headers directly to browser → `exit`.

**Supporting files:**
- `constants.php` — All magic values, option keys (`OPT_` prefix), defaults (`DEF_` prefix), nonce actions, export actions.
- `functions-private.php` — Utility functions prefixed `pri_` (WooCommerce check, SKU lookup, CSV field definitions, sanitization).
- `admin-templates/` — Tabbed admin UI (Import, Export, Settings, Help) using printf/echo pattern.

## Coding Conventions

These are project-specific conventions beyond standard WordPress practices:

- **Single-Entry Single-Exit (SESE):** Functions must have one return statement at the end. Use a result variable that different code paths modify rather than early returns.
- **No `declare(strict_types=1)`:** WordPress/WooCommerce pass strings where you might expect ints; strict types breaks hook interoperability.
- **All magic values in `constants.php`:** Option keys use `OPT_` prefix, defaults use `DEF_` prefix. Exception: translatable strings use `__()` directly.
- **Boolean options:** Always use `filter_var($value, FILTER_VALIDATE_BOOLEAN)` — never compare against specific strings like `'yes'`.
- **Date/time storage:** Human-readable format `Y-m-d H:i:s T`, not Unix timestamps.
- **Function prefixing:** Global functions use `product_reviews_importer_` prefix. Namespaced helper functions use `pri_` prefix. Classes use the `Product_Reviews_Importer` namespace.
- **Error handling:** Return `WP_Error` on failure, not exceptions or false.
- **CSV field definitions are filterable** via `product_reviews_importer_csv_field_definitions` filter.
- **No composer:** `phpcs` and `phpcbf` are installed globally. Never introduce composer to this project.

## Release Process

Tag with `v*.*.*` format → GitHub Actions workflow (`.github/workflows/release.yml`) builds zip excluding files listed in `.distignore` → creates GitHub Release with artifacts.
