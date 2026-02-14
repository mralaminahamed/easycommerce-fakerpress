<?php

namespace EasyCommerceFakerPress\Tests\Controllers;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Controllers\Product;
use EasyCommerceFakerPress\Controllers\Order;
use EasyCommerceFakerPress\Controllers\Customer;
use EasyCommerceFakerPress\Controllers\Coupon;
use EasyCommerceFakerPress\Controllers\Cart_Session;
use EasyCommerceFakerPress\Controllers\Transaction;
use EasyCommerceFakerPress\Controllers\Location;
use EasyCommerceFakerPress\Controllers\Shipping_Plan;
use EasyCommerceFakerPress\Controllers\Tax_Class;
use EasyCommerceFakerPress\Controllers\Product_Variation;
use WP_REST_Request;

/**
 * API Integration Test for Parameter Schema Alignment
 *
 * Tests that all 11 generators have properly aligned frontend and backend parameter schemas
 * after the parameter schema alignment work was completed.
 *
 * @covers \EasyCommerceFakerPress\Controllers\Product
 * @covers \EasyCommerceFakerPress\Controllers\Order
 * @covers \EasyCommerceFakerPress\Controllers\Customer
 * @covers \EasyCommerceFakerPress\Controllers\Coupon
 * @covers \EasyCommerceFakerPress\Controllers\Cart_Session
 * @covers \EasyCommerceFakerPress\Controllers\Transaction
 * @covers \EasyCommerceFakerPress\Controllers\Location
 * @covers \EasyCommerceFakerPress\Controllers\Shipping_Plan
 * @covers \EasyCommerceFakerPress\Controllers\Tax_Class
 * @covers \EasyCommerceFakerPress\Controllers\Product_Variation
 */
class ParameterSchemaAlignmentTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * Controllers to test
	 */
	private array $controllers = array();

	/**
	 * Admin user ID for testing
	 */
	private int $admin_user_id;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Product' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		// Initialize all controllers
		$this->controllers = array(
			'products'           => new Product(),
			'orders'             => new Order(),
			'customers'          => new Customer(),
			'coupons'            => new Coupon(),
			'cart_sessions'      => new Cart_Session(),
			'transactions'       => new Transaction(),
			'locations'          => new Location(),
			'shipping_plans'     => new Shipping_Plan(),
			'tax_classes'        => new Tax_Class(),
			'product_variations' => new Product_Variation(),
		);

		// Register routes for all controllers
		foreach ( $this->controllers as $controller ) {
			$controller->register_routes();
		}

		$this->admin_user_id = $this->create_admin_user();
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->cleanup_test_data();
	}

	/**
	 * Test Products generator with aligned parameters
	 */
	public function test_products_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/products/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned product_type parameter
		$request->set_param( 'product_type', 'simple' );

		// Test aligned price_range parameter
		$request->set_param(
			'price_range',
			array(
				'min' => 10,
				'max' => 100,
			)
		);

		// Test aligned categories parameter
		$request->set_param(
			'categories',
			array(
				'create_new'      => true,
				'max_per_product' => 2,
			)
		);

		// Test aligned attributes parameter
		$request->set_param(
			'attributes',
			array(
				'include_attributes' => true,
				'variation_count'    => 3,
			)
		);

		// Test aligned inventory parameter
		$request->set_param(
			'inventory',
			array(
				'manage_stock' => true,
				'stock_range'  => array(
					'min' => 0,
					'max' => 50,
				),
			)
		);

		// Test aligned content_options parameter
		$request->set_param(
			'content_options',
			array(
				'include_images'     => false,
				'description_length' => 'medium',
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'products', $data );
		$this->assertEquals( 2, $data['generated'] );
		$this->assertCount( 2, $data['products'] );
	}

	/**
	 * Test Orders generator with aligned parameters
	 */
	public function test_orders_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/orders/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned order_status parameter
		$request->set_param( 'order_status', 'completed' );

		// Test aligned customer_type parameter
		$request->set_param( 'customer_type', 'existing' );

		// Test aligned customer_distribution parameter
		$request->set_param(
			'customer_distribution',
			array(
				'existing_ratio' => 70,
				'new_ratio'      => 30,
			)
		);

		// Test aligned order_value parameter
		$request->set_param(
			'order_value',
			array(
				'min_total' => 25,
				'max_total' => 200,
			)
		);

		// Test aligned items_per_order parameter
		$request->set_param(
			'items_per_order',
			array(
				'min' => 1,
				'max' => 5,
			)
		);

		// Test aligned payment_methods parameter
		$request->set_param( 'payment_methods', array( 'stripe', 'paypal' ) );

		// Test aligned geographical_distribution parameter
		$request->set_param(
			'geographical_distribution',
			array(
				'countries' => array( 'US', 'CA' ),
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'orders', $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test Customers generator with aligned parameters
	 */
	public function test_customers_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/customers/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned customer_types parameter (array)
		$request->set_param( 'customer_types', array( 'regular', 'vip' ) );

		// Test aligned demographics parameter
		$request->set_param(
			'demographics',
			array(
				'age_groups'          => array( '26-35', '36-45' ),
				'gender_distribution' => array(
					'male'   => 45,
					'female' => 45,
					'other'  => 10,
				),
			)
		);

		// Test aligned address_preferences parameter
		$request->set_param(
			'address_preferences',
			array(
				'include_billing'           => true,
				'include_shipping'          => true,
				'different_addresses_ratio' => 25,
			)
		);

		// Test aligned purchase_history parameter
		$request->set_param(
			'purchase_history',
			array(
				'simulate_history' => true,
				'loyalty_tiers'    => true,
			)
		);

		// Test aligned contact_preferences parameter
		$request->set_param(
			'contact_preferences',
			array(
				'phone_numbers'          => true,
				'marketing_opt_in_ratio' => 70,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'customers', $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test Coupons generator with aligned parameters
	 */
	public function test_coupons_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/coupons/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned discount_types parameter (array)
		$request->set_param( 'discount_types', array( 'percentage', 'fixed_amount' ) );

		// Test aligned discount_range parameter
		$request->set_param(
			'discount_range',
			array(
				'min_percentage' => 10,
				'max_percentage' => 30,
				'min_fixed'      => 5,
				'max_fixed'      => 50,
			)
		);

		// Test aligned usage_limits parameter
		$request->set_param(
			'usage_limits',
			array(
				'set_usage_limits'  => true,
				'max_uses'          => 50,
				'max_uses_per_user' => 1,
			)
		);

		// Test aligned validity_period parameter
		$request->set_param(
			'validity_period',
			array(
				'min_days' => 7,
				'max_days' => 60,
			)
		);

		// Test aligned restrictions parameter
		$request->set_param(
			'restrictions',
			array(
				'minimum_spend'        => true,
				'maximum_spend'        => false,
				'exclude_sale_items'   => false,
				'product_restrictions' => true,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'coupons', $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test Cart Sessions generator with aligned parameters
	 */
	public function test_cart_sessions_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/cart-sessions/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned customer_type parameter
		$request->set_param( 'customer_type', 'mixed' );

		// Test aligned guest_cart_ratio parameter
		$request->set_param( 'guest_cart_ratio', 35 );

		// Test aligned abandonment_rate parameter
		$request->set_param( 'abandonment_rate', 25 );

		// Test aligned status_distribution parameter
		$request->set_param(
			'status_distribution',
			array(
				'pending'   => 20,
				'abandoned' => 60,
				'completed' => 20,
			)
		);

		// Test aligned cart_value_range parameter
		$request->set_param(
			'cart_value_range',
			array(
				'min' => 10,
				'max' => 150,
			)
		);

		// Test aligned items_per_cart parameter
		$request->set_param(
			'items_per_cart',
			array(
				'min' => 1,
				'max' => 4,
			)
		);

		// Test aligned abandonment_tracking parameter
		$request->set_param(
			'abandonment_tracking',
			array(
				'generate_reminders' => true,
				'reminder_count'     => 2,
				'recovery_rate'      => 20,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'cart_sessions', $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test Transactions generator with aligned parameters
	 */
	public function test_transactions_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/transactions/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned customer_type parameter
		$request->set_param( 'customer_type', 'all' );

		// Test aligned order_status_filter parameter
		$request->set_param( 'order_status_filter', array( 'completed', 'processing' ) );

		// Test aligned transaction_types parameter
		$request->set_param( 'transaction_types', array( 'payment', 'refund' ) );

		// Test aligned payment_gateways parameter
		$request->set_param( 'payment_gateways', array( 'stripe', 'paypal' ) );

		// Test aligned date_range parameter
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
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'transactions', $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test Locations generator with aligned parameters
	 */
	public function test_locations_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/locations/generate' );
		$request->set_param( 'count', 5 );

		// Test aligned regions parameter
		$request->set_param( 'regions', array( 'Europe', 'Americas' ) );

		// Test aligned max_countries parameter
		$request->set_param( 'max_countries', 8 );

		// Test aligned include_states parameter
		$request->set_param( 'include_states', true );

		// Test aligned include_cities parameter
		$request->set_param( 'include_cities', true );

		// Test aligned cities_per_state parameter
		$request->set_param(
			'cities_per_state',
			array(
				'min' => 2,
				'max' => 10,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'locations', $data );
	}

	/**
	 * Test Shipping Plans generator with aligned parameters
	 */
	public function test_shipping_plans_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/shipping-plans/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned shipping_types parameter (array)
		$request->set_param( 'shipping_types', array( 'standard', 'express', 'free' ) );

		// Test aligned cost_range parameter
		$request->set_param(
			'cost_range',
			array(
				'min' => 0,
				'max' => 25,
			)
		);

		// Test aligned coverage_areas parameter
		$request->set_param( 'coverage_areas', array( 'domestic', 'international' ) );

		// Test aligned calculation_methods parameter
		$request->set_param( 'calculation_methods', array( 'flat_rate', 'weight_based' ) );

		// Test aligned delivery_timeframes parameter
		$request->set_param(
			'delivery_timeframes',
			array(
				'min_days' => 1,
				'max_days' => 7,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'shipping_plans', $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test Tax Classes generator with aligned parameters
	 */
	public function test_tax_classes_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/tax_classes/generate' );
		$request->set_param( 'count', 2 );

		// Test aligned tax_types parameter (array)
		$request->set_param( 'tax_types', array( 'standard', 'reduced', 'zero' ) );

		// Test aligned jurisdictions parameter
		$request->set_param( 'jurisdictions', array( 'country', 'state' ) );

		// Test aligned rate_ranges parameter
		$request->set_param(
			'rate_ranges',
			array(
				'standard' => array(
					'min' => 5,
					'max' => 20,
				),
				'reduced'  => array(
					'min' => 1,
					'max' => 8,
				),
			)
		);

		// Test aligned location_coverage parameter
		$request->set_param(
			'location_coverage',
			array(
				'countries'        => array( 'US', 'CA', 'GB' ),
				'include_compound' => true,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'tax_classes', $data );
		$this->assertEquals( 2, $data['generated'] );
	}

	/**
	 * Test Product Variations generator with aligned parameters
	 */
	public function test_product_variations_generator_parameter_alignment(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = $this->get_wp_rest_request( 'POST', '/product-variations/generate' );
		$request->set_param( 'count', 3 );

		// Test aligned product_types parameter
		$request->set_param( 'product_types', array( 'variable', 'simple' ) );

		// Test aligned price_variance parameter
		$request->set_param(
			'price_variance',
			array(
				'min_percentage' => -15,
				'max_percentage' => 25,
			)
		);

		// Test aligned stock_settings parameter
		$request->set_param(
			'stock_settings',
			array(
				'manage_stock' => true,
				'stock_range'  => array(
					'min' => 0,
					'max' => 30,
				),
			)
		);

		// Test aligned variation_attributes parameter
		$request->set_param(
			'variation_attributes',
			array(
				'create_missing_attributes'    => true,
				'max_attributes_per_variation' => 2,
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'product_variations', $data );
		$this->assertEquals( 3, $data['generated'] );
	}

	/**
	 * Test parameter validation for all generators
	 */
	public function test_parameter_validation_across_generators(): void {
		wp_set_current_user( $this->admin_user_id );

		$test_cases = array(
			array(
				'endpoint'       => '/products/generate',
				'invalid_param'  => array( 'product_type' => 'invalid_type' ),
				'expected_error' => 'invalid_product_type',
			),
			array(
				'endpoint'       => '/orders/generate',
				'invalid_param'  => array( 'order_status' => 'invalid_status' ),
				'expected_error' => 'invalid_order_status',
			),
			array(
				'endpoint'       => '/customers/generate',
				'invalid_param'  => array( 'customer_types' => 'not_an_array' ),
				'expected_error' => 'invalid_customer_types',
			),
			array(
				'endpoint'       => '/coupons/generate',
				'invalid_param'  => array( 'discount_types' => 'not_an_array' ),
				'expected_error' => 'invalid_discount_types',
			),
			array(
				'endpoint'       => '/cart-sessions/generate',
				'invalid_param'  => array( 'customer_type' => 'invalid_type' ),
				'expected_error' => 'invalid_customer_type',
			),
			array(
				'endpoint'       => '/transactions/generate',
				'invalid_param'  => array( 'transaction_types' => 'not_an_array' ),
				'expected_error' => 'invalid_transaction_types',
			),
			array(
				'endpoint'       => '/locations/generate',
				'invalid_param'  => array( 'max_countries' => 200 ), // Exceeds maximum
				'expected_error' => 'invalid_max_countries',
			),
			array(
				'endpoint'       => '/shipping-plans/generate',
				'invalid_param'  => array( 'shipping_types' => 'not_an_array' ),
				'expected_error' => 'invalid_shipping_types',
			),
			array(
				'endpoint'       => '/tax_classes/generate',
				'invalid_param'  => array( 'tax_types' => 'not_an_array' ),
				'expected_error' => 'invalid_tax_types',
			),
			array(
				'endpoint'       => '/product-variations/generate',
				'invalid_param'  => array( 'price_variance' => array( 'min_percentage' => -200 ) ), // Below minimum
				'expected_error' => 'invalid_price_variance',
			),
		);

		foreach ( $test_cases as $test_case ) {
			$request = $this->get_wp_rest_request( 'POST', $test_case['endpoint'] );
			$request->set_param( 'count', 1 );

			foreach ( $test_case['invalid_param'] as $param => $value ) {
				$request->set_param( $param, $value );
			}

			$response = $this->server->dispatch( $request );
			$data     = $response->get_data();

			// Should return validation error
			$this->assertGreaterThanOrEqual(
				400,
				$response->get_status(),
				"Expected validation error for {$test_case['endpoint']} with invalid {$param}"
			);
		}
	}

	/**
	 * Test that all generators respond with proper structure
	 */
	public function test_all_generators_response_structure(): void {
		wp_set_current_user( $this->admin_user_id );

		$endpoints = array(
			'/products/generate'           => 'products',
			'/orders/generate'             => 'orders',
			'/customers/generate'          => 'customers',
			'/coupons/generate'            => 'coupons',
			'/cart-sessions/generate'      => 'cart_sessions',
			'/transactions/generate'       => 'transactions',
			'/locations/generate'          => 'locations',
			'/shipping-plans/generate'     => 'shipping_plans',
			'/tax_classes/generate'        => 'tax_classes',
			'/product-variations/generate' => 'product_variations',
		);

		foreach ( $endpoints as $endpoint => $data_key ) {
			$request = $this->get_wp_rest_request( 'POST', $endpoint );
			$request->set_param( 'count', 1 );

			$response = $this->server->dispatch( $request );
			$data     = $response->get_data();

			$this->assertEquals(
				200,
				$response->get_status(),
				"Failed for endpoint: {$endpoint}"
			);
			$this->assertIsArray( $data, "Response data should be array for {$endpoint}" );
			$this->assertArrayHasKey(
				'generated',
				$data,
				"Response should have 'generated' key for {$endpoint}"
			);
			$this->assertArrayHasKey(
				$data_key,
				$data,
				"Response should have '{$data_key}' key for {$endpoint}"
			);
			$this->assertEquals(
				1,
				$data['generated'],
				"Should generate 1 item for {$endpoint}"
			);
		}
	}

	/**
	 * Test parameter schema consistency across all controllers
	 */
	public function test_parameter_schema_consistency(): void {
		foreach ( $this->controllers as $name => $controller ) {
			$params = $controller->get_generation_params();

			$this->assertIsArray( $params, "Controller {$name} should return array of parameters" );
			$this->assertArrayHasKey( 'count', $params, "Controller {$name} should have count parameter" );

			// Validate count parameter structure
			$count_param = $params['count'];
			$this->assertArrayHasKey( 'type', $count_param, "Count parameter should have type in {$name}" );
			$this->assertEquals( 'integer', $count_param['type'], "Count should be integer type in {$name}" );
			$this->assertArrayHasKey( 'minimum', $count_param, "Count should have minimum in {$name}" );
			$this->assertArrayHasKey( 'maximum', $count_param, "Count should have maximum in {$name}" );
			$this->assertTrue( $count_param['required'], "Count should be required in {$name}" );
		}
	}
}
