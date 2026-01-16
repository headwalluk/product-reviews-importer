<?php
/**
 * Main plugin class.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Main Plugin class.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Settings instance.
	 *
	 * @since 1.0.0
	 * @var Settings|null
	 */
	private ?Settings $settings = null;

	/**
	 * Admin Hooks instance.
	 *
	 * @since 1.0.0
	 * @var Admin_Hooks|null
	 */
	private ?Admin_Hooks $admin_hooks = null;


	/**
	 * Run the plugin.
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		// Initialize settings early (before admin_init).
		$this->get_settings();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		}
	}

	/**
	 * Plugin initialization.
	 *
	 * Runs on WordPress 'init' hook.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		// Placeholder for future initialization code.
	}

	/**
	 * Admin initialization.
	 *
	 * Runs on WordPress 'admin_init' hook.
	 *
	 * @since 1.0.0
	 */
	public function admin_init(): void {
		// Register admin enqueue hook.
		$admin_hooks = $this->get_admin_hooks();
		add_action( 'admin_enqueue_scripts', array( $admin_hooks, 'enqueue_assets' ) );

		// Preserve hash fragment when redirecting after settings save.
		add_filter( 'wp_redirect', array( $admin_hooks, 'preserve_settings_hash' ) );
	}

	/**
	 * Register admin menu.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Product Reviews Importer', 'product-reviews-importer' ),
			__( 'Reviews Importer', 'product-reviews-importer' ),
			ADMIN_CAPABILITY,
			ADMIN_PAGE_SLUG,
			array( $this->get_admin_hooks(), 'render_admin_page' )
		);
	}

	/**
	 * Get Settings instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Settings Settings instance.
	 */
	public function get_settings(): Settings {
		if ( is_null( $this->settings ) ) {
			$this->settings = new Settings();
		}
		return $this->settings;
	}

	/**
	 * Get Admin Hooks instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Admin_Hooks Admin Hooks instance.
	 */
	public function get_admin_hooks(): Admin_Hooks {
		if ( is_null( $this->admin_hooks ) ) {
			$this->admin_hooks = new Admin_Hooks();
		}
		return $this->admin_hooks;
	}

	/**
	 * Get plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @return string Plugin version.
	 */
	public function get_version(): string {
		return PRODUCT_REVIEWS_IMPORTER_VERSION;
	}
}
