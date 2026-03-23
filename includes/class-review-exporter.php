<?php
/**
 * Review Exporter class.
 *
 * Handles exporting WooCommerce product reviews in various formats.
 *
 * @package Product_Reviews_Importer
 * @since   1.2.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Review Exporter class.
 *
 * @since 1.2.0
 */
class Review_Exporter {

	/**
	 * Export reviews as Walmart syndication CSV.
	 *
	 * Queries all approved WooCommerce product reviews, maps them to the
	 * Walmart review syndication format, and streams a CSV download.
	 *
	 * @since 1.2.0
	 */
	public function export_walmart_csv(): void {
		// Verify nonce and capability.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), NONCE_EXPORT ) ) {
			wp_die( esc_html__( 'Security check failed.', 'product-reviews-importer' ) );
		}

		if ( ! current_user_can( ADMIN_CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'product-reviews-importer' ) );
		}

		$reviews = $this->get_all_reviews();
		$rows    = $this->map_reviews_to_walmart_format( $reviews );

		$this->send_csv_download( 'walmart-review-syndication', $this->get_walmart_headers(), $rows );
	}

	/**
	 * Get all approved WooCommerce product reviews.
	 *
	 * @since 1.2.0
	 *
	 * @return array Array of comment objects.
	 */
	private function get_all_reviews(): array {
		return get_comments(
			array(
				'type'   => 'review',
				'status' => 'approve',
				'number' => 0,
			)
		);
	}

	/**
	 * Get Walmart CSV column headers.
	 *
	 * @since 1.2.0
	 *
	 * @return array Column headers.
	 */
	private function get_walmart_headers(): array {
		return array(
			'Walmart Item ID',
			'SKU',
			'Review Title',
			'Review Body',
			'Review Rating',
			'Review Created Date',
			'Review User Name',
			'URL link',
			'Incentivized Review',
		);
	}

	/**
	 * Map review objects to Walmart syndication format.
	 *
	 * @since 1.2.0
	 *
	 * @param array $reviews Array of comment objects.
	 *
	 * @return array Array of row arrays in Walmart format.
	 */
	private function map_reviews_to_walmart_format( array $reviews ): array {
		$rows = array();

		foreach ( $reviews as $review ) {
			$product_id = (int) $review->comment_post_ID;
			$product    = wc_get_product( $product_id );

			// Skip reviews for products that no longer exist.
			if ( ! $product ) {
				continue;
			}

			$rating = (int) get_comment_meta( $review->comment_ID, 'rating', true );

			// Walmart requires whole numbers 1-5, round down any half-stars.
			$rating = max( MIN_STAR_RATING, min( MAX_STAR_RATING, $rating ) );

			// Format date as MM/DD/YYYY per Walmart specification.
			$date = gmdate( 'm/d/Y', strtotime( $review->comment_date ) );

			// Strip HTML from review body — Walmart expects plain text.
			$review_body = wp_strip_all_tags( $review->comment_content );

			$rows[] = array(
				'',
				$product->get_sku(),
				'',
				$review_body,
				$rating,
				$date,
				$review->comment_author,
				get_permalink( $product_id ),
				'No',
			);
		}

		return $rows;
	}

	/**
	 * Stream a CSV file download to the browser.
	 *
	 * @since 1.2.0
	 *
	 * @param string $filename_prefix Filename prefix (date will be appended).
	 * @param array  $headers         Column headers.
	 * @param array  $rows            Data rows.
	 */
	private function send_csv_download( string $filename_prefix, array $headers, array $rows ): void {
		$filename = $filename_prefix . '-' . gmdate( 'Y-m-d' ) . '.csv';

		// Send download headers.
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Write UTF-8 BOM for Excel compatibility.
		// phpcs:disable WordPress.WP.AlternativeFunctions -- Writing to php://output stream, not filesystem.
		$output = fopen( 'php://output', 'w' );
		fwrite( $output, "\xEF\xBB\xBF" );

		// Write header row.
		fputcsv( $output, $headers );

		// Write data rows.
		foreach ( $rows as $row ) {
			fputcsv( $output, $row );
		}

		fclose( $output );
		// phpcs:enable
		exit;
	}

	/**
	 * Get the count of approved product reviews.
	 *
	 * @since 1.2.0
	 *
	 * @return int Number of approved product reviews.
	 */
	public function get_review_count(): int {
		return (int) get_comments(
			array(
				'type'   => 'review',
				'status' => 'approve',
				'count'  => true,
			)
		);
	}
}
