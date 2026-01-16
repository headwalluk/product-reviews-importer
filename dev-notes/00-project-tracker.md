# Product Reviews Importer - Project Tracker

**Last Updated:** 16 January 2026  
**Current Version:** 0.3.0  
**Status:** Active Development - CSV Import Engine Complete

---

## Current Sprint: Foundation & Infrastructure

### ✅ Completed

**Phase 0: Project Setup**
- [x] Requirements document created
- [x] File structure established
- [x] Main plugin file created with WooCommerce dependency
- [x] Constants file with all magic values
- [x] Private functions file (namespaced helpers)
- [x] Plugin class with lazy loading
- [x] Settings class with WordPress Settings API
- [x] Admin Hooks class for admin functionality
- [x] HPOS compatibility declared
- [x] Translation ready (text domain configured)
- [x] README.md and readme.txt created
- [x] CHANGELOG.md initialized
- [x] PHPCS configuration (phpcs.xml)
- [x] Code standards compliance verified
- [x] Plugin successfully activated

**Infrastructure:**
- Namespace: `Product_Reviews_Importer`
- Function prefix: `pri_` (root namespace functions)
- Text domain: `product-reviews-importer`
- Admin page: WooCommerce > Reviews Importer

**Settings Configured:**
- Create user accounts for new reviewers (boolean, default: false)
- Minimum review length (integer, default: 10)
- Default IP address (string, defaults to server public IP via icanhazip.com)
- Auto-approve imported reviews (boolean, default: true)
- Mark as verified purchase (boolean, default: false)

**Recent Improvements:**
- CSV_Importer class with native PHP fgetcsv() parsing
- UTF-8 BOM detection and multi-line field support
- Memory-efficient streaming for large files
- Column mapping and validation complete
- Author name uses WordPress user display_name when email matches
- `get_server_ip()` fetches public IP from icanhazip.com (cached 7 days)
- All functions refactored to SESE pattern
- Settings tab preserves hash on save
- End-to-end CSV import tested and verified

---

## Current Focus: AJAX Import Orchestration

**Next Steps:**
1. Add AJAX file upload handler in Admin_Hooks
2. Implement batch processing AJAX endpoint
3. Add frontend JavaScript for upload, validation, progress
4. Wire up progress bar and results display

**See:** [`dev-notes/architecture.md`](architecture.md) for component design and [`dev-notes/import-logic.md`](import-logic.md) for processing details.

---

## Milestone 1: Core Import Engine (Foundation) 🏗️

**Goal:** Build source-agnostic review import engine.

**Status:** ✅ COMPLETED

### Tasks

- [x] Create `includes/class-review-importer.php`
  - [x] `import_reviews(array $reviews): array` - Batch processing
  - [x] `import_review(array $review_data)` - Single review processing
  - [x] Product matching by SKU (use parent ID for variations)
  - [x] Duplicate detection (product_id + author_email)
  - [x] User account creation/lookup (based on setting)
  - [x] Review text sanitization (wp_kses with br/p tags)
  - [x] Comment creation with `comment_type=review`
  - [x] Rating metadata (`rating` and `verified` keys)
  - [x] Error handling with WP_Error
  - [x] Return structured results (success count, errors array)

**Acceptance Criteria:**
- ✅ Can import array of normalized review data
- ✅ Handles duplicates (updates content + rating only)
- ✅ Creates WordPress comments with correct type and meta
- ✅ Respects "create user accounts" setting
- ✅ Returns actionable error messages
- ✅ Unit testable (no dependencies on CSV or AJAX)

**Implementation Notes:**
- All methods follow SESE pattern (single return statement)
- Settings integration complete (auto-approve, verified purchase)
- Proper WordPress comment structure with HPOS compatibility
- Error logging for user creation failures
- One intentional PHPCS warning for debug error_log remains

---

## Milestone 2: Admin Interface (UI Foundation) 🎨

**Goal:** Tabbed admin interface with Import, Settings, Help tabs.

**Status:** ✅ COMPLETED

### Tasks

**Admin Templates:**
- [x] Create `admin-templates/` directory
- [x] Create `admin-templates/main-page.php` (tabbed wrapper)
- [x] Create `admin-templates/tab-import.php` (import interface)
- [x] Create `admin-templates/tab-settings.php` (global settings form)
- [x] Create `admin-templates/tab-help.php` (documentation)

**Assets:**
- [x] Create `assets/admin/` directory
- [x] Create `assets/admin/admin.css` (tab styling, progress bar)
- [x] Create `assets/admin/admin.js` (hash-based tab navigation)

**Admin Hooks Updates:**
- [x] Update `enqueue_assets()` to load CSS/JS on plugin page
- [x] Add nonce localization for AJAX calls
- [x] Add `preserve_settings_hash()` filter for redirect handling

**Acceptance Criteria:**
- ✅ Three tabs render correctly (Import, Settings, Help)
- ✅ Tab state persists with URL hash
- ✅ Browser back/forward works with tabs
- ✅ Settings form displays registered options
- ✅ Help tab shows basic documentation
- ✅ No PHP errors or warnings

**Implementation Notes:**
- Hash-based navigation fully functional
- Settings save correctly and preserve tab state
- Admin page accessible at WooCommerce > Reviews Importer
- All forms properly nonce-protected
- Responsive styling with WordPress admin aesthetics

---

## Milestone 3: CSV Source Adapter (Data Layer) 📄

**Goal:** Parse CSV files, normalize to review format, provide batched access.

**Status:** ✅ COMPLETED

### Tasks

- [x] **INVESTIGATED:** CSV parsing approach - Native PHP fgetcsv() chosen
- [x] **INVESTIGATED:** Multi-line fields - Handled automatically by fgetcsv()
- [x] **INVESTIGATED:** Encoding - UTF-8 BOM detection implemented
- [x] **INVESTIGATED:** Streaming - Line-by-line reading for memory efficiency

- [x] Create `includes/class-csv-importer.php`
- [x] Constructor: Accept file path
- [x] `parse_headers()` - Detect and validate CSV columns
- [x] `get_batch(int $offset, int $limit): array` - Return normalized reviews
- [x] `get_total_count(): int` - Count rows in CSV (cached)
- [x] `validate(): array` - Check structure, data types, required fields
- [x] Column mapping to Review_Importer format
- [x] Normalize data with `_row_number` for error reporting

**Acceptance Criteria:**
- ✅ Parses CSV with quoted multi-line fields
- ✅ Handles UTF-8 with/without BOM
- ✅ Streams large files (doesn't load entire file into memory)
- ✅ Returns normalized review data matching architecture spec
- ✅ Validates before import (headers, required columns, data rows)
- ✅ Reports errors with specific row numbers

**Implementation Notes:**
- Native PHP fgetcsv() for zero dependencies
- UTF-8 BOM detection: checks for \xEF\xBB\xBF marker
- Memory efficient: reads line-by-line, caches row count
- Column mapping: CSV headers → product_sku, author_name, etc.
- Empty row handling: skips rows with all empty fields
- Tested end-to-end with real product data (SKU E-SOL-REP-U4, product_id 13170)
- UTF-8 with BOM, without BOM

---

## Milestone 4: AJAX Import Orchestration (Integration) 🔄

**Goal:** Wire up UI to CSV adapter and core importer with AJAX batching.

**Status:** Not Started

### Tasks

**AJAX Handlers (in `class-admin-hooks.php`):**
- [ ] `ajax_upload_csv()` - Handle file upload
  - [ ] Security: Validate file type, size, nonce
  - [ ] Move to secure temp directory
  - [ ] Create CSV_Importer instance
  - [ ] Call `validate()` and return results
  - [ ] Store file path in transient
  - [ ] Return JSON response

- [ ] `ajax_import_batch()` - Process one batch
  - [ ] Get file path from transient
  - [ ] Create CSV_Importer instance
  - [ ] Get batch: `get_batch($offset, BATCH_SIZE)`
  - [ ] Create Review_Importer instance
  - [ ] Import batch: `import_reviews($batch)`
  - [ ] Update progress transient
  - [ ] Return results (success count, errors) as JSON

- [ ] `ajax_cancel_import()` - Cancel in-progress import
  - [ ] Delete transients
  - [ ] Cleanup temp file

**Constants (add to `constants.php`):**
- [ ] `NONCE_CSV_PROCESS` - for batch processing
- [ ] `TRANSIENT_CSV_FILE_PATH` - store uploaded file path
- [ ] `UPLOAD_TEMP_DIR` - relative path for temp uploads

**Frontend (in `admin.js`):**
- [ ] File upload handler
- [ ] Trigger validation AJAX call
- [ ] Display validation results
- [ ] Import button click handler
- [ ] Batch processing loop (recursive AJAX calls)
- [ ] Progress bar updates
- [ ] Display final results (success/error counts)
- [ ] Error log display

**Import Tab Template Updates:**
- [ ] File upload form with nonce
- [ ] Validation results section
- [ ] Progress bar HTML
- [ ] Results summary section
- [ ] Error log table

**Acceptance Criteria:**
- ✅ User can upload CSV file
- ✅ Validation runs and displays errors before import
- ✅ Progress bar updates in real-time during import
- ✅ Import processes in batches (doesn't timeout)
- ✅ Success/error counts display correctly
- ✅ Can cancel in-progress import
- ✅ Temp files cleaned up after import

**Testing:**
- Upload small CSV, verify imports correctly
- Upload large CSV, verify batching works
- Upload CSV with errors, verify validation catches them
- Cancel import mid-process
- Check temp directory cleanup
- Test with slow server (verify no timeouts)

---

## Milestone 5: Settings Integration (Configuration) ⚙️

**Goal:** Wire up settings tab to existing Settings class.

**Status:** ✅ COMPLETED

### Tasks

- [x] Update `tab-settings.php` to use Settings API
  - [x] Render settings sections and fields
  - [x] Add submit button with nonce
  - [x] Display success/error messages

- [x] Settings form styling
- [x] Hash preservation on save (wp_redirect filter)

**Acceptance Criteria:**
- ✅ Settings form displays all registered options
- ✅ Settings save correctly
- ✅ Success message displays after save
- ✅ Values persist and display correctly on reload
- ✅ Returns to Settings tab after save

**Implementation Notes:**
- All 5 settings functional: create users, min length, default IP, auto-approve, verified
- IP address fetches and caches public IP from icanhazip.com
- Settings validation working (IP format, min length range)
- Tab state preserved on settings save

---

## Milestone 6: Polish & Testing (Production Ready) ✨

**Goal:** Plugin ready for staging site testing.

**Status:** Not Started

### Tasks

**Help/Documentation:**
- [ ] Write Help tab content
  - [ ] CSV format requirements
  - [ ] Sample CSV download link
  - [ ] Field descriptions
  - [ ] Troubleshooting guide

**Error Handling:**
- [ ] User-friendly error messages
- [ ] Admin notices for common issues
- [ ] Graceful degradation if WooCommerce disabled

**Code Quality:**
- [ ] Run PHPCS on all files
- [ ] Fix code standards violations
- [ ] Add PHPDoc to all methods
- [ ] Remove debug code / error_log statements

**Security Audit:**
- [ ] Verify all nonces
- [ ] Verify all capability checks
- [ ] Verify all input sanitization
- [ ] Verify all output escaping
- [ ] File upload security review

**Performance:**
- [ ] Test with 1000+ row CSV
- [ ] Monitor memory usage
- [ ] Verify batch size is optimal
- [ ] Check for N+1 queries

**User Experience:**
- [ ] Loading states for AJAX calls
- [ ] Disable import button during processing
- [ ] Helpful validation messages
- [ ] Progress percentage display

**Acceptance Criteria:**
- ✅ All PHPCS violations fixed
- ✅ All security checks in place
- ✅ Can import large CSV without timeout
- ✅ Help documentation complete
- ✅ Ready for staging site deployment

**Testing:**
- Manual QA on staging site
- Test with real WooCommerce products
- Test with client's actual review data
- Cross-browser testing
- Mobile responsive check

---

## Future Milestones (Post-MVP)

### Milestone 7: Import History & Logging
- [ ] Create database table for import history
- [ ] Track import source, date, counts
- [ ] Display import history in admin
- [ ] Download error logs

### Milestone 8: Amazon Reviews Importer
- [ ] Create `class-amazon-importer.php`
- [ ] Amazon API integration
- [ ] Add Amazon Settings tab
- [ ] Cron handler for automated sync
- [ ] Map Amazon data to normalized format

### Milestone 9: Export Functionality
- [ ] Export reviews to CSV
- [ ] Filter by product, date range, rating
- [ ] Download generated CSV

---

## Development Workflow

**Before Each Coding Session:**
1. Pull latest code
2. Review current milestone tasks
3. Update task status in this file

**During Development:**
1. Work on one task at a time
2. Test each component individually
3. Run PHPCS before committing
4. Update CHANGELOG.md

**Before Committing:**
1. Run `phpcs` - fix violations
2. Run `phpcbf` - auto-fix issues
3. Test changes manually
4. Update this tracker
5. Write clear commit message

**After Each Milestone:**
1. Full manual test on staging site
2. Update version number
3. Update CHANGELOG.md
4. Tag release (if appropriate)

---

## Code Standards Checklist (Ongoing)

✅ All files pass PHPCS  
✅ WordPress Coding Standards followed  
✅ PHP 8.0+ type hints used  
✅ No `declare(strict_types=1)`  
✅ All magic values in constants.php  
✅ Proper doc comments on all functions/classes  
✅ Security: Nonces, capability checks, input sanitization, output escaping  
✅ WooCommerce HPOS compatibility (use WC_Product/WC_Order methods)  
✅ Security: nonces, capability checks, sanitization  
✅ HPOS compatible  

---

## File Structure

```
product-reviews-importer/
├── product-reviews-importer.php   ✅ Main plugin file
├── constants.php                   ✅ Plugin constants
├── functions-private.php           ✅ Namespaced helper functions (SESE pattern)
├── phpcs.xml                       ✅ Code standards config
├── readme.txt                      ✅ WordPress.org format
├── README.md                       ✅ GitHub format
├── CHANGELOG.md                    ✅ Version history
├── .github/
│   └── copilot-instructions.md     ✅ v1.5.0 (SESE pattern added)
├── includes/
│   ├── class-plugin.php            ✅ Main plugin class
│   ├── class-settings.php          ✅ Settings management (5 settings)
│   ├── class-admin-hooks.php       ✅ Admin functionality + hash preservation
│   ├── class-review-importer.php   ✅ Core import engine (COMPLETE)
│   └── class-csv-importer.php      ✅ CSV parser (COMPLETE)
├── admin-templates/
│   ├── main-page.php               ✅ Tabbed wrapper
│   ├── tab-import.php              ✅ CSV upload form (needs AJAX wiring)
│   ├── tab-settings.php            ✅ Settings form (functional)
│   └── tab-help.php                ✅ Help documentation
├── assets/
│   └── admin/
│       ├── admin.css               ✅ Tab styling + progress bars
│       └── admin.js                ✅ Hash navigation (needs AJAX upload)
├── languages/                      📁 Empty (translation files)
├── sample-data/                    🔒 Ignored (sensitive data)
│   └── bullfix-reviews-1.csv       ✅ Test CSV with real product SKUs
└── dev-notes/
    ├── 00-project-tracker.md       ✅ This file (updated)
    ├── 01-requirements.md          ✅ Requirements document
    ├── architecture.md             ✅ Component architecture
    ├── import-logic.md             ✅ Import processing flow
    ├── patterns/                   ✅ Pattern references
    └── workflows/                  ✅ Workflow guides
```

---

## Notes

**Completed Milestones:**
- ✅ Milestone 1: Core Import Engine - Review_Importer class fully functional
- ✅ Milestone 2: Admin Interface - All templates and assets created
- ✅ Milestone 3: CSV Source Adapter - CSV_Importer class complete and tested
- ✅ Milestone 5: Settings Integration - Settings save/load working correctly

**Current State (v0.3.0):**
- Plugin activated and functional
- Admin page accessible at WooCommerce > Reviews Importer
- CSV import working end-to-end (tested with real product data)
- All code passes PHPCS (intentional warnings for filesystem operations)
- Settings properly integrated and persisting
- Tab navigation working with hash preservation
- Public IP detection and caching implemented
- Author name intelligence (uses WordPress user display_name)

**Ready for Next Phase:**
- AJAX file upload handler
- Batch processing orchestration
- Progress bar and real-time feedback
- Frontend JavaScript for import flow

**Code Quality:**
- All functions follow SESE pattern (single-entry single-exit)
- Type hints and return types throughout
- Proper WordPress comment structure with HPOS compatibility
- Security: nonces, capability checks, sanitization, escaping
- Native PHP parsing (zero dependencies)

---

## Questions / Decisions Needed

None at this time - all initial decisions documented in requirements.md

**Version:** VERSION IN HERE
**Last Updated:** DATE IN HERE

---

## Overview

PROJECT SUMMARY

---

## Active TODO Items

---

## Milestones

---

## Technical Debt

---

## Notes for Development

