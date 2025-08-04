<?php
/**
 * Product Generator
 *
 * @package EasyCommerceFakerPress\Generators
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Product;
use WP_Error;

/**
 * Product Generator Class
 *
 * Generates fake product data for EasyCommerce
 *
 * @since 1.0.0
 */
class Product_Generator extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'product';
	}

	/**
	 * Generate a single product
	 *
	 * @since 1.0.0
	 *
	 * @return array|WP_Error|false Single product data, error, or false on failure.
	 */
	protected function generate_single_item() {
		try {
			// Check if EasyCommerce Product class exists
			if ( ! class_exists( 'EasyCommerce\\Models\\Product' ) ) {
				return new WP_Error( 'missing_model', 'EasyCommerce Product model not found. Please ensure EasyCommerce plugin is active.' );
			}

			$product_title = $this->faker->words( 3, true );
			$gallery_images = $this->generateGalleryImages();
			$categories = $this->getOrCreateProductCategories();
			$brands = $this->getOrCreateProductBrands();
			
			// Use EasyCommerce Product model
			$product = new Product();
			$product_id = $product->create([
				'title'      => $product_title,
				'slug'       => sanitize_title( $product_title ),
				'content'    => $this->faker->paragraphs( 3, true ),
				'status'     => 'publish',
				'description' => $this->faker->paragraphs( 2, true ),
				'summary'    => $this->faker->sentence(),
				'thumbnail'  => 0, // Could add image generation later
				'categories' => array_slice( $categories, 0, $this->faker->numberBetween( 1, 3 ) ),
				'brands'     => array_slice( $brands, 0, 1 ),
				'attributes' => $this->generateProductAttributes(),
				'variations' => $this->generateProductVariations(),
				'meta'       => [
					'gallery' => $gallery_images,
					'template' => 'template-1',
				],
			]);

			if ( ! $product_id ) {
				return new WP_Error( 'product_creation_failed', 'Failed to create product using EasyCommerce model.' );
			}

			// Assign product tags
			$this->assignProductTags( $product_id );

			return [
				'id'         => $product_id,
				'title'      => $product_title,
				'variations' => count( $this->generateProductVariations() ),
				'categories' => count( $categories ),
				'brands'     => count( $brands )
			];

		} catch ( \Exception $e ) {
			$this->log( 'Product creation failed: ' . $e->getMessage(), 'error' );
			return new WP_Error( 'product_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate gallery images URLs
	 *
	 * @since 1.0.0
	 *
	 * @return array Gallery image URLs.
	 */
	private function generateGalleryImages(): array {
		$gallery_images = [];
		for ( $i = 0; $i < $this->faker->numberBetween( 1, 5 ); $i++ ) {
			$gallery_images[] = $this->faker->imageUrl( 800, 600, 'product' );
		}
		return $gallery_images;
	}

	/**
	 * Generate product variations data for EasyCommerce model
	 *
	 * @since 1.0.0
	 *
	 * @return array Variations data for product creation.
	 */
	private function generateProductVariations(): array {
		$variations = [];
		$variation_count = $this->faker->numberBetween( 1, 4 );

		for ( $i = 0; $i < $variation_count; $i++ ) {
			$regular_price = $this->faker->randomFloat( 2, 10, 1000 );
			$sale_price = $this->faker->optional( 0.3 )->randomFloat( 2, 5, $regular_price * 0.8 );
			
			$variations[] = [
				'name'           => $this->faker->words( 2, true ),
				'sku'            => $this->faker->unique()->regexify( '[A-Z]{3}[0-9]{6}' ),
				'type'           => $this->faker->randomElement( [ 'physical', 'digital' ] ),
				'regular_price'  => $regular_price,
				'sale_price'     => $sale_price,
				'stock_quantity' => $this->faker->numberBetween( 0, 100 ),
				'stock_limit'    => $this->faker->numberBetween( 5, 20 ),
				'status'         => 'publish',
				'attributes'     => $this->generateVariationAttributes(),
				'meta'           => [
					'weight' => $this->faker->randomFloat( 2, 0.1, 50 ),
					'dimensions' => [
						'length' => $this->faker->randomFloat( 2, 1, 100 ),
						'width'  => $this->faker->randomFloat( 2, 1, 100 ),
						'height' => $this->faker->randomFloat( 2, 1, 100 ),
					],
					'shipping_class' => $this->faker->randomElement( [ 'standard', 'express', 'overnight' ] ),
				],
				'downloads'      => [],
			];
		}

		return $variations;
	}

	/**
	 * Get or create product categories
	 *
	 * @since 1.0.0
	 *
	 * @return array Category names/IDs.
	 */
	private function getOrCreateProductCategories(): array {
		$categories = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'names',
			'number'     => 20
		] );

		if ( empty( $categories ) || is_wp_error( $categories ) ) {
			// Create default categories
			$default_categories = [ 'Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports', 'Toys' ];
			foreach ( $default_categories as $cat_name ) {
				wp_insert_term( $cat_name, 'product_cat' );
			}
			return $default_categories;
		}

		return is_array( $categories ) ? $categories : [];
	}

	/**
	 * Get or create product brands
	 *
	 * @since 1.0.0
	 *
	 * @return array Brand names/IDs.
	 */
	private function getOrCreateProductBrands(): array {
		$brands = get_terms( [
			'taxonomy'   => 'product_brand',
			'hide_empty' => false,
			'fields'     => 'names',
			'number'     => 10
		] );

		if ( empty( $brands ) || is_wp_error( $brands ) ) {
			// Create default brands
			$default_brands = [ 'Nike', 'Adidas', 'Apple', 'Samsung', 'Sony', 'Canon' ];
			foreach ( $default_brands as $brand_name ) {
				wp_insert_term( $brand_name, 'product_brand' );
			}
			return $default_brands;
		}

		return is_array( $brands ) ? $brands : [];
	}

	/**
	 * Generate product attributes for EasyCommerce model
	 *
	 * @since 1.0.0
	 *
	 * @return array Product attributes data.
	 */
	private function generateProductAttributes(): array {
		$attributes = [];
		$attribute_types = [ 'Color', 'Size', 'Material', 'Brand' ];

		foreach ( $attribute_types as $type ) {
			if ( $this->faker->boolean( 70 ) ) {
				$attributes[ $type ] = $this->getAttributeValuesForType( $type );
			}
		}

		return $attributes;
	}

	/**
	 * Generate variation-specific attributes
	 *
	 * @since 1.0.0
	 *
	 * @return array Variation attributes.
	 */
	private function generateVariationAttributes(): array {
		$attributes = [];
		$possible_attributes = [ 'Color', 'Size', 'Material' ];
		$selected_attributes = $this->get_random_elements( $possible_attributes, 1, 2 );

		foreach ( $selected_attributes as $attribute ) {
			$values = $this->getAttributeValuesForType( $attribute );
			$attributes[ $attribute ] = $this->faker->randomElement( $values );
		}

		return $attributes;
	}

	/**
	 * Get attribute values for a specific type
	 *
	 * @since 1.0.0
	 *
	 * @param string $attribute_name Attribute name.
	 *
	 * @return array Attribute values.
	 */
	private function getAttributeValuesForType( string $attribute_name ): array {
		switch ( $attribute_name ) {
			case 'Color':
				return [ 'Red', 'Blue', 'Green', 'Black', 'White', 'Yellow' ];
			case 'Size':
				return [ 'XS', 'S', 'M', 'L', 'XL', 'XXL' ];
			case 'Material':
				return [ 'Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Denim' ];
			case 'Brand':
				return [ $this->faker->company, $this->faker->company, $this->faker->company ];
			default:
				return [ $this->faker->word, $this->faker->word, $this->faker->word ];
		}
	}

	/**
	 * Assign product tags
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return void
	 */
	private function assignProductTags( int $product_id ): void {
		$tags = [];
		for ( $i = 0; $i < $this->faker->numberBetween( 2, 6 ); $i++ ) {
			$tags[] = $this->faker->word;
		}
		wp_set_object_terms( $product_id, $tags, 'product_tag' );
	}
}
