<?php
/**
 * Product Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerce\Models\Product;
use EasyCommerceFakerPress\Abstracts\Generator;
use Exception;
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
	 * @return array|WP_Error Single product data, error, or false on failure.
	 */
	protected function generate_single_item() {
		try {
			// Check if EasyCommerce Product class exists.
			if ( ! class_exists( Product::class ) ) {
				return new WP_Error( 'missing_model', 'EasyCommerce Product model not found. Please ensure EasyCommerce plugin is active.' );
			}

			$product_title  = $this->faker->words( 3, true );
			$gallery_images = $this->generate_gallery_images();
			$categories     = $this->get_or_create_product_categories();
			$brands         = $this->get_or_create_product_brands();

			// Use EasyCommerce Product model.
			$product    = new Product();
			$product_id = $product->create(
				array(
					'title'       => $product_title,
					'slug'        => sanitize_title( $product_title ),
					'content'     => $this->faker->paragraphs( 3, true ),
					'status'      => 'publish',
					'description' => $this->faker->paragraphs( 2, true ),
					'summary'     => $this->faker->sentence(),
					'thumbnail'   => 0, // Could add image generation later.
					'categories'  => array_slice( $categories, 0, $this->faker->numberBetween( 1, 3 ) ),
					'brands'      => array_slice( $brands, 0, 1 ),
					'attributes'  => $this->generate_product_attributes(),
					'variations'  => $this->generate_product_variations(),
					'meta'        => array(
						'gallery'  => $gallery_images,
						'template' => 'template-1',
					),
				)
			);

			if ( ! $product_id ) {
				return new WP_Error( 'product_creation_failed', 'Failed to create product using EasyCommerce model.' );
			}

			// Assign product tags.
			$this->assign_product_tags( $product_id );

			return array(
				'id'         => $product_id,
				'title'      => $product_title,
				'variations' => count( $this->generate_product_variations() ),
				'categories' => count( $categories ),
				'brands'     => count( $brands ),
			);
		} catch ( Exception $e ) {
			$this->log( 'Product creation failed: ' . $e->getMessage(), 'error' );

			return new WP_Error( 'product_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate gallery images for product
	 *
	 * Creates an array of fake image URLs to be used as product gallery images.
	 *
	 * @since 1.0.0
	 *
	 * @return array Gallery image URLs.
	 */
	private function generate_gallery_images(): array {
		$gallery_images = array();
		for ( $i = 0; $i < $this->faker->numberBetween( 1, 5 ); $i++ ) {
			$gallery_images[] = array(
				'id'          => $this->faker->unique()->numberBetween( 1000, 9999 ),
				'url'         => $this->faker->imageUrl( 800, 800, 'product' ),
				'alt'         => $this->faker->sentence( 6 ),
				'caption'     => $this->faker->sentence( 10 ),
				'description' => $this->faker->paragraph( 2 ),
			);
		}

		return $gallery_images;
	}

	/**
	 * Generate product variations data for EasyCommerce model
	 *
	 * Creates multiple product variations with different attributes, prices, and stock levels.
	 *
	 * @since 1.0.0
	 *
	 * @return array Variations data for product creation.
	 */
	private function generate_product_variations(): array {
		$variation_count = $this->faker->numberBetween( 1, 4 );
		$variations      = array();

		$base_price      = $this->faker->randomFloat( 2, 10, 500 );
		$variation_attrs = $this->generate_variation_attributes();

		for ( $i = 0; $i < $variation_count; $i++ ) {
			$price_modifier = $this->faker->randomFloat( 2, 0.8, 1.3 );
			$final_price    = $base_price * $price_modifier;

			$variations[] = array(
				'sku'          => $this->faker->unique()->ean8,
				'price'        => number_format( $final_price, 2, '.', '' ),
				'sale_price'   => $this->faker->optional( 0.3 )->numberBetween( $final_price * 0.7, $final_price * 0.9 ),
				'stock'        => $this->faker->numberBetween( 0, 100 ),
				'status'       => $this->faker->randomElement( array( 'in_stock', 'out_of_stock', 'backorder' ) ),
				'weight'       => $this->faker->optional( 0.8 )->randomFloat( 2, 0.1, 10 ),
				'dimensions'   => array(
					'length' => $this->faker->randomFloat( 2, 1, 50 ),
					'width'  => $this->faker->randomFloat( 2, 1, 50 ),
					'height' => $this->faker->randomFloat( 2, 1, 50 ),
				),
				'attributes'   => $this->get_random_variation_attributes( $variation_attrs ),
				'image'        => $this->faker->imageUrl( 600, 600, 'product' ),
				'downloadable' => $this->faker->boolean( 10 ),
				'virtual'      => $this->faker->boolean( 15 ),
				'manage_stock' => $this->faker->boolean( 80 ),
				'tax_status'   => $this->faker->randomElement( array( 'taxable', 'shipping', 'none' ) ),
				'tax_class'    => $this->faker->randomElement( array( 'standard', 'reduced-rate', 'zero-rate' ) ),
			);
		}

		return $variations;
	}

	/**
	 * Get or create product categories
	 *
	 * Retrieves existing product categories or creates new ones if none exist.
	 *
	 * @since 1.0.0
	 *
	 * @return array Category IDs.
	 */
	private function get_or_create_product_categories(): array {
		$categories_table = $this->wpdb->prefix . 'product_categories';

		// Check if categories exist.
		$existing_categories = $this->wpdb->get_col( "SELECT id FROM {$categories_table} LIMIT 10" );

		if ( ! empty( $existing_categories ) ) {
			return $this->faker->randomElements( $existing_categories, $this->faker->numberBetween( 1, 3 ) );
		}

		// Create some default categories.
		$default_categories = array(
			'Electronics',
			'Clothing',
			'Books',
			'Home & Garden',
			'Sports & Outdoors',
			'Health & Beauty',
			'Toys & Games',
			'Automotive',
			'Food & Beverage',
			'Jewelry',
		);

		$category_ids = array();
		foreach ( $default_categories as $category_name ) {
			$this->wpdb->insert(
				$categories_table,
				array(
					'name'        => $category_name,
					'slug'        => sanitize_title( $category_name ),
					'description' => $this->faker->sentence(),
					'image'       => $this->faker->imageUrl( 300, 300, 'category' ),
					'created_at'  => wp_date( 'Y-m-d H:i:s' ),
				)
			);
			$category_ids[] = $this->wpdb->insert_id;
		}

		return $this->faker->randomElements( $category_ids, $this->faker->numberBetween( 1, 3 ) );
	}

	/**
	 * Get or create product brands
	 *
	 * Retrieves existing product brands or creates new ones if none exist.
	 *
	 * @since 1.0.0
	 *
	 * @return array Brand IDs.
	 */
	private function get_or_create_product_brands(): array {
		$brands_table = $this->wpdb->prefix . 'product_brands';

		// Check if brands exist.
		$existing_brands = $this->wpdb->get_col( "SELECT id FROM {$brands_table} LIMIT 10" );

		if ( ! empty( $existing_brands ) ) {
			return array( $this->faker->randomElement( $existing_brands ) );
		}

		// Create some default brands.
		$default_brands = array(
			'TechCorp',
			'StyleMaker',
			'HomeComfort',
			'SportsPro',
			'EcoFriendly',
			'LuxuryLife',
			'BudgetBest',
			'QualityFirst',
			'Innovation Labs',
			'Classic Brand',
		);

		$brand_ids = array();
		foreach ( $default_brands as $brand_name ) {
			$this->wpdb->insert(
				$brands_table,
				array(
					'name'        => $brand_name,
					'slug'        => sanitize_title( $brand_name ),
					'description' => $this->faker->company() . ' - ' . $this->faker->catchPhrase(),
					'logo'        => $this->faker->imageUrl( 200, 100, 'logo' ),
					'website'     => $this->faker->url(),
					'created_at'  => wp_date( 'Y-m-d H:i:s' ),
				)
			);
			$brand_ids[] = $this->wpdb->insert_id;
		}

		return array( $this->faker->randomElement( $brand_ids ) );
	}

	/**
	 * Generate product attributes
	 *
	 * Creates various product attributes like color, size, material, etc.
	 *
	 * @since 1.0.0
	 *
	 * @return array Product attributes.
	 */
	private function generate_product_attributes(): array {
		$attribute_types = array( 'Color', 'Size', 'Material', 'Brand', 'Style' );
		$selected_types  = $this->faker->randomElements( $attribute_types, $this->faker->numberBetween( 2, 4 ) );

		$attributes = array();
		foreach ( $selected_types as $type ) {
			$values              = $this->get_attribute_values_for_type( $type );
			$attributes[ $type ] = $this->faker->randomElements( $values, $this->faker->numberBetween( 1, 3 ) );
		}

		return $attributes;
	}

	/**
	 * Generate variation attributes
	 *
	 * Creates attributes that can be used for product variations.
	 *
	 * @since 1.0.0
	 *
	 * @return array Variation attributes.
	 */
	private function generate_variation_attributes(): array {
		$variation_types = array( 'Size', 'Color' );
		$attributes      = array();

		foreach ( $variation_types as $type ) {
			if ( $this->faker->boolean( 70 ) ) {
				$values              = $this->get_attribute_values_for_type( $type );
				$attributes[ $type ] = $this->faker->randomElements( $values, $this->faker->numberBetween( 2, 4 ) );
			}
		}

		return $attributes;
	}

	/**
	 * Get attribute values for specific attribute type
	 *
	 * Returns predefined values for different attribute types.
	 *
	 * @since 1.0.0
	 *
	 * @param string $attribute_name The attribute type name.
	 *
	 * @return array Attribute values.
	 */
	private function get_attribute_values_for_type( string $attribute_name ): array {
		$attribute_values = array(
			'Color'    => array( 'Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple', 'Orange', 'Pink', 'Gray' ),
			'Size'     => array( 'XS', 'S', 'M', 'L', 'XL', 'XXL', '32', '34', '36', '38', '40', '42' ),
			'Material' => array( 'Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Metal', 'Plastic', 'Wood', 'Glass' ),
			'Brand'    => array( 'Premium', 'Standard', 'Economy', 'Luxury', 'Sport', 'Casual', 'Professional' ),
			'Style'    => array( 'Modern', 'Classic', 'Vintage', 'Contemporary', 'Traditional', 'Minimalist', 'Rustic' ),
		);

		return $attribute_values[ $attribute_name ] ?? array();
	}

	/**
	 * Assign product tags to a product
	 *
	 * Creates and assigns relevant tags to the product.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id The product ID to assign tags to.
	 *
	 * @return void
	 */
	private function assign_product_tags( int $product_id ): void {
		$product_tags_table = $this->wpdb->prefix . 'product_tags';

		$tag_names = array(
			'New Arrival',
			'Best Seller',
			'Sale',
			'Limited Edition',
			'Popular',
			'Featured',
			'Trending',
			'Eco-Friendly',
			'Premium Quality',
			'Budget Friendly',
			'Gift Idea',
			'Seasonal',
			'Exclusive',
			'Top Rated',
			'Customer Favorite',
		);

		$selected_tags = $this->faker->randomElements( $tag_names, $this->faker->numberBetween( 1, 4 ) );

		foreach ( $selected_tags as $tag_name ) {
			// Check if tag exists.
			$existing_tag = $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT id FROM {$product_tags_table} WHERE name = %s",
					$tag_name
				)
			);

			if ( ! $existing_tag ) {
				// Create new tag.
				$this->wpdb->insert(
					$product_tags_table,
					array(
						'name'        => $tag_name,
						'slug'        => sanitize_title( $tag_name ),
						'description' => $this->faker->sentence(),
						'created_at'  => wp_date( 'Y-m-d H:i:s' ),
					)
				);
				$tag_id = $this->wpdb->insert_id;
			} else {
				$tag_id = $existing_tag;
			}

			// Assign tag to product.
			$product_tag_relations_table = $this->wpdb->prefix . 'product_tag_relations';
			$this->wpdb->replace(
				$product_tag_relations_table,
				array(
					'product_id' => $product_id,
					'tag_id'     => $tag_id,
				)
			);
		}
	}

	/**
	 * Get random variation attributes for a specific variation
	 *
	 * Selects random attribute values from the available variation attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $variation_attrs Available variation attributes.
	 *
	 * @return array Selected variation attributes.
	 */
	private function get_random_variation_attributes( array $variation_attrs ): array {
		return array_map(
			function ( $attr_values ) {
				return $this->faker->randomElement( $attr_values );
			},
			$variation_attrs
		);
	}
}
