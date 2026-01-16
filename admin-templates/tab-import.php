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
?>

<div class="pri-import-section">
	<h2><?php esc_html_e( 'Import Reviews from CSV', 'product-reviews-importer' ); ?></h2>
	
	<div class="pri-upload-section">
		<h3><?php esc_html_e( 'Step 1: Upload CSV File', 'product-reviews-importer' ); ?></h3>
		
		<?php
		printf(
			'<p>%s</p>',
			esc_html__( 'Select a CSV file containing product reviews. The file must include the following columns: SKU, Author Name, Author Email, Review Text, and Review Stars (1-5).', 'product-reviews-importer' )
		);
		?>
		
		<form id="pri-upload-form" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( NONCE_CSV_UPLOAD, 'pri_nonce' ); ?>
			
			<?php
			printf(
				'<p><input type="file" name="csv_file" id="pri-csv-file" accept=".csv" required /></p>'
			);
			printf(
				'<p><button type="submit" class="button button-primary">%s</button></p>',
				esc_html__( 'Upload and Validate', 'product-reviews-importer' )
			);
			?>
		</form>
		
		<div id="pri-validation-results" style="display:none;">
			<h3><?php esc_html_e( 'Validation Results', 'product-reviews-importer' ); ?></h3>
			<div id="pri-validation-messages"></div>
			<div id="pri-import-controls" style="display:none;">
				<?php
				printf(
					'<p><button type="button" id="pri-start-import" class="button button-primary">%s</button></p>',
					esc_html__( 'Start Import', 'product-reviews-importer' )
				);
				?>
			</div>
		</div>
	</div>
	
	<div id="pri-progress-section" style="display:none;">
		<h3><?php esc_html_e( 'Import Progress', 'product-reviews-importer' ); ?></h3>
		<?php
		printf(
			'<div class="pri-progress-bar"><div class="pri-progress-fill" style="width:0%%"></div></div>'
		);
		printf(
			'<p id="pri-progress-text">%s</p>',
			esc_html__( 'Preparing import...', 'product-reviews-importer' )
		);
		?>
	</div>
	
	<div id="pri-results-section" style="display:none;">
		<h3><?php esc_html_e( 'Import Results', 'product-reviews-importer' ); ?></h3>
		<div id="pri-results-summary"></div>
		<div id="pri-error-log"></div>
	</div>
</div>
