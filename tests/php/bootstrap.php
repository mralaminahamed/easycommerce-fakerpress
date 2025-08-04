<?php
/**
 * PHPUnit bootstrap file for EasyCommerce FakerPress
 */

// Define plugin directories
define( 'TEST_ECFP_DIR', dirname( __DIR__, 2 ) );
define( 'TEST_EC_DIR', dirname( TEST_ECFP_DIR, 1 ) . '/easycommerce' );

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once TEST_ECFP_DIR . '/vendor/autoload.php';

// Define WordPress test environment path
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: getenv( 'WP_PHPUNIT__DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

/**
 * Truncate EasyCommerce FakerPress tables for clean test runs
 */
function ecfp_truncate_table_data(): void {
	$tables = array(
		'ecfp_generated_data',
		// Add other tables as needed
	);

	global $wpdb;
	foreach ( $tables as $table_name ) {
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . $table_name ) );
		if ( $table_exists ) {
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}{$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugins being tested
 */
function _manually_load_plugin() {
	// Load EasyCommerce if the directory exists
	if ( file_exists( TEST_EC_DIR . '/easycommerce.php' ) ) {
		require TEST_EC_DIR . '/easycommerce.php';
	}

	// Load our plugin
	require TEST_ECFP_DIR . '/easycommerce-fakerpress.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Install EasyCommerce for testing
 */
function install_easycommerce() {
	// Skip if EasyCommerce doesn't exist
	if ( ! file_exists( TEST_EC_DIR . '/easycommerce.php' ) ) {
		echo 'Warning: EasyCommerce plugin not found. Some tests may fail.' . PHP_EOL;
		return;
	}

	echo 'Installing EasyCommerce...' . PHP_EOL;

	// Install EasyCommerce if it has an installation method
	if ( function_exists( 'easycommerce_install' ) ) {
		easycommerce_install();
	}

	// Reload capabilities after install
	if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
		$GLOBALS['wp_roles']->reinit();
	} else {
		$GLOBALS['wp_roles'] = null;
		wp_roles();
	}
}

/**
 * Install EasyCommerce FakerPress for testing
 */
function install_ecfp() {
	echo 'Installing EasyCommerce FakerPress...' . PHP_EOL;

	// Clean up existing tables
	ecfp_truncate_table_data();

	// Activate the plugin
	if ( function_exists( 'easycommerce_fakerpress' ) ) {
		easycommerce_fakerpress()->activate();
	}
}

// Install dependencies and our plugin
tests_add_filter( 'setup_theme', 'install_easycommerce' );
tests_add_filter( 'setup_theme', 'install_ecfp' );

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';