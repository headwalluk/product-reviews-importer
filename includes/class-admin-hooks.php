<?php
/**
 * Admin Hooks class.
 *
 * Handles all admin-related functionality for the plugin.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Admin Hooks class.
 *
 * @since 1.0.0
 */
class Admin_Hooks {

	/**
	 * Enqueue admin assets.
	 *
	 * Only loads assets on plugin admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		// Only load on our plugin pages.
		$plugin_pages = array(
			'woocommerce_page_' . ADMIN_PAGE_SLUG,
		);

		if ( ! in_array( $hook_suffix, $plugin_pages, true ) ) {
			return;
		}

		// Enqueue admin CSS.
		wp_enqueue_style(
			'product-reviews-importer-admin',
			PRODUCT_REVIEWS_IMPORTER_URL . 'assets/admin/admin.css',
			array(),
			PRODUCT_REVIEWS_IMPORTER_VERSION
		);

		// Enqueue admin JS.
		wp_enqueue_script(
			'product-reviews-importer-admin',
			PRODUCT_REVIEWS_IMPORTER_URL . 'assets/admin/admin.js',
			array( 'jquery' ),
			PRODUCT_REVIEWS_IMPORTER_VERSION,
			true
		);

		// Localize script with admin data.
		wp_localize_script(
			'product-reviews-importer-admin',
			'productReviewsImporter',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'uploadNonce' => wp_create_nonce( NONCE_CSV_UPLOAD ),
				'importNonce' => wp_create_nonce( NONCE_CSV_IMPORT ),
				'batchSize'   => BATCH_SIZE,
			)
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page(): void {
		// Check user capabilities.
		if ( ! current_user_can( ADMIN_CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'product-reviews-importer' ) );
		}

		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			printf(
				'<div class="wrap"><div class="notice notice-error"><p>%s</p></div></div>',
				esc_html__( 'Product Reviews Importer requires WooCommerce to be installed and activated.', 'product-reviews-importer' )
			);
			return;
		}

		// Load admin template.
		require_once PRODUCT_REVIEWS_IMPORTER_DIR . 'admin-templates/main-page.php';
	}

	/**
	 * Preserve hash fragment when redirecting after settings save.
	 *
	 * WordPress strips URL fragments during redirect. This restores the #settings hash
	 * so users return to the Settings tab instead of the default Import tab.
	 *
	 * @since 1.0.0
	 *
	 * @param string $location Redirect URL.
	 *
	 * @return string Modified redirect URL with hash fragment.
	 */
	public function preserve_settings_hash( string $location ): string {
		// Only modify redirect for our settings page.
		if ( false === strpos( $location, 'page=' . ADMIN_PAGE_SLUG ) ) {
			return $location;
		}

		// Check if this is a settings update (settings-updated query param present).
		if ( false !== strpos( $location, 'settings-updated=true' ) ) {
			// Add hash fragment to return to Settings tab.
			$location .= '#settings';
		}

		return $location;
	}

	/**
	 * Handle CSV file upload via AJAX.
	 *
	 * Validates file, moves to temp location, validates CSV structure,
	 * and stores upload info in transient for batch processing.
	 *
	 * @since 1.0.0
	 */
	public function ajax_upload_csv(): void {
		$response = array(
			'success' => false,
			'message' => '',
			'data'    => array(),
		);

		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), NONCE_CSV_UPLOAD ) ) {
			$response['message'] = __( 'Security check failed.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Check capability.
		if ( ! current_user_can( ADMIN_CAPABILITY ) ) {
			$response['message'] = __( 'Insufficient permissions.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Validate file upload.
		if ( empty( $_FILES['csv_file'] ) || ! isset( $_FILES['csv_file']['tmp_name'] ) ) {
			$response['message'] = __( 'No file uploaded.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput -- $_FILES array validated via file type, size, and upload error checks
		$file = $_FILES['csv_file'];

		// Check for upload errors.
		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			$response['message'] = __( 'File upload failed.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Validate file type.
		$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( 'csv' !== $file_extension ) {
			$response['message'] = __( 'Invalid file type. Only CSV files are allowed.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Validate file size (max 10MB).
		$max_size = 10 * 1024 * 1024;
		if ( $file['size'] > $max_size ) {
			$response['message'] = __( 'File too large. Maximum size is 10MB.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Move file to temp location.
		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'pri-temp';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$temp_filename = 'import_' . time() . '_' . wp_generate_password( 8, false ) . '.csv';
		$temp_filepath = trailingslashit( $temp_dir ) . $temp_filename;

		if ( ! move_uploaded_file( $file['tmp_name'], $temp_filepath ) ) {
			$response['message'] = __( 'Failed to move uploaded file.', 'product-reviews-importer' );
			wp_send_json( $response );
		}
		// phpcs:enable

		// Validate CSV structure.
		$csv        = new CSV_Importer( $temp_filepath );
		$headers    = $csv->parse_headers();
		$validation = $csv->validate();

		if ( false === $headers ) {
			wp_delete_file( $temp_filepath );
			$response['message'] = __( 'Failed to read CSV file.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		if ( ! $validation['valid'] ) {
			wp_delete_file( $temp_filepath );
			$response['message'] = implode( ' ', $validation['errors'] );
			wp_send_json( $response );
		}

		// Store upload data in transient.
		$upload_id   = wp_generate_password( 12, false );
		$upload_data = array(
			'file_path'  => $temp_filepath,
			'total_rows' => $csv->get_total_count(),
			'headers'    => $headers,
			'uploaded'   => time(),
		);

		set_transient( TRANSIENT_UPLOAD_DATA . $upload_id, $upload_data, TRANSIENT_EXPIRATION );

		// Return success response.
		$response['success'] = true;
		$response['message'] = __( 'File uploaded successfully.', 'product-reviews-importer' );
		$response['data']    = array(
			'uploadId'  => $upload_id,
			'totalRows' => $upload_data['total_rows'],
			'headers'   => $headers,
		);

		wp_send_json( $response );
	}

	/**
	 * Process CSV import batch via AJAX.
	 *
	 * Reads one batch from CSV, processes reviews, updates progress,
	 * and returns results for frontend display.
	 *
	 * @since 1.0.0
	 */
	public function ajax_import_batch(): void {
		$response = array(
			'success' => false,
			'message' => '',
			'data'    => array(),
		);

		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), NONCE_CSV_IMPORT ) ) {
			$response['message'] = __( 'Security check failed.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Check capability.
		if ( ! current_user_can( ADMIN_CAPABILITY ) ) {
			$response['message'] = __( 'Insufficient permissions.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		// Get upload ID and offset.
		$upload_id = isset( $_POST['uploadId'] ) ? sanitize_text_field( wp_unslash( $_POST['uploadId'] ) ) : '';
		$offset    = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

		if ( empty( $upload_id ) ) {
			$response['message'] = __( 'Invalid upload ID.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Get upload data from transient.
		$upload_data = get_transient( TRANSIENT_UPLOAD_DATA . $upload_id );

		if ( false === $upload_data ) {
			$response['message'] = __( 'Upload session expired. Please upload the file again.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Verify file still exists.
		if ( ! file_exists( $upload_data['file_path'] ) ) {
			delete_transient( TRANSIENT_UPLOAD_DATA . $upload_id );
			$response['message'] = __( 'CSV file not found.', 'product-reviews-importer' );
			wp_send_json( $response );
		}

		// Get batch from CSV.
		$csv   = new CSV_Importer( $upload_data['file_path'] );
		$batch = $csv->get_batch( $offset, BATCH_SIZE );

		if ( empty( $batch ) ) {
			// Get final progress before cleanup.
			$progress = get_transient( TRANSIENT_IMPORT_PROGRESS . $upload_id );
			if ( false === $progress ) {
				$progress = array(
					'success' => 0,
					'updated' => 0,
					'errors'  => array(),
				);
			}

			// Cleanup.
			wp_delete_file( $upload_data['file_path'] );
			delete_transient( TRANSIENT_UPLOAD_DATA . $upload_id );
			delete_transient( TRANSIENT_IMPORT_PROGRESS . $upload_id );

			// Build completion message.
			$error_count = count( $progress['errors'] );
			if ( $error_count > 0 ) {
				$response['message'] = sprintf(
					/* translators: 1: Success count, 2: Updated count, 3: Error count */
					__( 'Import complete! Created %1$d new reviews, updated %2$d existing reviews. %3$d errors occurred.', 'product-reviews-importer' ),
					$progress['success'],
					$progress['updated'],
					$error_count
				);
			} else {
				$response['message'] = sprintf(
					/* translators: 1: Success count, 2: Updated count */
					__( 'Import complete! Created %1$d new reviews, updated %2$d existing reviews.', 'product-reviews-importer' ),
					$progress['success'],
					$progress['updated']
				);
			}

			$response['success'] = true;
			$response['data']    = array(
				'complete'   => true,
				'success'    => $progress['success'],
				'updated'    => $progress['updated'],
				'errorCount' => $error_count,
				'errorList'  => $progress['errors'],
			);
			wp_send_json( $response );
		}

		// Import batch.
		$plugin   = get_plugin_instance();
		$settings = $plugin->get_settings();
		$importer = new Review_Importer( $settings );
		$results  = $importer->import_reviews( $batch );

		// Update progress in transient.
		$progress = get_transient( TRANSIENT_IMPORT_PROGRESS . $upload_id );
		if ( false === $progress ) {
			$progress = array(
				'processed' => 0,
				'success'   => 0,
				'updated'   => 0,
				'errors'    => array(),
			);
		}

		$progress['processed'] += count( $batch );
		$progress['success']   += $results['success'];
		$progress['updated']   += $results['updated'];
		$progress['errors']     = array_merge( $progress['errors'], $results['errors'] );

		set_transient( TRANSIENT_IMPORT_PROGRESS . $upload_id, $progress, TRANSIENT_EXPIRATION );

		// Return response.
		$response['success'] = true;
		$response['message'] = sprintf(
			/* translators: 1: Number of rows processed, 2: Total rows */
			__( 'Processed %1$d of %2$d rows...', 'product-reviews-importer' ),
			$progress['processed'],
			$upload_data['total_rows']
		);
		$response['data'] = array(
			'processed' => $progress['processed'],
			'total'     => $upload_data['total_rows'],
			'success'   => $progress['success'],
			'updated'   => $progress['updated'],
			'errors'    => count( $progress['errors'] ),
			'complete'  => false,
		);

		wp_send_json( $response );
	}
}
