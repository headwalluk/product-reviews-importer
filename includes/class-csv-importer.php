<?php
/**
 * CSV Importer class.
 *
 * Handles CSV file parsing and normalization for review imports.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * CSV Importer class.
 *
 * @since 1.0.0
 */
class CSV_Importer {

	/**
	 * CSV file path.
	 *
	 * @var string
	 */
	private string $file_path;

	/**
	 * CSV column headers.
	 *
	 * @var array<int, string>
	 */
	private array $headers = array();

	/**
	 * Total row count (excluding header).
	 *
	 * @var int|null
	 */
	private ?int $total_rows = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_path Absolute path to CSV file.
	 */
	public function __construct( string $file_path ) {
		$this->file_path = $file_path;
	}

	/**
	 * Parse CSV headers.
	 *
	 * Detects UTF-8 BOM and reads first row as headers.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, string>|false Column headers or false on failure.
	 */
	public function parse_headers() {
		$headers = array();

		if ( ! file_exists( $this->file_path ) || ! is_readable( $this->file_path ) ) {
			return false;
		}

		// phpcs:disable WordPress.WP.AlternativeFunctions -- fopen/fread/fclose required for fgetcsv() streaming. WP_Filesystem doesn't provide compatible stream resources.
		$handle = fopen( $this->file_path, 'r' );
		if ( false === $handle ) {
			return false;
		}

		// Detect and skip UTF-8 BOM if present.
		$bom = fread( $handle, 3 );
		if ( "\xEF\xBB\xBF" !== $bom ) {
			// No BOM found, rewind to start.
			rewind( $handle );
		}

		// Read first row as headers.
		$row = fgetcsv( $handle );
		fclose( $handle );
		// phpcs:enable WordPress.WP.AlternativeFunctions

		if ( false === $row || empty( $row ) ) {
			return false;
		}

		// Store and return headers.
		$headers          = array_map( 'trim', $row );
		$this->headers    = $headers;
		$this->total_rows = null; // Reset cached count.

		return $headers;
	}

	/**
	 * Get total row count.
	 *
	 * Counts data rows (excludes header row).
	 *
	 * @since 1.0.0
	 *
	 * @return int Row count or 0 on failure.
	 */
	public function get_total_count(): int {
		$count = 0;

		// Return cached count if available.
		if ( ! is_null( $this->total_rows ) ) {
			$count = $this->total_rows;
			return $count;
		}

		if ( ! file_exists( $this->file_path ) || ! is_readable( $this->file_path ) ) {
			return $count;
		}

		// phpcs:disable WordPress.WP.AlternativeFunctions -- fopen/fread/fclose required for fgetcsv() streaming.
		$handle = fopen( $this->file_path, 'r' );
		if ( false === $handle ) {
			return $count;
		}

		// Skip BOM if present.
		$bom = fread( $handle, 3 );
		if ( "\xEF\xBB\xBF" !== $bom ) {
			rewind( $handle );
		}

		// Skip header row.
		fgetcsv( $handle );

		// Count remaining rows.
		while ( false !== fgetcsv( $handle ) ) {
			++$count;
		}

		fclose( $handle );
		// phpcs:enable WordPress.WP.AlternativeFunctions

		$this->total_rows = $count;
		return $count;
	}

	/**
	 * Get batch of reviews.
	 *
	 * Returns normalized review data ready for Review_Importer.
	 *
	 * @since 1.0.0
	 *
	 * @param int $offset Starting row (0-indexed, excludes header).
	 * @param int $limit  Maximum rows to return.
	 *
	 * @return array<int, array<string, mixed>> Array of normalized review data.
	 */
	public function get_batch( int $offset, int $limit ): array {
		$reviews = array();

		if ( empty( $this->headers ) ) {
			$this->parse_headers();
		}

		if ( empty( $this->headers ) || ! file_exists( $this->file_path ) ) {
			return $reviews;
		}
		// phpcs:disable WordPress.WP.AlternativeFunctions -- fopen/fread/fclose required for fgetcsv() streaming. WP_Filesystem doesn't provide compatible stream resources.		$handle = fopen( $this->file_path, 'r' );
		if ( false === $handle ) {
			return $reviews;
		}

		// Skip BOM if present.
		$bom = fread( $handle, 3 );
		if ( "\xEF\xBB\xBF" !== $bom ) {
			rewind( $handle );
		}

		// Skip header row.
		fgetcsv( $handle );

		// Skip to offset position.
		$current_row = 0;
		while ( $current_row < $offset && false !== fgetcsv( $handle ) ) {
			++$current_row;
		}

		// Read batch.
		$rows_read = 0;
		while ( $rows_read < $limit && ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( empty( $row ) || ( 1 === count( $row ) && empty( $row[0] ) ) ) {
				// Skip empty rows.
				++$current_row;
				continue;
			}

			$review = $this->normalize_row( $row, $current_row + 1 );
			if ( ! empty( $review ) ) {
				$reviews[] = $review;
			}

			++$rows_read;
			++$current_row;
		}

		fclose( $handle );
		// phpcs:enable WordPress.WP.AlternativeFunctions

		return $reviews;
	}

	/**
	 * Normalize CSV row to review data format.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int, string> $row        CSV row data.
	 * @param int                $row_number Row number for error reporting.
	 *
	 * @return array<string, mixed> Normalized review data.
	 */
	private function normalize_row( array $row, int $row_number ): array {
		$normalized = array();

		// Map CSV columns to array keys.
		$column_map = $this->get_column_map();

		foreach ( $column_map as $csv_column => $review_key ) {
			$index = array_search( $csv_column, $this->headers, true );
			if ( false !== $index && isset( $row[ $index ] ) ) {
				$normalized[ $review_key ] = trim( $row[ $index ] );
			}
		}

		// Add row number for error reporting.
		$normalized['_row_number'] = $row_number;

		return $normalized;
	}

	/**
	 * Get column mapping.
	 *
	 * Maps CSV column names to normalized review data keys.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string> Column mapping.
	 */
	private function get_column_map(): array {
		$map = array(
			'SKU'          => 'product_sku',
			'Author Name'  => 'author_name',
			'Author Email' => 'author_email',
			'Author IP'    => 'author_ip',
			'Review Date'  => 'review_date',
			'Review Text'  => 'review_text',
			'Review Stars' => 'review_stars',
		);

		return $map;
	}

	/**
	 * Validate CSV structure and data.
	 *
	 * Checks headers, required columns, and validates sample rows.
	 *
	 * @since 1.0.0
	 *
	 * @return array{valid: bool, errors: array<string>} Validation result.
	 */
	public function validate(): array {
		$result = array(
			'valid'  => true,
			'errors' => array(),
		);

		// Parse headers if not already done.
		if ( empty( $this->headers ) ) {
			$headers = $this->parse_headers();
			if ( false === $headers ) {
				$result['valid']    = false;
				$result['errors'][] = __( 'Unable to read CSV file.', 'product-reviews-importer' );
				return $result;
			}
		}

		// Check for required columns.
		$required_columns = array( 'SKU', 'Author Name', 'Review Text', 'Review Stars' );
		$missing_columns  = array();

		foreach ( $required_columns as $column ) {
			if ( ! in_array( $column, $this->headers, true ) ) {
				$missing_columns[] = $column;
			}
		}

		if ( ! empty( $missing_columns ) ) {
			$result['valid']    = false;
			$result['errors'][] = sprintf(
				/* translators: %s: Comma-separated list of missing column names */
				__( 'Missing required columns: %s', 'product-reviews-importer' ),
				implode( ', ', $missing_columns )
			);
		}

		// Check if file has data rows.
		$total = $this->get_total_count();
		if ( 0 === $total ) {
			$result['valid']    = false;
			$result['errors'][] = __( 'CSV file contains no data rows.', 'product-reviews-importer' );
		}

		return $result;
	}
}
