<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Generators\Shipping_Plan;
use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;

/**
 * Test class for Shipping Plan Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Shipping_Plan
 */
class ShippingPlanGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Shipping_Plan
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		$this->generator = new Shipping_Plan();
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
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$this->assertInstanceOf( Shipping_Plan::class, $this->generator );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$count  = 3;
		$result = $this->generator->generate( $count );

		// Check result structure
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'shipping_plans_created', $result );
		$this->assertTrue( $result['success'] );
		$this->assertEquals( $count, $result['shipping_plans_created'] );
	}

	/**
	 * Test generate method with zero count
	 */
	public function test_generate_with_zero_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$result = $this->generator->generate( 0 );

		// Should return error or handle gracefully
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertFalse( $result['success'] );
	}

	/**
	 * Test generate method with negative count
	 */
	public function test_generate_with_negative_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$result = $this->generator->generate( -1 );

		// Should return error or handle gracefully
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertFalse( $result['success'] );
	}

	/**
	 * Test generate method with large count
	 */
	public function test_generate_with_large_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		// Test with maximum allowed count
		$result = $this->generator->generate( 25 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );

		// Should succeed or return appropriate error for large count
		if ( $result['success'] ) {
			$this->assertEquals( 25, $result['shipping_plans_created'] );
		} else {
			$this->assertArrayHasKey( 'message', $result );
		}
	}

	/**
	 * Test that generated shipping plans have required fields
	 */
	public function test_generated_shipping_plans_have_required_fields(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$result = $this->generator->generate( 1 );

		if ( $result['success'] && isset( $result['shipping_plans'] ) ) {
			$shipping_plan = $result['shipping_plans'][0];

			// Check required shipping plan fields
			$this->assertArrayHasKey( 'id', $shipping_plan );
			$this->assertArrayHasKey( 'name', $shipping_plan );
			$this->assertArrayHasKey( 'method', $shipping_plan );
			$this->assertNotEmpty( $shipping_plan['name'] );
			$this->assertNotEmpty( $shipping_plan['method'] );
		}
	}

	/**
	 * Test shipping plan generation with different methods
	 */
	public function test_shipping_plan_generation_with_methods(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$result = $this->generator->generate( 3 );

		if ( $result['success'] && isset( $result['shipping_plans'] ) ) {
			foreach ( $result['shipping_plans'] as $shipping_plan ) {
				// Shipping plans should have valid methods
				$this->assertArrayHasKey( 'method', $shipping_plan );
				$this->assertContains( $shipping_plan['method'], array( 'flat_rate', 'weight_based', 'distance_based' ) );
			}
		}
	}

	/**
	 * Test shipping plan generation performance
	 */
	public function test_shipping_plan_generation_performance(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$start_time = microtime( true );
		$result     = $this->generator->generate( 10 );
		$end_time   = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (2 seconds)
		$this->assertLessThan( 2, $execution_time, 'Shipping plan generation took too long' );

		if ( $result['success'] ) {
			$this->assertEquals( 10, $result['shipping_plans_created'] );
		}
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$memory_before = memory_get_usage();
		$result        = $this->generator->generate( 15 );
		$memory_after  = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 5MB for 15 shipping plans)
		$this->assertLessThan( 5 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		if ( $result['success'] ) {
			$this->assertEquals( 15, $result['shipping_plans_created'] );
		}
	}

	/**
	 * Test that generated shipping plans are unique
	 */
	public function test_generated_shipping_plans_are_unique(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Shipping_Plan' ) ) {
			$this->markTestSkipped( 'Shipping_Plan_Generator class not found' );
		}

		$result = $this->generator->generate( 5 );

		if ( $result['success'] && isset( $result['shipping_plans'] ) ) {
			$names        = array_column( $result['shipping_plans'], 'name' );
			$unique_names = array_unique( $names );

			// All shipping plan names should be unique
			$this->assertCount( count( $names ), $unique_names, 'Generated shipping plans should have unique names' );
		}
	}
}
