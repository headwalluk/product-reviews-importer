<?php
/**
 * Plugin Name:       Product Reviews Importer
 * Plugin URI:        https://headwall-hosting.com/
 * Description:       Import product reviews from multiple sources (CSV, Google, etc.) into WooCommerce products.
 * Version:           0.2.0
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

defined( 'ABSPATH' ) || die();

define( 'PRODUCT_REVIEWS_IMPORTER_VERSION', '0.2.0' );
define( 'PRODUCT_REVIEWS_IMPORTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODUCT_REVIEWS_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODUCT_REVIEWS_IMPORTER_BASENAME', plugin_basename( __FILE__ ) );

require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'constants.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'functions-private.php';

// Load plugin classes.
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-plugin.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-settings.php';
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'includes/class-admin-hooks.php';

/**
 * Display admin notice if WooCommerce is not active.
 *
 * @since 1.0.0
 */
function pri_woocommerce_missing_notice(): void {
	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Product Reviews Importer requires WooCommerce to be installed and active.', 'product-reviews-importer' )
	);
}

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 */
function pri_init(): void {
	// Check if WooCommerce is active.
	if ( ! \Product_Reviews_Importer\is_woocommerce_active() ) {
		add_action( 'admin_notices', 'pri_woocommerce_missing_notice' );
		return;
	}

	// Initialize plugin instance.
	global $product_reviews_importer;
	$product_reviews_importer = new Product_Reviews_Importer\Plugin();
	$product_reviews_importer->run();
}
add_action( 'plugins_loaded', 'pri_init' );

/**
 * Declare HPOS compatibility.
 *
 * @since 1.0.0
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
		}
	}
);

/**
 * Load plugin text domain for translations.
 *
 * @since 1.0.0
 */
function pri_load_textdomain(): void {
	load_plugin_textdomain(
		'product-reviews-importer',
		false,
		dirname( PRODUCT_REVIEWS_IMPORTER_BASENAME ) . '/languages'
	);
}
add_action( 'init', 'pri_load_textdomain' );
