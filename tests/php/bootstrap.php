<?php
/**
 * PHPUnit bootstrap file for EasyCommerce FakerPress.
 *
 * DB credentials and paths are read from environment variables defined in
 * phpunit.xml.dist so there are no hard-coded values in this file.
 */

/* Plugin root and sibling EasyCommerce path. */
define( 'TEST_EASYCOMMERCE_FAKERPRESS_DIR', dirname( __DIR__, 2 ) );
define( 'TEST_EC_DIR', dirname( __DIR__, 3 ) . '/easycommerce' );

/* Composer autoloaders. */
require_once TEST_EASYCOMMERCE_FAKERPRESS_DIR . '/vendor/autoload.php';

if ( file_exists( TEST_EC_DIR . '/vendor/autoload.php' ) ) {
	require_once TEST_EC_DIR . '/vendor/autoload.php';
}

/* Locate the WP PHPUnit test library (set by wp-phpunit's __loaded.php via autoload). */
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: getenv( 'WP_PHPUNIT__DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php — run bin/install-wp-tests.sh first." . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

/**
 * Truncate plugin-specific test tables via WP-CLI so there is no direct DB
 * dependency in PHP bootstrap code.
 */
function easycommerce_fakerpress_truncate_table_data(): void {
	$wp_path  = getenv( 'WP_PATH' ) ?: dirname( TEST_EASYCOMMERCE_FAKERPRESS_DIR, 3 );
	$db_name  = getenv( 'WP_DB_NAME' ) ?: 'wp_phpunit_tests';
	$db_user  = getenv( 'WP_DB_USER' ) ?: 'root';
	$db_pass  = getenv( 'WP_DB_PASS' ) ?: 'password';
	$db_host  = getenv( 'WP_DB_HOST' ) ?: 'localhost';
	$prefix   = getenv( 'WP_TABLE_PREFIX' ) ?: 'easycommerce_fakerpress_test_';

	$tables = array(
		'easycommerce_fakerpress_generated_data',
	);

	foreach ( $tables as $table_name ) {
		$sql = sprintf( 'TRUNCATE TABLE IF EXISTS `%s%s`', $prefix, $table_name );

		exec(
			sprintf(
				'wp --path=%s --dbname=%s --dbuser=%s --dbpass=%s --dbhost=%s --allow-root db query %s 2>/dev/null',
				escapeshellarg( $wp_path ),
				escapeshellarg( $db_name ),
				escapeshellarg( $db_user ),
				escapeshellarg( $db_pass ),
				escapeshellarg( $db_host ),
				escapeshellarg( $sql )
			)
		);
	}
}

/* Give access to tests_add_filter(). */
require_once $_tests_dir . '/includes/functions.php';

/**
 * Load the plugins under test.
 */
function _manually_load_plugin(): void {
	require TEST_EC_DIR . '/easycommerce.php';
	require TEST_EASYCOMMERCE_FAKERPRESS_DIR . '/easycommerce-fakerpress.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Run EasyCommerce's installer so its tables and roles exist.
 */
function install_easycommerce(): void {
	if ( ! file_exists( TEST_EC_DIR . '/easycommerce.php' ) ) {
		echo 'Warning: EasyCommerce plugin not found — some tests may fail.' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	echo 'Installing EasyCommerce...' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	\EasyCommerce\easycommerce_install();

	if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
		$GLOBALS['wp_roles']->reinit();
	} else {
		$GLOBALS['wp_roles'] = null;
		wp_roles();
	}
}

/**
 * Activate EasyCommerce FakerPress and clean plugin tables.
 */
function install_easycommerce_fakerpress(): void {
	echo 'Installing EasyCommerce FakerPress...' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	easycommerce_fakerpress_truncate_table_data();

	if ( function_exists( 'easycommerce_fakerpress' ) ) {
		easycommerce_fakerpress()->activate_plugin();
	}
}

tests_add_filter( 'setup_theme', 'install_easycommerce' );
tests_add_filter( 'setup_theme', 'install_easycommerce_fakerpress' );

/* Boot the WP testing environment. */
require $_tests_dir . '/includes/bootstrap.php';
