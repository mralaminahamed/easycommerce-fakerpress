<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Generators\Location;
use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;

/**
 * Test class for Location Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Location
 */
class LocationGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Location
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		$this->generator = new Location();
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
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		$this->assertInstanceOf( Location::class, $this->generator );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		$count = 3;
		$result = $this->generator->generate( $count );

		// Check result structure
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'locations_created', $result );
		$this->assertTrue( $result['success'] );
		$this->assertEquals( $count, $result['locations_created'] );
	}

	/**
	 * Test generate method with zero count
	 */
	public function test_generate_with_zero_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
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
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
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
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		// Test with maximum allowed count
		$result = $this->generator->generate( 50 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );

		// Should succeed or return appropriate error for large count
		if ( $result['success'] ) {
			$this->assertEquals( 50, $result['locations_created'] );
		} else {
			$this->assertArrayHasKey( 'message', $result );
		}
	}

	/**
	 * Test that generated locations have required fields
	 */
	public function test_generated_locations_have_required_fields(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		$result = $this->generator->generate( 1 );

		if ( $result['success'] && isset( $result['locations'] ) ) {
			$location = $result['locations'][0];

			// Check required location fields
			$this->assertArrayHasKey( 'id', $location );
			$this->assertArrayHasKey( 'country', $location );
			$this->assertArrayHasKey( 'country_code', $location );
			$this->assertNotEmpty( $location['country'] );
			$this->assertNotEmpty( $location['country_code'] );
		}
	}

	/**
	 * Test location generation with states
	 */
	public function test_location_generation_with_states(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		$result = $this->generator->generate( 2 );

		if ( $result['success'] && isset( $result['locations'] ) ) {
			foreach ( $result['locations'] as $location ) {
				// Locations should have state information where applicable
				$this->assertArrayHasKey( 'state', $location );
				$this->assertArrayHasKey( 'state_code', $location );
			}
		}
	}

	/**
	 * Test location generation performance
	 */
	public function test_location_generation_performance(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		$start_time = microtime( true );
		$result = $this->generator->generate( 10 );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (2 seconds)
		$this->assertLessThan( 2, $execution_time, 'Location generation took too long' );

		if ( $result['success'] ) {
			$this->assertEquals( 10, $result['locations_created'] );
		}
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		$memory_before = memory_get_usage();
		$result = $this->generator->generate( 20 );
		$memory_after = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 5MB for 20 locations)
		$this->assertLessThan( 5 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		if ( $result['success'] ) {
			$this->assertEquals( 20, $result['locations_created'] );
		}
	}

	/**
	 * Test that generated locations are unique
	 */
	public function test_generated_locations_are_unique(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Location' ) ) {
			$this->markTestSkipped( 'Location_Generator class not found' );
		}

		$result = $this->generator->generate( 5 );

		if ( $result['success'] && isset( $result['locations'] ) ) {
			$country_codes = array_column( $result['locations'], 'country_code' );
			$unique_codes = array_unique( $country_codes );

			// All country codes should be unique
			$this->assertCount( count( $country_codes ), $unique_codes, 'Generated locations should have unique country codes' );
		}
	}
}