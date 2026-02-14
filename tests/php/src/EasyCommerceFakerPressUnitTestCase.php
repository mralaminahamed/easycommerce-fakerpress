<?php
/**
 * Base test case for all EasyCommerce FakerPress tests.
 *
 * @package EasyCommerceFakerPress\Tests
 */

namespace EasyCommerceFakerPress\Tests;

use Brain\Monkey;
use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

/**
 * Base test case for all EasyCommerce FakerPress tests
 *
 * Provides utility methods and helpers for testing EasyCommerce FakerPress components
 *
 * @since 1.0.0
 */
abstract class EasyCommerceFakerPressUnitTestCase extends WP_UnitTestCase {
	use TestHelpers\Traits\Wp_Rest_Request_Trait;

	/**
	 * Rest API server instance.
	 *
	 * @var WP_REST_Server The Rest server instance.
	 */
	protected WP_REST_Server $server;

	/**
	 * The namespace of the Rest route.
	 *
	 * @var string EasyCommerce FakerPress API Namespace
	 */
	protected string $namespace = 'easycommerce-fakerpress/v1';

	/**
	 * Indicates whether the feature is enabled only for unit testing purposes.
	 *
	 * @var bool
	 */
	protected bool $is_unit_test = false;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// There is no need of Rest and DB for Unit test.
		if ( $this->is_unit_test ) {
			return;
		}

		// Initiating the Rest API.
		global $wp_rest_server;

		$wp_rest_server = new WP_REST_Server();
		$this->server   = $wp_rest_server;

		/**
		 * Fires when preparing to serve a Rest API request.
		 *
		 * Endpoint objects should be created and register their hooks on this action rather
		 * than another action to ensure they're only loaded when needed.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Server $wp_rest_server Server object.
		 */
		do_action( 'rest_api_init', $wp_rest_server );
	}

	/**
	 * Tear down the test case.
	 *
	 * @see https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-fixtures
	 *
	 * @return void
	 */
	public function tear_down() {
		Monkey\tearDown();

		parent::tear_down();
	}

	/**
	 * Create a WP_REST_Request object
	 *
	 * @param string $method HTTP method.
	 * @param string $route REST route.
	 * @return WP_REST_Request
	 */
	protected function get_wp_rest_request( string $method, string $route ): WP_REST_Request {
		return new WP_REST_Request( strtoupper( $method ), $this->get_route( $route ) );
	}

	/**
	 * Get the full route with namespace
	 *
	 * @param string $route The route path.
	 * @return string Full route
	 */
	protected function get_route( string $route ): string {
		return '/' . $this->namespace . $route;
	}

	/**
	 * Create a test user with admin capabilities
	 *
	 * @return int User ID
	 */
	protected function create_admin_user(): int {
		return $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
	}

	/**
	 * Create a test user with customer capabilities
	 *
	 * @return int User ID
	 */
	protected function create_customer_user(): int {
		return $this->factory->user->create(
			array(
				'role' => 'customer',
			)
		);
	}

	/**
	 * Assert that an action hook was fired
	 *
	 * @param string $action_name Action name.
	 */
	protected function assertActionFired( string $action_name ): void {
		$this->assertTrue( did_action( $action_name ) > 0, "Action '{$action_name}' was not fired." );
	}

	/**
	 * Assert that a filter hook was applied
	 *
	 * @param string $filter_name Filter name.
	 */
	protected function assertFilterApplied( string $filter_name ): void {
		$this->assertTrue( has_filter( $filter_name ) !== false, "Filter '{$filter_name}' was not applied." );
	}

	/**
	 * Spy on an action hook
	 *
	 * @param string $action_name Action name.
	 * @return callable Spy function
	 */
	protected function spy_on_action( string $action_name ): callable {
		$spy = function () use ( $action_name ) {
			// Action spy - just track that it was called.
		};

		add_action( $action_name, $spy );
		return $spy;
	}

	/**
	 * Spy on a filter hook
	 *
	 * @param string $filter_name Filter name.
	 * @return callable Spy function
	 */
	protected function spy_on_filter( string $filter_name ): callable {
		$spy = function ( $value ) {
			return $value; // Filter spy - pass through value.
		};

		add_filter( $filter_name, $spy );
		return $spy;
	}

	/**
	 * Clean up generated test data
	 */
	protected function cleanup_test_data(): void {
		// Clean up any test data created during tests.
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

		// Delete test posts.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_title LIKE %s", '%test_%' ) );

		// Delete test users (except admin).
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->users} WHERE user_login LIKE %s", '%test_%' ) );

		// Clean up meta tables.
		$post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts}" );
		$user_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->users}" );

		if ( ! empty( $post_ids ) ) {
			$post_ids_placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN ($post_ids_placeholders)", $post_ids ) );
		} else {
			$wpdb->query( "DELETE FROM {$wpdb->postmeta}" );
		}

		if ( ! empty( $user_ids ) ) {
			$user_ids_placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE user_id NOT IN ($user_ids_placeholders)", $user_ids ) );
		} else {
			$wpdb->query( "DELETE FROM {$wpdb->usermeta}" );
		}

		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	}
}
