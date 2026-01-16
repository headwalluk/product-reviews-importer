<?php
/**
 * Settings tab template.
 *
 * Global plugin settings.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

// Get settings instance.
$pri_plugin_instance   = get_plugin_instance();
$pri_settings_instance = $pri_plugin_instance ? $pri_plugin_instance->get_settings() : null;

if ( ! $pri_settings_instance ) {
	printf(
		'<p>%s</p>',
		esc_html__( 'Settings not available.', 'product-reviews-importer' )
	);
	return;
}

// Settings section.
printf( '<div class="pri-settings-section">' );
printf(
	'<h2>%s</h2>',
	esc_html__( 'Import Settings', 'product-reviews-importer' )
);

// Form start.
printf( '<form method="post" action="options.php">' );
settings_fields( 'product_reviews_importer' );

// Settings table.
printf( '<table class="form-table" role="presentation"><tbody>' );

// Create User Accounts.
printf( '<tr><th scope="row">%s</th><td>', esc_html__( 'Create User Accounts', 'product-reviews-importer' ) );
printf(
	'<label><input type="checkbox" name="%s" value="1" %s /> %s</label>',
	esc_attr( OPT_CREATE_USER_ACCOUNTS ),
	checked( $pri_settings_instance->get_create_user_accounts(), true, false ),
	esc_html__( 'Create WordPress user accounts for new reviewers (Customer role)', 'product-reviews-importer' )
);
printf(
	'<p class="description">%s</p>',
	esc_html__( 'If disabled, reviews will be created as guest comments (user_id = 0).', 'product-reviews-importer' )
);
printf( '</td></tr>' );

// Minimum Review Length.
printf(
	'<tr><th scope="row"><label for="pri_min_review_length">%s</label></th><td>',
	esc_html__( 'Minimum Review Length', 'product-reviews-importer' )
);
printf(
	'<input type="number" id="pri_min_review_length" name="%s" value="%d" min="1" class="small-text" /> %s',
	esc_attr( OPT_MIN_REVIEW_LENGTH ),
	absint( $pri_settings_instance->get_min_review_length() ),
	esc_html__( 'characters', 'product-reviews-importer' )
);
printf(
	'<p class="description">%s</p>',
	esc_html__( 'Reviews shorter than this will be rejected during import.', 'product-reviews-importer' )
);
printf( '</td></tr>' );

// Default IP Address.
printf(
	'<tr><th scope="row"><label for="pri_default_ip">%s</label></th><td>',
	esc_html__( 'Default IP Address', 'product-reviews-importer' )
);
printf(
	'<input type="text" id="pri_default_ip" name="%s" value="%s" class="regular-text" placeholder="%s" />',
	esc_attr( OPT_DEFAULT_IP_ADDRESS ),
	esc_attr( get_option( OPT_DEFAULT_IP_ADDRESS, '' ) ),
	esc_attr( get_server_ip() )
);
printf(
	'<p class="description">%s</p>',
	esc_html__( 'Used when CSV does not provide an author IP address. Leave blank to use server IP.', 'product-reviews-importer' )
);
printf( '</td></tr>' );

// Auto-Approve Reviews.
printf( '<tr><th scope="row">%s</th><td>', esc_html__( 'Auto-Approve Reviews', 'product-reviews-importer' ) );
printf(
	'<label><input type="checkbox" name="%s" value="1" %s /> %s</label>',
	esc_attr( OPT_AUTO_APPROVE_REVIEWS ),
	checked( $pri_settings_instance->get_auto_approve_reviews(), true, false ),
	esc_html__( 'Automatically approve imported reviews', 'product-reviews-importer' )
);
printf(
	'<p class="description">%s</p>',
	esc_html__( 'If disabled, imported reviews will require manual moderation.', 'product-reviews-importer' )
);
printf( '</td></tr>' );

// Mark as Verified Purchase.
printf( '<tr><th scope="row">%s</th><td>', esc_html__( 'Mark as Verified Purchase', 'product-reviews-importer' ) );
printf(
	'<label><input type="checkbox" name="%s" value="1" %s /> %s</label>',
	esc_attr( OPT_REVIEWS_ARE_VERIFIED ),
	checked( $pri_settings_instance->get_reviews_are_verified(), true, false ),
	esc_html__( 'Mark imported reviews as verified purchases', 'product-reviews-importer' )
);
printf(
	'<p class="description">%s</p>',
	esc_html__( 'Enable this if you have verified that the reviews came from actual purchases (e.g., importing from another WooCommerce store).', 'product-reviews-importer' )
);
printf( '</td></tr>' );

// Close table.
printf( '</tbody></table>' );

submit_button();
printf( '</form>' );
printf( '</div>' ); // pri-settings-section.
