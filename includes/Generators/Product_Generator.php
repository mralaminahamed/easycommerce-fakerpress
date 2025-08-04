<?php
/**
 * Product Generator
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

use EasyCommerce\Models\Product;
use EasyCommerce\Models\Attribute;
use EasyCommerce\Models\Attribute_Value;
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

			$product_title = $this->generate_product_title();
			$product_type  = $this->faker->randomElement( array( 'physical', 'digital' ) );
			$categories    = $this->get_or_create_product_categories();
			$brands        = $this->get_or_create_product_brands();
			$attributes    = $this->get_or_create_product_attributes( $product_type );
			$variations    = $this->generate_product_variations( $attributes, $product_type );

			// Use EasyCommerce Product model with complete data structure.
			$product    = new Product();
			$product_id = $product->create(
				array(
					// Required fields
					'title'       => $product_title,

					// Optional core fields
					'slug'        => sanitize_title( $product_title . '-' . uniqid( '', true ) ),
					'content'     => $this->generate_product_description(),
					'status'      => $this->faker->randomElement( array( 'publish', 'draft' ) ),
					'description' => $this->generate_short_description(),
					'summary'     => $this->faker->sentence( 40 ),
					'thumbnail'   => 0, // Could integrate with media library later

					// Taxonomy relationships
					'categories'  => array_slice( $categories, 0, $this->faker->numberBetween( 1, 3 ) ),
					'brands'      => array_slice( $brands, 0, 1 ),

					// Product attributes and variations
					'attributes'  => $attributes,
					'variations'  => $variations,

					// Additional meta data
					'meta'        => array(
						'gallery'         => $this->generate_gallery_images(),
						'template'        => $this->faker->randomElement( array( 'template-1', 'template-2', 'default' ) ),
						'featured'        => $this->faker->boolean( 20 ),
						'seo_title'       => $product_title . ' - ' . $this->faker->words( 2, true ),
						'seo_description' => $this->faker->sentence( 15 ),
					),
				)
			);

			if ( ! $product_id ) {
				return new WP_Error( 'product_creation_failed', 'Failed to create product using EasyCommerce model.' );
			}

			// Assign product tags after creation
			$this->assign_product_tags( $product_id );

			return array(
				'id'          => $product_id,
				'title'       => $product_title,
				'type'        => $product_type,
				'variations'  => count( $variations ),
				'categories'  => count( $categories ),
				'brands'      => count( $brands ),
				'attributes'  => count( $attributes ),
				'price_range' => $this->get_price_range( $variations ),
			);
		} catch ( Exception $e ) {
			$this->log( 'Product creation failed: ' . $e->getMessage(), 'error' );

			return new WP_Error( 'product_creation_failed', $e->getMessage() );
		}
	}

	/**
	 * Generate realistic product title
	 *
	 * @since 1.0.0
	 *
	 * @return string Product title.
	 */
	private function generate_product_title(): string {
		$product_types = array(
			'Premium',
			'Deluxe',
			'Professional',
			'Classic',
			'Modern',
			'Vintage',
			'Ultra',
			'Advanced',
			'Standard',
			'Essential',
			'Limited Edition',
		);

		$product_names = array(
			'Wireless Headphones',
			'Smart Watch',
			'Bluetooth Speaker',
			'Gaming Mouse',
			'Laptop Stand',
			'Coffee Maker',
			'Water Bottle',
			'Backpack',
			'Phone Case',
			'Desk Lamp',
			'Keyboard',
			'Monitor',
			'Tablet',
			'Camera',
			'Fitness Tracker',
			'Power Bank',
			'Wireless Charger',
			'USB Cable',
			'Screen Protector',
			'Car Mount',
		);

		return $this->faker->randomElement( $product_types ) . ' ' . $this->faker->randomElement( $product_names );
	}

	/**
	 * Generate detailed product description
	 *
	 * @since 1.0.0
	 *
	 * @return string Product description.
	 */
	private function generate_product_description(): string {
		$paragraphs = array();

		// Feature paragraph
		$paragraphs[] = 'Experience the perfect blend of innovation and functionality with this exceptional product. ' .
						$this->faker->sentence( 12 ) . ' ' . $this->faker->sentence( 10 );

		// Benefits paragraph
		$paragraphs[] = 'Designed with the modern user in mind, this product offers unparalleled performance and reliability. ' .
						$this->faker->sentence( 8 ) . ' ' . $this->faker->sentence( 15 );

		// Technical paragraph
		$paragraphs[] = 'Built using premium materials and cutting-edge technology, ensuring long-lasting durability. ' .
						$this->faker->sentence( 10 ) . ' ' . $this->faker->sentence( 12 );

		return implode( "\n\n", $paragraphs );
	}

	/**
	 * Generate short product description
	 *
	 * @since 1.0.0
	 *
	 * @return string Short description.
	 */
	private function generate_short_description(): string {
		return $this->faker->sentence( 120 ) . ' Perfect for ' .
				$this->faker->randomElement( array( 'professionals', 'students', 'gamers', 'home use', 'office work' ) ) . '.';
	}

	/**
	 * Get price range from variations
	 *
	 * @since 1.0.0
	 *
	 * @param array $variations Product variations.
	 *
	 * @return string Price range.
	 */
	private function get_price_range( array $variations ): string {
		if ( empty( $variations ) ) {
			return '$0.00';
		}

		$prices    = array_column( $variations, 'regular_price' );
		$min_price = min( $prices );
		$max_price = max( $prices );

		if ( $min_price === $max_price ) {
			return '$' . number_format( $min_price, 2 );
		}

		return '$' . number_format( $min_price, 2 ) . ' - $' . number_format( $max_price, 2 );
	}

	/**
	 * Generate gallery images for product
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of image IDs for product gallery.
	 */
	private function generate_gallery_images(): array {
		$gallery_images = array();
		$image_count    = $this->faker->numberBetween( 2, 6 );

		for ( $i = 0; $i < $image_count; $i++ ) {
			// In a real implementation, you might upload images to WordPress media library.
			// For now, we'll just use placeholder image URLs.
			$gallery_images[] = array(
				'id'          => 0, // Would be WordPress attachment ID.
				'url'         => $this->faker->imageUrl( 800, 600, 'products' ),
				'alt'         => $this->faker->words( 3, true ),
				'caption'     => $this->faker->sentence( 6 ),
				'description' => $this->faker->sentence( 10 ),
			);
		}

		return $gallery_images;
	}

	/**
	 * Get or create product attributes using EasyCommerce attribute system
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_type Product type (physical/digital).
	 *
	 * @return array Array of product attributes with their possible values.
	 */
	private function get_or_create_product_attributes( string $product_type = 'physical' ): array {
		$attributes = array();

		if ( $product_type === 'physical' ) {
			// Physical product attributes
			$possible_attributes = array(
				'color'    => array(
					'name'   => 'Color',
					'type'   => 'text',
					'values' => array( 'red', 'blue', 'green', 'black', 'white', 'gray', 'silver', 'gold' )
				),
				'size'     => array(
					'name'   => 'Size',
					'type'   => 'text',
					'values' => array( 'small', 'medium', 'large', 'extra-large' )
				),
				'material' => array(
					'name'   => 'Material',
					'type'   => 'text',
					'values' => array( 'plastic', 'metal', 'wood', 'glass', 'ceramic', 'fabric' )
				),
				'style'    => array(
					'name'   => 'Style',
					'type'   => 'text',
					'values' => array( 'modern', 'classic', 'vintage', 'minimalist', 'premium' )
				),
			);
		} else {
			// Digital product attributes
			$possible_attributes = array(
				'format'   => array(
					'name'   => 'Format',
					'type'   => 'text',
					'values' => array( 'pdf', 'video', 'audio', 'software' )
				),
				'license'  => array(
					'name'   => 'License',
					'type'   => 'text',
					'values' => array( 'personal', 'commercial', 'extended' )
				),
				'version'  => array(
					'name'   => 'Version',
					'type'   => 'text',
					'values' => array( 'basic', 'pro', 'enterprise' )
				),
				'platform' => array(
					'name'   => 'Platform',
					'type'   => 'text',
					'values' => array( 'windows', 'mac', 'linux', 'web', 'mobile' )
				),
			);
		}

		// Select 2-3 random attributes
		$selected_attributes = $this->faker->randomElements(
			array_keys( $possible_attributes ),
			$this->faker->numberBetween( 2, 3 )
		);

		foreach ( $selected_attributes as $attribute_slug ) {
			$attribute_info = $possible_attributes[ $attribute_slug ];
			
			// Create or get the attribute using EasyCommerce models
			$attribute_id = $this->get_or_create_attribute( 
				$attribute_info['name'], 
				$attribute_info['type'], 
				$attribute_slug 
			);

			if ( $attribute_id ) {
				// Create or get attribute values
				$attribute_values = array();
				foreach ( $attribute_info['values'] as $value ) {
					$value_id = $this->get_or_create_attribute_value( 
						$attribute_id, 
						ucfirst( $value ), 
						$value 
					);
					if ( $value_id ) {
						$attribute_values[] = $value;
					}
				}

				if ( ! empty( $attribute_values ) ) {
					$attributes[ $attribute_slug ] = $attribute_values;
				}
			}
		}

		return $attributes;
	}

	/**
	 * Get or create an attribute using EasyCommerce Attribute model
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Attribute name.
	 * @param string $type Attribute type.
	 * @param string $slug Attribute slug.
	 *
	 * @return int|false Attribute ID or false on failure.
	 */
	private function get_or_create_attribute( string $name, string $type, string $slug ) {
		try {
			$attribute_model = new Attribute();
			
			// Check if attribute already exists
			$existing_attribute = $attribute_model->get_by_slug( $slug );
			if ( $existing_attribute ) {
				return $existing_attribute->id;
			}

			// Create new attribute
			return $attribute_model->add( $name, $type, $slug );
		} catch ( Exception $e ) {
			$this->log( 'Failed to create attribute: ' . $e->getMessage(), 'error' );
			return false;
		}
	}

	/**
	 * Get or create an attribute value using EasyCommerce Attribute_Value model
	 *
	 * @since 1.0.0
	 *
	 * @param int    $attribute_id Attribute ID.
	 * @param string $name         Value name.
	 * @param string $value        Value slug/value.
	 *
	 * @return int|false Attribute value ID or false on failure.
	 */
	private function get_or_create_attribute_value( int $attribute_id, string $name, string $value ) {
		try {
			$value_model = new Attribute_Value();
			$value_slug  = sanitize_title( $value );
			
			// Check if value already exists
			$existing_value = $value_model->get_by_slug( $value_slug );
			if ( $existing_value && $existing_value->attribute_id == $attribute_id ) {
				return $existing_value->id;
			}

			// Create new attribute value
			return $value_model->add( $attribute_id, $name, $value, $value_slug );
		} catch ( Exception $e ) {
			$this->log( 'Failed to create attribute value: ' . $e->getMessage(), 'error' );
			return false;
		}
	}

	/**
	 * Generate product variations based on attributes
	 *
	 * @since 1.0.0
	 *
	 * @param array  $attributes   Product attributes.
	 * @param string $product_type Product type (physical/digital).
	 *
	 * @return array Array of product variations with pricing and stock information.
	 */
	private function generate_product_variations( array $attributes, string $product_type = 'physical' ): array {
		$variations  = array();
		$base_price  = $this->faker->randomFloat( 2, 10, 500 );
		$price_range = $base_price * 0.2; // 20% price variation

		// Generate combinations of attributes
		$attribute_keys   = array_keys( $attributes );
		$attribute_values = array_values( $attributes );

		// Limit to reasonable number of variations
		$max_variations = min( 12, array_product( array_map( 'count', $attribute_values ) ) );
		$generated      = 0;

		// Generate cartesian product of attributes
		foreach ( $this->cartesian_product( $attribute_values ) as $combination ) {
			if ( $generated >= $max_variations ) {
				break;
			}

			$variation_attributes = array();
			$variation_name_parts = array();

			foreach ( $combination as $index => $value ) {
				$attribute_name                          = $attribute_keys[ $index ];
				$variation_attributes[ $attribute_name ] = $value;
				$variation_name_parts[]                  = ucfirst( $value );
			}

			$variation_name = implode( ' - ', $variation_name_parts );
			$price_modifier = $this->faker->randomFloat( 2, -$price_range, $price_range );
			$regular_price  = max( 1, $base_price + $price_modifier );
			$sale_price     = $this->faker->boolean( 30 ) ? $regular_price * $this->faker->randomFloat( 2, 0.7, 0.9 ) : null;
			$stock_quantity = $product_type === 'physical' ? $this->faker->optional( 0.8 )->numberBetween( 0, 100 ) : null;

			$variation = array(
				'name'           => $variation_name,
				'sku'            => $this->generate_unique_sku(),
				'type'           => $product_type,
				'regular_price'  => $regular_price,
				'sale_price'     => $sale_price,
				'stock_quantity' => $stock_quantity,
				'stock_limit'    => $this->faker->numberBetween( 5, 20 ),
				'status'         => $this->determine_stock_status( $stock_quantity ),
				'attributes'     => $variation_attributes,
				'meta'           => $this->generate_variation_meta( $product_type ),
			);

			// Add downloads for digital products
			if ( $product_type === 'digital' ) {
				$variation['downloads'] = $this->generate_digital_downloads();
			}

			$variations[] = $variation;
			++$generated;
		}

		return $variations;
	}

	/**
	 * Determine stock status based on quantity
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $stock_quantity Stock quantity.
	 *
	 * @return string Stock status.
	 */
	private function determine_stock_status( $stock_quantity ): string {
		if ( is_null( $stock_quantity ) ) {
			return 'in_stock'; // Digital products or unlimited stock
		}

		if ( $stock_quantity > 10 ) {
			return 'in_stock';
		} elseif ( $stock_quantity > 0 ) {
			return $this->faker->randomElement( array( 'in_stock', 'backorder' ) );
		} else {
			return 'out_of_stock';
		}
	}

	/**
	 * Generate variation meta data
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_type Product type.
	 *
	 * @return array Variation meta data.
	 */
	private function generate_variation_meta( string $product_type ): array {
		$meta = array(
			'tax_class'        => $this->faker->numberBetween( 1, 3 ),
			'is_managed_stock' => $product_type === 'physical',
		);

		if ( $product_type === 'physical' ) {
			$meta = array_merge(
				$meta,
				array(
					'weight'            => array(
						'value' => $this->faker->randomFloat( 2, 0.1, 5.0 ),
						'unit'  => 'kg',
					),
					'height'            => array(
						'value' => $this->faker->randomFloat( 2, 1, 30 ),
						'unit'  => 'cm',
					),
					'width'             => array(
						'value' => $this->faker->randomFloat( 2, 5, 50 ),
						'unit'  => 'cm',
					),
					'length'            => array(
						'value' => $this->faker->randomFloat( 2, 5, 50 ),
						'unit'  => 'cm',
					),
					'requires_shipping' => true,
				)
			);
		} else {
			$meta['requires_shipping'] = false;
			$meta['download_limit']    = $this->faker->optional( 0.7 )->numberBetween( 1, 10 );
			$meta['download_expiry']   = $this->faker->optional( 0.5 )->numberBetween( 1, 365 ); // days
		}

		return $meta;
	}

	/**
	 * Generate digital downloads for digital products
	 *
	 * @since 1.0.0
	 *
	 * @return array Digital download files.
	 */
	private function generate_digital_downloads(): array {
		$download_types = array(
			'Software License Key',
			'PDF Guide',
			'Video Tutorial',
			'Audio File',
			'Template Pack',
			'Digital Asset',
		);

		$downloads = array();
		$count     = $this->faker->numberBetween( 1, 3 );

		for ( $i = 0; $i < $count; $i++ ) {
			$downloads[] = array(
				'media_id'  => 0, // Would reference WordPress media library
				'name'      => $this->faker->randomElement( $download_types ),
				'file_url'  => '', // Would be populated with actual file URL
				'file_size' => $this->faker->numberBetween( 1024, 104857600 ), // 1KB to 100MB
			);
		}

		return $downloads;
	}

	/**
	 * Generate a cartesian product of arrays
	 *
	 * @since 1.0.0
	 *
	 * @param array $arrays Arrays to combine.
	 *
	 * @return array Cartesian product.
	 */
	private function cartesian_product( array $arrays ): array {
		$result = array( array() );

		foreach ( $arrays as $array ) {
			$temp = array();
			foreach ( $result as $r ) {
				foreach ( $array as $item ) {
					$temp[] = array_merge( $r, array( $item ) );
				}
			}
			$result = $temp;
		}

		return $result;
	}

	/**
	 * Generate unique SKU for product variation
	 *
	 * @since 1.0.0
	 *
	 * @return string Unique SKU.
	 */
	private function generate_unique_sku(): string {
		$prefix = strtoupper( $this->faker->lexify( '???' ) );
		$number = $this->faker->unique()->numberBetween( 1000, 9999 );

		return $prefix . '-' . $number;
	}

	/**
	 * Get or create product categories
	 *
	 * @since 1.0.0
	 *
	 * @return array Category IDs.
	 */
	private function get_or_create_product_categories(): array {
		// Use WordPress taxonomy for product categories
		$category_names = array(
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
		foreach ( $category_names as $category_name ) {
			$term = get_term_by( 'name', $category_name, 'product_cat' );
			if ( ! $term ) {
				$term_result = wp_insert_term(
					$category_name,
					'product_cat',
					array(
						'description' => $this->faker->sentence(),
						'slug'        => sanitize_title( $category_name ),
					)
				);

				if ( ! is_wp_error( $term_result ) ) {
					$category_ids[] = $term_result['term_id'];
				}
			} else {
				$category_ids[] = $term->term_id;
			}
		}

		return $this->faker->randomElements( $category_ids, $this->faker->numberBetween( 1, 3 ) );
	}

	/**
	 * Get or create product brands
	 *
	 * @since 1.0.0
	 *
	 * @return array Brand IDs.
	 */
	private function get_or_create_product_brands(): array {
		// Use WordPress taxonomy for product brands
		$brand_names = array(
			'TechCorp',
			'InnovateCo',
			'QualityBrand',
			'PremiumLine',
			'ModernTech',
			'ClassicDesign',
			'FutureTech',
			'ProSeries',
		);

		$brand_ids = array();
		foreach ( $brand_names as $brand_name ) {
			$term = get_term_by( 'name', $brand_name, 'product_brand' );
			if ( ! $term ) {
				$term_result = wp_insert_term(
					$brand_name,
					'product_brand',
					array(
						'description' => $this->faker->company . ' - ' . $this->faker->sentence(),
						'slug'        => sanitize_title( $brand_name ),
					)
				);

				if ( ! is_wp_error( $term_result ) ) {
					$brand_ids[] = $term_result['term_id'];
				}
			} else {
				$brand_ids[] = $term->term_id;
			}
		}

		return array_slice( $brand_ids, 0, 1 ); // Return only one brand
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
	private function assign_product_tags( int $product_id ): void {
		$tags = array(
			'new',
			'popular',
			'featured',
			'bestseller',
			'trending',
			'premium',
			'limited',
			'exclusive',
			'sale',
			'clearance',
		);

		$selected_tags = $this->faker->randomElements( $tags, $this->faker->numberBetween( 1, 4 ) );
		wp_set_post_terms( $product_id, $selected_tags, 'product_tag' );
	}
}
