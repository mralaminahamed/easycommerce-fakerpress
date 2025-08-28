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
 * Description:       Generate fake eCommerce data (orders, products, customers, coupons) for EasyCommerce plugin using PHPFaker library with modern React-based admin interface.
 * Version:           1.0.0
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

define( 'EASYCOMMERCE_FAKERPRESS_VERSION', '1.0.0' );
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
 * @return EasyCommerceFakerPress Plugin instance.
 */
function easycommerce_fakerpress(): EasyCommerceFakerPress {
	return EasyCommerceFakerPress::get_instance();
}

easycommerce_fakerpress()->init();
