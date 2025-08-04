<?php
/**
 * Main Plugin Class
 *
 * @package EasyCommerceFakerPress
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EasyCommerceFakerPress\REST\Controllers\Product_REST_Controller;
use EasyCommerceFakerPress\REST\Controllers\Customer_REST_Controller;
use EasyCommerceFakerPress\REST\Controllers\Order_REST_Controller;
use EasyCommerceFakerPress\REST\Controllers\Coupon_REST_Controller;

/**
 * Main Plugin Class
 *
 * Handles plugin initialization, dependencies, admin interface, and REST API registration.
 *
 * @since 1.0.0
 */
class EasyCommerce_FakerPress {

	/**
	 * Single instance of the class
	 *
	 * @since 1.0.0
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Plugin version
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public string $version = ECFP_VERSION;

	/**
	 * Get single instance of the class
	 *
	 * Implements singleton pattern to ensure only one instance exists.
	 *
	 * @since 1.0.0
	 *
	 * @return self Plugin instance.
	 */
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Initialize the plugin
	 *
	 * Sets up hooks for activation, deactivation, and core functionality.
	 * Checks dependencies before proceeding with initialization.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init(): void {
		register_activation_hook( ECFP_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( ECFP_PLUGIN_FILE, array( $this, 'deactivate' ) );

		if ( ! $this->check_dependencies() ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Add admin menu page
	 *
	 * Creates the main admin menu page for the plugin interface.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
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
	 * Outputs the React root element where the admin interface will be mounted.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		echo '<div id="easycommerce-fakerpress-root"></div>';
	}

	/**
	 * Enqueue admin assets
	 *
	 * Loads JavaScript, CSS, and localization data for the admin interface.
	 * Only loads on the plugin's admin page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( 'toplevel_page_easycommerce-fakerpress' !== $hook ) {
			return;
		}

		$asset_file = ECFP_PLUGIN_PATH . 'build/admin.asset.php';
		if ( file_exists( $asset_file ) ) {
			$asset_data = require $asset_file;
			$deps       = $asset_data['dependencies'];
			$version    = $asset_data['version'];
		} else {
			$deps    = array( 'wp-element', 'wp-i18n' );
			$version = ECFP_VERSION;
		}

		wp_enqueue_script(
			'easycommerce-fakerpress-admin',
			ECFP_PLUGIN_URL . 'build/admin.js',
			$deps,
			$version,
			true
		);

		wp_enqueue_style(
			'easycommerce-fakerpress-admin',
			ECFP_PLUGIN_URL . 'build/admin.css',
			array(),
			$version
		);

		wp_localize_script(
			'easycommerce-fakerpress-admin',
			'ecfpApi',
			array(
				'restUrl'   => rest_url( 'easycommerce-fakerpress/v1/' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_set_script_translations( 'easycommerce-fakerpress-admin', 'easycommerce-fakerpress' );
	}

	/**
	 * Register REST API routes
	 *
	 * Initializes and registers all REST API controllers for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		$controllers = array(
			new Product_REST_Controller(),
			new Customer_REST_Controller(),
			new Order_REST_Controller(),
			new Coupon_REST_Controller(),
		);

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}

	/**
	 * Plugin activation handler
	 *
	 * Checks dependencies and flushes rewrite rules on activation.
	 * Terminates activation if dependencies are not met.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function activate(): void {
		if ( ! $this->check_dependencies() ) {
			wp_die(
				esc_html__( 'EasyCommerce FakerPress requires EasyCommerce plugin.', 'easycommerce-fakerpress' ),
				esc_html__( 'Plugin Activation Error', 'easycommerce-fakerpress' ),
				array( 'back_link' => true )
			);
		}
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation handler
	 *
	 * Flushes rewrite rules on deactivation to clean up any custom endpoints.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Display dependency notice
	 *
	 * Shows an admin notice when required dependencies are not met.
	 *
	 * @since 1.0.0
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
	 * Verifies that the required EasyCommerce plugin is installed and activated.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if EasyCommerce is active, false otherwise.
	 */
	public function is_easycommerce_active(): bool {
		return is_plugin_active( 'easycommerce/easycommerce.php' );
	}

	/**
	 * Check plugin dependencies
	 *
	 * Validates that all required dependencies are met before initialization.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if all dependencies are met, false otherwise.
	 */
	public function check_dependencies(): bool {
		return $this->is_easycommerce_active();
	}

	/**
	 * Prevent cloning of the instance
	 *
	 * Part of singleton pattern implementation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Prevent unserialization of the instance
	 *
	 * Part of singleton pattern implementation.
	 *
	 * @since 1.0.0
	 *
	 * @throws RuntimeException When attempting to unserialize.
	 *
	 * @return void
	 */
	public function __wakeup() {
		throw new RuntimeException( 'Cannot unserialize singleton' );
	}
}
