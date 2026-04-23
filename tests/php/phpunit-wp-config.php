<?php
/**
 * WordPress test configuration for EasyCommerce FakerPress.
 *
 * All values are driven by environment variables set in phpunit.xml.dist.
 * Override any variable in your shell or CI environment to customise the run.
 *
 * WARNING: the WordPress test suite DROPS AND RECREATES all tables that share
 * WP_TABLE_PREFIX. Never point this at a production database.
 */

/* Path to the WordPress installation used for testing. */
define( 'ABSPATH', rtrim( getenv( 'WP_PATH' ) ?: '/home/alamin/Sites/easycommerce-development', '/\\' ) . DIRECTORY_SEPARATOR );

/* Active theme — kept as 'default' for headless CLI tests. */
define( 'WP_DEFAULT_THEME', 'default' );

/* Debug settings — enable all error output during tests. */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Database credentials — sourced from phpunit.xml.dist env vars. */
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wp_phpunit_tests' );
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASS' ) ?: 'password' );
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

/* Authentication keys and salts — deterministic values safe for testing only. */
define( 'AUTH_KEY',         'ecfp-test-auth-key-not-for-production-use' );
define( 'SECURE_AUTH_KEY',  'ecfp-test-secure-auth-key-not-for-production-use' );
define( 'LOGGED_IN_KEY',    'ecfp-test-logged-in-key-not-for-production-use' );
define( 'NONCE_KEY',        'ecfp-test-nonce-key-not-for-production-use' );
define( 'AUTH_SALT',        'ecfp-test-auth-salt-not-for-production-use' );
define( 'SECURE_AUTH_SALT', 'ecfp-test-secure-auth-salt-not-for-production-use' );
define( 'LOGGED_IN_SALT',   'ecfp-test-logged-in-salt-not-for-production-use' );
define( 'NONCE_SALT',       'ecfp-test-nonce-salt-not-for-production-use' );

/*
 * Table prefix for the test installation.
 * Must differ from both the production prefix and EasyCommerce's own prefix
 * to avoid any risk of data loss during test setup/teardown.
 */
$table_prefix = getenv( 'WP_TABLE_PREFIX' ) ?: 'easycommerce_fakerpress_test_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

/* Test site identity. */
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'EasyCommerce FakerPress Test Blog' );

define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
