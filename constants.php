<?php
/**
 * Plugin constants.
 *
 * All magic strings and numbers must be defined here.
 * Exception: Translatable text strings use __() or _e() directly.
 *
 * @package Product_Reviews_Importer
 * @since   1.0.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

// WordPress option keys - prefix with OPT_.
const OPT_CREATE_USER_ACCOUNTS = 'pri_create_user_accounts';
const OPT_MIN_REVIEW_LENGTH    = 'pri_min_review_length';
const OPT_DEFAULT_IP_ADDRESS   = 'pri_default_ip_address';
const OPT_AUTO_APPROVE_REVIEWS = 'pri_auto_approve_reviews';
const OPT_REVIEWS_ARE_VERIFIED = 'pri_reviews_are_verified';

// Default values - prefix with DEF_.
const DEF_CREATE_USER_ACCOUNTS = false;
const DEF_MIN_REVIEW_LENGTH    = 10;
const DEF_AUTO_APPROVE_REVIEWS = true;
const DEF_REVIEWS_ARE_VERIFIED = false;

// CSV column mappings.
const CSV_COL_SKU          = 'SKU';
const CSV_COL_AUTHOR_NAME  = 'Author Name';
const CSV_COL_AUTHOR_EMAIL = 'Author Email';
const CSV_COL_AUTHOR_IP    = 'Author IP';
const CSV_COL_REVIEW_DATE  = 'Review Date';
const CSV_COL_REVIEW_TEXT  = 'Review Text';
const CSV_COL_REVIEW_STARS = 'Review Stars';

// Comment meta keys.
const META_RATING = 'rating';

// Date format for storage.
const DATE_FORMAT = 'Y-m-d H:i:s T';

// Minimum and maximum star ratings.
const MIN_STAR_RATING = 1;
const MAX_STAR_RATING = 5;

// Batch processing size.
const BATCH_SIZE = 50;

// Allowed HTML tags in review content.
const ALLOWED_REVIEW_TAGS = array(
	'br' => array(),
	'p'  => array(),
);

// Admin page slug.
const ADMIN_PAGE_SLUG = 'product-reviews-importer';

// Admin capability required.
const ADMIN_CAPABILITY = 'manage_woocommerce';

// Nonce actions.
const NONCE_CSV_UPLOAD = 'pri_csv_upload';
const NONCE_CSV_IMPORT = 'pri_csv_import';

// Transient keys.
const TRANSIENT_UPLOAD_DATA     = 'pri_upload_data_';
const TRANSIENT_IMPORT_PROGRESS = 'pri_import_progress_';

// Transient expiration (1 hour).
const TRANSIENT_EXPIRATION = HOUR_IN_SECONDS;
