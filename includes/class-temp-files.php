<?php
/**
 * Temporary upload storage.
 *
 * Owns the pri-temp/ directory where uploaded CSVs wait to be imported:
 * protects it from remote access and purges files left by unfinished imports.
 *
 * @package Product_Reviews_Importer
 * @since   1.3.0
 */

namespace Product_Reviews_Importer;

defined( 'ABSPATH' ) || die();

/**
 * Temporary upload storage.
 *
 * @since 1.3.0
 */
class Temp_Files {

	/**
	 * Settings instance.
	 *
	 * @since 1.3.0
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 *
	 * @param Settings $settings Plugin settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register the purge cron callback and make sure the event is scheduled.
	 *
	 * @since 1.3.0
	 */
	public function register(): void {
		add_action( CRON_PURGE_TEMP_FILES, array( $this, 'purge_expired_files' ) );

		if ( false === wp_next_scheduled( CRON_PURGE_TEMP_FILES ) ) {
			wp_schedule_event( time(), 'daily', CRON_PURGE_TEMP_FILES );
		}
	}

	/**
	 * Get the absolute path of the temporary upload directory.
	 *
	 * @since 1.3.0
	 *
	 * @return string Directory path, without a trailing slash.
	 */
	public static function get_dir_path(): string {
		$upload_dir = wp_upload_dir( null, false );

		return $upload_dir['basedir'] . '/' . TEMP_DIR_NAME;
	}

	/**
	 * Create the temporary directory if needed and write its access-blocking files.
	 *
	 * @since 1.3.0
	 *
	 * @return bool True when the directory exists and both protection files are in place.
	 */
	public function protect_dir(): bool {
		$dir_path     = self::get_dir_path();
		$is_protected = false;

		if ( ! wp_mkdir_p( $dir_path ) ) {
			$this->log_error( 'protect_dir: could not create ' . $dir_path . '.' );
		} else {
			$filesystem = $this->get_filesystem();
			$protection = array(
				TEMP_DIR_HTACCESS => TEMP_DIR_HTACCESS_RULES,
				TEMP_DIR_INDEX    => TEMP_DIR_INDEX_CONTENTS,
			);

			$is_protected = true;
			foreach ( $protection as $file_name => $contents ) {
				$file_path = $dir_path . '/' . $file_name;

				if ( $filesystem->exists( $file_path ) && $filesystem->get_contents( $file_path ) === $contents ) {
					// Already in place.
				} elseif ( $filesystem->put_contents( $file_path, $contents, TEMP_DIR_FILE_MODE ) ) {
					// Written.
				} else {
					$this->log_error( 'protect_dir: could not write ' . $file_path . '.' );
					$is_protected = false;
				}
			}
		}

		return $is_protected;
	}

	/**
	 * Delete uploaded files older than the retention setting. Runs on the daily cron event.
	 *
	 * @since 1.3.0
	 *
	 * @return int Number of files deleted.
	 */
	public function purge_expired_files(): int {
		$deleted_count = 0;
		$dir_path      = self::get_dir_path();

		if ( is_dir( $dir_path ) ) {
			// Also protects a directory created before the plugin wrote its .htaccess.
			$this->protect_dir();

			$cutoff     = time() - ( $this->settings->get_temp_retention_days() * DAY_IN_SECONDS );
			$file_names = scandir( $dir_path );

			if ( false === $file_names ) {
				$this->log_error( 'purge_expired_files: could not list ' . $dir_path . '.' );
				$file_names = array();
			}

			foreach ( $file_names as $file_name ) {
				$file_path = $dir_path . '/' . $file_name;

				if ( in_array( $file_name, array( '.', '..', TEMP_DIR_HTACCESS, TEMP_DIR_INDEX ), true ) || ! is_file( $file_path ) ) {
					continue;
				}

				$modified_time = filemtime( $file_path );

				if ( false === $modified_time ) {
					$this->log_error( 'purge_expired_files: could not read the age of ' . $file_path . '.' );
				} elseif ( $modified_time > $cutoff ) {
					// Not expired yet.
				} else {
					wp_delete_file( $file_path );

					if ( file_exists( $file_path ) ) {
						$this->log_error( 'purge_expired_files: could not delete ' . $file_path . '.' );
					} else {
						++$deleted_count;
					}
				}
			}
		}

		return $deleted_count;
	}

	/**
	 * Remove the scheduled purge event. Called on plugin deactivation.
	 *
	 * @since 1.3.0
	 */
	public static function unschedule(): void {
		wp_clear_scheduled_hook( CRON_PURGE_TEMP_FILES );
	}

	/**
	 * Get a direct filesystem instance.
	 *
	 * The global WP_Filesystem() can need FTP credentials, which a cron or AJAX
	 * request can't supply; the plugin writes only inside wp-content/uploads.
	 *
	 * @since 1.3.0
	 *
	 * @return \WP_Filesystem_Direct Filesystem instance.
	 */
	private function get_filesystem(): \WP_Filesystem_Direct {
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		return new \WP_Filesystem_Direct( null );
	}

	/**
	 * Log an error message to the PHP error log unconditionally.
	 *
	 * @since 1.3.0
	 *
	 * @param string $message The message to log.
	 */
	private function log_error( string $message ): void {
		error_log( 'Product_Reviews_Importer Temp_Files [error]: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Failures must be visible without WP_DEBUG.
	}
}
