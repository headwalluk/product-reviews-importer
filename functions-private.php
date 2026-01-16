<?php
/**
 * Private helper functions.
 *
 * Internal helper functions for the plugin (namespaced).
 * Use sparingly - prefer class methods when possible.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Check if WooCommerce is active.
 *
 * @since 1.0.0
 *
 * @return bool True if WooCommerce is active.
 */
function is_woocommerce_active(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * Get the plugin instance.
 *
 * @since 1.0.0
 *
 * @return Plugin|null Plugin instance or null if not initialized.
 */
function get_plugin_instance(): ?Plugin {
	global $product_reviews_importer;
	return $product_reviews_importer ?? null;
}

/**
 * Get server public IP address.
 *
 * Fetches the server's public IP address from icanhazip.com and caches it.
 * Falls back to 127.0.0.1 if unable to determine public IP (avoids leaking internal network info).
 *
 * @since 1.0.0
 *
 * @return string Server public IP address or 127.0.0.1 fallback.
 */
function get_server_ip(): string {
	$ip_address = null;

	// Check cache first.
	$cached_ip = get_transient( 'pri_server_public_ip' );
	if ( false !== $cached_ip && filter_var( $cached_ip, FILTER_VALIDATE_IP ) ) {
		$ip_address = $cached_ip;
	}

	// If not cached, fetch public IP from icanhazip.com.
	if ( is_null( $ip_address ) ) {
		$response = wp_remote_get(
			'https://icanhazip.com',
			array(
				'timeout'     => 5,
				'redirection' => 0,
				'headers'     => array( 'Accept' => 'text/plain' ),
			)
		);

		// Validate response.
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$fetched_ip = trim( wp_remote_retrieve_body( $response ) );

			// Validate IP address format.
			if ( filter_var( $fetched_ip, FILTER_VALIDATE_IP ) ) {
				$ip_address = $fetched_ip;
				// Cache for 7 days.
				set_transient( 'pri_server_public_ip', $ip_address, 7 * DAY_IN_SECONDS );
			}
		}
	}

	// If still no valid IP, use fallback.
	if ( is_null( $ip_address ) ) {
		$ip_address = '127.0.0.1';
		// Cache fallback for 1 day (shorter TTL in case network issue is temporary).
		set_transient( 'pri_server_public_ip', $ip_address, DAY_IN_SECONDS );
	}

	return $ip_address;
}

/**
 * Sanitize review text.
 *
 * Allows only basic HTML tags for line breaks and paragraphs.
 *
 * @since 1.0.0
 *
 * @param string $text Review text to sanitize.
 *
 * @return string Sanitized review text.
 */
function sanitize_review_text( string $text ): string {
	// Convert plain text line breaks to <br> tags.
	$text = nl2br( $text );

	// Allow only specific HTML tags.
	return wp_kses( $text, ALLOWED_REVIEW_TAGS );
}

/**
 * Validate star rating.
 *
 * @since 1.0.0
 *
 * @param mixed $rating Star rating to validate.
 *
 * @return int|false Valid star rating (1-5) or false if invalid.
 */
function validate_star_rating( $rating ) {
	$rating = absint( $rating );

	if ( $rating < MIN_STAR_RATING || $rating > MAX_STAR_RATING ) {
		return false;
	}

	return $rating;
}

/**
 * Get product ID by SKU for review attachment.
 *
 * If product is a variation, returns the parent product ID.
 * Reviews always attach to parent products, not variations.
 *
 * @since 1.0.0
 *
 * @param string $sku Product SKU.
 *
 * @return int|false Product ID or false if not found.
 */
function get_product_id_by_sku( string $sku ) {
	$product_id = wc_get_product_id_by_sku( $sku );

	if ( ! $product_id ) {
		return false;
	}

	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		return false;
	}

	// If variation, reviews go on parent product.
	if ( $product->is_type( 'variation' ) ) {
		return $product->get_parent_id();
	}

	// For all other product types, use product_id directly.
	return $product_id;
}

/**
 * Check if user exists by email.
 *
 * @since 1.0.0
 *
 * @param string $email Email address.
 *
 * @return int User ID if exists, 0 if not.
 */
function get_user_id_by_email( string $email ): int {
	$user = get_user_by( 'email', $email );
	return $user ? $user->ID : 0;
}
