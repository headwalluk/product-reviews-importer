# Changelog

All notable changes to Product Reviews Importer will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned
- Google Reviews import via Place ID
- Additional import sources
- Export reviews to CSV
- Import history tracking UI
- Scheduled/automated imports via cron

---

## [1.1.0] - 2026-01-30

### Added
- Centralized CSV field definitions system (`get_csv_field_definitions()`)
- `get_sample_csv()` helper function for dynamic CSV samples
- `product_reviews_importer_csv_field_definitions` filter hook for extensibility
- Sample data in field definitions for consistent UI rendering
- Developer documentation for filter hook in Help tab and readme.txt
- Field definitions now include `sample` data for UI generation

### Changed
- Admin menu item text from "Reviews Importer" to "Import Reviews"
- Import tab now dynamically builds required fields list from definitions
- Help tab now dynamically builds field list from definitions
- Sample CSV now generated from field definitions (no hardcoding)
- Removed 7 obsolete `CSV_COL_*` constants (replaced by field definitions)
- CSV validation now uses dynamic required fields from definitions
- Made column names bold in Import tab requirements text

### Fixed
- Undefined `$handle` variable in CSV_Importer (line 172)
- Escaped HTML tags in Import tab field list (now properly rendered as bold)
- Added horizontal scroll for CSV sample on narrow displays

### Developer
- Field definitions now cached to reduce redundant function calls
- All CSV-related configuration now centralized in one function
- Plugin is fully extensible - developers can add custom CSV fields via filter
- PHPCS warnings properly suppressed with explanatory comments

---

## [1.0.0] - 2026-01-30

### Added
- WooCommerce dependency check with admin notice
- Button loading states ("Uploading...", "Importing...")
- Enhanced UX with proper button disable/enable states
- PHPCS suppressions with documentation for required native file operations

### Changed
- **Author Email is now optional** (recommended for duplicate detection)
- Reviews without email will be created as guest reviews
- Duplicate detection only works when email is provided
- User account creation skipped when no email provided
- Removed all diagnostic console.log() and error_log() statements
- Replaced unlink() with wp_delete_file() for WordPress best practices
- Production-ready error handling

### Fixed
- Client compatibility: CSV files without email addresses now supported
- Upload button properly re-enables on error
- Import button shows processing state during batch operations

### Code Quality
- PHPCS compliant with only 1 intentional warning (standard CSV reading idiom)
- All native file operations documented and justified
- WordPress Coding Standards followed throughout

---

## [0.4.0] - 2026-01-16

### Added
- AJAX file upload handler with comprehensive security
- Batch processing orchestration (10 rows per batch)
- Real-time progress bar with percentage display
- Detailed error reporting with row-level feedback
- File upload validation (CSV only, 10MB max)
- Secure temp file storage in wp-uploads/pri-temp/
- Transient-based upload session management
- Error list display with scrollable container
- All admin templates refactored to code-first design (no inline HTML)

### Security
- Nonce verification on all AJAX endpoints
- Capability checks: `manage_woocommerce` required for uploads and imports
- File type enforcement: Only `.csv` extension allowed
- File size limit: 10MB maximum
- Random filename generation for uploaded files
- Automatic cleanup of temp files after import

### Changed
- Error reporting now includes actual CSV row numbers (not array indices)
- Progress updates shown during batch processing
- Import completion message includes success/update/error counts
- Settings save redirects to Settings tab (hash preserved)

### Technical
- All AJAX handlers follow WordPress best practices
- Proper indentation and code formatting (PHPCS compliant)
- Diagnostic `error_log()` calls for troubleshooting (to be removed before production)
- Test CSV file created with deliberate errors for validation

**Milestone 4 Complete:** AJAX Import Orchestration fully functional

---

## [0.3.0] - 2026-01-16

### Added
- CSV_Importer class with native PHP fgetcsv() parsing
- UTF-8 BOM detection and handling
- Multi-line CSV field support (quoted text)
- Memory-efficient streaming for large CSV files
- CSV validation (headers, required columns, data rows)
- Column mapping from CSV to normalized review format
- Batch reading with offset/limit support
- Row-level error reporting with line numbers

### Changed
- Updated `get_server_ip()` to fetch public IP from icanhazip.com (cached 7 days)
- Refactored all functions to SESE pattern (single-entry single-exit)
- Review_Importer now uses WordPress user's display_name when email matches existing user
- Settings tab preserves hash state after save

### Fixed
- Internal IP addresses no longer leaked (defaults to 127.0.0.1 on failure)

### Documentation
- Added SESE pattern to core coding standards (copilot-instructions.md v1.5.0)
- Updated requirements.md with author name priority logic
- CSV import fully tested with real product data

**Milestone 3 Complete:** CSV Source Adapter fully functional

---

## [0.2.0] - 2026-01-16

### Added
- Complete plugin foundation and infrastructure
- Settings class with WordPress Settings API integration
- Admin Hooks class for admin functionality
- PHPCS configuration (phpcs.xml) for code standards
- All helper functions in functions-private.php
- Comprehensive documentation (README.md, readme.txt, CHANGELOG.md)

### Technical
- All files pass WordPress Coding Standards (PHPCS)
- Proper namespacing (Product_Reviews_Importer)
- Type hints on all functions and methods
- Security implementation (nonces, capability checks, sanitization)
- Lazy loading pattern for class instances

---

## [0.1.0] - 2026-01-16

### Added
- Initial development version
- Basic project structure
- Product matching via SKU
- Support for variable products (reviews added to parent)
- Duplicate review detection (product_id + author_email)
- Update existing reviews (content and star rating only)
- Optional user account creation for new reviewers
- Multi-line review text support in CSV
- Default IP address handling (uses server IP when blank)
- Review text sanitization (wp_kses with br/p tags only)
- Star rating validation (1-5)
- Batch processing for large imports
- WooCommerce HPOS compatibility
- Admin interface under WooCommerce menu
- Settings page for configuration
- Minimum review length validation
- Translation ready (text domain: product-reviews-importer)

### Technical
- PHP 8.0+ type hints and return types
- WordPress Coding Standards compliance
- Namespaced classes (Product_Reviews_Importer)
- Prefixed functions (pri_)
- Constants file for magic values
- Helper functions file
- Lazy loading for class instances
- Nonce verification for security
- Capability checks (manage_woocommerce)

---

## Version History

- **0.2.0** - Foundation complete (2026-01-16)
- **0.1.0** - Initial development (2026-01-16)

---

[Unreleased]: https://github.com/yourusername/product-reviews-importer/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/yourusername/product-reviews-importer/releases/tag/v0.2.0
[0.1.0]: https://github.com/yourusername/product-reviews-importer/releases/tag/v0.1.0
