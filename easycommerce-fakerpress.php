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
 * Plugin URI:        https://alaminahamed.com/projects/easycommerce-fakerpress
 * Description:       Generate fake eCommerce data (orders, products, customers, coupons) for EasyCommerce plugin using PHPFaker library with modern React-based admin interface.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Al Amin Ahamed
 * Author URI:        https://alaminahamed.com
 * Text Domain:       easycommerce-fakerpress
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Requires Plugins:  easycommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ECFP_VERSION', '1.0.0' );
define( 'ECFP_PLUGIN_FILE', __FILE__ );
define( 'ECFP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ECFP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once __DIR__ . '/class-easycommerce-fakerpress.php';

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
