<?php
/**
 * Review Importer class.
 *
 * Core import engine - source-agnostic review processing.
 * Handles product matching, duplicate detection, user creation, and comment creation.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Review Importer class.
 *
 * @since 1.0.0
 */
class Review_Importer {

	/**
	 * Settings instance.
	 *
	 * @since 1.0.0
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Import a batch of reviews.
	 *
	 * @since 1.0.0
	 *
	 * @param array $reviews Array of normalized review data.
	 *
	 * @return array Results with success/error counts and details.
	 */
	public function import_reviews( array $reviews ): array {
		$results = array(
			'success' => 0,
			'updated' => 0,
			'errors'  => array(),
		);

		foreach ( $reviews as $index => $review_data ) {
			$result = $this->import_review( $review_data );

			if ( is_wp_error( $result ) ) {
				$results['errors'][] = array(
					'index'   => $index,
					'message' => $result->get_error_message(),
					'data'    => $review_data,
				);
			} else {
				// Check if this was an update or new insert.
				$existing = $this->find_existing_review(
					$this->get_review_product_id( $review_data['product_sku'] ),
					sanitize_email( $review_data['author_email'] )
				);

				if ( $existing ) {
					++$results['updated'];
				} else {
					++$results['success'];
				}
			}
		}

		return $results;
	}

	/**
	 * Import a single review.
	 *
	 * @since 1.0.0
	 *
	 * @param array $review_data Normalized review data.
	 *
	 * @return int|\WP_Error Comment ID on success, WP_Error on failure.
	 */
	public function import_review( array $review_data ) {
		// Step 1: Validate required fields.
		$required = array( 'product_sku', 'author_name', 'author_email', 'review_text', 'review_stars' );

		foreach ( $required as $field ) {
			if ( empty( $review_data[ $field ] ) ) {
				return new \WP_Error( 'missing_field', sprintf( 'Missing required field: %s', $field ) );
			}
		}

		// Step 2: Validate star rating.
		$rating = validate_star_rating( $review_data['review_stars'] );

		if ( false === $rating ) {
			return new \WP_Error( 'invalid_rating', 'Star rating must be 1-5' );
		}

		// Step 3: Get product ID.
		$product_id = $this->get_review_product_id( $review_data['product_sku'] );

		if ( ! $product_id ) {
			return new \WP_Error( 'product_not_found', sprintf( 'Product not found: %s', $review_data['product_sku'] ) );
		}

		// Step 4: Sanitize and validate email.
		$author_email = sanitize_email( $review_data['author_email'] );

		if ( ! is_email( $author_email ) ) {
			return new \WP_Error( 'invalid_email', sprintf( 'Invalid email: %s', $review_data['author_email'] ) );
		}

		// Step 5: Sanitize and validate review text.
		$review_text = sanitize_review_text( $review_data['review_text'] );

		$min_length = $this->settings->get_min_review_length();

		if ( strlen( $review_text ) < $min_length ) {
			return new \WP_Error(
				'review_too_short',
				sprintf( 'Review text too short (minimum %d characters)', $min_length )
			);
		}

		// Step 6: Process optional fields.
		$author_ip   = $this->process_author_ip( $review_data['author_ip'] ?? '' );
		$review_date = $this->parse_review_date( $review_data['review_date'] ?? '' );

		// Step 7: Get or create user account.
		$user_id = $this->get_or_create_user( $author_email, $review_data['author_name'] );

		// Step 8: Check for existing review.
		$existing = $this->find_existing_review( $product_id, $author_email );

		if ( $existing ) {
			// Update existing review (content and rating only).
			return $this->update_review( $existing->comment_ID, $review_text, $rating );
		}

		// Step 9: Create new review.
		return $this->create_review( $product_id, $review_data['author_name'], $author_email, $author_ip, $review_text, $review_date, $rating, $user_id );
	}

	/**
	 * Get product ID for review attachment.
	 *
	 * Wrapper around helper function for better encapsulation.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sku Product SKU.
	 *
	 * @return int|false Product ID or false if not found.
	 */
	private function get_review_product_id( string $sku ) {
		return get_product_id_by_sku( $sku );
	}

	/**
	 * Process author IP address.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip Author IP from import data.
	 *
	 * @return string Valid IP address.
	 */
	private function process_author_ip( string $ip ): string {
		if ( ! empty( $ip ) && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}

		return $this->settings->get_default_ip_address();
	}

	/**
	 * Parse review date.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_string Date string from import data.
	 *
	 * @return string Formatted date for WordPress (Y-m-d H:i:s).
	 */
	private function parse_review_date( string $date_string ): string {
		if ( empty( $date_string ) ) {
			$now = new \DateTime( 'now', wp_timezone() );
			return $now->format( 'Y-m-d H:i:s' );
		}

		try {
			$date = new \DateTime( $date_string );
			// WordPress stores dates as Y-m-d H:i:s (no timezone).
			return $date->format( 'Y-m-d H:i:s' );
		} catch ( \Exception $e ) {
			// Invalid date format, use current time.
			$now = new \DateTime( 'now', wp_timezone() );
			return $now->format( 'Y-m-d H:i:s' );
		}
	}

	/**
	 * Get or create user account.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email Author email.
	 * @param string $name  Author name.
	 *
	 * @return int User ID (0 for guest).
	 */
	private function get_or_create_user( string $email, string $name ): int {
		// Check for existing user by email.
		$user_id = get_user_id_by_email( $email );

		// If no user found and setting enabled, create new user.
		if ( ! $user_id && $this->settings->get_create_user_accounts() ) {
			$user_id = $this->create_customer_user( $email, $name );
		}

		return $user_id;
	}

	/**
	 * Create customer user account.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email Author email.
	 * @param string $name  Author name.
	 *
	 * @return int User ID (0 if creation failed).
	 */
	private function create_customer_user( string $email, string $name ): int {
		// Double-check email doesn't exist.
		if ( email_exists( $email ) ) {
			$user = get_user_by( 'email', $email );
			return $user ? $user->ID : 0;
		}

		// Create username from email (everything before @).
		$username = sanitize_user( current( explode( '@', $email ) ), true );

		// Ensure username is unique.
		if ( username_exists( $username ) ) {
			$username = $username . '_' . wp_rand( 100, 999 );
		}

		// Create user with random password.
		$user_id = wp_create_user( $username, wp_generate_password(), $email );

		if ( is_wp_error( $user_id ) ) {
			// User creation failed, return 0 (guest).
			error_log( 'Product Reviews Importer: Failed to create user - ' . $user_id->get_error_message() );
			return 0;
		}

		// Set user role to Customer.
		$user = new \WP_User( $user_id );
		$user->set_role( 'customer' );

		// Set display name.
		wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => $name,
			)
		);

		return $user_id;
	}

	/**
	 * Find existing review.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $product_id   Product ID.
	 * @param string $author_email Author email.
	 *
	 * @return object|false Comment object or false if not found.
	 */
	private function find_existing_review( int $product_id, string $author_email ) {
		$comments = get_comments(
			array(
				'post_id'      => $product_id,
				'author_email' => $author_email,
				'type'         => 'review',
				'status'       => 'all', // Include approved, pending, spam, trash.
				'number'       => 1,
			)
		);

		return ! empty( $comments ) ? $comments[0] : false;
	}

	/**
	 * Update existing review.
	 *
	 * Updates content and rating only, preserves original date/author info.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $comment_id  Comment ID.
	 * @param string $content     Review text.
	 * @param int    $rating      Star rating (1-5).
	 *
	 * @return int Comment ID.
	 */
	private function update_review( int $comment_id, string $content, int $rating ): int {
		// Update comment content.
		wp_update_comment(
			array(
				'comment_ID'      => $comment_id,
				'comment_content' => $content,
			)
		);

		// Update rating metadata.
		update_comment_meta( $comment_id, 'rating', $rating );

		return $comment_id;
	}

	/**
	 * Create new review.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $product_id   Product ID.
	 * @param string $author_name  Author name.
	 * @param string $author_email Author email.
	 * @param string $author_ip    Author IP.
	 * @param string $content      Review text.
	 * @param string $date         Review date (Y-m-d H:i:s).
	 * @param int    $rating       Star rating (1-5).
	 * @param int    $user_id      User ID (0 for guest).
	 *
	 * @return int|\WP_Error Comment ID on success, WP_Error on failure.
	 */
	private function create_review( int $product_id, string $author_name, string $author_email, string $author_ip, string $content, string $date, int $rating, int $user_id ) {
		// If user exists, use their WordPress display name instead of CSV author name.
		$display_name = $author_name;
		if ( $user_id > 0 ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$display_name = $user->display_name;
			}
		}

		$comment_id = wp_insert_comment(
			array(
				'comment_post_ID'      => $product_id,
				'comment_author'       => trim( $display_name ),
				'comment_author_email' => $author_email,
				'comment_author_url'   => '',
				'comment_content'      => $content,
				'comment_type'         => 'review',
				'comment_parent'       => 0,
				'user_id'              => $user_id,
				'comment_author_IP'    => $author_ip,
				'comment_agent'        => 'Product Reviews Importer',
				'comment_date'         => $date,
				'comment_approved'     => $this->settings->get_auto_approve_reviews() ? 1 : 0,
			)
		);

		if ( ! $comment_id || is_wp_error( $comment_id ) ) {
			return new \WP_Error( 'comment_insert_failed', 'Failed to create review' );
		}

		// Add rating metadata.
		update_comment_meta( $comment_id, 'rating', $rating );

		// Set verified status from settings.
		$verified = $this->settings->get_reviews_are_verified() ? 1 : 0;
		update_comment_meta( $comment_id, 'verified', $verified );

		return $comment_id;
	}
}
