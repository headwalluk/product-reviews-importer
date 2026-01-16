# Changelog

All notable changes to Product Reviews Importer will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned
- AJAX file upload and batch processing
- Progress bar and real-time import status
- Google Reviews import via Place ID
- Additional import sources
- Export reviews to CSV
- Import history tracking UI
- Scheduled/automated imports via cron

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
