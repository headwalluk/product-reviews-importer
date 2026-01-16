=== Product Reviews Importer ===
Contributors: paulfaulkner
Tags: woocommerce, reviews, import, csv, products
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import product reviews from multiple sources (CSV, Google, etc.) into WooCommerce products.

== Description ==

Product Reviews Importer allows you to easily import product reviews from various sources into your WooCommerce store. Currently supports CSV file imports with plans for additional sources like Google Reviews.

**Features:**

* Import reviews from CSV files
* Automatic product matching via SKU
* Support for variable products (reviews added to parent product)
* Handles duplicate reviews intelligently (updates existing reviews)
* Optional user account creation for new reviewers
* Multi-line review text support
* Preserves review dates and ratings
* Batch processing for large imports

**CSV Format:**

The plugin expects a CSV file with the following columns:

* SKU (required)
* Author Name (required)
* Author Email (required)
* Author IP (optional)
* Review Date (required, format: Y-m-d H:i:s T)
* Review Text (required, multi-line supported)
* Review Stars (required, 1-5)

**Duplicate Handling:**

Reviews are identified by product ID + author email. If a review already exists:
* Review text and star rating are updated
* Original author details, date, and IP are preserved

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

= 0.2.0 =
* Foundation complete and code standards verified
* All core classes implemented (Plugin, Settings, Admin_Hooks)
* PHPCS configuration added
* Plugin activatable and functional

= 0.1.0 =
* Initial development version
* Basic structure and infrastructure

== Upgrade Notice ==

= 1.0.0 =
Initial release of Product Reviews Importer.
