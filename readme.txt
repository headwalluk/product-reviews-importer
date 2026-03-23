=== Product Reviews Importer ===
Contributors: paulfaulkner
Tags: woocommerce, reviews, import, export, csv
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import and export WooCommerce product reviews. CSV import with batch processing, and export for Walmart review syndication.

== Description ==

Product Reviews Importer allows you to import product reviews from CSV files into your WooCommerce store, and export reviews for syndication to marketplaces like Walmart.

**Import Features:**

* CSV import with native PHP parsing (no dependencies)
* AJAX file upload with comprehensive security validation
* Batch processing (50 rows per batch) prevents timeouts
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

**Export Features:**

* Walmart review syndication CSV export
* All approved reviews exported in Walmart's required format
* Dates formatted as MM/DD/YYYY per Walmart specification
* Product URLs included automatically
* UTF-8 BOM for Excel compatibility

**General:**

* WooCommerce HPOS compatible
* Settings link on Plugins page
* Translation ready
* Extensible via filter hooks

**CSV Import Format:**

The plugin expects a CSV file with the following columns:

* SKU (required)
* Author Name (required)
* Author Email (optional, but recommended — enables duplicate detection)
* Review Text (required, multi-line supported in quotes)
* Review Stars (required, 1-5)
* Author IP (optional — defaults to server public IP)
* Review Date (optional, format: Y-m-d H:i:s T — defaults to current time)

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
4. Navigate to WooCommerce > Import Reviews to import or export reviews

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, WooCommerce must be installed and activated for this plugin to work.

= What happens if a product SKU is not found? =

The review will be skipped and reported as an error. You can review all errors after the import completes.

= Can I update existing reviews? =

Yes. If a review already exists for a product by the same email address, the review text and star rating will be updated.

= What format should the CSV file use? =

All fields should be quoted. The Review Text field can span multiple lines. See the Description section for required columns.

= Can I customize the CSV field definitions? =

Yes. Developers can use the `product_reviews_importer_csv_field_definitions` filter to add, remove, or modify CSV field definitions. See the Developer Hooks section below.

= How do I export reviews for Walmart? =

Go to WooCommerce > Import Reviews, click the Export tab, then click "Export Walmart CSV". The downloaded file will contain all approved reviews in Walmart's syndication format. You will need to fill in the Walmart Item ID column manually.

== Developer Hooks ==

= Filters =

**product_reviews_importer_csv_field_definitions**

Allows developers to customize CSV field definitions, add custom fields, or modify existing field behavior.

Parameters:
* `$fields` (array) - Array of field definitions with keys: `required`, `description`, `map_to`, `sample`

Example:

`
add_filter( 'product_reviews_importer_csv_field_definitions', function( $fields ) {
    // Add a custom field
    $fields['Custom Field'] = array(
        'required'    => false,
        'description' => __( 'Custom field description', 'my-plugin' ),
        'map_to'      => 'custom_field_key',
        'sample'      => 'Example Value',
    );

    // Make Author Email required
    $fields['Author Email']['required'] = true;

    return $fields;
} );
`

== Screenshots ==

1. Import interface - CSV file upload
2. Settings page - Configure import options
3. Export interface - Walmart review syndication

== Changelog ==

= 1.2.0 =
* Added: Export tab with Walmart review syndication CSV export
* Added: Settings link on Plugins page row actions
* Added: Review Exporter class with streaming CSV download
* Changed: Replaced move_uploaded_file() with wp_handle_upload()
* Changed: Renamed pri_init() to product_reviews_importer_init()
* Changed: Updated WC tested up to 10.6
* Changed: Updated WordPress tested up to 6.9
* Fixed: Plugin Check compliance — resolved all errors

= 1.1.1 =
* Changed: Refactored main plugin file — moved functionality to appropriate locations
* Changed: Moved show_woocommerce_missing_notice() to functions-private.php
* Changed: Moved textdomain loading into Plugin::init() method
* Changed: Moved HPOS compatibility declaration into Plugin::declare_hpos_compatibility() method
* Added: PRODUCT_REVIEWS_IMPORTER_FILE constant for proper HPOS declaration
* Fixed: Code formatting and indentation issues
* Fixed: Missing PHPDoc comments

= 1.1.0 =
* Added: Centralized CSV field definitions system (get_csv_field_definitions())
* Added: get_sample_csv() helper function for dynamic CSV samples
* Added: product_reviews_importer_csv_field_definitions filter hook for extensibility
* Added: Sample data in field definitions for consistent UI rendering
* Added: Developer documentation for filter hook in Help tab and readme.txt
* Changed: Import and Help tabs now dynamically build field lists from definitions
* Changed: Admin menu item text from "Reviews Importer" to "Import Reviews"
* Fixed: Undefined variable bug in CSV_Importer

= 1.0.0 =
* Initial production release
* CSV import with AJAX batch processing
* Real-time progress bar
* Detailed error reporting with row-level feedback
* WooCommerce dependency check with admin notice
* Button loading states during upload and import
* Author Email now optional (recommended for duplicate detection)

= 0.4.0 =
* AJAX file upload with security validation
* Batch processing orchestration (50 rows per batch)
* Secure temp file storage with automatic cleanup
* All admin templates refactored to code-first design

= 0.3.0 =
* CSV importer engine with native PHP parsing
* UTF-8 BOM detection and multi-line field support
* Memory-efficient streaming for large CSV files
* Public IP detection via icanhazip.com (cached, secure fallback)
* SESE pattern refactoring

= 0.2.0 =
* Foundation complete — all core classes implemented
* Settings class with WordPress Settings API
* PHPCS configuration and code standards compliance

= 0.1.0 =
* Initial development version

== Upgrade Notice ==

= 1.2.0 =
New export functionality for Walmart review syndication. Plugin Check compliance improvements.

= 1.0.0 =
Initial release of Product Reviews Importer.
