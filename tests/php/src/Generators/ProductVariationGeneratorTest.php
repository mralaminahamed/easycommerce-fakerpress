<?php

namespace EasyCommerceFakerPress\Tests\Generators;

use EasyCommerceFakerPress\Tests\EasyCommerceFakerPressUnitTestCase;
use EasyCommerceFakerPress\Generators\Product_Variation;

/**
 * Test class for Product Variation Generator
 *
 * @covers \EasyCommerceFakerPress\Generators\Product_Variation
 */
class ProductVariationGeneratorTest extends EasyCommerceFakerPressUnitTestCase {

	/**
	 * @var Product_Variation
	 */
	private $generator;

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if EasyCommerce plugin is not active
		if ( ! class_exists( 'EasyCommerce\Models\Product_Variation' ) ) {
			$this->markTestSkipped( 'EasyCommerce plugin not active' );
		}

		$this->generator = new Product_Variation();
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
		$this->assertInstanceOf( Product_Variation::class, $this->generator );
	}

	/**
	 * Test get_resource_type method
	 */
	public function test_get_resource_type(): void {
		$reflection = new \ReflectionClass( $this->generator );
		$method     = $reflection->getMethod( 'get_resource_type' );
		$method->setAccessible( true );

		$this->assertEquals( 'product_variation', $method->invoke( $this->generator ) );
	}

	/**
	 * Test generate method with valid count
	 */
	public function test_generate_with_valid_count(): void {
		$count  = 3;
		$result = $this->generator->generate( $count );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'generated', $result );
		$this->assertArrayHasKey( 'product_variations', $result );
		$this->assertEquals( $count, $result['generated'] );
		$this->assertCount( $count, $result['product_variations'] );
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
	 * Test that generated variations have required fields
	 */
	public function test_generated_variations_have_required_fields(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'product_variations', $result );
		$this->assertCount( 1, $result['product_variations'] );

		$variation = $result['product_variations'][0];

		// Check required variation fields
		$this->assertArrayHasKey( 'id', $variation );
		$this->assertArrayHasKey( 'product_id', $variation );
		$this->assertArrayHasKey( 'name', $variation );
		$this->assertArrayHasKey( 'sku', $variation );
		$this->assertArrayHasKey( 'price', $variation );
		$this->assertArrayHasKey( 'sale_price', $variation );
		$this->assertArrayHasKey( 'stock_quantity', $variation );
		$this->assertArrayHasKey( 'type', $variation );
		$this->assertArrayHasKey( 'status', $variation );
		$this->assertArrayHasKey( 'attributes', $variation );

		// Validate data types
		$this->assertIsInt( $variation['id'] );
		$this->assertIsInt( $variation['product_id'] );
		$this->assertIsString( $variation['name'] );
		$this->assertIsString( $variation['sku'] );
		$this->assertIsFloat( $variation['price'] );
		$this->assertIsString( $variation['type'] );
		$this->assertIsString( $variation['status'] );
		$this->assertIsArray( $variation['attributes'] );

		// Validate numeric values
		$this->assertGreaterThan( 0, $variation['price'] );
		$this->assertGreaterThanOrEqual( 0, $variation['stock_quantity'] );
	}

	/**
	 * Test variation type values
	 */
	public function test_variation_type_values(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_types = array( 'physical', 'digital' );

		foreach ( $result['product_variations'] as $variation ) {
			$this->assertContains( $variation['type'], $valid_types );
		}
	}

	/**
	 * Test variation status values
	 */
	public function test_variation_status_values(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );
		$valid_statuses = array( 'active', 'inactive', 'draft' );

		foreach ( $result['product_variations'] as $variation ) {
			$this->assertContains( $variation['status'], $valid_statuses );
		}
	}

	/**
	 * Test variation attributes
	 */
	public function test_variation_attributes(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['product_variations'] as $variation ) {
			$this->assertArrayHasKey( 'attributes', $variation );
			$this->assertIsArray( $variation['attributes'] );
			$this->assertGreaterThan( 0, count( $variation['attributes'] ) );

			// Common attribute types that should be present
			$possible_attributes = array( 'size', 'color', 'storage', 'band', 'format', 'language' );
			$variation_keys      = array_keys( $variation['attributes'] );

			// At least one common attribute should be present
			$intersection = array_intersect( $possible_attributes, $variation_keys );
			$this->assertGreaterThan( 0, count( $intersection ), 'Variation should have at least one recognizable attribute' );
		}
	}

	/**
	 * Test variation name generation from attributes
	 */
	public function test_variation_name_from_attributes(): void {
		$result = $this->generator->generate( 1 );

		$this->assertIsArray( $result );
		$variation = $result['product_variations'][0];

		$this->assertArrayHasKey( 'name', $variation );
		$this->assertArrayHasKey( 'attributes', $variation );

		// Name should contain attribute values separated by ' - '
		$attribute_values = array_values( $variation['attributes'] );
		$expected_name    = implode( ' - ', $attribute_values );

		$this->assertEquals( $expected_name, $variation['name'] );
	}

	/**
	 * Test variation SKU generation
	 */
	public function test_variation_sku_generation(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );

		foreach ( $result['product_variations'] as $variation ) {
			$this->assertArrayHasKey( 'sku', $variation );
			$this->assertIsString( $variation['sku'] );
			$this->assertNotEmpty( $variation['sku'] );

			// SKU should contain dashes (generated from attributes)
			$this->assertStringContainsString( '-', $variation['sku'] );
		}
	}

	/**
	 * Test variation SKU uniqueness
	 */
	public function test_variation_sku_uniqueness(): void {
		$result = $this->generator->generate( 5 );

		$this->assertIsArray( $result );
		$skus        = array_column( $result['product_variations'], 'sku' );
		$unique_skus = array_unique( $skus );

		// All variation SKUs should be unique
		$this->assertCount( count( $skus ), $unique_skus, 'Generated variations should have unique SKUs' );
	}

	/**
	 * Test variation pricing relationships
	 */
	public function test_variation_pricing_relationships(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );

		foreach ( $result['product_variations'] as $variation ) {
			// If sale price exists, it should be less than regular price
			if ( ! is_null( $variation['sale_price'] ) && $variation['sale_price'] > 0 ) {
				$this->assertLessThan( $variation['price'], $variation['sale_price'], 'Sale price should be less than regular price' );
			}
		}
	}

	/**
	 * Test variation meta data for physical products
	 */
	public function test_variation_meta_physical_products(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );

		foreach ( $result['product_variations'] as $variation ) {
			if ( $variation['type'] === 'physical' ) {
				$this->assertArrayHasKey( 'meta', $variation );
				$meta = $variation['meta'];

				$this->assertArrayHasKey( 'weight', $meta );
				$this->assertArrayHasKey( 'dimensions', $meta );
				$this->assertArrayHasKey( 'tax_class', $meta );

				// Check dimensions structure
				$this->assertArrayHasKey( 'length', $meta['dimensions'] );
				$this->assertArrayHasKey( 'width', $meta['dimensions'] );
				$this->assertArrayHasKey( 'height', $meta['dimensions'] );

				// Validate numeric values
				$this->assertIsFloat( $meta['weight'] );
				$this->assertIsFloat( $meta['dimensions']['length'] );
				$this->assertIsFloat( $meta['dimensions']['width'] );
				$this->assertIsFloat( $meta['dimensions']['height'] );

				$this->assertGreaterThan( 0, $meta['weight'] );
				$this->assertGreaterThan( 0, $meta['dimensions']['length'] );
				$this->assertGreaterThan( 0, $meta['dimensions']['width'] );
				$this->assertGreaterThan( 0, $meta['dimensions']['height'] );
			}
		}
	}

	/**
	 * Test variation meta data for digital products
	 */
	public function test_variation_meta_digital_products(): void {
		$result = $this->generator->generate( 20 ); // Generate more to get digital products

		$this->assertIsArray( $result );

		foreach ( $result['product_variations'] as $variation ) {
			if ( $variation['type'] === 'digital' ) {
				$this->assertArrayHasKey( 'meta', $variation );
				$meta = $variation['meta'];

				// Digital products should not have weight/dimensions but may have download settings
				$this->assertArrayHasKey( 'tax_class', $meta );
			}
		}
	}

	/**
	 * Test stock quantity validation
	 */
	public function test_stock_quantity_validation(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );

		foreach ( $result['product_variations'] as $variation ) {
			$this->assertIsInt( $variation['stock_quantity'] );
			$this->assertGreaterThanOrEqual( 0, $variation['stock_quantity'] );
			$this->assertLessThanOrEqual( 100, $variation['stock_quantity'] ); // Based on generator logic
		}
	}

	/**
	 * Test variation generation performance
	 */
	public function test_variation_generation_performance(): void {
		$start_time = microtime( true );
		$result     = $this->generator->generate( 10 );
		$end_time   = microtime( true );

		$execution_time = $end_time - $start_time;

		// Generation should complete within reasonable time (5 seconds)
		$this->assertLessThan( 5, $execution_time, 'Variation generation took too long' );

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

		// Memory usage should be reasonable (less than 5MB for 15 variations)
		$this->assertLessThan( 5 * 1024 * 1024, $memory_used, 'Memory usage too high during generation' );

		$this->assertIsArray( $result );
		$this->assertEquals( 15, $result['generated'] );
	}

	/**
	 * Test attribute value consistency
	 */
	public function test_attribute_value_consistency(): void {
		$result = $this->generator->generate( 10 );

		$this->assertIsArray( $result );

		foreach ( $result['product_variations'] as $variation ) {
			$attributes = $variation['attributes'];

			foreach ( $attributes as $attribute_name => $attribute_value ) {
				$this->assertIsString( $attribute_value );
				$this->assertNotEmpty( $attribute_value );

				// Validate common attribute value formats
				if ( $attribute_name === 'size' ) {
					$valid_sizes = array( 'XS', 'S', 'M', 'L', 'XL', 'XXL', '6', '6.5', '7', '7.5', '8', '8.5', '9', '9.5', '10', '10.5', '11', '12', '38mm', '40mm', '42mm', '44mm', '45mm' );
					$this->assertContains( $attribute_value, $valid_sizes );
				} elseif ( $attribute_name === 'color' ) {
					// Color should be a valid color name
					$this->assertMatchesRegularExpression( '/^[A-Za-z\s]+$/', $attribute_value );
				} elseif ( $attribute_name === 'storage' ) {
					$valid_storage = array( '64GB', '128GB', '256GB', '512GB', '1TB' );
					$this->assertContains( $attribute_value, $valid_storage );
				}
			}
		}
	}

	/**
	 * Test get_supported_types method
	 */
	public function test_get_supported_types(): void {
		$supported_types = $this->generator->get_supported_types();

		$this->assertIsArray( $supported_types );
		$this->assertArrayHasKey( 'product_variations', $supported_types );
		$this->assertEquals( 'Product Variations with Attributes', $supported_types['product_variations'] );
	}

	/**
	 * Test get_description method
	 */
	public function test_get_description(): void {
		$description = $this->generator->get_description();

		$this->assertIsString( $description );
		$this->assertStringContainsString( 'variations', $description );
		$this->assertStringContainsString( 'attributes', $description );
	}

	/**
	 * Test generate_multiple method
	 */
	public function test_generate_multiple_method(): void {
		$count  = 5;
		$result = $this->generator->generate_multiple( $count );

		$this->assertIsArray( $result );
		$this->assertCount( $count, $result );

		foreach ( $result as $variation ) {
			$this->assertIsArray( $variation );
			$this->assertArrayHasKey( 'id', $variation );
			$this->assertArrayHasKey( 'product_id', $variation );
			$this->assertArrayHasKey( 'attributes', $variation );
		}
	}

	/**
	 * Test variation handles missing parent products gracefully
	 */
	public function test_handles_missing_products_gracefully(): void {
		// This test verifies that the generator handles the case where no parent products exist
		// The actual behavior depends on the implementation - it might return empty results or throw exceptions

		$result = $this->generator->generate( 1 );

		// Result should either be a valid array or a WP_Error, but not cause a fatal error
		$this->assertTrue(
			is_array( $result ) || is_wp_error( $result ),
			'Generator should handle missing products gracefully'
		);
	}
}
