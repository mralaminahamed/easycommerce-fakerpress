<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Coupon;
use EasyCommerce\Models\Coupon;

/**
 * Test class for Coupon Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Coupon
 */
class CouponGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Coupon
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Coupon' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new Coupon();
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
		$this->assertInstanceOf( Coupon::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new \ReflectionClass( $this->generator );
		$method = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'coupon', $method->invoke( $this->generator ) );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		$count = 3;
		$result = $this->generator->generate( $count );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'generated', $result );
		$this->assertArrayHasKey( 'coupons', $result );
		$this->assertEquals( $count, $result['generated'] );
		$this->assertCount( $count, $result['coupons'] );
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
	 * Test that generated coupons have required fields
	 */
	public function test_generated_coupons_have_required_fields(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'coupons', $result );
		$this->assertCount( 1, $result['coupons'] );

		$coupon = $result['coupons'][0];

		// Check required coupon fields
		$this->assertArrayHasKey( 'id', $coupon );
		$this->assertArrayHasKey( 'code', $coupon );
		$this->assertArrayHasKey( 'type', $coupon );
		$this->assertArrayHasKey( 'amount', $coupon );
		$this->assertArrayHasKey( 'status', $coupon );
		$this->assertArrayHasKey( 'usage_limit', $coupon );
		$this->assertArrayHasKey( 'usage_count', $coupon );
		$this->assertArrayHasKey( 'start_date', $coupon );
		$this->assertArrayHasKey( 'end_date', $coupon );
		$this->assertArrayHasKey( 'minimum_amount', $coupon );
		$this->assertArrayHasKey( 'maximum_amount', $coupon );

		// Validate data types
		$this->assertIsInt( $coupon['id'] );
		$this->assertIsString( $coupon['code'] );
		$this->assertIsString( $coupon['type'] );
		$this->assertIsFloat( $coupon['amount'] );
		$this->assertIsString( $coupon['status'] );
		$this->assertIsInt( $coupon['usage_limit'] );
		$this->assertIsInt( $coupon['usage_count'] );

		// Validate coupon code format
		$this->assertMatchesRegularExpression( '/^[A-Z0-9]+$/', $coupon['code'] );
		$this->assertGreaterThanOrEqual( 4, strlen( $coupon['code'] ) );
	}

	/**
	 * Test coupon types
	 */
	public function test_coupon_types(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_types = array( 'percentage', 'fixed_amount', 'free_shipping', 'bogo', 'tiered' );

		foreach ( $result['coupons'] as $coupon ) {
			$this->assertContains( $coupon['type'], $valid_types );
		}
	}

	/**
	 * Test coupon status values
	 */
	public function test_coupon_status_values(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_statuses = array( 'active', 'inactive', 'expired', 'draft' );

		foreach ( $result['coupons'] as $coupon ) {
			$this->assertContains( $coupon['status'], $valid_statuses );
		}
	}

	/**
	 * Test coupon amount values based on type
	 */
	public function test_coupon_amounts_by_type(): void {
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );

		foreach ( $result['coupons'] as $coupon ) {
			if ( $coupon['type'] === 'percentage' ) {
				// Percentage discounts should be between 1-100
				$this->assertGreaterThan( 0, $coupon['amount'] );
				$this->assertLessThanOrEqual( 100, $coupon['amount'] );
			} elseif ( $coupon['type'] === 'fixed_amount' ) {
				// Fixed amount discounts should be positive
				$this->assertGreaterThan( 0, $coupon['amount'] );
			} elseif ( $coupon['type'] === 'free_shipping' ) {
				// Free shipping coupons typically have 0 amount
				$this->assertGreaterThanOrEqual( 0, $coupon['amount'] );
			}
		}
	}

	/**
	 * Test coupon code uniqueness
	 */
	public function test_coupon_code_uniqueness(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );
		$coupon_codes = array_column( $result['coupons'], 'code' );
		$unique_codes = array_unique( $coupon_codes );

		// All coupon codes should be unique
		$this->assertCount( count( $coupon_codes ), $unique_codes, 'Generated coupons should have unique codes' );
	}

	/**
	 * Test coupon usage limits
	 */
	public function test_coupon_usage_limits(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['coupons'] as $coupon ) {
			$this->assertGreaterThanOrEqual( 0, $coupon['usage_limit'] );
			$this->assertGreaterThanOrEqual( 0, $coupon['usage_count'] );
			$this->assertLessThanOrEqual( $coupon['usage_limit'], $coupon['usage_count'] );
		}
	}

	/**
	 * Test coupon date validation
	 */
	public function test_coupon_date_validation(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$coupon = $result['coupons'][0];

		// Validate date formats
		if ( ! is_null( $coupon['start_date'] ) ) {
			$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $coupon['start_date'] );
		}

		if ( ! is_null( $coupon['end_date'] ) ) {
			$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $coupon['end_date'] );
		}

		// End date should be after start date if both exist
		if ( ! is_null( $coupon['start_date'] ) && ! is_null( $coupon['end_date'] ) ) {
			$start_timestamp = strtotime( $coupon['start_date'] );
			$end_timestamp = strtotime( $coupon['end_date'] );
			$this->assertGreaterThan( $start_timestamp, $end_timestamp );
		}
	}

	/**
	 * Test coupon restrictions
	 */
	public function test_coupon_restrictions(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$coupon = $result['coupons'][0];

		$this->assertArrayHasKey( 'restrictions', $coupon );
		$restrictions = $coupon['restrictions'];

		$this->assertArrayHasKey( 'allowed_products', $restrictions );
		$this->assertArrayHasKey( 'excluded_products', $restrictions );
		$this->assertArrayHasKey( 'allowed_categories', $restrictions );
		$this->assertArrayHasKey( 'excluded_categories', $restrictions );
		$this->assertArrayHasKey( 'customer_restrictions', $restrictions );

		$this->assertIsArray( $restrictions['allowed_products'] );
		$this->assertIsArray( $restrictions['excluded_products'] );
		$this->assertIsArray( $restrictions['allowed_categories'] );
		$this->assertIsArray( $restrictions['excluded_categories'] );
		$this->assertIsArray( $restrictions['customer_restrictions'] );
	}

	/**
	 * Test minimum and maximum amount validation
	 */
	public function test_minimum_maximum_amount_validation(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['coupons'] as $coupon ) {
			if ( ! is_null( $coupon['minimum_amount'] ) ) {
				$this->assertGreaterThan( 0, $coupon['minimum_amount'] );
			}

			if ( ! is_null( $coupon['maximum_amount'] ) ) {
				$this->assertGreaterThan( 0, $coupon['maximum_amount'] );
			}

			// Maximum should be greater than minimum if both exist
			if ( ! is_null( $coupon['minimum_amount'] ) && ! is_null( $coupon['maximum_amount'] ) ) {
				$this->assertGreaterThan( $coupon['minimum_amount'], $coupon['maximum_amount'] );
			}
		}
	}

	/**
	 * Test coupon generation performance
	 */
	public function test_coupon_generation_performance(): void {
		$start_time = microtime( true );
		$result = $this->generator->generate( 10 );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (3 seconds)
		$this->assertLessThan( 3, $execution_time, 'Coupon generation took too long' );

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

		// Memory usage should be reasonable (less than 3MB for 20 coupons)
		$this->assertLessThan( 3 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		$this->assertIsArray( $result );
		$this->assertEquals( 20, $result['generated'] );
	}

	/**
	 * Test coupon meta data
	 */
	public function test_coupon_meta_data(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$coupon = $result['coupons'][0];

		$this->assertArrayHasKey( 'meta', $coupon );
		$meta = $coupon['meta'];

		$this->assertArrayHasKey( 'description', $meta );
		$this->assertArrayHasKey( 'created_by', $meta );
		$this->assertArrayHasKey( 'campaign', $meta );

		$this->assertIsString( $meta['description'] );
		$this->assertIsInt( $meta['created_by'] );
		$this->assertIsString( $meta['campaign'] );
	}

	/**
	 * Test BOGO (Buy One Get One) coupon specific fields
	 */
	public function test_bogo_coupon_fields(): void {
		// Generate enough coupons to likely get a BOGO type
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );

		foreach ( $result['coupons'] as $coupon ) {
			if ( $coupon['type'] === 'bogo' ) {
				$this->assertArrayHasKey( 'bogo_settings', $coupon );
				$bogo = $coupon['bogo_settings'];

				$this->assertArrayHasKey( 'buy_quantity', $bogo );
				$this->assertArrayHasKey( 'get_quantity', $bogo );
				$this->assertArrayHasKey( 'get_discount_type', $bogo );

				$this->assertIsInt( $bogo['buy_quantity'] );
				$this->assertIsInt( $bogo['get_quantity'] );
				$this->assertIsString( $bogo['get_discount_type'] );

				$this->assertGreaterThan( 0, $bogo['buy_quantity'] );
				$this->assertGreaterThan( 0, $bogo['get_quantity'] );

				$valid_discount_types = array( 'free', 'percentage', 'fixed_amount' );
				$this->assertContains( $bogo['get_discount_type'], $valid_discount_types );
			}
		}
	}

	/**
	 * Test tiered coupon specific fields
	 */
	public function test_tiered_coupon_fields(): void {
		// Generate enough coupons to likely get a tiered type
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );

		foreach ( $result['coupons'] as $coupon ) {
			if ( $coupon['type'] === 'tiered' ) {
				$this->assertArrayHasKey( 'tier_settings', $coupon );
				$tiers = $coupon['tier_settings'];

				$this->assertIsArray( $tiers );
				$this->assertGreaterThan( 0, count( $tiers ) );

				foreach ( $tiers as $tier ) {
					$this->assertArrayHasKey( 'min_amount', $tier );
					$this->assertArrayHasKey( 'discount_type', $tier );
					$this->assertArrayHasKey( 'discount_value', $tier );

					$this->assertIsFloat( $tier['min_amount'] );
					$this->assertIsString( $tier['discount_type'] );
					$this->assertIsFloat( $tier['discount_value'] );

					$this->assertGreaterThan( 0, $tier['min_amount'] );
					$this->assertGreaterThan( 0, $tier['discount_value'] );

					$valid_types = array( 'percentage', 'fixed_amount' );
					$this->assertContains( $tier['discount_type'], $valid_types );
				}
			}
		}
	}

	/**
	 * Test generate_multiple method
	 */
	public function test_generate_multiple_method(): void {
		$count = 5;
		$result = $this->generator->generate_multiple( $count );

		$this->assertIsArray( $result );
		$this->assertCount( $count, $result );

		foreach ( $result as $coupon ) {
			$this->assertIsArray( $coupon );
			$this->assertArrayHasKey( 'id', $coupon );
			$this->assertArrayHasKey( 'code', $coupon );
			$this->assertArrayHasKey( 'type', $coupon );
		}
	}
}
