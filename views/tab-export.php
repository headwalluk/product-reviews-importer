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
// Header names are Walmart's and appear untranslated, exactly as in the exported file.
foreach ( $pri_exporter->get_walmart_columns() as $pri_column_header => $pri_column_description ) {
	printf(
		'<li><strong>%s</strong> — %s</li>',
		esc_html( $pri_column_header ),
		esc_html( $pri_column_description )
	);
}
printf( '</ul>' );

if ( $pri_review_count > 0 ) {
	printf(
		'<p>%s</p>',
		esc_html(
			sprintf(
				/* translators: %d: number of approved reviews */
				_n( 'There is currently %d approved review to export.', 'There are currently %d approved reviews to export.', $pri_review_count, 'product-reviews-importer' ),
				(int) $pri_review_count
			)
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
