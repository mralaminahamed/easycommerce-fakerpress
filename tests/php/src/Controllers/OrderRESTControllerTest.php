<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Order_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Test class for Order REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Order_REST_Controller
 */
class OrderRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Order_REST_Controller
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
		if ( ! class_exists( 'EasyCommerce\Models\Order' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->controller = new Order_REST_Controller();
		$this->controller->register_routes();

		$this->admin_user_id = $this->create_admin_user();
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
		$this->assertInstanceOf( Order_REST_Controller::class, $this->controller );
	}

	/**
	 * Test route registration
	 */
	public function test_route_registration(): void {
		$routes = $this->server->get_routes();
		$namespace = '/' . $this->namespace;

		$this->assertArrayHasKey( $namespace . '/orders/generate', $routes );

		$route = $routes[ $namespace . '/orders/generate' ];
		$this->assertCount( 1, $route );
		$this->assertEquals( 'POST', $route[0]['methods']['POST'] );
	}

	/**
	 * Test generate orders endpoint with valid request
	 */
	public function test_generate_orders_valid_request(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 4 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'orders', $data );
		$this->assertEquals( 4, $data['generated'] );
		$this->assertCount( 4, $data['orders'] );
	}

	/**
	 * Test generate orders endpoint without authentication
	 */
	public function test_generate_orders_no_auth(): void {
		wp_set_current_user( 0 ); // No user

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 401, $response->get_status() );
		$this->assertEquals( 'rest_forbidden', $data['code'] );
	}

	/**
	 * Test generate orders endpoint with insufficient permissions
	 */
	public function test_generate_orders_insufficient_permissions(): void {
		wp_set_current_user( $this->customer_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 403, $response->get_status() );
		$this->assertEquals( 'rest_forbidden', $data['code'] );
	}

	/**
	 * Test generate orders endpoint response structure
	 */
	public function test_generate_orders_response_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 1 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'orders', $data );

		if ( ! empty( $data['orders'] ) ) {
			$order = $data['orders'][0];
			$this->assertIsArray( $order );

			// Check for common order fields
			$expected_fields = array( 'id', 'order_number', 'customer_id', 'status', 'total', 'currency' );
			foreach ( $expected_fields as $field ) {
				if ( isset( $order[ $field ] ) ) {
					$this->assertNotNull( $order[ $field ] );
				}
			}

			// Validate numeric fields
			if ( isset( $order['total'] ) ) {
				$this->assertIsFloat( $order['total'] );
				$this->assertGreaterThan( 0, $order['total'] );
			}

			if ( isset( $order['customer_id'] ) ) {
				$this->assertIsInt( $order['customer_id'] );
				$this->assertGreaterThan( 0, $order['customer_id'] );
			}
		}
	}

	/**
	 * Test generate orders endpoint with order status parameter
	 */
	public function test_generate_orders_with_status(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'order_status', 'completed' );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 3, $data['generated'] );

		// Verify status filter is applied (if generator supports it)
		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				if ( isset( $order['status'] ) ) {
					$this->assertEquals( 'completed', $order['status'] );
				}
			}
		}
	}

	/**
	 * Test generate orders endpoint with payment method parameter
	 */
	public function test_generate_orders_with_payment_method(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'payment_method', 'stripe' );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );

		// Verify payment method filter is applied (if generator supports it)
		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				if ( isset( $order['payment_method'] ) ) {
					$this->assertEquals( 'stripe', $order['payment_method'] );
				}
			}
		}
	}

	/**
	 * Test generate orders endpoint with customer assignment
	 */
	public function test_generate_orders_with_customer_assignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'customer_assignment', array(
			'link_existing' => true,
			'create_missing' => true
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 3, $data['generated'] );
	}

	/**
	 * Test generate orders endpoint with order value range
	 */
	public function test_generate_orders_with_value_range(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'order_value', array(
			'min_total' => 50.00,
			'max_total' => 500.00
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 3, $data['generated'] );

		// Verify order totals are within range (if generator supports it)
		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				if ( isset( $order['total'] ) ) {
					$this->assertGreaterThanOrEqual( 50.00, $order['total'] );
					$this->assertLessThanOrEqual( 500.00, $order['total'] );
				}
			}
		}
	}

	/**
	 * Test generate orders endpoint with shipping options
	 */
	public function test_generate_orders_with_shipping_options(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'shipping_options', array(
			'include_shipping' => true,
			'different_billing' => true
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );

		// Check shipping address structure
		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				if ( isset( $order['shipping_address'] ) ) {
					$shipping = $order['shipping_address'];
					$this->assertIsArray( $shipping );
					$this->assertArrayHasKey( 'street', $shipping );
					$this->assertArrayHasKey( 'city', $shipping );
					$this->assertArrayHasKey( 'country', $shipping );
				}
			}
		}
	}

	/**
	 * Test generate orders endpoint with tax options
	 */
	public function test_generate_orders_with_tax_options(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'tax_options', array(
			'include_tax' => true,
			'tax_rate' => 8.5
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test order data validation
	 */
	public function test_order_data_validation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 5 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				// Validate order status
				if ( isset( $order['status'] ) ) {
					$valid_statuses = array( 'pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'on-hold' );
					$this->assertContains( $order['status'], $valid_statuses );
				}

				// Validate payment method
				if ( isset( $order['payment_method'] ) ) {
					$valid_payment_methods = array( 'credit_card', 'paypal', 'stripe', 'bank_transfer', 'cash_on_delivery', 'apple_pay', 'google_pay' );
					$this->assertContains( $order['payment_method'], $valid_payment_methods );
				}

				// Validate currency
				if ( isset( $order['currency'] ) ) {
					$valid_currencies = array( 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'BRL' );
					$this->assertContains( $order['currency'], $valid_currencies );
				}

				// Validate numeric fields
				$numeric_fields = array( 'total', 'subtotal', 'tax_amount', 'shipping_amount', 'discount_amount' );
				foreach ( $numeric_fields as $field ) {
					if ( isset( $order[ $field ] ) ) {
						$this->assertIsFloat( $order[ $field ] );
						$this->assertGreaterThanOrEqual( 0, $order[ $field ] );
					}
				}

				// Validate order number format
				if ( isset( $order['order_number'] ) ) {
					$this->assertIsString( $order['order_number'] );
					$this->assertNotEmpty( $order['order_number'] );
					$this->assertMatchesRegularExpression( '/^[A-Z0-9-]+$/', $order['order_number'] );
				}
			}
		}
	}

	/**
	 * Test order items structure
	 */
	public function test_order_items_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 2 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				if ( isset( $order['items'] ) ) {
					$this->assertIsArray( $order['items'] );
					$this->assertGreaterThan( 0, count( $order['items'] ) );

					foreach ( $order['items'] as $item ) {
						$this->assertIsArray( $item );
						$this->assertArrayHasKey( 'product_id', $item );
						$this->assertArrayHasKey( 'quantity', $item );
						$this->assertArrayHasKey( 'price', $item );
						$this->assertArrayHasKey( 'total', $item );

						$this->assertIsInt( $item['product_id'] );
						$this->assertIsInt( $item['quantity'] );
						$this->assertIsFloat( $item['price'] );
						$this->assertIsFloat( $item['total'] );

						$this->assertGreaterThan( 0, $item['product_id'] );
						$this->assertGreaterThan( 0, $item['quantity'] );
						$this->assertGreaterThan( 0, $item['price'] );
						$this->assertGreaterThan( 0, $item['total'] );
					}
				}
			}
		}
	}

	/**
	 * Test order total calculation consistency
	 */
	public function test_order_total_calculation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				if ( isset( $order['subtotal'], $order['tax_amount'], $order['shipping_amount'], $order['discount_amount'], $order['total'] ) ) {
					$calculated_total = $order['subtotal'] + $order['tax_amount'] + $order['shipping_amount'] - $order['discount_amount'];

					// Allow for small floating point differences
					$this->assertEqualsWithDelta(
						$calculated_total,
						$order['total'],
						0.01,
						'Order total should equal subtotal + tax + shipping - discount'
					);
				}
			}
		}
	}

	/**
	 * Test order number uniqueness
	 */
	public function test_order_number_uniqueness(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 10 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['orders'] ) ) {
			$order_numbers = array();
			foreach ( $data['orders'] as $order ) {
				if ( isset( $order['order_number'] ) ) {
					$order_numbers[] = $order['order_number'];
				}
			}

			if ( ! empty( $order_numbers ) ) {
				$unique_numbers = array_unique( $order_numbers );
				$this->assertCount(
					count( $order_numbers ),
					$unique_numbers,
					'All order numbers should be unique'
				);
			}
		}
	}

	/**
	 * Test generate orders endpoint performance
	 */
	public function test_generate_orders_performance(): void {
		wp_set_current_user( $this->admin_user_id );

		$start_time = microtime( true );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 10 );

		$response = $this->server->dispatch( $request );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		$this->assertEquals( 200, $response->get_status() );
		$this->assertLessThan( 15, $execution_time, 'Order generation endpoint took too long' );
	}

	/**
	 * Test comprehensive order generation parameters
	 */
	public function test_comprehensive_order_parameters(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'locale', 'en_US' );
		$request->set_param( 'seed', 54321 );
		$request->set_param( 'order_status', 'processing' );
		$request->set_param( 'payment_method', 'credit_card' );
		$request->set_param( 'order_value', array(
			'min_total' => 25.00,
			'max_total' => 200.00
		) );
		$request->set_param( 'customer_assignment', array(
			'link_existing' => true
		) );
		$request->set_param( 'shipping_options', array(
			'include_shipping' => true
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 3, $data['generated'] );
		$this->assertCount( 3, $data['orders'] );
	}

	/**
	 * Test order address validation
	 */
	public function test_order_address_validation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 2 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['orders'] ) ) {
			foreach ( $data['orders'] as $order ) {
				// Validate billing address
				if ( isset( $order['billing_address'] ) ) {
					$billing = $order['billing_address'];
					$this->assertIsArray( $billing );

					$required_fields = array( 'first_name', 'last_name', 'email', 'street', 'city', 'country' );
					foreach ( $required_fields as $field ) {
						$this->assertArrayHasKey( $field, $billing );
						$this->assertIsString( $billing[ $field ] );
						$this->assertNotEmpty( $billing[ $field ] );
					}

					// Validate email format
					$this->assertFilter( $billing['email'], FILTER_VALIDATE_EMAIL );
				}

				// Validate shipping address
				if ( isset( $order['shipping_address'] ) ) {
					$shipping = $order['shipping_address'];
					$this->assertIsArray( $shipping );

					$required_fields = array( 'first_name', 'last_name', 'street', 'city', 'country' );
					foreach ( $required_fields as $field ) {
						$this->assertArrayHasKey( $field, $shipping );
						$this->assertIsString( $shipping[ $field ] );
						$this->assertNotEmpty( $shipping[ $field ] );
					}
				}
			}
		}
	}

	/**
	 * Test maximum orders generation
	 */
	public function test_generate_maximum_orders(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 100 ); // Maximum allowed

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 100, $data['generated'] );
		$this->assertCount( 100, $data['orders'] );
	}
}
