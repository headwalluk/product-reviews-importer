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
	 * Review Exporter instance.
	 *
	 * @since 1.2.0
	 * @var Review_Exporter|null
	 */
	private ?Review_Exporter $review_exporter = null;

	/**
	 * Temporary upload storage instance.
	 *
	 * @since 1.3.0
	 * @var Temp_Files|null
	 */
	private ?Temp_Files $temp_files = null;

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
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_filter( 'plugin_action_links_' . PRODUCT_REVIEWS_IMPORTER_BASENAME, array( $this, 'add_settings_link' ) );
		}

		// Protect and purge the temporary upload directory.
		$this->get_temp_files()->register();

		// Register export handler (admin-post.php).
		add_action( 'admin_post_' . EXPORT_ACTION_WALMART, array( $this->get_review_exporter(), 'export_walmart_csv' ) );
	}

	/**
	 * Plugin initialization.
	 *
	 * Runs on WordPress 'init' hook.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		load_plugin_textdomain(
			'product-reviews-importer',
			false,
			dirname( PRODUCT_REVIEWS_IMPORTER_BASENAME ) . '/languages'
		);
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

		// Register AJAX handlers.
		add_action( 'wp_ajax_pri_upload_csv', array( $admin_hooks, 'ajax_upload_csv' ) );
		add_action( 'wp_ajax_pri_import_batch', array( $admin_hooks, 'ajax_import_batch' ) );
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
			_x( 'Import Reviews', 'admin menu label', 'product-reviews-importer' ),
			ADMIN_CAPABILITY,
			ADMIN_PAGE_SLUG,
			array( $this->get_admin_hooks(), 'render_admin_page' )
		);
	}

	/**
	 * Declare HPOS compatibility.
	 *
	 * @since 1.0.0
	 */
	public function declare_hpos_compatibility(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				PRODUCT_REVIEWS_IMPORTER_FILE,
				true
			);
		}
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
	 * Get Review Exporter instance.
	 *
	 * @since 1.2.0
	 *
	 * @return Review_Exporter Review Exporter instance.
	 */
	public function get_review_exporter(): Review_Exporter {
		if ( is_null( $this->review_exporter ) ) {
			$this->review_exporter = new Review_Exporter();
		}
		return $this->review_exporter;
	}

	/**
	 * Get temporary upload storage instance.
	 *
	 * @since 1.3.0
	 *
	 * @return Temp_Files Temporary upload storage instance.
	 */
	public function get_temp_files(): Temp_Files {
		if ( is_null( $this->temp_files ) ) {
			$this->temp_files = new Temp_Files( $this->get_settings() );
		}

		return $this->temp_files;
	}

	/**
	 * Add Settings link to plugin action links on the Plugins page.
	 *
	 * @since 1.2.0
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . ADMIN_PAGE_SLUG . '#settings' ) ),
			esc_html_x( 'Settings', 'plugin action link', 'product-reviews-importer' )
		);

		array_unshift( $links, $settings_link );

		return $links;
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
