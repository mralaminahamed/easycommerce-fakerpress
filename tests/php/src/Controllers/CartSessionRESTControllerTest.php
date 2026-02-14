<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Cart_Session;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Test class for Cart Session REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Cart_Session
 */
class CartSessionRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Cart_Session
	 */
	private $controller;

	/**
	 * @var int
	 */
	private $admin_user_id;

	/**
	 * @var int
	 */
	private $customer_user_id;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Product' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->controller = new Cart_Session();
		$this->controller->register_routes();

		$this->admin_user_id    = $this->create_admin_user();
		$this->customer_user_id = $this->create_customer_user();
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->cleanup_test_data();
	}

	/**
	 * Test controller instantiation
	 */
	public function test_controller_instantiation(): void {
		$this->assertInstanceOf( Cart_Session::class, $this->controller );
	}

	/**
	 * Test route registration
	 */
	public function test_route_registration(): void {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( '/easycommerce-fakerpress/v1/cart-sessions/generate', $routes );
	}

	/**
	 * Test successful cart session generation
	 */
	public function test_successful_cart_session_generation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', 2 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'cart_sessions', $data );
		$this->assertEquals( 2, $data['generated'] );
		$this->assertCount( 2, $data['cart_sessions'] );
	}

	/**
	 * Test cart session generation with invalid count
	 */
	public function test_cart_session_generation_invalid_count(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', -1 );

		$response = $this->server->dispatch( $request );

		$this->assertGreaterThanOrEqual( 400, $response->get_status() );
	}

	/**
	 * Test cart session generation without authentication
	 */
	public function test_cart_session_generation_no_auth(): void {
		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', 1 );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 401, $response->get_status() );
	}

	/**
	 * Test cart session generation with insufficient permissions
	 */
	public function test_cart_session_generation_insufficient_permissions(): void {
		wp_set_current_user( $this->customer_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', 1 );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status() );
	}

	/**
	 * Test cart session generation with valid parameters
	 */
	public function test_cart_session_generation_with_parameters(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', 1 );
		$request->set_param( 'customer_type', 'mixed' );
		$request->set_param( 'abandonment_rate', 25 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertEquals( 1, $data['generated'] );
	}

	/**
	 * Test parameter validation
	 */
	public function test_parameter_validation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', 1 );
		$request->set_param( 'customer_type', 'invalid_type' );

		$response = $this->server->dispatch( $request );

		$this->assertGreaterThanOrEqual( 400, $response->get_status() );
	}

	/**
	 * Test response structure
	 */
	public function test_response_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', 1 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'success', $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'cart_sessions', $data );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertTrue( $data['success'] );
	}
}
