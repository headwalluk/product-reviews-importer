<?php
/**
 * Help tab template.
 *
 * Documentation and troubleshooting.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

// Help section.
printf( '<div class="pri-help-section">' );
printf(
	'<h2>%s</h2>',
	esc_html__( 'How to Import Product Reviews', 'product-reviews-importer' )
);

// CSV File Format section.
printf(
	'<h3>%s</h3>',
	esc_html__( 'CSV File Format', 'product-reviews-importer' )
);
printf(
	'<p>%s</p>',
	esc_html__( 'Your CSV file must include the following columns (all values should be quoted):', 'product-reviews-importer' )
);

// Column list.
printf( '<ul>' );
printf(
	'<li><strong>%s</strong> - %s</li>',
	esc_html__( 'SKU', 'product-reviews-importer' ),
	esc_html__( 'Product SKU (required)', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> - %s</li>',
	esc_html__( 'Author Name', 'product-reviews-importer' ),
	esc_html__( 'Reviewer name (required)', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> - %s</li>',
	esc_html__( 'Author Email', 'product-reviews-importer' ),
	esc_html__( 'Reviewer email address (required)', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> - %s</li>',
	esc_html__( 'Review Text', 'product-reviews-importer' ),
	esc_html__( 'Review content (required, can span multiple lines)', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> - %s</li>',
	esc_html__( 'Review Stars', 'product-reviews-importer' ),
	esc_html__( 'Star rating 1-5 (required)', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> - %s</li>',
	esc_html__( 'Author IP', 'product-reviews-importer' ),
	esc_html__( 'IP address (optional)', 'product-reviews-importer' )
);
printf(
	'<li><strong>%s</strong> - %s</li>',
	esc_html__( 'Review Date', 'product-reviews-importer' ),
	esc_html__( 'Date in Y-m-d H:i:s T format (optional)', 'product-reviews-importer' )
);
printf( '</ul>' );

// Sample CSV.
printf(
	'<h3>%s</h3>',
	esc_html__( 'Sample CSV', 'product-reviews-importer' )
);
printf(
	'<pre><code>%s</code></pre>',
	'"SKU","Author Name","Author Email","Author IP","Review Date","Review Text","Review Stars"' . "\n" .
	'"ABC123","John Doe","john.doe@example.com","123.123.123.123","2026-01-15 14:30:00 GMT","Great product, highly recommend!","5"' . "\n" .
	'"ABC123","Jane Smith","jane.smith@example.com","","2026-01-16 10:00:00 GMT","Not what I expected.","2"'
);

// Important Notes.
printf(
	'<h3>%s</h3>',
	esc_html__( 'Important Notes', 'product-reviews-importer' )
);
printf( '<ul>' );
printf(
	'<li>%s</li>',
	esc_html__( 'Reviews are matched to products using the SKU field', 'product-reviews-importer' )
);
printf(
	'<li>%s</li>',
	esc_html__( 'If a product variation SKU is provided, the review will be attached to the parent product', 'product-reviews-importer' )
);
printf(
	'<li>%s</li>',
	esc_html__( 'Duplicate reviews (same email + product) will update the existing review', 'product-reviews-importer' )
);
printf(
	'<li>%s</li>',
	esc_html__( 'Review text supports line breaks and basic formatting', 'product-reviews-importer' )
);
printf(
	'<li>%s</li>',
	esc_html__( 'Invalid rows will be skipped and reported in the import results', 'product-reviews-importer' )
);
printf( '</ul>' );

// Troubleshooting.
printf(
	'<h3>%s</h3>',
	esc_html__( 'Troubleshooting', 'product-reviews-importer' )
);
printf(
	'<p><strong>%s</strong><br />%s</p>',
	esc_html__( 'Product not found errors:', 'product-reviews-importer' ),
	esc_html__( 'Verify that the SKU exactly matches a product in your store. SKUs are case-sensitive.', 'product-reviews-importer' )
);
printf(
	'<p><strong>%s</strong><br />%s</p>',
	esc_html__( 'Invalid email errors:', 'product-reviews-importer' ),
	esc_html__( 'Ensure email addresses are in valid format (e.g., user@example.com).', 'product-reviews-importer' )
);
printf(
	'<p><strong>%s</strong><br />%s</p>',
	esc_html__( 'Invalid star rating errors:', 'product-reviews-importer' ),
	esc_html__( 'Star ratings must be whole numbers from 1 to 5.', 'product-reviews-importer' )
);
printf(
	'<p><strong>%s</strong><br />%s</p>',
	esc_html__( 'Review text too short:', 'product-reviews-importer' ),
	esc_html__( 'Check the minimum review length setting in the Settings tab.', 'product-reviews-importer' )
);

printf( '</div>' ); // pri-help-section.
