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

// Wrapper div.
printf( '<div class="wrap">' );

// Page title.
printf(
	'<h1>%s</h1>',
	esc_html( get_admin_page_title() )
);

// Tab navigation.
printf(
	'<nav class="nav-tab-wrapper wp-clearfix" aria-label="%s">',
	esc_attr__( 'Secondary menu', 'product-reviews-importer' )
);
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
printf( '</nav>' );

// Tab content container.
printf( '<div class="tab-content">' );

// Import tab panel.
printf( '<div id="import-panel" class="tab-panel active">' );
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/tab-import.php';
printf( '</div>' );

// Settings tab panel.
printf( '<div id="settings-panel" class="tab-panel" style="display:none;">' );
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/tab-settings.php';
printf( '</div>' );

// Help tab panel.
printf( '<div id="help-panel" class="tab-panel" style="display:none;">' );
require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/tab-help.php';
printf( '</div>' );

// Close containers.
printf( '</div>' ); // tab-content.
printf( '</div>' ); // wrap.
