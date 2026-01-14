<?php
/**
 * Main Plugin Class for EasyCommerce FakerPress
 *
 * The main plugin class that orchestrates the entire EasyCommerce FakerPress plugin functionality.
 * This class handles plugin initialization, admin interface setup, REST API registration,
 * asset management, and WordPress admin color scheme integration.
 *
 * @package EasyCommerceFakerPress
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EasyCommerceFakerPress\Controllers\Product;
use EasyCommerceFakerPress\Controllers\Customer;
use EasyCommerceFakerPress\Controllers\Order;
use EasyCommerceFakerPress\Controllers\Coupon;
use EasyCommerceFakerPress\Controllers\Product_Variation;
use EasyCommerceFakerPress\Controllers\Shipping_Plan;
use EasyCommerceFakerPress\Controllers\Tax_Class;
use EasyCommerceFakerPress\Controllers\Transaction;
use EasyCommerceFakerPress\Controllers\Cart_Session;
use EasyCommerceFakerPress\Controllers\Location;
use EasyCommerceFakerPress\Controllers\Product_Review;

/**
 * Main Plugin Class for EasyCommerce FakerPress
 *
 * This class serves as the central orchestrator for the EasyCommerce FakerPress plugin,
 * providing comprehensive test data generation capabilities for EasyCommerce stores.
 * It manages 11 specialized generators, implements real-time validation, features a
 * modern React Router v7 interface, integrates with WordPress admin color schemes,
 * and provides advanced parameter configuration options.
 *
 * Key Features:
 * - 11 specialized data generators (Products, Customers, Orders, Coupons, Reviews, etc.)
 * - Real-time validation and dependency checking
 * - Modern React-based admin interface with Router v7
 * - WordPress admin color scheme integration
 * - Advanced parameter configuration system
 * - REST API endpoints for programmatic access
 * - Comprehensive logging and error handling
 * - Multi-locale support for international data generation
 *
 * @since 1.0.0
 * @version 2.0.3
 */
class EasyCommerce_FakerPress {

	/**
	 * Single instance of the plugin class
	 *
	 * Implements the singleton pattern to ensure only one instance of the plugin
	 * exists throughout the WordPress execution lifecycle. This prevents multiple
	 * initializations and ensures consistent state management.
	 *
	 * @since 1.0.0
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Plugin version number
	 *
	 * Stores the current version of the EasyCommerce FakerPress plugin.
	 * Used for asset versioning, database migrations, and compatibility checks.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public string $version = EASYCOMMERCE_FAKERPRESS_VERSION;

	/**
	 * Get single instance of the plugin class
	 *
	 * Implements the singleton pattern to ensure only one instance of the plugin
	 * exists throughout the WordPress execution lifecycle. This method provides
	 * global access to the plugin instance while preventing multiple instantiations.
	 *
	 * @since 1.0.0
	 *
	 * @return self The single plugin instance.
	 */
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Initialize the plugin
	 *
	 * Sets up all necessary WordPress hooks and actions for plugin functionality.
	 * Registers activation/deactivation hooks, admin menus, assets, and REST routes.
	 * Performs dependency checks to ensure EasyCommerce is available before
	 * enabling plugin features.
	 *
	 * This method is called automatically during WordPress plugin loading.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init(): void {
		register_activation_hook( EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE, array( $this, 'flush_rewrite_rules' ) );

		add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE ), array( $this, 'add_plugin_action_links' ) );
	}

	/**
	 * Add admin menu page
	 *
	 * Creates the main admin menu page for the EasyCommerce FakerPress interface.
	 * Adds a top-level menu item in the WordPress admin sidebar with the plugin icon.
	 * Only registers the menu if dependencies are met (EasyCommerce is active).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		// Skip to register the admin menu.
		if ( ! $this->check_dependencies() ) {
			return;
		}

		add_menu_page(
			__( 'EasyCommerce FakerPress', 'easycommerce-fakerpress' ),
			__( 'EC FakerPress', 'easycommerce-fakerpress' ),
			'manage_options',
			'easycommerce-fakerpress',
			array( $this, 'render_admin_page' ),
			'dashicons-randomize',
			30
		);
	}

	/**
	 * Render the admin page
	 *
	 * Outputs the HTML container element where the React admin interface will be mounted.
	 * This method serves as the callback for the WordPress add_menu_page() function,
	 * providing the entry point for the React Router v7 application.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		echo '<div id="easycommerce-fakerpress-root"></div>';
	}

	/**
	 * Add plugin action links
	 *
	 * Adds a "Get Started" link to the plugin's action links on the plugins page.
	 * Provides quick access to the admin page for new users.
	 *
	 * @since 1.0.0
	 * @hooked plugin_action_links_{plugin-file}
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array Modified plugin action links.
	 */
	public function add_plugin_action_links( array $links ): array {
		$links[] = '<a href="' . admin_url( 'admin.php?page=easycommerce-fakerpress' ) . '">' . __( 'Get Started', 'easycommerce-fakerpress' ) . '</a>';
		return $links;
	}

	/**
	 * Enqueue admin assets
	 *
	 * Loads and enqueues all necessary JavaScript, CSS, and localization assets
	 * for the admin interface. Only loads assets on the plugin's admin page to
	 * optimize performance. Includes WordPress admin color scheme integration
	 * and locale data for the React application.
	 *
	 * @since 1.0.0
	 * @hooked admin_enqueue_scripts
	 *
	 * @param string $hook The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		global $_wp_admin_css_colors;

		if ( 'toplevel_page_easycommerce-fakerpress' !== $hook ) {
			return;
		}

		$asset_file = EASYCOMMERCE_FAKERPRESS_PLUGIN_PATH . 'build/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset_data = require $asset_file;
		$deps       = $asset_data['dependencies'];
		$version    = $asset_data['version'];

		$current_color = get_user_option( 'admin_color', get_current_user_id() ) ?? 'fresh';
		$color_scheme  = $_wp_admin_css_colors[ $current_color ] ?? $_wp_admin_css_colors['fresh'];

		// Extract colors for Tailwind CSS variables.
		$admin_colors = array(
			'primary'   => $color_scheme->colors[0] ?? '#2271b1',
			'secondary' => $color_scheme->colors[1] ?? '#135e96',
			'highlight' => $color_scheme->colors[2] ?? '#043f54',
			'accent'    => $color_scheme->colors[3] ?? '#0a4b78',
		);

		wp_enqueue_script(
			'easycommerce-fakerpress-admin',
			EASYCOMMERCE_FAKERPRESS_PLUGIN_URL . 'build/admin.js',
			$deps,
			$version,
			true
		);

		wp_enqueue_style(
			'easycommerce-fakerpress-admin',
			EASYCOMMERCE_FAKERPRESS_PLUGIN_URL . 'build/admin.css',
			array(),
			$version
		);

		// Add CSS variables for admin colors.
		$css_vars = sprintf(
			':root { --wp-admin-primary: %s; --wp-admin-secondary: %s; --wp-admin-highlight: %s; --wp-admin-accent: %s; }',
			esc_attr( $admin_colors['primary'] ),
			esc_attr( $admin_colors['secondary'] ),
			esc_attr( $admin_colors['highlight'] ),
			esc_attr( $admin_colors['accent'] )
		);
		wp_add_inline_style( 'easycommerce-fakerpress-admin', $css_vars );

		// Get locale information for frontend display.
		$wp_locale     = get_locale();
		$faker_locale  = $this->get_faker_locale( $wp_locale );
		$locale_labels = $this->get_locale_labels();

		wp_localize_script(
			'easycommerce-fakerpress-admin',
			'easycommerceFakerpressApi',
			array(
				'restUrl'     => rest_url( 'easycommerce-fakerpress/v1/' ),
				'restNonce'   => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'adminColors' => $admin_colors,
				'colorScheme' => $current_color,
				'locale'      => array(
					'wordpress'  => $wp_locale,
					'faker'      => $faker_locale,
					'label'      => $locale_labels[ $faker_locale ] ?? 'English (United States)',
					'allLocales' => $locale_labels,
				),
			)
		);

		wp_set_script_translations( 'easycommerce-fakerpress-admin', 'easycommerce-fakerpress' );
	}

	/**
	 * Register REST API routes
	 *
	 * Initializes and registers all REST API controllers for data generation.
	 * Creates endpoints for all 11 specialized generators including core generators
	 * (Products, Customers, Orders, Coupons) and enhanced generators (Variations,
	 * Shipping, Tax, Transactions, Cart Sessions, Locations).
	 *
	 * @since 1.0.0
	 * @hooked rest_api_init
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		// Skip to register the admin menu.
		if ( ! $this->check_dependencies() ) {
			return;
		}

		$controllers = array(
			// Core generators.
			new Product(),
			new Customer(),
			new Order(),
			new Coupon(),

			// Enhanced generators (Version 2.0).
			new Product_Variation(),
			new Shipping_Plan(),
			new Tax_Class(),
			new Transaction(),
			new Cart_Session(),
			new Location(),

			// Additional generators (Version 2.0.3).
			new Product_Review(),
		);

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}

		// Register download sample data endpoint.
		register_rest_route(
			'easycommerce-fakerpress/v1',
			'/download-sample',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_download_sample_data' ),
				'permission_callback' => array( $this, 'rest_permission_check' ),
			)
		);
	}

	/**
	 * Plugin activation hook
	 *
	 * Handles plugin activation tasks including flushing rewrite rules.
	 * Sample data is downloaded when the admin page is first visited.
	 *
	 * @since 1.0.0
	 * @hooked register_activation_hook
	 *
	 * @return void
	 */
	public function activate_plugin(): void {
		// Flush rewrite rules.
		$this->flush_rewrite_rules();
	}

	/**
	 * Ensure sample data is available
	 *
	 * Downloads and extracts sample data from the remote repository if not already present.
	 * This ensures the plugin has access to locale-specific sample data for generation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if sample data is available, false on failure.
	 */
	public function ensure_sample_data(): bool {
		$sample_data_dir = $this->get_sample_data_directory();

		// Check if sample data already exists.
		if ( $this->sample_data_exists() ) {
			return true;
		}

		// Create sample data directory if it doesn't exist.
		if ( ! wp_mkdir_p( $sample_data_dir ) ) {
			return false;
		}

		// Download and extract sample data.
		return $this->download_sample_data();
	}

	/**
	 * Check if sample data exists
	 *
	 * Verifies that the required sample data directories and files are present.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if sample data exists, false otherwise.
	 */
	public function sample_data_exists(): bool {
		$sample_data_dir = $this->get_sample_data_directory();

		// Check for key directories that should exist.
		$required_dirs = array( 'products', 'customers', 'orders' );

		foreach ( $required_dirs as $dir ) {
			if ( ! is_dir( $sample_data_dir . '/' . $dir ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get sample data directory path
	 *
	 * Returns the path where sample data should be stored.
	 *
	 * @since 1.0.0
	 *
	 * @return string Path to sample data directory.
	 */
	public function get_sample_data_directory(): string {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . '/easycommerce-fakerpress-sample-data';
	}

	/**
	 * Download sample data from remote repository
	 *
	 * Downloads the sample data archive from GitHub and extracts it to the local directory.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	private function download_sample_data(): bool {
		$repo_owner = 'mralaminahamed';
		$repo_name  = 'easycommerce-fakerpress-sample-data';
		$branch     = 'trunk';

		// GitHub API URL for downloading the repository as zip.
		$download_url = "https://github.com/{$repo_owner}/{$repo_name}/archive/refs/heads/{$branch}.zip";

		$sample_data_dir = $this->get_sample_data_directory();
		$temp_zip_file   = $sample_data_dir . '/sample-data-temp.zip';
		$extracted_dir   = $sample_data_dir . '/temp-extract';

		// Initialize WordPress filesystem.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Download the zip file.
		$response = wp_remote_get(
			$download_url,
			array(
				'timeout' => 300, // 5 minutes timeout
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$zip_content = wp_remote_retrieve_body( $response );
		if ( empty( $zip_content ) ) {
			return false;
		}

		// Save zip file temporarily.
		if ( ! $wp_filesystem->put_contents( $temp_zip_file, $zip_content ) ) {
			return false;
		}

		// Extract the zip file.
		if ( ! $this->extract_zip( $temp_zip_file, $extracted_dir ) ) {
			$wp_filesystem->delete( $temp_zip_file );
			return false;
		}

		// Move extracted contents to the final location.
		$extracted_contents = $wp_filesystem->dirlist( $extracted_dir );
		if ( ! empty( $extracted_contents ) ) {
			$source_dir = $extracted_dir . '/' . key( $extracted_contents );
			$this->move_directory_contents( $source_dir, $sample_data_dir );
		}

		// Clean up temporary files.
		$wp_filesystem->delete( $temp_zip_file );
		$wp_filesystem->delete( $extracted_dir, true );

		return $this->sample_data_exists();
	}

	/**
	 * Extract ZIP file
	 *
	 * Extracts a ZIP file to the specified directory using WordPress filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @param string $zip_file Path to the ZIP file.
	 * @param string $extract_to Directory to extract to.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function extract_zip( string $zip_file, string $extract_to ): bool {
		if ( ! class_exists( 'ZipArchive' ) ) {
			error_log( 'EasyCommerce FakerPress: ZipArchive class not available' );
			return false;
		}

		$zip = new ZipArchive();
		if ( $zip->open( $zip_file ) !== true ) {
			error_log( 'EasyCommerce FakerPress: Failed to open zip file' );
			return false;
		}

		if ( ! $zip->extractTo( $extract_to ) ) {
			error_log( 'EasyCommerce FakerPress: Failed to extract zip file' );
			$zip->close();
			return false;
		}

		$zip->close();
		return true;
	}

	/**
	 * Move directory contents
	 *
	 * Moves all contents from one directory to another.
	 *
	 * @since 1.0.0
	 *
	 * @param string $source_dir Source directory.
	 * @param string $dest_dir Destination directory.
	 *
	 * @return void
	 */
	private function move_directory_contents( string $source_dir, string $dest_dir ): void {
		global $wp_filesystem;

		$items = $wp_filesystem->dirlist( $source_dir );
		foreach ( $items as $item ) {
			$source_path = $source_dir . '/' . $item['name'];
			$dest_path   = $dest_dir . '/' . $item['name'];

			if ( 'd' === $item['type'] ) {
				// Directory.
				if ( ! $wp_filesystem->exists( $dest_path ) ) {
					$wp_filesystem->mkdir( $dest_path );
				}
				$this->move_directory_contents( $source_path, $dest_path );
			} else {
				// File.
				$wp_filesystem->move( $source_path, $dest_path );
			}
		}
	}

	/**
	 * Flush rewrite rules on activation and deactivation
	 *
	 * Ensures WordPress rewrite rules are properly flushed when the plugin is
	 * activated or deactivated. This maintains clean URL routing and prevents
	 * conflicts with custom endpoints.
	 *
	 * @since 1.0.0
	 * @hooked register_deactivation_hook
	 *
	 * @return void
	 */
	public function flush_rewrite_rules(): void {
		\flush_rewrite_rules();
	}

	/**
	 * Display dependency notice
	 *
	 * Shows an admin notice when required dependencies are not met.
	 * Specifically checks for EasyCommerce plugin activation and displays
	 * an error message if it's not available, preventing plugin functionality.
	 *
	 * @since 1.0.0
	 * @hooked admin_notices
	 *
	 * @return void
	 */
	public function dependency_notice(): void {
		if ( ! $this->is_easycommerce_active() ) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'EasyCommerce FakerPress requires EasyCommerce plugin to be installed and active.', 'easycommerce-fakerpress' )
			);
		}
	}

	/**
	 * Check if EasyCommerce plugin is active
	 *
	 * Verifies that the required EasyCommerce plugin is installed and activated
	 * by checking the active plugins list. This is a critical dependency check
	 * that prevents the plugin from functioning without its core requirement.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if EasyCommerce is active, false otherwise.
	 */
	public function is_easycommerce_active(): bool {
		$plugin = 'easycommerce/easycommerce.php';

		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
	}

	/**
	 * Check plugin dependencies
	 *
	 * Validates that all required dependencies are met before enabling plugin features.
	 * Currently checks for EasyCommerce plugin activation, but can be extended
	 * for additional dependencies in the future.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if all dependencies are met, false otherwise.
	 */
	public function check_dependencies(): bool {
		return $this->is_easycommerce_active();
	}

	/**
	 * Prevent cloning of the plugin instance
	 *
	 * Prevents cloning of the singleton instance to maintain the singleton pattern.
	 * Throws an exception if attempted, ensuring only one plugin instance exists.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Prevent unserialization of the plugin instance
	 *
	 * Prevents unserialization of the singleton instance to maintain the singleton pattern.
	 * Throws a RuntimeException if attempted, ensuring plugin integrity and preventing
	 * multiple instances through deserialization attacks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 * @throws RuntimeException When attempting to unserialize the singleton instance.
	 */
	public function __wakeup() {
		throw new RuntimeException( 'Cannot unserialize singleton' );
	}

	/**
	 * Get FakerPHP locale for display purposes
	 *
	 * Converts WordPress locale codes to FakerPHP compatible locale codes for
	 * the admin interface display. Applies filters for customization and provides
	 * fallback logic for unsupported locales. Used by the React frontend to
	 * display current locale information.
	 *
	 * @since 1.0.0
	 *
	 * @param string $locale WordPress locale code (e.g., 'en_US', 'fr_FR').
	 *
	 * @return string FakerPHP compatible locale code (defaults to 'en_US').
	 */
	public function get_faker_locale( string $locale ): string {
		/**
		 * Filters the locale used for test data generation.
		 *
		 * Allows developers to override the default locale used by EasyCommerce FakerPress
		 * for generating test data. Useful for generating data in specific languages
		 * or regional formats regardless of the site's locale setting.
		 *
		 * @since 1.0.0
		 * @hook  easycommerce_fakerpress_locale
		 *
		 * @param string $locale The current WordPress locale code (e.g., 'en_US').
		 */
		$custom_locale = apply_filters( 'easycommerce_fakerpress_locale', $locale );

		// Get supported locales.
		$supported_locales = array_keys( $this->get_locale_labels() );

		// Direct match.
		if ( in_array( $custom_locale, $supported_locales, true ) ) {
			return $custom_locale;
		}

		// Try language fallback.
		$language = substr( $custom_locale, 0, 2 );
		foreach ( $supported_locales as $locale_code ) {
			if ( strpos( $locale_code, $language . '_' ) === 0 ) {
				return $locale_code;
			}
		}

		// Default fallback.
		return 'en_US';
	}

	/**
	 * REST API handler for downloading sample data
	 *
	 * Handles asynchronous download of sample data via REST API to avoid blocking
	 * the admin page load. Only accessible to users with manage_options capability.
	 *
	 * @since 2.0.4
	 *
	 * @param WP_REST_Request $request REST request object.
	 *
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public function rest_download_sample_data( WP_REST_Request $request ) {
		$result = $this->ensure_sample_data();
		if ( $result ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => 'Sample data downloaded successfully',
				),
				200
			);
		} else {
			return new WP_Error( 'download_failed', 'Failed to download sample data', array( 'status' => 500 ) );
		}
	}

	/**
	 * REST API permission check
	 *
	 * Verifies that the current user has the required permissions for REST API operations.
	 *
	 * @since 2.0.4
	 *
	 * @return bool True if user has permissions, false otherwise.
	 */
	public function rest_permission_check(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get human-readable labels for all supported FakerPHP locales
	 *
	 * Returns a comprehensive array of supported FakerPHP locales with their
	 * human-readable labels for use in the admin interface. Includes major
	 * world languages and regions for international data generation support.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string> Associative array mapping locale codes to display labels.
	 */
	public function get_locale_labels(): array {
		return array(
			'ar_SA'      => 'Arabic (Saudi Arabia)',
			'at_AT'      => 'Austrian German',
			'bg_BG'      => 'Bulgarian (Bulgaria)',
			'bn_BD'      => 'Bangla (Bangladesh)',
			'cs_CZ'      => 'Czech (Czech Republic)',
			'da_DK'      => 'Danish (Denmark)',
			'de_AT'      => 'German (Austria)',
			'de_CH'      => 'German (Switzerland)',
			'de_DE'      => 'German (Germany)',
			'el_CY'      => 'Greek (Cyprus)',
			'el_GR'      => 'Greek (Greece)',
			'en_AU'      => 'English (Australia)',
			'en_GB'      => 'English (Great Britain)',
			'en_HK'      => 'English (Hong Kong)',
			'en_IN'      => 'English (India)',
			'en_NG'      => 'English (Nigeria)',
			'en_NZ'      => 'English (New Zealand)',
			'en_PH'      => 'English (Philippines)',
			'en_SG'      => 'English (Singapore)',
			'en_UG'      => 'English (Uganda)',
			'en_US'      => 'English (United States)',
			'en_ZA'      => 'English (South Africa)',
			'es_AR'      => 'Spanish (Argentina)',
			'es_ES'      => 'Spanish (Spain)',
			'es_PE'      => 'Spanish (Peru)',
			'es_VE'      => 'Spanish (Venezuela)',
			'et_EE'      => 'Estonian (Estonia)',
			'fa_IR'      => 'Persian (Iran)',
			'fi_FI'      => 'Finnish (Finland)',
			'fr_BE'      => 'French (Belgium)',
			'fr_CA'      => 'French (Canada)',
			'fr_CH'      => 'French (Switzerland)',
			'fr_FR'      => 'French (France)',
			'he_IL'      => 'Hebrew (Israel)',
			'hr_HR'      => 'Croatian (Croatia)',
			'hu_HU'      => 'Hungarian (Hungary)',
			'hy_AM'      => 'Armenian (Armenia)',
			'id_ID'      => 'Indonesian (Indonesia)',
			'is_IS'      => 'Icelandic (Iceland)',
			'it_CH'      => 'Italian (Switzerland)',
			'it_IT'      => 'Italian (Italy)',
			'ja_JP'      => 'Japanese (Japan)',
			'ka_GE'      => 'Georgian (Georgia)',
			'kk_KZ'      => 'Kazakh (Kazakhstan)',
			'ko_KR'      => 'Korean (South Korea)',
			'lt_LT'      => 'Lithuanian (Lithuania)',
			'lv_LV'      => 'Latvian (Latvia)',
			'me_ME'      => 'Montenegrin (Montenegro)',
			'mn_MN'      => 'Mongolian (Mongolia)',
			'ms_MY'      => 'Malay (Malaysia)',
			'nb_NO'      => 'Norwegian Bokmål (Norway)',
			'ne_NP'      => 'Nepali (Nepal)',
			'nl_BE'      => 'Dutch (Belgium)',
			'nl_NL'      => 'Dutch (Netherlands)',
			'pl_PL'      => 'Polish (Poland)',
			'pt_AO'      => 'Portuguese (Angola)',
			'pt_BR'      => 'Portuguese (Brazil)',
			'pt_PT'      => 'Portuguese (Portugal)',
			'ro_MD'      => 'Romanian (Moldova)',
			'ro_RO'      => 'Romanian (Romania)',
			'ru_RU'      => 'Russian (Russia)',
			'sk_SK'      => 'Slovak (Slovakia)',
			'sl_SI'      => 'Slovenian (Slovenia)',
			'sr_Cyrl_RS' => 'Serbian Cyrillic (Serbia)',
			'sr_Latn_RS' => 'Serbian Latin (Serbia)',
			'sr_RS'      => 'Serbian (Serbia)',
			'sv_SE'      => 'Swedish (Sweden)',
			'th_TH'      => 'Thai (Thailand)',
			'tr_TR'      => 'Turkish (Turkey)',
			'uk_UA'      => 'Ukrainian (Ukraine)',
			'vi_VN'      => 'Vietnamese (Vietnam)',
			'zh_CN'      => 'Chinese (China)',
			'zh_TW'      => 'Chinese (Taiwan)',
		);
	}
}
