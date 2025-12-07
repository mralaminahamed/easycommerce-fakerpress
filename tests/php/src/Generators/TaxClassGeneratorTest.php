<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Generators\Tax_Class;
use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;

/**
 * Test class for Tax Class Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Tax_Class
 */
class TaxClassGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Tax_Class
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		$this->generator = new Tax_Class();
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
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		$this->assertInstanceOf( Tax_Class::class, $this->generator );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		$count = 3;
		$result = $this->generator->generate( $count );

		// Check result structure
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'tax_classes_created', $result );
		$this->assertTrue( $result['success'] );
		$this->assertEquals( $count, $result['tax_classes_created'] );
	}

	/**
	 * Test generate method with zero count
	 */
	public function test_generate_with_zero_count(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
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
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
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
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		// Test with maximum allowed count
		$result = $this->generator->generate( 20 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );

		// Should succeed or return appropriate error for large count
		if ( $result['success'] ) {
			$this->assertEquals( 20, $result['tax_classes_created'] );
		} else {
			$this->assertArrayHasKey( 'message', $result );
		}
	}

	/**
	 * Test that generated tax classes have required fields
	 */
	public function test_generated_tax_classes_have_required_fields(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		$result = $this->generator->generate( 1 );

		if ( $result['success'] && isset( $result['tax_classes'] ) ) {
			$tax_class = $result['tax_classes'][0];

			// Check required tax class fields
			$this->assertArrayHasKey( 'id', $tax_class );
			$this->assertArrayHasKey( 'name', $tax_class );
			$this->assertArrayHasKey( 'rate', $tax_class );
			$this->assertNotEmpty( $tax_class['name'] );
			$this->assertIsNumeric( $tax_class['rate'] );
		}
	}

	/**
	 * Test tax class generation with different types
	 */
	public function test_tax_class_generation_with_types(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		$result = $this->generator->generate( 4 );

		if ( $result['success'] && isset( $result['tax_classes'] ) ) {
			foreach ( $result['tax_classes'] as $tax_class ) {
				// Tax classes should have valid types
				$this->assertArrayHasKey( 'type', $tax_class );
				$this->assertContains( $tax_class['type'], ['standard', 'reduced', 'zero', 'exempt'] );
			}
		}
	}

	/**
	 * Test tax class generation performance
	 */
	public function test_tax_class_generation_performance(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		$start_time = microtime( true );
		$result = $this->generator->generate( 10 );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (2 seconds)
		$this->assertLessThan( 2, $execution_time, 'Tax class generation took too long' );

		if ( $result['success'] ) {
			$this->assertEquals( 10, $result['tax_classes_created'] );
		}
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		$memory_before = memory_get_usage();
		$result = $this->generator->generate( 15 );
		$memory_after = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 5MB for 15 tax classes)
		$this->assertLessThan( 5 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		if ( $result['success'] ) {
			$this->assertEquals( 15, $result['tax_classes_created'] );
		}
	}

	/**
	 * Test that generated tax classes are unique
	 */
	public function test_generated_tax_classes_are_unique(): void {
		if ( ! class_exists( '\EasyCommerceFakerPress\Generators\Tax_Class' ) ) {
			$this->markTestSkipped( 'Tax_Class_Generator class not found' );
		}

		$result = $this->generator->generate( 5 );

		if ( $result['success'] && isset( $result['tax_classes'] ) ) {
			$names = array_column( $result['tax_classes'], 'name' );
			$unique_names = array_unique( $names );

			// All tax class names should be unique
			$this->assertCount( count( $names ), $unique_names, 'Generated tax classes should have unique names' );
		}
	}
}