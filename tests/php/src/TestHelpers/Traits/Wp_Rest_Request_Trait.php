<?php

namespace EasyCommerceFakerPress\Tests\TestHelpers\Traits;

use WP_REST_Request;

/**
 * Helper trait for WordPress REST API requests in tests
 *
 * @since 1.0.0
 */
trait Wp_Rest_Request_Trait {

	/**
	 * Create a REST request with authentication
	 *
	 * @param string $method HTTP method
	 * @param string $route Route path
	 * @param array  $params Request parameters
	 * @param int    $user_id User ID for authentication
	 * @return WP_REST_Request
	 */
	protected function create_authenticated_request( string $method, string $route, array $params = array(), int $user_id = 0 ): WP_REST_Request {
		$request = new WP_REST_Request( strtoupper( $method ), $route );

		// Set parameters based on method
		if ( in_array( strtoupper( $method ), array( 'POST', 'PUT', 'PATCH' ), true ) ) {
			$request->set_body_params( $params );
		} else {
			$request->set_query_params( $params );
		}

		// Set authentication if user ID provided
		if ( $user_id > 0 ) {
			wp_set_current_user( $user_id );
		}

		return $request;
	}

	/**
	 * Create a REST request with admin authentication
	 *
	 * @param string $method HTTP method
	 * @param string $route Route path
	 * @param array  $params Request parameters
	 * @return WP_REST_Request
	 */
	protected function create_admin_request( string $method, string $route, array $params = array() ): WP_REST_Request {
		$admin_user = $this->factory->user->create( array( 'role' => 'administrator' ) );
		return $this->create_authenticated_request( $method, $route, $params, $admin_user );
	}

	/**
	 * Create a REST request with customer authentication
	 *
	 * @param string $method HTTP method
	 * @param string $route Route path
	 * @param array  $params Request parameters
	 * @return WP_REST_Request
	 */
	protected function create_customer_request( string $method, string $route, array $params = array() ): WP_REST_Request {
		$customer_user = $this->factory->user->create( array( 'role' => 'customer' ) );
		return $this->create_authenticated_request( $method, $route, $params, $customer_user );
	}

	/**
	 * Create a REST request without authentication
	 *
	 * @param string $method HTTP method
	 * @param string $route Route path
	 * @param array  $params Request parameters
	 * @return WP_REST_Request
	 */
	protected function create_unauthenticated_request( string $method, string $route, array $params = array() ): WP_REST_Request {
		return $this->create_authenticated_request( $method, $route, $params );
	}

	/**
	 * Assert REST response has expected status code
	 *
	 * @param int               $expected_status Expected status code
	 * @param \WP_REST_Response $response REST response
	 * @param string            $message Optional assertion message
	 */
	protected function assertResponseStatus( int $expected_status, $response, string $message = '' ): void {
		$actual_status = $response->get_status();
		$this->assertEquals(
			$expected_status,
			$actual_status,
			$message ?: "Expected status {$expected_status}, got {$actual_status}. Response: " . wp_json_encode( $response->get_data() )
		);
	}

	/**
	 * Assert REST response has expected data structure
	 *
	 * @param array             $expected_keys Expected keys in response data
	 * @param \WP_REST_Response $response REST response
	 */
	protected function assertResponseHasKeys( array $expected_keys, $response ): void {
		$data = $response->get_data();
		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $data, "Response missing expected key: {$key}" );
		}
	}

	/**
	 * Assert REST response contains error
	 *
	 * @param \WP_REST_Response $response REST response
	 * @param string            $error_code Expected error code
	 */
	protected function assertResponseError( $response, string $error_code = '' ): void {
		$this->assertTrue( $response->is_error(), 'Response should be an error' );

		if ( $error_code ) {
			$data = $response->get_data();
			$this->assertEquals( $error_code, $data['code'] ?? '', "Expected error code {$error_code}" );
		}
	}
}
