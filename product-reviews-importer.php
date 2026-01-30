<?php
/**
 * Plugin Name:       Product Reviews Importer
 * Plugin URI:        https://headwall-hosting.com/plugins/product-reviews-importer-for-woocommerce/
 * Description:       Import product reviews from multiple sources (CSV, Google, etc.) into WooCommerce products.
 * Version:           1.1.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Author:            Paul Faulkner
 * Author URI:        https://headwall-hosting.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       product-reviews-importer
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   9.0
 *
 * @package Product_Reviews_Importer
 */

// Block direct access.
defined( 'ABSPATH' ) || die();

define( 'PRODUCT_REVIEWS_IMPORTER_VERSION', '1.1.1' );
define( 'PRODUCT_REVIEWS_IMPORTER_FILE', __FILE__ );
define( 'PRODUCT_REVIEWS_IMPORTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODUCT_REVIEWS_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODUCT_REVIEWS_IMPORTER_BASENAME', plugin_basename( __FILE__ ) );

require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'constants.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'functions-private.php';

require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-plugin.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-settings.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-admin-hooks.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-review-importer.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-csv-importer.php';

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 */
function pri_init(): void {
	// Check if WooCommerce is active.
	if ( ! \Product_Reviews_Importer\is_woocommerce_active() ) {
		add_action( 'admin_notices', '\\Product_Reviews_Importer\\show_woocommerce_missing_notice' );
		return;
	}

	// Initialize plugin instance.
	global $product_reviews_importer;
	$product_reviews_importer = new Product_Reviews_Importer\Plugin();
	$product_reviews_importer->run();
}
add_action( 'plugins_loaded', 'pri_init' );
