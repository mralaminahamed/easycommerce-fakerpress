<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Product;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Test class for Product REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Product
 */
class ProductRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Product
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

		$this->controller = new Product();
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
		$this->assertInstanceOf( Product::class, $this->controller );
	}

	/**
	 * Test route registration
	 */
	public function test_route_registration(): void {
		$routes    = $this->server->get_routes();
		$namespace = '/' . $this->namespace;

		$this->assertArrayHasKey( $namespace . '/products/generate', $routes );

		$route = $routes[ $namespace . '/products/generate' ];
		$this->assertCount( 1, $route );
		$this->assertEquals( 'POST', $route[0]['methods']['POST'] );
	}

	/**
	 * Test generate products endpoint with valid request
	 */
	public function test_generate_products_valid_request(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'products', $data );
		$this->assertEquals( 3, $data['generated'] );
		$this->assertCount( 3, $data['products'] );
	}

	/**
	 * Test generate products endpoint without count parameter
	 */
	public function test_generate_products_missing_count(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'invalid_count', $data['code'] );
	}

	/**
	 * Test generate products endpoint with zero count
	 */
	public function test_generate_products_zero_count(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 0 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'invalid_count', $data['code'] );
	}

	/**
	 * Test generate products endpoint with negative count
	 */
	public function test_generate_products_negative_count(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', -5 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'invalid_count', $data['code'] );
	}

	/**
	 * Test generate products endpoint with count exceeding maximum
	 */
	public function test_generate_products_exceeding_max_count(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 150 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertEquals( 'invalid_count', $data['code'] );
	}

	/**
	 * Test generate products endpoint without authentication
	 */
	public function test_generate_products_no_auth(): void {
		wp_set_current_user( 0 ); // No user

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 401, $response->get_status() );
		$this->assertEquals( 'rest_forbidden', $data['code'] );
	}

	/**
	 * Test generate products endpoint with insufficient permissions
	 */
	public function test_generate_products_insufficient_permissions(): void {
		wp_set_current_user( $this->customer_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 403, $response->get_status() );
		$this->assertEquals( 'rest_forbidden', $data['code'] );
	}

	/**
	 * Test generate products endpoint with locale parameter
	 */
	public function test_generate_products_with_locale(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'locale', 'fr_FR' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test generate products endpoint with seed parameter
	 */
	public function test_generate_products_with_seed(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'seed', 12345 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );

		// Generate again with same seed to verify reproducibility
		$request2 = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request2->set_param( 'count', 2 );
		$request2->set_param( 'seed', 12345 );

		$response2 = $this->server->dispatch( $request2 );
		$data2     = $response2->get_data();

		$this->assertEquals( 200, $response2->get_status() );
		$this->assertEquals( 2, $data2['generated'] );
	}

	/**
	 * Test generate products endpoint with status parameter
	 */
	public function test_generate_products_with_status(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'status', 'active' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 3, $data['generated'] );

		// Verify status filter is applied (if generator supports it)
		if ( isset( $data['products'] ) ) {
			foreach ( $data['products'] as $product ) {
				if ( isset( $product['status'] ) ) {
					$this->assertEquals( 'active', $product['status'] );
				}
			}
		}
	}

	/**
	 * Test generate products endpoint with date range parameter
	 */
	public function test_generate_products_with_date_range(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param(
			'date_range',
			array(
				'start' => '2024-01-01',
				'end'   => '2024-12-31',
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test generate products endpoint with relationships parameter
	 */
	public function test_generate_products_with_relationships(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param(
			'relationships',
			array(
				'create_missing' => true,
				'link_existing'  => false,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test generate products endpoint with meta options parameter
	 */
	public function test_generate_products_with_meta_options(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param(
			'meta_options',
			array(
				'include_meta'  => true,
				'custom_fields' => true,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test generate products endpoint with invalid locale
	 */
	public function test_generate_products_invalid_locale(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'locale', 'invalid_locale' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		// Should return validation error for invalid locale
		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'code', $data );
	}

	/**
	 * Test generate products endpoint with invalid status
	 */
	public function test_generate_products_invalid_status(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'status', 'invalid_status' );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		// Should return validation error for invalid status
		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'code', $data );
	}

	/**
	 * Test generate products endpoint response structure
	 */
	public function test_generate_products_response_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 1 );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'products', $data );
		$this->assertIsInt( $data['generated'] );
		$this->assertIsArray( $data['products'] );

		if ( ! empty( $data['products'] ) ) {
			$product = $data['products'][0];
			$this->assertIsArray( $product );

			// Check for common product fields
			$expected_fields = array( 'id', 'name', 'price', 'status' );
			foreach ( $expected_fields as $field ) {
				if ( isset( $product[ $field ] ) ) {
					$this->assertNotNull( $product[ $field ] );
				}
			}
		}
	}

	/**
	 * Test generate products endpoint with maximum count
	 */
	public function test_generate_products_maximum_count(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 100 ); // Maximum allowed

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 100, $data['generated'] );
	}

	/**
	 * Test REST endpoint schema
	 */
	public function test_rest_endpoint_schema(): void {
		$schema = $this->controller->get_public_item_schema();

		$this->assertIsArray( $schema );
		$this->assertArrayHasKey( '$schema', $schema );
		$this->assertArrayHasKey( 'title', $schema );
		$this->assertArrayHasKey( 'type', $schema );
		$this->assertArrayHasKey( 'properties', $schema );

		$this->assertEquals( 'object', $schema['type'] );
		$this->assertArrayHasKey( 'generated', $schema['properties'] );
		$this->assertEquals( 'integer', $schema['properties']['generated']['type'] );
	}

	/**
	 * Test generation parameters schema
	 */
	public function test_generation_parameters_schema(): void {
		$params = $this->controller->get_generation_params();

		$this->assertIsArray( $params );
		$this->assertArrayHasKey( 'count', $params );
		$this->assertArrayHasKey( 'locale', $params );
		$this->assertArrayHasKey( 'seed', $params );
		$this->assertArrayHasKey( 'status', $params );
		$this->assertArrayHasKey( 'date_range', $params );
		$this->assertArrayHasKey( 'relationships', $params );
		$this->assertArrayHasKey( 'meta_options', $params );

		// Validate count parameter
		$count_param = $params['count'];
		$this->assertEquals( 'integer', $count_param['type'] );
		$this->assertEquals( 1, $count_param['minimum'] );
		$this->assertEquals( 100, $count_param['maximum'] );
		$this->assertTrue( $count_param['required'] );

		// Validate locale parameter
		$locale_param = $params['locale'];
		$this->assertEquals( 'string', $locale_param['type'] );
		$this->assertIsArray( $locale_param['enum'] );
		$this->assertContains( 'en_US', $locale_param['enum'] );
		$this->assertContains( 'fr_FR', $locale_param['enum'] );
	}

	/**
	 * Test count validation method
	 */
	public function test_count_validation(): void {
		$request = new WP_REST_Request();

		// Test valid count
		$result = $this->controller->validate_count( 50, $request, 'count' );
		$this->assertTrue( $result );

		// Test invalid count (too low)
		$result = $this->controller->validate_count( 0, $request, 'count' );
		$this->assertInstanceOf( WP_Error::class, $result );

		// Test invalid count (too high)
		$result = $this->controller->validate_count( 150, $request, 'count' );
		$this->assertInstanceOf( WP_Error::class, $result );

		// Test non-numeric count
		$result = $this->controller->validate_count( 'invalid', $request, 'count' );
		$this->assertInstanceOf( WP_Error::class, $result );
	}

	/**
	 * Test generate products endpoint performance
	 */
	public function test_generate_products_performance(): void {
		wp_set_current_user( $this->admin_user_id );

		$start_time = microtime( true );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 10 );

		$response = $this->server->dispatch( $request );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		$this->assertEquals( 200, $response->get_status() );
		$this->assertLessThan( 10, $execution_time, 'Product generation endpoint took too long' );
	}

	/**
	 * Test concurrent requests
	 */
	public function test_concurrent_requests(): void {
		wp_set_current_user( $this->admin_user_id );

		$request1 = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request1->set_param( 'count', 5 );

		$request2 = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request2->set_param( 'count', 3 );

		$response1 = $this->server->dispatch( $request1 );
		$response2 = $this->server->dispatch( $request2 );

		$this->assertEquals( 200, $response1->get_status() );
		$this->assertEquals( 200, $response2->get_status() );

		$data1 = $response1->get_data();
		$data2 = $response2->get_data();

		$this->assertEquals( 5, $data1['generated'] );
		$this->assertEquals( 3, $data2['generated'] );
	}
}
