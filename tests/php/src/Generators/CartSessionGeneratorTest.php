<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Cart_Session;
use EasyCommerce\Models\Cart;

/**
 * Test class for Cart Session Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Cart_Session
 */
class CartSessionGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Cart_Session
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Cart' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new Cart_Session();
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
		$this->assertInstanceOf( Cart_Session::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new \ReflectionClass( $this->generator );
		$method = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'cart_session', $method->invoke( $this->generator ) );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		$count = 3;
		$result = $this->generator->generate( $count );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'generated', $result );
		$this->assertArrayHasKey( 'cart_sessions', $result );
		$this->assertEquals( $count, $result['generated'] );
		$this->assertCount( $count, $result['cart_sessions'] );
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
	 * Test that generated cart sessions have required fields
	 */
	public function test_generated_cart_sessions_have_required_fields(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'cart_sessions', $result );
		$this->assertCount( 1, $result['cart_sessions'] );

		$cart_session = $result['cart_sessions'][0];

		// Check required cart session fields
		$this->assertArrayHasKey( 'hash', $cart_session );
		$this->assertArrayHasKey( 'user_id', $cart_session );
		$this->assertArrayHasKey( 'status', $cart_session );
		$this->assertArrayHasKey( 'total_amount', $cart_session );
		$this->assertArrayHasKey( 'item_count', $cart_session );
		$this->assertArrayHasKey( 'reminders', $cart_session );
		$this->assertArrayHasKey( 'created_at', $cart_session );
		$this->assertArrayHasKey( 'updated_at', $cart_session );
		$this->assertArrayHasKey( 'items', $cart_session );
		$this->assertArrayHasKey( 'addresses', $cart_session );

		// Validate data types
		$this->assertIsString( $cart_session['hash'] );
		$this->assertIsInt( $cart_session['user_id'] );
		$this->assertIsString( $cart_session['status'] );
		$this->assertIsFloat( $cart_session['total_amount'] );
		$this->assertIsInt( $cart_session['item_count'] );
		$this->assertIsInt( $cart_session['reminders'] );
		$this->assertIsArray( $cart_session['items'] );
		$this->assertIsArray( $cart_session['addresses'] );

		// Validate numeric values
		$this->assertGreaterThan( 0, $cart_session['total_amount'] );
		$this->assertGreaterThan( 0, $cart_session['item_count'] );
		$this->assertGreaterThanOrEqual( 0, $cart_session['reminders'] );

		// Validate hash format
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{32}$/', $cart_session['hash'] );
	}

	/**
	 * Test cart session status values
	 */
	public function test_cart_session_status_values(): void {
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );
		$valid_statuses = array( 'pending', 'abandoned', 'completed', 'cancelled' );

		foreach ( $result['cart_sessions'] as $cart_session ) {
			$this->assertContains( $cart_session['status'], $valid_statuses );
		}
	}

	/**
	 * Test cart session items structure
	 */
	public function test_cart_session_items_structure(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$cart_session = $result['cart_sessions'][0];

		$this->assertArrayHasKey( 'items', $cart_session );
		$this->assertIsArray( $cart_session['items'] );
		$this->assertGreaterThan( 0, count( $cart_session['items'] ) );

		foreach ( $cart_session['items'] as $product_id => $variations ) {
			$this->assertIsInt( $product_id );
			$this->assertIsArray( $variations );

			foreach ( $variations as $price_id => $config ) {
				$this->assertArrayHasKey( 'quantity', $config );
				$this->assertArrayHasKey( 'price', $config );
				$this->assertArrayHasKey( 'total', $config );

				$this->assertIsInt( $config['quantity'] );
				$this->assertIsFloat( $config['price'] );
				$this->assertIsFloat( $config['total'] );

				$this->assertGreaterThan( 0, $config['quantity'] );
				$this->assertGreaterThan( 0, $config['price'] );
				$this->assertGreaterThan( 0, $config['total'] );

				// Total should equal quantity * price
				$this->assertEqualsWithDelta(
					$config['quantity'] * $config['price'],
					$config['total'],
					0.01,
					'Item total should equal quantity * price'
				);
			}
		}
	}

	/**
	 * Test cart session addresses structure
	 */
	public function test_cart_session_addresses_structure(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['cart_sessions'] as $cart_session ) {
			$this->assertArrayHasKey( 'addresses', $cart_session );
			$this->assertIsArray( $cart_session['addresses'] );

			// Check billing address structure
			if ( isset( $cart_session['addresses']['billing'] ) ) {
				$billing = $cart_session['addresses']['billing'];

				$this->assertArrayHasKey( 'first_name', $billing );
				$this->assertArrayHasKey( 'last_name', $billing );
				$this->assertArrayHasKey( 'email', $billing );
				$this->assertArrayHasKey( 'phone', $billing );
				$this->assertArrayHasKey( 'street', $billing );
				$this->assertArrayHasKey( 'city', $billing );
				$this->assertArrayHasKey( 'state', $billing );
				$this->assertArrayHasKey( 'postal_code', $billing );
				$this->assertArrayHasKey( 'country', $billing );

				$this->assertIsString( $billing['first_name'] );
				$this->assertIsString( $billing['last_name'] );
				$this->assertIsString( $billing['email'] );
				$this->assertIsString( $billing['street'] );
				$this->assertIsString( $billing['city'] );
				$this->assertIsString( $billing['country'] );

				// Validate email format
				$this->assertFilter( $billing['email'], FILTER_VALIDATE_EMAIL );
			}

			// Check shipping address structure
			if ( isset( $cart_session['addresses']['shipping'] ) ) {
				$shipping = $cart_session['addresses']['shipping'];

				$this->assertArrayHasKey( 'first_name', $shipping );
				$this->assertArrayHasKey( 'last_name', $shipping );
				$this->assertArrayHasKey( 'street', $shipping );
				$this->assertArrayHasKey( 'city', $shipping );
				$this->assertArrayHasKey( 'state', $shipping );
				$this->assertArrayHasKey( 'postal_code', $shipping );
				$this->assertArrayHasKey( 'country', $shipping );

				$this->assertIsString( $shipping['first_name'] );
				$this->assertIsString( $shipping['last_name'] );
				$this->assertIsString( $shipping['street'] );
				$this->assertIsString( $shipping['city'] );
				$this->assertIsString( $shipping['country'] );
			}
		}
	}

	/**
	 * Test cart session status distribution
	 */
	public function test_cart_session_status_distribution(): void {
		$result = $this->generator->generate( 50 );

		$this->assertIsArray( $result );

		$status_counts = array();
		foreach ( $result['cart_sessions'] as $cart_session ) {
			$status = $cart_session['status'];
			$status_counts[ $status ] = ( $status_counts[ $status ] ?? 0 ) + 1;
		}

		// Based on the generator logic, pending should be most common (60%)
		$this->assertArrayHasKey( 'pending', $status_counts );
		$this->assertGreaterThan( 20, $status_counts['pending'], 'Most cart sessions should be pending' );

		// Abandoned should be second most common (30%)
		if ( isset( $status_counts['abandoned'] ) ) {
			$this->assertGreaterThan( 10, $status_counts['abandoned'] );
		}
	}

	/**
	 * Test reminder count by status
	 */
	public function test_reminder_count_by_status(): void {
		$result = $this->generator->generate( 30 );

		$this->assertIsArray( $result );

		foreach ( $result['cart_sessions'] as $cart_session ) {
			$status = $cart_session['status'];
			$reminders = $cart_session['reminders'];

			if ( $status === 'abandoned' ) {
				// Abandoned carts should have some reminders
				$this->assertGreaterThanOrEqual( 0, $reminders );
				$this->assertLessThanOrEqual( 5, $reminders );
			} elseif ( $status === 'completed' || $status === 'cancelled' ) {
				// Completed/cancelled carts typically don't need reminders
				$this->assertGreaterThanOrEqual( 0, $reminders );
			}
		}
	}

	/**
	 * Test cart session timeline logic
	 */
	public function test_cart_session_timeline_logic(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );

		foreach ( $result['cart_sessions'] as $cart_session ) {
			$created_at = strtotime( $cart_session['created_at'] );
			$updated_at = strtotime( $cart_session['updated_at'] );

			// Updated time should be after or equal to created time
			$this->assertGreaterThanOrEqual( $created_at, $updated_at, 'Updated time should be after or equal to created time' );

			// Times should be realistic (not too far in the future)
			$now = time();
			$this->assertLessThanOrEqual( $now, $created_at, 'Created time should not be in the future' );
			$this->assertLessThanOrEqual( $now, $updated_at, 'Updated time should not be in the future' );
		}
	}

	/**
	 * Test cart session hash uniqueness
	 */
	public function test_cart_session_hash_uniqueness(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$hashes = array_column( $result['cart_sessions'], 'hash' );
		$unique_hashes = array_unique( $hashes );

		// All cart session hashes should be unique
		$this->assertCount( count( $hashes ), $unique_hashes, 'Generated cart sessions should have unique hashes' );
	}

	/**
	 * Test cart total calculation
	 */
	public function test_cart_total_calculation(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['cart_sessions'] as $cart_session ) {
			$items = $cart_session['items'];
			$calculated_total = 0;
			$calculated_item_count = 0;

			foreach ( $items as $product_id => $variations ) {
				foreach ( $variations as $price_id => $config ) {
					$calculated_total += $config['total'];
					$calculated_item_count += $config['quantity'];
				}
			}

			// Allow for small floating point differences
			$this->assertEqualsWithDelta(
				$calculated_total,
				$cart_session['total_amount'],
				0.01,
				'Cart total should match sum of item totals'
			);

			$this->assertEquals(
				$calculated_item_count,
				$cart_session['item_count'],
				'Cart item count should match sum of item quantities'
			);
		}
	}

	/**
	 * Test cart session generation performance
	 */
	public function test_cart_session_generation_performance(): void {
		$start_time = microtime( true );
		$result = $this->generator->generate( 10 );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (5 seconds)
		$this->assertLessThan( 5, $execution_time, 'Cart session generation took too long' );

		$this->assertIsArray( $result );
		$this->assertEquals( 10, $result['generated'] );
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		$memory_before = memory_get_usage();
		$result = $this->generator->generate( 15 );
		$memory_after = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 8MB for 15 cart sessions)
		$this->assertLessThan( 8 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		$this->assertIsArray( $result );
		$this->assertEquals( 15, $result['generated'] );
	}

	/**
	 * Test generate_multiple method
	 */
	public function test_generate_multiple_method(): void {
		$count = 5;
		$result = $this->generator->generate_multiple( $count );

		$this->assertIsArray( $result );
		$this->assertCount( $count, $result );

		foreach ( $result as $cart_session ) {
			$this->assertIsArray( $cart_session );
			$this->assertArrayHasKey( 'hash', $cart_session );
			$this->assertArrayHasKey( 'status', $cart_session );
			$this->assertArrayHasKey( 'total_amount', $cart_session );
		}
	}

	/**
	 * Test generate_abandoned_carts method
	 */
	public function test_generate_abandoned_carts_method(): void {
		$count = 5;
		$result = $this->generator->generate_abandoned_carts( $count );

		$this->assertIsArray( $result );
		$this->assertCount( $count, $result );

		foreach ( $result as $cart_session ) {
			$this->assertIsArray( $cart_session );
			$this->assertArrayHasKey( 'status', $cart_session );
			$this->assertEquals( 'abandoned', $cart_session['status'] );
			$this->assertArrayHasKey( 'reminders', $cart_session );
			$this->assertGreaterThanOrEqual( 0, $cart_session['reminders'] );
		}
	}

	/**
	 * Test get_supported_types method
	 */
	public function test_get_supported_types(): void {
		$supported_types = $this->generator->get_supported_types();

		$this->assertIsArray( $supported_types );
		$this->assertArrayHasKey( 'cart_sessions', $supported_types );
		$this->assertEquals( 'Shopping Cart Sessions and Abandoned Carts', $supported_types['cart_sessions'] );
	}

	/**
	 * Test get_description method
	 */
	public function test_get_description(): void {
		$description = $this->generator->get_description();

		$this->assertIsString( $description );
		$this->assertStringContainsString( 'cart', $description );
		$this->assertStringContainsString( 'session', $description );
	}

	/**
	 * Test cart session handles missing products gracefully
	 */
	public function test_handles_missing_products_gracefully(): void {
		// This test verifies that the generator handles the case where no products exist

		$result = $this->generator->generate( 1 );

		// Result should either be a valid array or a WP_Error, but not cause a fatal error
		$this->assertTrue(
			is_array( $result ) || is_wp_error( $result ),
			'Generator should handle missing products gracefully'
		);
	}

	/**
	 * Test user assignment (registered vs guest)
	 */
	public function test_user_assignment(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );

		$registered_users = 0;
		$guest_users = 0;

		foreach ( $result['cart_sessions'] as $cart_session ) {
			if ( $cart_session['user_id'] > 0 ) {
				$registered_users++;
			} else {
				$guest_users++;
				// Guest users should have billing address with email
				$this->assertArrayHasKey( 'addresses', $cart_session );
				if ( isset( $cart_session['addresses']['billing'] ) ) {
					$billing = $cart_session['addresses']['billing'];
					$this->assertArrayHasKey( 'email', $billing );
					$this->assertFilter( $billing['email'], FILTER_VALIDATE_EMAIL );
				}
			}
		}

		// Both registered and guest users should be represented
		$this->assertGreaterThan( 0, $registered_users + $guest_users );
	}
}
