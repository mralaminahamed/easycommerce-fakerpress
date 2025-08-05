<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Customer_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Test class for Customer REST Controller
 *
 * @covers \EasyCommerceFakerPress\Controllers\Customer_REST_Controller
 */
class CustomerRESTControllerTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Customer_REST_Controller
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
		if ( ! class_exists( 'EasyCommerce\Models\Customer' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->controller = new Customer_REST_Controller();
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
		$this->assertInstanceOf( Customer_REST_Controller::class, $this->controller );
	}

	/**
	 * Test route registration
	 */
	public function test_route_registration(): void {
		$routes = $this->server->get_routes();
		$namespace = '/' . $this->namespace;

		$this->assertArrayHasKey( $namespace . '/customers/generate', $routes );

		$route = $routes[ $namespace . '/customers/generate' ];
		$this->assertCount( 1, $route );
		$this->assertEquals( 'POST', $route[0]['methods']['POST'] );
	}

	/**
	 * Test generate customers endpoint with valid request
	 */
	public function test_generate_customers_valid_request(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 5 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'customers', $data );
		$this->assertEquals( 5, $data['generated'] );
		$this->assertCount( 5, $data['customers'] );
	}

	/**
	 * Test generate customers endpoint without authentication
	 */
	public function test_generate_customers_no_auth(): void {
		wp_set_current_user( 0 ); // No user

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 401, $response->get_status() );
		$this->assertEquals( 'rest_forbidden', $data['code'] );
	}

	/**
	 * Test generate customers endpoint with insufficient permissions
	 */
	public function test_generate_customers_insufficient_permissions(): void {
		wp_set_current_user( $this->customer_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 3 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 403, $response->get_status() );
		$this->assertEquals( 'rest_forbidden', $data['code'] );
	}

	/**
	 * Test generate customers endpoint response structure
	 */
	public function test_generate_customers_response_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 1 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'customers', $data );

		if ( ! empty( $data['customers'] ) ) {
			$customer = $data['customers'][0];
			$this->assertIsArray( $customer );

			// Check for common customer fields
			$expected_fields = array( 'id', 'first_name', 'last_name', 'email', 'status' );
			foreach ( $expected_fields as $field ) {
				if ( isset( $customer[ $field ] ) ) {
					$this->assertNotNull( $customer[ $field ] );
				}
			}

			// Validate email format if present
			if ( isset( $customer['email'] ) ) {
				$this->assertFilter( $customer['email'], FILTER_VALIDATE_EMAIL );
			}
		}
	}

	/**
	 * Test generate customers endpoint with loyalty tier parameter
	 */
	public function test_generate_customers_with_loyalty_tier(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'loyalty_tier', 'gold' );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 3, $data['generated'] );

		// Verify loyalty tier filter is applied (if generator supports it)
		if ( isset( $data['customers'] ) ) {
			foreach ( $data['customers'] as $customer ) {
				if ( isset( $customer['loyalty_tier'] ) ) {
					$this->assertEquals( 'gold', $customer['loyalty_tier'] );
				}
			}
		}
	}

	/**
	 * Test generate customers endpoint with demographics parameter
	 */
	public function test_generate_customers_with_demographics(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'demographics', array(
			'age_group'    => '25-34',
			'income_level' => 'high'
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test generate customers endpoint with address options
	 */
	public function test_generate_customers_with_address_options(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'address_options', array(
			'include_billing'  => true,
			'include_shipping' => true,
			'different_shipping' => true
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );

		// Check if addresses are included
		if ( isset( $data['customers'] ) && ! empty( $data['customers'] ) ) {
			$customer = $data['customers'][0];
			if ( isset( $customer['billing_address'] ) ) {
				$this->assertIsArray( $customer['billing_address'] );
				$this->assertArrayHasKey( 'street', $customer['billing_address'] );
				$this->assertArrayHasKey( 'city', $customer['billing_address'] );
				$this->assertArrayHasKey( 'country', $customer['billing_address'] );
			}

			if ( isset( $customer['shipping_address'] ) ) {
				$this->assertIsArray( $customer['shipping_address'] );
				$this->assertArrayHasKey( 'street', $customer['shipping_address'] );
				$this->assertArrayHasKey( 'city', $customer['shipping_address'] );
				$this->assertArrayHasKey( 'country', $customer['shipping_address'] );
			}
		}
	}

	/**
	 * Test generate customers endpoint with preferences parameter
	 */
	public function test_generate_customers_with_preferences(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'preferences', array(
			'marketing_emails' => true,
			'newsletter' => false,
			'preferred_currency' => 'USD'
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test generate customers endpoint with order history parameter
	 */
	public function test_generate_customers_with_order_history(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'order_history', array(
			'generate_orders' => true,
			'min_orders' => 1,
			'max_orders' => 5
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test generate customers endpoint with invalid loyalty tier
	 */
	public function test_generate_customers_invalid_loyalty_tier(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 2 );
		$request->set_param( 'loyalty_tier', 'invalid_tier' );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		// Should return validation error or ignore invalid tier
		$this->assertTrue(
			$response->get_status() === 400 || $response->get_status() === 200,
			'Should handle invalid loyalty tier gracefully'
		);
	}

	/**
	 * Test generate customers endpoint with comprehensive parameters
	 */
	public function test_generate_customers_comprehensive_parameters(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'locale', 'en_US' );
		$request->set_param( 'seed', 98765 );
		$request->set_param( 'status', 'active' );
		$request->set_param( 'loyalty_tier', 'silver' );
		$request->set_param( 'demographics', array(
			'age_group' => '35-44',
			'income_level' => 'medium'
		) );
		$request->set_param( 'address_options', array(
			'include_billing' => true,
			'include_shipping' => true
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 3, $data['generated'] );
		$this->assertCount( 3, $data['customers'] );
	}

	/**
	 * Test generate customers endpoint performance
	 */
	public function test_generate_customers_performance(): void {
		wp_set_current_user( $this->admin_user_id );

		$start_time = microtime( true );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 15 );

		$response = $this->server->dispatch( $request );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		$this->assertEquals( 200, $response->get_status() );
		$this->assertLessThan( 8, $execution_time, 'Customer generation endpoint took too long' );
	}

	/**
	 * Test customer data validation
	 */
	public function test_customer_data_validation(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 5 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['customers'] ) ) {
			foreach ( $data['customers'] as $customer ) {
				// Validate customer data structure
				if ( isset( $customer['email'] ) ) {
					$this->assertFilter( $customer['email'], FILTER_VALIDATE_EMAIL, 'Invalid email format' );
				}

				if ( isset( $customer['phone'] ) ) {
					$this->assertIsString( $customer['phone'] );
					$this->assertNotEmpty( $customer['phone'] );
				}

				if ( isset( $customer['status'] ) ) {
					$valid_statuses = array( 'active', 'inactive', 'suspended' );
					$this->assertContains( $customer['status'], $valid_statuses );
				}

				if ( isset( $customer['loyalty_tier'] ) ) {
					$valid_tiers = array( 'bronze', 'silver', 'gold', 'platinum' );
					$this->assertContains( $customer['loyalty_tier'], $valid_tiers );
				}

				if ( isset( $customer['total_spent'] ) ) {
					$this->assertIsFloat( $customer['total_spent'] );
					$this->assertGreaterThanOrEqual( 0, $customer['total_spent'] );
				}

				if ( isset( $customer['order_count'] ) ) {
					$this->assertIsInt( $customer['order_count'] );
					$this->assertGreaterThanOrEqual( 0, $customer['order_count'] );
				}
			}
		}
	}

	/**
	 * Test customer uniqueness
	 */
	public function test_customer_uniqueness(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 10 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['customers'] ) ) {
			$emails = array();
			foreach ( $data['customers'] as $customer ) {
				if ( isset( $customer['email'] ) ) {
					$emails[] = $customer['email'];
				}
			}

			if ( ! empty( $emails ) ) {
				$unique_emails = array_unique( $emails );
				$this->assertCount(
					count( $emails ),
					$unique_emails,
					'All customer emails should be unique'
				);
			}
		}
	}

	/**
	 * Test customer address structure
	 */
	public function test_customer_address_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 3 );
		$request->set_param( 'address_options', array(
			'include_billing' => true,
			'include_shipping' => true
		) );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['customers'] ) ) {
			foreach ( $data['customers'] as $customer ) {
				if ( isset( $customer['billing_address'] ) ) {
					$billing = $customer['billing_address'];
					$required_fields = array( 'street', 'city', 'country' );

					foreach ( $required_fields as $field ) {
						$this->assertArrayHasKey( $field, $billing );
						$this->assertIsString( $billing[ $field ] );
						$this->assertNotEmpty( $billing[ $field ] );
					}

					// Optional fields
					$optional_fields = array( 'state', 'postal_code', 'first_name', 'last_name' );
					foreach ( $optional_fields as $field ) {
						if ( isset( $billing[ $field ] ) ) {
							$this->assertIsString( $billing[ $field ] );
						}
					}
				}

				if ( isset( $customer['shipping_address'] ) ) {
					$shipping = $customer['shipping_address'];
					$required_fields = array( 'street', 'city', 'country' );

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
	 * Test customer demographics structure
	 */
	public function test_customer_demographics_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 2 );

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );

		if ( isset( $data['customers'] ) ) {
			foreach ( $data['customers'] as $customer ) {
				if ( isset( $customer['demographics'] ) ) {
					$demographics = $customer['demographics'];

					if ( isset( $demographics['age_group'] ) ) {
						$valid_age_groups = array( '18-24', '25-34', '35-44', '45-54', '55-64', '65+' );
						$this->assertContains( $demographics['age_group'], $valid_age_groups );
					}

					if ( isset( $demographics['income_level'] ) ) {
						$valid_income_levels = array( 'low', 'medium', 'high', 'premium' );
						$this->assertContains( $demographics['income_level'], $valid_income_levels );
					}

					if ( isset( $demographics['interests'] ) ) {
						$this->assertIsArray( $demographics['interests'] );
					}
				}
			}
		}
	}

	/**
	 * Test maximum customers generation
	 */
	public function test_generate_maximum_customers(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 100 ); // Maximum allowed

		$response = $this->server->dispatch( $request );
		$data = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertEquals( 100, $data['generated'] );
		$this->assertCount( 100, $data['customers'] );
	}
}
