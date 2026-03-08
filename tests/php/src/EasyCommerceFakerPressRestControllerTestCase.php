<?php
/**
 * Base test case for EasyCommerce FakerPress REST Controller tests.
 *
 * @package EasyCommerceFakerPress\Tests
 */

namespace EasyCommerceFakerPress\Tests;

use WP_Test_REST_Controller_Testcase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Abstract test case for REST Controller tests
 *
 * Provides utility methods for testing EasyCommerce FakerPress REST API controllers
 *
 * @since 1.0.0
 */
abstract class EasyCommerceFakerPressRestControllerTestCase extends WP_Test_REST_Controller_Testcase {

	/**
	 * The namespace of the REST API.
	 *
	 * @var string
	 */
	protected string $namespace = 'easycommerce-fakerpress/v1';

	/**
	 * The rest base of the controller.
	 *
	 * @var string
	 */
	protected string $rest_base;

	/**
	 * Create an admin user for testing.
	 *
	 * @return int User ID.
	 */
	protected function create_admin_user(): int {
		return $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
	}

	/**
	 * Create a customer user for testing.
	 *
	 * @return int User ID.
	 */
	protected function create_customer_user(): int {
		return $this->factory->user->create(
			array(
				'role' => 'customer',
			)
		);
	}

	/**
	 * Set the current user to admin.
	 *
	 * @return int User ID.
	 */
	protected function set_current_user_to_admin(): int {
		$user_id = $this->create_admin_user();
		wp_set_current_user( $user_id );
		return $user_id;
	}

	/**
	 * Set the current user to customer.
	 *
	 * @return int User ID.
	 */
	protected function set_current_user_to_customer(): int {
		$user_id = $this->create_customer_user();
		wp_set_current_user( $user_id );
		return $user_id;
	}

	/**
	 * Create a REST request.
	 *
	 * @param string $method HTTP method.
	 * @param string $route  REST route (without namespace).
	 * @param array  $params Request parameters.
	 * @return WP_REST_Request
	 */
	protected function create_request( string $method, string $route, array $params = array() ): WP_REST_Request {
		$request = new WP_REST_Request( strtoupper( $method ), '/' . $this->namespace . $route );
		$request->set_query_params( $params );
		return $request;
	}

	/**
	 * Get the full route URL.
	 *
	 * @param string $route Route path.
	 * @return string Full route URL.
	 */
	protected function get_route_url( string $route ): string {
		return '/' . $this->namespace . $route;
	}

	/**
	 * Perform a REST API request.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	protected function server_callback( WP_REST_Request $request ) {
		return rest_get_server()->dispatch( $request );
	}

	/**
	 * Assert that a REST response is valid.
	 *
	 * @param WP_REST_Response $response The response to check.
	 */
	protected function assertResponseSuccess( $response ): void {
		$this->assertInstanceOf( 'WP_REST_Response', $response );
		$this->assertArrayHasKey( 'data', $response->get_data() );
	}

	/**
	 * Clean up test data.
	 */
	protected function cleanup_test_data(): void {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		// Delete test posts.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->posts} WHERE post_title LIKE %s",
				'%test_%'
			)
		);

		// Delete test users.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->users} WHERE user_login LIKE %s",
				'%test_%'
			)
		);

		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
	}
}
