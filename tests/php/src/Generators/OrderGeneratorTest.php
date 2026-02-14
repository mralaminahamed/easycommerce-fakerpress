<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerce\Models\Order;
use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Order as OrderGenerator;

/**
 * Test class for Order Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Order
 */
class OrderGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var OrderGenerator
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( Order::class ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new OrderGenerator();
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
		$this->assertInstanceOf( OrderGenerator::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new \ReflectionClass( $this->generator );
		$method     = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'order', $method->invoke( $this->generator ) );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		$count  = 3;
		$result = $this->generator->generate( $count );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'generated', $result );
		$this->assertArrayHasKey( 'orders', $result );
		$this->assertEquals( $count, $result['generated'] );
		$this->assertCount( $count, $result['orders'] );
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
	 * Test that generated orders have required fields
	 */
	public function test_generated_orders_have_required_fields(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'orders', $result );
		$this->assertCount( 1, $result['orders'] );

		$order = $result['orders'][0];

		// Check required order fields
		$this->assertArrayHasKey( 'id', $order );
		$this->assertArrayHasKey( 'order_number', $order );
		$this->assertArrayHasKey( 'customer_id', $order );
		$this->assertArrayHasKey( 'status', $order );
		$this->assertArrayHasKey( 'total', $order );
		$this->assertArrayHasKey( 'subtotal', $order );
		$this->assertArrayHasKey( 'tax_amount', $order );
		$this->assertArrayHasKey( 'shipping_amount', $order );
		$this->assertArrayHasKey( 'discount_amount', $order );
		$this->assertArrayHasKey( 'currency', $order );
		$this->assertArrayHasKey( 'payment_method', $order );
		$this->assertArrayHasKey( 'order_date', $order );

		// Validate data types
		$this->assertIsInt( $order['id'] );
		$this->assertIsString( $order['order_number'] );
		$this->assertIsInt( $order['customer_id'] );
		$this->assertIsString( $order['status'] );
		$this->assertIsFloat( $order['total'] );
		$this->assertIsFloat( $order['subtotal'] );
		$this->assertIsFloat( $order['tax_amount'] );
		$this->assertIsFloat( $order['shipping_amount'] );
		$this->assertIsFloat( $order['discount_amount'] );
		$this->assertIsString( $order['currency'] );
		$this->assertIsString( $order['payment_method'] );

		// Validate numeric values
		$this->assertGreaterThan( 0, $order['total'] );
		$this->assertGreaterThanOrEqual( 0, $order['subtotal'] );
		$this->assertGreaterThanOrEqual( 0, $order['tax_amount'] );
		$this->assertGreaterThanOrEqual( 0, $order['shipping_amount'] );
		$this->assertGreaterThanOrEqual( 0, $order['discount_amount'] );
	}

	/**
	 * Test order status values
	 */
	public function test_order_status_values(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_statuses = array( 'pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'on-hold' );

		foreach ( $result['orders'] as $order ) {
			$this->assertContains( $order['status'], $valid_statuses );
		}
	}

	/**
	 * Test order items generation
	 */
	public function test_order_items_generation(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$order = $result['orders'][0];

		$this->assertArrayHasKey( 'items', $order );
		$this->assertIsArray( $order['items'] );
		$this->assertGreaterThan( 0, count( $order['items'] ) );

		$item = $order['items'][0];
		$this->assertArrayHasKey( 'product_id', $item );
		$this->assertArrayHasKey( 'variation_id', $item );
		$this->assertArrayHasKey( 'quantity', $item );
		$this->assertArrayHasKey( 'price', $item );
		$this->assertArrayHasKey( 'total', $item );

		$this->assertIsInt( $item['product_id'] );
		$this->assertIsInt( $item['quantity'] );
		$this->assertIsFloat( $item['price'] );
		$this->assertIsFloat( $item['total'] );

		$this->assertGreaterThan( 0, $item['quantity'] );
		$this->assertGreaterThan( 0, $item['price'] );
		$this->assertGreaterThan( 0, $item['total'] );
	}

	/**
	 * Test order addresses generation
	 */
	public function test_order_addresses_generation(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$order = $result['orders'][0];

		// Check billing address
		$this->assertArrayHasKey( 'billing_address', $order );
		$billing = $order['billing_address'];

		$this->assertArrayHasKey( 'first_name', $billing );
		$this->assertArrayHasKey( 'last_name', $billing );
		$this->assertArrayHasKey( 'email', $billing );
		$this->assertArrayHasKey( 'phone', $billing );
		$this->assertArrayHasKey( 'street', $billing );
		$this->assertArrayHasKey( 'city', $billing );
		$this->assertArrayHasKey( 'state', $billing );
		$this->assertArrayHasKey( 'postal_code', $billing );
		$this->assertArrayHasKey( 'country', $billing );

		// Check shipping address
		$this->assertArrayHasKey( 'shipping_address', $order );
		$shipping = $order['shipping_address'];

		$this->assertArrayHasKey( 'first_name', $shipping );
		$this->assertArrayHasKey( 'last_name', $shipping );
		$this->assertArrayHasKey( 'street', $shipping );
		$this->assertArrayHasKey( 'city', $shipping );
		$this->assertArrayHasKey( 'state', $shipping );
		$this->assertArrayHasKey( 'postal_code', $shipping );
		$this->assertArrayHasKey( 'country', $shipping );
	}

	/**
	 * Test order payment method values
	 */
	public function test_order_payment_methods(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_payment_methods = array( 'credit_card', 'paypal', 'stripe', 'bank_transfer', 'cash_on_delivery', 'apple_pay', 'google_pay' );

		foreach ( $result['orders'] as $order ) {
			$this->assertContains( $order['payment_method'], $valid_payment_methods );
		}
	}

	/**
	 * Test order currency values
	 */
	public function test_order_currencies(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );
		$valid_currencies = array( 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'BRL' );

		foreach ( $result['orders'] as $order ) {
			$this->assertContains( $order['currency'], $valid_currencies );
		}
	}

	/**
	 * Test order number uniqueness
	 */
	public function test_order_number_uniqueness(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );
		$order_numbers  = array_column( $result['orders'], 'order_number' );
		$unique_numbers = array_unique( $order_numbers );

		// All order numbers should be unique
		$this->assertCount( count( $order_numbers ), $unique_numbers, 'Generated orders should have unique order numbers' );
	}

	/**
	 * Test order total calculation consistency
	 */
	public function test_order_total_calculation(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$order = $result['orders'][0];

		$calculated_total = $order['subtotal'] + $order['tax_amount'] + $order['shipping_amount'] - $order['discount_amount'];

		// Allow for small floating point differences
		$this->assertEqualsWithDelta( $calculated_total, $order['total'], 0.01, 'Order total should equal subtotal + tax + shipping - discount' );
	}

	/**
	 * Test order generation performance
	 */
	public function test_order_generation_performance(): void {
		$start_time = microtime( true );
		$result     = $this->generator->generate( 10 );
		$end_time   = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (5 seconds)
		$this->assertLessThan( 5, $execution_time, 'Order generation took too long' );

		$this->assertIsArray( $result );
		$this->assertEquals( 10, $result['generated'] );
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		$memory_before = memory_get_usage();
		$result        = $this->generator->generate( 15 );
		$memory_after  = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 8MB for 15 orders)
		$this->assertLessThan( 8 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		$this->assertIsArray( $result );
		$this->assertEquals( 15, $result['generated'] );
	}

	/**
	 * Test order notes generation
	 */
	public function test_order_notes_generation(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$order = $result['orders'][0];

		$this->assertArrayHasKey( 'notes', $order );
		$this->assertIsArray( $order['notes'] );

		if ( ! empty( $order['notes'] ) ) {
			$note = $order['notes'][0];
			$this->assertArrayHasKey( 'note', $note );
			$this->assertArrayHasKey( 'type', $note );
			$this->assertArrayHasKey( 'created_at', $note );

			$this->assertIsString( $note['note'] );
			$this->assertIsString( $note['type'] );

			$valid_note_types = array( 'customer', 'private', 'system' );
			$this->assertContains( $note['type'], $valid_note_types );
		}
	}

	/**
	 * Test order coupons application
	 */
	public function test_order_coupons(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['orders'] as $order ) {
			$this->assertArrayHasKey( 'coupons', $order );
			$this->assertIsArray( $order['coupons'] );

			// If coupons are applied, discount should be > 0
			if ( ! empty( $order['coupons'] ) && $order['discount_amount'] > 0 ) {
				foreach ( $order['coupons'] as $coupon ) {
					$this->assertArrayHasKey( 'code', $coupon );
					$this->assertArrayHasKey( 'discount_amount', $coupon );
					$this->assertIsString( $coupon['code'] );
					$this->assertIsFloat( $coupon['discount_amount'] );
					$this->assertGreaterThan( 0, $coupon['discount_amount'] );
				}
			}
		}
	}

	/**
	 * Test generate_multiple method
	 */
	public function test_generate_multiple_method(): void {
		$count  = 5;
		$result = $this->generator->generate_multiple( $count );

		$this->assertIsArray( $result );
		$this->assertCount( $count, $result );

		foreach ( $result as $order ) {
			$this->assertIsArray( $order );
			$this->assertArrayHasKey( 'id', $order );
			$this->assertArrayHasKey( 'order_number', $order );
			$this->assertArrayHasKey( 'total', $order );
		}
	}
}
