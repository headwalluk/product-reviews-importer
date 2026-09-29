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
const OPT_TEMP_RETENTION_DAYS  = 'pri_temp_retention_days';

// Default values - prefix with DEF_.
const DEF_CREATE_USER_ACCOUNTS = false;
const DEF_MIN_REVIEW_LENGTH    = 10;
const DEF_AUTO_APPROVE_REVIEWS = true;
const DEF_REVIEWS_ARE_VERIFIED = false;
const DEF_TEMP_RETENTION_DAYS  = 2;

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
const NONCE_EXPORT     = 'pri_export';

// Export action.
const EXPORT_ACTION_WALMART = 'pri_export_walmart_csv';

// Walmart syndication CSV values, fixed by Walmart's specification.
const WALMART_DATE_FORMAT          = 'm/d/Y';
const WALMART_DATE_FORMAT_LABEL    = 'MM/DD/YYYY';
const WALMART_INCENTIVIZED_DEFAULT = 'No';

// Transient keys.
const TRANSIENT_UPLOAD_DATA     = 'pri_upload_data_';
const TRANSIENT_IMPORT_PROGRESS = 'pri_import_progress_';

// Transient expiration (1 hour).
const TRANSIENT_EXPIRATION = HOUR_IN_SECONDS;

// Uploaded CSVs awaiting import, in a subdirectory of wp-content/uploads.
const TEMP_DIR_NAME = 'pri-temp';

// Files the plugin writes into TEMP_DIR_NAME; the purge never deletes them.
const TEMP_DIR_HTACCESS = '.htaccess';
const TEMP_DIR_INDEX    = 'index.php';

// Blocks remote access on Apache and LiteSpeed; nginx ignores it.
const TEMP_DIR_HTACCESS_RULES = "# Product Reviews Importer: uploaded CSVs. Block all remote access.\n"
	. "<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n"
	. "<IfModule !mod_authz_core.c>\n\tOrder allow,deny\n\tDeny from all\n</IfModule>\n";

const TEMP_DIR_INDEX_CONTENTS = "<?php\n// Silence is golden.\n";

// Permissions for the files above. Passed explicitly: WP_Filesystem_Direct falls back to
// FS_CHMOD_FILE, which is only defined once WP_Filesystem() has run.
const TEMP_DIR_FILE_MODE = 0644;

// Retention bounds for the Settings tab, in days.
const MIN_TEMP_RETENTION_DAYS = 1;
const MAX_TEMP_RETENTION_DAYS = 365;

// Daily cron event that purges expired uploads.
const CRON_PURGE_TEMP_FILES = 'pri_purge_temp_files';

// GitHub updater.
const UPDATER_GITHUB_REPO = 'headwalluk/product-reviews-importer';
const UPDATER_CACHE_TTL   = 12 * HOUR_IN_SECONDS;
const UPDATER_CACHE_KEY   = 'pri_github_release';

// Back-off after a failed release lookup, shorter than UPDATER_CACHE_TTL.
const UPDATER_FAILURE_CACHE_KEY = 'pri_github_failed';
const UPDATER_FAILURE_CACHE_TTL = HOUR_IN_SECONDS;

// Seconds to wait on the GitHub API before giving up.
const UPDATER_REQUEST_TIMEOUT = 10;
