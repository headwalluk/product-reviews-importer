<?php
/**
 * Admin Hooks class.
 *
 * Handles all admin-related functionality for the plugin.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Admin Hooks class.
 *
 * @since 1.0.0
 */
class Admin_Hooks {

	/**
	 * Enqueue admin assets.
	 *
	 * Only loads assets on plugin admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		// Only load on our plugin pages.
		$plugin_pages = array(
			'woocommerce_page_' . ADMIN_PAGE_SLUG,
		);

		if ( ! in_array( $hook_suffix, $plugin_pages, true ) ) {
			return;
		}

		// Enqueue admin CSS.
		wp_enqueue_style(
			'product-reviews-importer-admin',
			PRODUCT_REVIEWS_IMPORTER_URL . 'assets/admin/admin.css',
			array(),
			PRODUCT_REVIEWS_IMPORTER_VERSION
		);

		// Enqueue admin JS.
		wp_enqueue_script(
			'product-reviews-importer-admin',
			PRODUCT_REVIEWS_IMPORTER_URL . 'assets/admin/admin.js',
			array( 'jquery' ),
			PRODUCT_REVIEWS_IMPORTER_VERSION,
			true
		);

		// Localize script with admin data.
		wp_localize_script(
			'product-reviews-importer-admin',
			'productReviewsImporter',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( NONCE_CSV_UPLOAD ),
			)
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page(): void {
		// Check user capabilities.
		if ( ! current_user_can( ADMIN_CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'product-reviews-importer' ) );
		}

		// Load admin template.
		require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/main-page.php';
	}

	/**
	 * Preserve hash fragment when redirecting after settings save.
	 *
	 * WordPress strips URL fragments during redirect. This restores the #settings hash
	 * so users return to the Settings tab instead of the default Import tab.
	 *
	 * @since 1.0.0
	 *
	 * @param string $location Redirect URL.
	 *
	 * @return string Modified redirect URL with hash fragment.
	 */
	public function preserve_settings_hash( string $location ): string {
		// Only modify redirect for our settings page.
		if ( false === strpos( $location, 'page=' . ADMIN_PAGE_SLUG ) ) {
			return $location;
		}

		// Check if this is a settings update (settings-updated query param present).
		if ( false !== strpos( $location, 'settings-updated=true' ) ) {
			// Add hash fragment to return to Settings tab.
			$location .= '#settings';
		}

		return $location;
	}
}
