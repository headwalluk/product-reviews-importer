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
 * Get server IP address.
 *
 * @since 1.0.0
 *
 * @return string Server IP address.
 */
function get_server_ip(): string {
	$server_addr = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '127.0.0.1';
	return $server_addr;
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
 * Get product ID by SKU.
 *
 * If product is a variation, returns the parent product ID.
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

	// If variation, get parent product ID.
	if ( $product->is_type( 'variation' ) ) {
		return $product->get_parent_id();
	}

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
