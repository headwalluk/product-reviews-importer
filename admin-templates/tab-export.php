<?php
/**
 * Admin export tab template.
 *
 * Export interface for generating review files in various formats.
 *
 * @package Product_Reviews_Importer
 * @since   1.2.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

$pri_exporter     = new Review_Exporter();
$pri_review_count = $pri_exporter->get_review_count();

printf( '<div class="pri-export-section">' );

// Walmart Review Syndication section.
printf(
	'<h2>%s</h2>',
	esc_html__( 'Walmart Review Syndication', 'product-reviews-importer' )
);

printf(
	'<p>%s</p>',
	esc_html__( 'Export all approved product reviews in the format required for Walmart review syndication. The exported CSV follows Walmart\'s specification with the following columns:', 'product-reviews-importer' )
);

printf( '<ul>' );
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'Walmart Item ID', 'product-reviews-importer' ),
	esc_html__( 'Left blank — fill in with your Walmart Item IDs after export.', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'SKU', 'product-reviews-importer' ),
	esc_html__( 'Your WooCommerce product SKU.', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'Review Title', 'product-reviews-importer' ),
	esc_html__( 'Left blank — WooCommerce reviews do not have titles.', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'Review Body', 'product-reviews-importer' ),
	esc_html__( 'The full review text.', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'Review Rating', 'product-reviews-importer' ),
	esc_html__( 'Star rating (1–5).', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'Review Created Date', 'product-reviews-importer' ),
	esc_html__( 'Formatted as MM/DD/YYYY per Walmart requirements.', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'Review User Name', 'product-reviews-importer' ),
	esc_html__( 'The reviewer\'s name as it appears on your site.', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'URL link', 'product-reviews-importer' ),
	esc_html__( 'Link to the product page on your website.', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> — %s</li>',
	esc_html__( 'Incentivized Review', 'product-reviews-importer' ),
	esc_html__( 'Defaults to "No" — update manually if applicable.', 'product-reviews-importer' )
);
printf( '</ul>' );

if ( $pri_review_count > 0 ) {
	printf(
		'<p>%s</p>',
		sprintf(
			/* translators: %d: number of approved reviews */
			esc_html__( 'There are currently %d approved reviews to export.', 'product-reviews-importer' ),
			(int) $pri_review_count
		)
	);

	// Export button — direct link to admin-post.php with nonce.
	$pri_export_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=' . EXPORT_ACTION_WALMART ),
		NONCE_EXPORT
	);

	printf(
		'<p><a href="%s" class="button button-primary">%s</a></p>',
		esc_url( $pri_export_url ),
		esc_html__( 'Export Walmart CSV', 'product-reviews-importer' )
	);
} else {
	printf(
		'<p><em>%s</em></p>',
		esc_html__( 'No approved reviews to export. Import some reviews first, or check that existing reviews are approved.', 'product-reviews-importer' )
	);
}

printf( '</div>' );
