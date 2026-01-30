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

// Column list from field definitions.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables, not globals.
$field_definitions = get_csv_field_definitions();

printf( '<ul>' );
foreach ( $field_definitions as $field_name => $field_info ) {
	printf(
		'<li><strong>%s</strong> - %s</li>',
		esc_html( $field_name ),
		esc_html( $field_info['description'] )
	);
}
printf( '</ul>' );
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Sample CSV.
printf(
	'<h3>%s</h3>',
	esc_html__( 'Sample CSV', 'product-reviews-importer' )
);
printf(
	'<pre><code>%s</code></pre>',
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV sample data, safe by design.
	get_sample_csv()
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

// Developer Hooks.
printf(
	'<h3>%s</h3>',
	esc_html__( 'Developer Hooks', 'product-reviews-importer' )
);
printf(
	'<p>%s</p>',
	esc_html__( 'The following filter hooks are available for developers to extend or customize plugin functionality:', 'product-reviews-importer' )
);
printf(
	'<p><strong>%s</strong><br />%s</p>',
	esc_html__( 'product_reviews_importer_csv_field_definitions', 'product-reviews-importer' ),
	esc_html__( 'Customize CSV field definitions, add custom fields, or modify existing field behavior.', 'product-reviews-importer' )
);
printf(
	'<pre><code>%s</code></pre>',
	'add_filter( \'product_reviews_importer_csv_field_definitions\', function( $fields ) {' . "\n" .
	'    // Add a custom field' . "\n" .
	'    $fields[\'Custom Field\'] = array(' . "\n" .
	'        \'required\'    => false,' . "\n" .
	'        \'description\' => __( \'Custom field description\', \'my-plugin\' ),' . "\n" .
	'        \'map_to\'      => \'custom_field_key\',' . "\n" .
	'        \'sample\'      => \'Example Value\',' . "\n" .
	'    );' . "\n" .
	'    return $fields;' . "\n" .
	'} );'
);

printf( '</div>' ); // pri-help-section.
