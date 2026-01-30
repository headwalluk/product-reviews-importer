=== Product Reviews Importer ===
Contributors: paulfaulkner
Tags: woocommerce, reviews, import, csv, products
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import product reviews from multiple sources (CSV, Google, etc.) into WooCommerce products.

== Description ==

Product Reviews Importer allows you to easily import product reviews from various sources into your WooCommerce store. Currently supports CSV file imports with plans for additional sources like Google Reviews.

**Features:**

* CSV import with native PHP parsing (no dependencies)
* AJAX file upload with comprehensive security validation
* Batch processing (10 rows per batch) prevents timeouts
* Real-time progress bar with percentage updates
* Detailed error reporting with row-level feedback
* UTF-8 encoding support with BOM detection
* Memory-efficient streaming for large files
* Automatic product matching via SKU
* Support for variable products (reviews added to parent product)
* Smart duplicate handling (updates existing reviews)
* Author name intelligence (uses WordPress user's display_name)
* Optional user account creation for new reviewers
* Multi-line review text with line break preservation
* Public IP detection with secure fallback
* WooCommerce HPOS compatible
* All code-first templates (WordPress Coding Standards)

**CSV Format:**

The plugin expects a CSV file with the following columns:

* SKU (required)
* Author Name (required)
* Author Email (optional, but recommended - enables duplicate detection)
* Author IP (optional - defaults to server public IP)
* Review Date (required, format: Y-m-d H:i:s T)
* Review Text (required, multi-line supported in quotes)
* Review Stars (required, 1-5)

**Duplicate Handling:**

When author email is provided, reviews are identified by product ID + author email. If a review already exists:
* Review text and star rating are updated
* Original author details, date, and IP are preserved

**Author Name Priority:**

* If email matches existing WordPress user: Uses user's display_name
* If creating new user: Uses CSV Author Name
* If guest comment: Uses CSV Author Name

**User Account Creation:**

Configure whether to create WordPress user accounts for new reviewers:
* If enabled: Creates users in Customer role
* If disabled: Reviews are added as guest comments

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/product-reviews-importer/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure WooCommerce is installed and activated
4. Navigate to WooCommerce > Reviews Importer to import reviews

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, WooCommerce must be installed and activated for this plugin to work.

= What happens if a product SKU is not found? =

The review will be skipped and reported as an error. You can review all errors after the import completes.

= Can I update existing reviews? =

Yes. If a review already exists for a product by the same email address, the review text and star rating will be updated.

= What format should the CSV file use? =

All fields should be quoted. The Review Text field can span multiple lines. See the Description section for required columns.

== Screenshots ==

1. Import interface - CSV file upload
2. Settings page - Configure import options

== Changelog ==

= 0.3.0 =
* CSV importer engine complete with native PHP parsing
* UTF-8 BOM detection and multi-line field support
* Memory-efficient streaming for large CSV files
* Public IP detection via icanhazip.com (cached, secure fallback)
* Author name intelligence (uses WordPress user display_name)
* SESE pattern refactoring for easier debugging
* Settings tab hash preservation on save
* Full end-to-end CSV import tested and verified

= 0.2.0 =
* Foundation complete and code standards verified
* All core classes implemented (Plugin, Settings, Admin_Hooks)
* PHPCS configuration added
* Plugin activatable and functional

= 0.1.0 =
* Initial development version
* Basic structure and infrastructure

== Upgrade Notice ==

= 0.3.0 =
CSV import engine complete! Core functionality ready for AJAX integration.

= 1.0.0 =
Initial release of Product Reviews Importer.
