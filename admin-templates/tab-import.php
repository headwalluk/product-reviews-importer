<?php
/**
 * Import tab template.
 *
 * CSV import interface.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

// Import section container.
printf( '<div class="pri-import-section">' );

printf(
	'<h2>%s</h2>',
	esc_html__( 'Import Reviews from CSV', 'product-reviews-importer' )
);

// Upload section.
printf( '<div class="pri-upload-section">' );
printf(
	'<h3>%s</h3>',
	esc_html__( 'Step 1: Upload CSV File', 'product-reviews-importer' )
);
printf(
	'<p>%s</p>',
	esc_html__( 'Select a CSV file containing product reviews. The file must include the following columns: SKU, Author Name, Author Email, Review Text, and Review Stars (1-5).', 'product-reviews-importer' )
);

// Upload form.
printf( '<form id="pri-upload-form" method="post" enctype="multipart/form-data">' );
wp_nonce_field( NONCE_CSV_UPLOAD, 'pri_nonce' );
printf( '<p><input type="file" name="csv_file" id="pri-csv-file" accept=".csv" required /></p>' );
printf(
	'<p><button type="submit" id="pri-upload-btn" class="button button-primary">%s</button></p>',
	esc_html__( 'Upload and Validate', 'product-reviews-importer' )
);
printf( '</form>' );

// Validation results.
printf( '<div id="pri-validation-results" style="display:none;">' );
printf(
	'<h3>%s</h3>',
	esc_html__( 'Validation Results', 'product-reviews-importer' )
);
printf( '<div id="pri-validation-messages"></div>' );
printf( '<div id="pri-import-controls" style="display:none;">' );
printf(
	'<p><button type="button" id="pri-start-import" class="button button-primary">%s</button></p>',
	esc_html__( 'Start Import', 'product-reviews-importer' )
);
printf( '</div>' ); // pri-import-controls.
printf( '</div>' ); // pri-validation-results.
printf( '</div>' ); // pri-upload-section.

// Progress section.
printf( '<div id="pri-progress-section" style="display:none;">' );
printf(
	'<h3>%s</h3>',
	esc_html__( 'Import Progress', 'product-reviews-importer' )
);
printf(
	'<div class="pri-progress-bar"><div id="pri-progress-bar-fill" class="pri-progress-fill" style="width:0%%"><span id="pri-progress-text">0%%</span></div></div>'
);
printf(
	'<p id="pri-progress-message">%s</p>',
	esc_html__( 'Preparing import...', 'product-reviews-importer' )
);
printf( '</div>' ); // pri-progress-section.

// Results section.
printf( '<div id="pri-results-section" style="display:none;">' );
printf(
	'<h3>%s</h3>',
	esc_html__( 'Import Results', 'product-reviews-importer' )
);
printf( '<div id="pri-results-content"></div>' );
printf(
	'<p><button type="button" id="pri-reset-import" class="button" style="display:none;">%s</button></p>',
	esc_html__( 'Import Another File', 'product-reviews-importer' )
);
printf( '</div>' ); // pri-results-section.

printf( '</div>' ); // pri-import-section.
