<?php

// Note: EasyCommerce compatibility features may be added here in the future

/**
 * Main Plugin Class
 *
 * @package EasyCommerceFakerPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EasyCommerce_FakerPress {

	private static ?self $instance = null;

	public string $version = ECFP_VERSION;

	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	public function init(): void {
		register_activation_hook( ECFP_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( ECFP_PLUGIN_FILE, array( $this, 'deactivate' ) );

		if ( ! $this->check_dependencies() ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
			return;
		}

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_ecfp_generate_data', array( $this, 'handle_ajax_request' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'easycommerce-fakerpress', false, dirname( plugin_basename( ECFP_PLUGIN_FILE ) ) . '/languages/' );
	}

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

	public function render_admin_page(): void {
		echo '<div id="easycommerce-fakerpress-root"></div>';
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( $hook !== 'toplevel_page_easycommerce-fakerpress' ) {
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
			'ecfpAjax',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'ecfp_nonce' ),
			)
		);

		wp_set_script_translations( 'easycommerce-fakerpress-admin', 'easycommerce-fakerpress' );
	}

	public function handle_ajax_request(): void {
		check_ajax_referer( 'ecfp_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'easycommerce-fakerpress' ) );
		}

		$type  = sanitize_text_field( $_POST['type'] ?? '' );
		$count = absint( $_POST['count'] ?? 0 );

		if ( empty( $type ) || $count <= 0 ) {
			wp_send_json_error( __( 'Invalid parameters.', 'easycommerce-fakerpress' ) );
		}

		try {
			switch ( $type ) {
				case 'products':
					$result = $this->generate_products( $count );
					break;
				case 'customers':
					$result = $this->generate_customers( $count );
					break;
				case 'orders':
					$result = $this->generate_orders( $count );
					break;
				case 'coupons':
					$result = $this->generate_coupons( $count );
					break;
				default:
					throw new InvalidArgumentException( __( 'Invalid data type.', 'easycommerce-fakerpress' ) );
			}

			wp_send_json_success( $result );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	private function generate_products( int $count ): array {
		require_once ECFP_PLUGIN_PATH . 'includes/class-product-generator.php';
		$generator = new ECFP_Product_Generator();
		return $generator->generate( $count );
	}

	private function generate_customers( int $count ): array {
		require_once ECFP_PLUGIN_PATH . 'includes/class-customer-generator.php';
		$generator = new ECFP_Customer_Generator();
		return $generator->generate( $count );
	}

	private function generate_orders( int $count ): array {
		require_once ECFP_PLUGIN_PATH . 'includes/class-order-generator.php';
		$generator = new ECFP_Order_Generator();
		return $generator->generate( $count );
	}

	private function generate_coupons( int $count ): array {
		require_once ECFP_PLUGIN_PATH . 'includes/class-coupon-generator.php';
		$generator = new ECFP_Coupon_Generator();
		return $generator->generate( $count );
	}

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

	public function deactivate(): void {
		flush_rewrite_rules();
	}

	public function dependency_notice(): void {
		if ( ! $this->is_easycommerce_active() ) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'EasyCommerce FakerPress requires EasyCommerce plugin to be installed and active.', 'easycommerce-fakerpress' )
			);
		}
	}

	public function is_easycommerce_active(): bool {
		return is_plugin_active( 'easycommerce/easycommerce.php' );
	}

	public function check_dependencies(): bool {
		return $this->is_easycommerce_active();
	}

	private function __clone() {
	}

	public function __wakeup() {
		throw new RuntimeException( 'Cannot unserialize singleton' );
	}
}