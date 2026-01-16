<?php
/**
 * Admin main page template.
 *
 * Tabbed interface for Product Reviews Importer.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e( 'Secondary menu', 'product-reviews-importer' ); ?>">
		<?php
		printf(
			'<a href="#import" class="nav-tab nav-tab-active" data-tab="import">%s</a>',
			esc_html__( 'Import', 'product-reviews-importer' )
		);
		printf(
			'<a href="#settings" class="nav-tab" data-tab="settings">%s</a>',
			esc_html__( 'Settings', 'product-reviews-importer' )
		);
		printf(
			'<a href="#help" class="nav-tab" data-tab="help">%s</a>',
			esc_html__( 'Help', 'product-reviews-importer' )
		);
		?>
	</nav>

	<div class="tab-content">
		<div id="import-panel" class="tab-panel active">
			<?php require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/tab-import.php'; ?>
		</div>

		<div id="settings-panel" class="tab-panel" style="display:none;">
			<?php require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/tab-settings.php'; ?>
		</div>

		<div id="help-panel" class="tab-panel" style="display:none;">
			<?php require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/tab-help.php'; ?>
		</div>
	</div>
</div>
