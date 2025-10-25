<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Customer;
use EasyCommerce\Models\Customer;

/**
 * Test class for Customer Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Customer
 */
class CustomerGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Customer
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Customer' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new Customer();
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		parent::tearDown();
		$this->cleanup_test_data();
	}

	/**
	 * Test generator instantiation
	 */
	public function test_generator_instantiation(): void {
		$this->assertInstanceOf( Customer::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new \ReflectionClass( $this->generator );
		$method = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'customer', $method->invoke( $this->generator ) );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		$count = 3;
		$result = $this->generator->generate( $count );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'generated', $result );
		$this->assertArrayHasKey( 'customers', $result );
		$this->assertEquals( $count, $result['generated'] );
		$this->assertCount( $count, $result['customers'] );
	}

	/**
	 * Test generate method with zero count
	 */
	public function test_generate_with_zero_count(): void {
		$result = $this->generator->generate( 0 );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'invalid_count', $result->get_error_code() );
	}

	/**
	 * Test generate method with negative count
	 */
	public function test_generate_with_negative_count(): void {
		$result = $this->generator->generate( -1 );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'invalid_count', $result->get_error_code() );
	}

	/**
	 * Test generate method with count exceeding batch size
	 */
	public function test_generate_with_large_count(): void {
		$result = $this->generator->generate( 150 );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertEquals( 'count_too_large', $result->get_error_code() );
	}

	/**
	 * Test that generated customers have required fields
	 */
	public function test_generated_customers_have_required_fields(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'customers', $result );
		$this->assertCount( 1, $result['customers'] );

		$customer = $result['customers'][0];

		// Check required customer fields
		$this->assertArrayHasKey( 'id', $customer );
		$this->assertArrayHasKey( 'first_name', $customer );
		$this->assertArrayHasKey( 'last_name', $customer );
		$this->assertArrayHasKey( 'email', $customer );
		$this->assertArrayHasKey( 'phone', $customer );
		$this->assertArrayHasKey( 'status', $customer );
		$this->assertArrayHasKey( 'registration_date', $customer );

		// Validate data types
		$this->assertIsInt( $customer['id'] );
		$this->assertIsString( $customer['first_name'] );
		$this->assertIsString( $customer['last_name'] );
		$this->assertIsString( $customer['email'] );
		$this->assertIsString( $customer['status'] );

		// Validate email format
		$this->assertFilter( $customer['email'], FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Test customer addresses generation
	 */
	public function test_customer_addresses_generation(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$customer = $result['customers'][0];

		// Check billing address
		$this->assertArrayHasKey( 'billing_address', $customer );
		$billing = $customer['billing_address'];

		$this->assertArrayHasKey( 'street', $billing );
		$this->assertArrayHasKey( 'city', $billing );
		$this->assertArrayHasKey( 'state', $billing );
		$this->assertArrayHasKey( 'postal_code', $billing );
		$this->assertArrayHasKey( 'country', $billing );

		// Check shipping address
		$this->assertArrayHasKey( 'shipping_address', $customer );
		$shipping = $customer['shipping_address'];

		$this->assertArrayHasKey( 'street', $shipping );
		$this->assertArrayHasKey( 'city', $shipping );
		$this->assertArrayHasKey( 'state', $shipping );
		$this->assertArrayHasKey( 'postal_code', $shipping );
		$this->assertArrayHasKey( 'country', $shipping );
	}

	/**
	 * Test customer loyalty tier assignment
	 */
	public function test_customer_loyalty_tier(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$customer = $result['customers'][0];

		$this->assertArrayHasKey( 'loyalty_tier', $customer );
		$this->assertArrayHasKey( 'total_spent', $customer );
		$this->assertArrayHasKey( 'order_count', $customer );

		$valid_tiers = array( 'bronze', 'silver', 'gold', 'platinum' );
		$this->assertContains( $customer['loyalty_tier'], $valid_tiers );

		$this->assertIsFloat( $customer['total_spent'] );
		$this->assertGreaterThanOrEqual( 0, $customer['total_spent'] );

		$this->assertIsInt( $customer['order_count'] );
		$this->assertGreaterThanOrEqual( 0, $customer['order_count'] );
	}

	/**
	 * Test customer status values
	 */
	public function test_customer_status_values(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_statuses = array( 'active', 'inactive', 'suspended' );

		foreach ( $result['customers'] as $customer ) {
			$this->assertContains( $customer['status'], $valid_statuses );
		}
	}

	/**
	 * Test customer demographic data
	 */
	public function test_customer_demographics(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$customer = $result['customers'][0];

		// Check demographic fields
		$this->assertArrayHasKey( 'demographics', $customer );
		$demographics = $customer['demographics'];

		$this->assertArrayHasKey( 'age_group', $demographics );
		$this->assertArrayHasKey( 'income_level', $demographics );
		$this->assertArrayHasKey( 'interests', $demographics );

		$valid_age_groups = array( '18-24', '25-34', '35-44', '45-54', '55-64', '65+' );
		$this->assertContains( $demographics['age_group'], $valid_age_groups );

		$valid_income_levels = array( 'low', 'medium', 'high', 'premium' );
		$this->assertContains( $demographics['income_level'], $valid_income_levels );

		$this->assertIsArray( $demographics['interests'] );
		$this->assertGreaterThan( 0, count( $demographics['interests'] ) );
	}

	/**
	 * Test generated customers are unique
	 */
	public function test_generated_customers_are_unique(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );
		$emails = array_column( $result['customers'], 'email' );
		$unique_emails = array_unique( $emails );

		// All customer emails should be unique
		$this->assertCount( count( $emails ), $unique_emails, 'Generated customers should have unique emails' );
	}

	/**
	 * Test customer generation performance
	 */
	public function test_customer_generation_performance(): void {
		$start_time = microtime( true );
		$result = $this->generator->generate( 10 );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (3 seconds)
		$this->assertLessThan( 3, $execution_time, 'Customer generation took too long' );

		$this->assertIsArray( $result );
		$this->assertEquals( 10, $result['generated'] );
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		$memory_before = memory_get_usage();
		$result = $this->generator->generate( 20 );
		$memory_after = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 5MB for 20 customers)
		$this->assertLessThan( 5 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		$this->assertIsArray( $result );
		$this->assertEquals( 20, $result['generated'] );
	}

	/**
	 * Test customer preferences generation
	 */
	public function test_customer_preferences(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$customer = $result['customers'][0];

		$this->assertArrayHasKey( 'preferences', $customer );
		$preferences = $customer['preferences'];

		$this->assertArrayHasKey( 'marketing_emails', $preferences );
		$this->assertArrayHasKey( 'sms_notifications', $preferences );
		$this->assertArrayHasKey( 'newsletter', $preferences );
		$this->assertArrayHasKey( 'preferred_currency', $preferences );
		$this->assertArrayHasKey( 'preferred_language', $preferences );

		$this->assertIsBool( $preferences['marketing_emails'] );
		$this->assertIsBool( $preferences['sms_notifications'] );
		$this->assertIsBool( $preferences['newsletter'] );
		$this->assertIsString( $preferences['preferred_currency'] );
		$this->assertIsString( $preferences['preferred_language'] );
	}

	/**
	 * Test generate_multiple method
	 */
	public function test_generate_multiple_method(): void {
		$count = 5;
		$result = $this->generator->generate_multiple( $count );

		$this->assertIsArray( $result );
		$this->assertCount( $count, $result );

		foreach ( $result as $customer ) {
			$this->assertIsArray( $customer );
			$this->assertArrayHasKey( 'id', $customer );
			$this->assertArrayHasKey( 'email', $customer );
		}
	}
}
