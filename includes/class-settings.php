<?php
/**
 * Settings class.
 *
 * Handles plugin settings registration and retrieval.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Settings class.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings(): void {
		// Create user accounts setting.
		register_setting(
			'product_reviews_importer',
			OPT_CREATE_USER_ACCOUNTS,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'default'           => DEF_CREATE_USER_ACCOUNTS,
			)
		);

		// Minimum review length setting.
		register_setting(
			'product_reviews_importer',
			OPT_MIN_REVIEW_LENGTH,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( $this, 'sanitize_min_length' ),
				'default'           => DEF_MIN_REVIEW_LENGTH,
			)
		);

		// Default IP address setting.
		register_setting(
			'product_reviews_importer',
			OPT_DEFAULT_IP_ADDRESS,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_ip_address' ),
				'default'           => '',
			)
		);
	}

	/**
	 * Get create user accounts setting.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether to create user accounts for new reviewers.
	 */
	public function get_create_user_accounts(): bool {
		return (bool) filter_var(
			get_option( OPT_CREATE_USER_ACCOUNTS, DEF_CREATE_USER_ACCOUNTS ),
			FILTER_VALIDATE_BOOLEAN
		);
	}

	/**
	 * Get minimum review length setting.
	 *
	 * @since 1.0.0
	 *
	 * @return int Minimum review length in characters.
	 */
	public function get_min_review_length(): int {
		return absint( get_option( OPT_MIN_REVIEW_LENGTH, DEF_MIN_REVIEW_LENGTH ) );
	}

	/**
	 * Get default IP address setting.
	 *
	 * @since 1.0.0
	 *
	 * @return string Default IP address to use when author IP is blank.
	 */
	public function get_default_ip_address(): string {
		$ip = get_option( OPT_DEFAULT_IP_ADDRESS, '' );

		// If empty, use server IP.
		if ( empty( $ip ) ) {
			return get_server_ip();
		}

		return $ip;
	}

	/**
	 * Sanitize boolean value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return bool Sanitized boolean value.
	 */
	public function sanitize_boolean( $value ): bool {
		return (bool) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Sanitize minimum review length.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return int Sanitized minimum length (at least 1).
	 */
	public function sanitize_min_length( $value ): int {
		$length = absint( $value );
		return max( 1, $length );
	}

	/**
	 * Sanitize IP address.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string Sanitized IP address or empty string.
	 */
	public function sanitize_ip_address( $value ): string {
		$ip = sanitize_text_field( $value );

		if ( empty( $ip ) ) {
			return '';
		}

		// Validate IP address format.
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}

		return '';
	}
}
