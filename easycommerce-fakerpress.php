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
 * Version:           2.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Al Amin Ahamed
 * Author URI:        https://github.com/mralaminahamed/
 * Text Domain:       easycommerce-fakerpress
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Requires Plugins:  easycommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EASYCOMMERCE_FAKERPRESS_VERSION', '1.0.3' );
define( 'EASYCOMMERCE_FAKERPRESS_PLUGIN_FILE', __FILE__ );
define( 'EASYCOMMERCE_FAKERPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EASYCOMMERCE_FAKERPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Load Composer autoloader.
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
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
