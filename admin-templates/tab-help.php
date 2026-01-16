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
?>

<div class="pri-help-section">
	<h2><?php esc_html_e( 'How to Import Product Reviews', 'product-reviews-importer' ); ?></h2>
	
	<h3><?php esc_html_e( 'CSV File Format', 'product-reviews-importer' ); ?></h3>
	<?php
	printf(
		'<p>%s</p>',
		esc_html__( 'Your CSV file must include the following columns (all values should be quoted):', 'product-reviews-importer' )
	);
	?>
	
	<ul>
		<li><strong><?php esc_html_e( 'SKU', 'product-reviews-importer' ); ?></strong> - <?php esc_html_e( 'Product SKU (required)', 'product-reviews-importer' ); ?></li>
		<li><strong><?php esc_html_e( 'Author Name', 'product-reviews-importer' ); ?></strong> - <?php esc_html_e( 'Reviewer name (required)', 'product-reviews-importer' ); ?></li>
		<li><strong><?php esc_html_e( 'Author Email', 'product-reviews-importer' ); ?></strong> - <?php esc_html_e( 'Reviewer email address (required)', 'product-reviews-importer' ); ?></li>
		<li><strong><?php esc_html_e( 'Review Text', 'product-reviews-importer' ); ?></strong> - <?php esc_html_e( 'Review content (required, can span multiple lines)', 'product-reviews-importer' ); ?></li>
		<li><strong><?php esc_html_e( 'Review Stars', 'product-reviews-importer' ); ?></strong> - <?php esc_html_e( 'Star rating 1-5 (required)', 'product-reviews-importer' ); ?></li>
		<li><strong><?php esc_html_e( 'Author IP', 'product-reviews-importer' ); ?></strong> - <?php esc_html_e( 'IP address (optional)', 'product-reviews-importer' ); ?></li>
		<li><strong><?php esc_html_e( 'Review Date', 'product-reviews-importer' ); ?></strong> - <?php esc_html_e( 'Date in Y-m-d H:i:s T format (optional)', 'product-reviews-importer' ); ?></li>
	</ul>
	
	<h3><?php esc_html_e( 'Sample CSV', 'product-reviews-importer' ); ?></h3>
	<pre><code>"SKU","Author Name","Author Email","Author IP","Review Date","Review Text","Review Stars"
"ABC123","John Doe","john.doe@example.com","123.123.123.123","2026-01-15 14:30:00 GMT","Great product, highly recommend!","5"
"ABC123","Jane Smith","jane.smith@example.com","","2026-01-16 10:00:00 GMT","Not what I expected.","2"</code></pre>
	
	<h3><?php esc_html_e( 'Important Notes', 'product-reviews-importer' ); ?></h3>
	<ul>
		<li><?php esc_html_e( 'Reviews are matched to products using the SKU field', 'product-reviews-importer' ); ?></li>
		<li><?php esc_html_e( 'If a product variation SKU is provided, the review will be attached to the parent product', 'product-reviews-importer' ); ?></li>
		<li><?php esc_html_e( 'Duplicate reviews (same email + product) will update the existing review', 'product-reviews-importer' ); ?></li>
		<li><?php esc_html_e( 'Review text supports line breaks and basic formatting', 'product-reviews-importer' ); ?></li>
		<li><?php esc_html_e( 'Invalid rows will be skipped and reported in the import results', 'product-reviews-importer' ); ?></li>
	</ul>
	
	<h3><?php esc_html_e( 'Troubleshooting', 'product-reviews-importer' ); ?></h3>
	<?php
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
	?>
</div>
