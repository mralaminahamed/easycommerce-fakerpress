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

use EasyCommerceFakerPress\Controllers\Product_REST_Controller;
use EasyCommerceFakerPress\Controllers\Customer_REST_Controller;
use EasyCommerceFakerPress\Controllers\Order_REST_Controller;
use EasyCommerceFakerPress\Controllers\Coupon_REST_Controller;
use EasyCommerceFakerPress\Controllers\Product_Variation_REST_Controller;
use EasyCommerceFakerPress\Controllers\Shipping_Plan_REST_Controller;
use EasyCommerceFakerPress\Controllers\Tax_Classes_REST_Controller;
use EasyCommerceFakerPress\Controllers\Transaction_REST_Controller;
use EasyCommerceFakerPress\Controllers\Cart_Session_REST_Controller;
use EasyCommerceFakerPress\Controllers\Location_REST_Controller;

/**
 * Main Plugin Class
 *
 * Comprehensive EasyCommerce test data generator featuring 10 specialized generators,
 * real-time validation system, modern React Router v7 interface, WordPress admin
 * color integration, and advanced parameter configuration.
 *
 * @since 1.0.0
 * @version 2.1.0
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
	public string $version = '2.1.0';

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
		register_activation_hook( EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE, array( $this, 'flush_rewrite_rules' ) );
		register_deactivation_hook( EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE, array( $this, 'flush_rewrite_rules' ) );

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
		global $_wp_admin_css_colors;

		if ( 'toplevel_page_easycommerce-fakerpress' !== $hook ) {
			return;
		}

		$asset_file = EASYCOMMERCE_FAKERPRESS_PLUGIN_PATH . 'build/admin.asset.php';
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
	 * Initializes and registers all REST API controllers for the plugin.
	 * Includes both core generators and enhanced Version 2.0 generators.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_rest_routes(): void {
		$controllers = array(
			// Core generators.
			new Product_REST_Controller(),
			new Customer_REST_Controller(),
			new Order_REST_Controller(),
			new Coupon_REST_Controller(),

			// Enhanced generators (Version 2.0).
			new Product_Variation_REST_Controller(),
			new Shipping_Plan_REST_Controller(),
			new Tax_Classes_REST_Controller(),
			new Transaction_REST_Controller(),
			new Cart_Session_REST_Controller(),
			new Location_REST_Controller(),
		);

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}

	/**
	 * Flush rewrite rules on activation and deactivation
	 *
	 * Flushes rewrite rules to clean up any custom endpoints.
	 *
	 * @since 1.0.0
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
		$plugin = 'easycommerce/easycommerce.php';

		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
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

	/**
	 * Get FakerPHP locale for display purposes
	 *
	 * Replicates Generator locale detection logic for frontend display
	 *
	 * @since 2.0.0
	 *
	 * @param string $wp_locale WordPress locale code.
	 *
	 * @return string FakerPHP compatible locale code.
	 */
	public function get_faker_locale( string $wp_locale ): string {
		// Allow developers to override the locale.
		$custom_locale = apply_filters( 'easycommerce_fakerpress_locale', $wp_locale );

		// Get supported locales.
		$supported_locales = array_keys( $this->get_locale_labels() );

		// Direct match.
		if ( in_array( $custom_locale, $supported_locales, true ) ) {
			return $custom_locale;
		}

		// Try language fallback.
		$language = substr( $custom_locale, 0, 2 );
		foreach ( $supported_locales as $locale ) {
			if ( strpos( $locale, $language . '_' ) === 0 ) {
				return $locale;
			}
		}

		// Default fallback.
		return 'en_US';
	}

	/**
	 * Get human-readable labels for all supported FakerPHP locales
	 *
	 * @since 2.0.0
	 *
	 * @return array Associative array of locale codes and labels.
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
