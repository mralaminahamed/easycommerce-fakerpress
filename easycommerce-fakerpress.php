<?php
/**
 * EasyCommerce FakerPress
 *
 * @package           EasyCommerceFakerPress
 * @author            Al Amin Ahamed
 * @copyright         2025 Al Amin Ahamed
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       EasyCommerce FakerPress
 * Plugin URI:        https://github.com/mralaminahamed/easycommerce-fakerpress
 * Description:       Create realistic test data for your EasyCommerce store in seconds! Generate products, customers, orders, coupons and more with our intuitive admin interface. Perfect for development, testing, and demos. Features smart defaults, real-time validation, and seamless WordPress integration.
 * Version:           2.4.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Requires Plugins:  easycommerce
 * Author:            Al Amin Ahamed
 * Author URI:        https://github.com/mralaminahamed/
 * Text Domain:       easycommerce-fakerpress
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EASYCOMMERCE_FAKERPRESS_VERSION', '2.4.0' );
define( 'EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE', __FILE__ );
define( 'EASYCOMMERCE_FAKERPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EASYCOMMERCE_FAKERPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'EASYCOMMERCE_FAKERPRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader.
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	/*
	 * Left silent, this plugin activated and then did nothing at all: no warning,
	 * no notice, and everything it should register simply absent. Say what is wrong.
	 */
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			printf(
				'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s</p></div>',
				esc_html__( 'EasyCommerce FakerPress is not running.', 'easycommerce-fakerpress' ),
				esc_html__( 'Its autoloader is missing. Run "composer install --no-dev" in the plugin directory, or install the packaged build from the release ZIP.', 'easycommerce-fakerpress' )
			);
		}
	);

	return;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Get main plugin instance
 *
 * @since 1.0.0
 * @return EasyCommerce_FakerPress Plugin instance.
 */
function easycommerce_fakerpress(): EasyCommerce_FakerPress {
	return EasyCommerce_FakerPress::get_instance();
}


easycommerce_fakerpress()->init();
