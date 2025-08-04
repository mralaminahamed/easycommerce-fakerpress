<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use ECFP_Product_Generator;

/**
 * Test class for Product Generator
 *
 * @covers \ECFP_Product_Generator
 */
class ProductGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var ECFP_Product_Generator
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Only create generator if the class exists
		if ( class_exists( 'ECFP_Product_Generator' ) ) {
			$this->generator = new ECFP_Product_Generator();
		}
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
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		$this->assertInstanceOf( ECFP_Product_Generator::class, $this->generator );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		$count = 3;
		$result = $this->generator->generate( $count );

		// Check result structure
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		$this->assertArrayHasKey( 'products_created', $result );
		$this->assertTrue( $result['success'] );
		$this->assertEquals( $count, $result['products_created'] );
	}

	/**
	 * Test generate method with zero count
	 */
	public function test_generate_with_zero_count(): void {
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
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
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
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
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		// Test with maximum allowed count
		$result = $this->generator->generate( 100 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'success', $result );
		
		// Should succeed or return appropriate error for large count
		if ( $result['success'] ) {
			$this->assertEquals( 100, $result['products_created'] );
		} else {
			$this->assertArrayHasKey( 'message', $result );
		}
	}

	/**
	 * Test that generated products have required fields
	 */
	public function test_generated_products_have_required_fields(): void {
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		$result = $this->generator->generate( 1 );

		if ( $result['success'] && isset( $result['products'] ) ) {
			$product = $result['products'][0];

			// Check required product fields
			$this->assertArrayHasKey( 'id', $product );
			$this->assertArrayHasKey( 'title', $product );
			$this->assertArrayHasKey( 'price', $product );
			$this->assertIsNumeric( $product['price'] );
			$this->assertGreaterThan( 0, $product['price'] );
		}
	}

	/**
	 * Test product generation with categories
	 */
	public function test_product_generation_with_categories(): void {
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		// Create some test categories first
		$category_ids = array();
		for ( $i = 0; $i < 3; $i++ ) {
			$category_ids[] = $this->factory->term->create(
				array(
					'taxonomy' => 'product_category',
					'name'     => "Test Category $i",
				)
			);
		}

		$result = $this->generator->generate( 2 );

		if ( $result['success'] && isset( $result['products'] ) ) {
			foreach ( $result['products'] as $product ) {
				// Products should be assigned to categories
				$this->assertArrayHasKey( 'categories', $product );
				$this->assertIsArray( $product['categories'] );
			}
		}
	}

	/**
	 * Test product generation performance
	 */
	public function test_product_generation_performance(): void {
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		$start_time = microtime( true );
		$result = $this->generator->generate( 10 );
		$end_time = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (5 seconds)
		$this->assertLessThan( 5, $execution_time, 'Product generation took too long' );
		
		if ( $result['success'] ) {
			$this->assertEquals( 10, $result['products_created'] );
		}
	}

	/**
	 * Test memory usage during generation
	 */
	public function test_memory_usage_during_generation(): void {
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		$memory_before = memory_get_usage();
		$result = $this->generator->generate( 20 );
		$memory_after = memory_get_usage();

		$memory_used = $memory_after - $memory_before;

		// Memory usage should be reasonable (less than 10MB for 20 products)
		$this->assertLessThan( 10 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );
		
		if ( $result['success'] ) {
			$this->assertEquals( 20, $result['products_created'] );
		}
	}

	/**
	 * Test that generated products are unique
	 */
	public function test_generated_products_are_unique(): void {
		if ( ! class_exists( 'ECFP_Product_Generator' ) ) {
			$this->markTestSkipped( 'ECFP_Product_Generator class not found' );
		}

		$result = $this->generator->generate( 5 );

		if ( $result['success'] && isset( $result['products'] ) ) {
			$titles = array_column( $result['products'], 'title' );
			$unique_titles = array_unique( $titles );

			// All product titles should be unique
			$this->assertCount( count( $titles ), $unique_titles, 'Generated products should have unique titles' );
		}
	}
}