<?php
/**
 * Product Generator.
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
 * Generates realistic fake product data for EasyCommerce
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
		// Check if EasyCommerce Product class exists.
		if ( ! class_exists( Product::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Product model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		$product_type  = $this->get_faker()->randomElement( array( 'physical', 'digital' ) );
		$product_title = $this->generate_product_title( $product_type );
		$categories    = $this->get_or_create_product_categories();
		$brands        = $this->get_or_create_product_brands();
		$attributes    = $this->get_or_create_product_attributes( $product_type );
		$variations    = $this->generate_product_variations( $attributes, $product_type );

		// Use EasyCommerce Product model with compatible data structure.
		$product = new Product();

		// Prepare variations with proper structure for EasyCommerce model.
		$formatted_variations = $this->format_variations_for_model( $variations, $attributes );

		$product_id = $product->create(
			array(
				// Required fields.
				'title'       => $product_title,

				// Optional core fields.
				'slug'        => sanitize_title( $product_title . '-' . uniqid( '', true ) ),
				'content'     => $this->generate_product_description( $product_type ),
				'status'      => $this->get_faker()->randomElement( array( 'publish', 'draft', 'pending' ) ),
				'description' => $this->generate_short_description( $product_type ),
				'summary'     => $this->get_faker()->sentence( 20, true ),
				'thumbnail'   => $this->generate_thumbnail(),

				// Taxonomy relationships.
				'categories'  => array_slice( $categories, 0, $this->get_faker()->numberBetween( 1, 4 ) ),
				'brands'      => array_slice( $brands, 0, $this->get_faker()->numberBetween( 1, 2 ) ),

				// Product attributes and variations.
				'attributes'  => $attributes,
				'variations'  => $formatted_variations,

				// Additional meta data.
				'meta'        => array(
					'gallery'         => $this->generate_gallery_images(),
					'template'        => $this->get_faker()->randomElement(
						array(
							'template-standard',
							'template-premium',
							'template-minimal',
						)
					),
					'featured'        => $this->get_faker()->boolean( 25 ),
					'seo_title'       => $product_title . ' | ' . $this->get_faker()->company,
					'seo_description' => $this->get_faker()->sentence( 15, true ),
					'seo_keywords'    => implode( ', ', (array) $this->faker->words( 5 ) ),
					'sku_prefix'      => strtoupper( $this->faker->lexify( '???' ) ),
					'release_date'    => $this->faker->dateTimeThisYear()->format( 'Y-m-d' ),
					'warranty'        => 'physical' === $product_type ? $this->faker->randomElement(
						array(
							'1 year',
							'2 years',
							'Limited Lifetime',
						)
					) : null,
					'shipping_class'  => 'physical' === $product_type ? $this->faker->randomElement(
						array(
							'standard',
							'expedited',
							'fragile',
						)
					) : null,
				),
			)
		);

		if ( ! $product_id ) {
			return new WP_Error( 'product_creation_failed', __( 'Failed to create product using EasyCommerce model.', 'easycommerce-fakerpress' ) );
		}

		// Assign product tags after creation.
		$this->assign_product_tags( $product_id );

		// Get aggregated stock using Product model.
		$product_instance = new Product( $product_id );
		$total_stock      = $product_instance->get_stock();

		return array(
			'id'           => $product_id,
			'title'        => $product_title,
			'type'         => $product_type,
			'variations'   => count( $variations ),
			'categories'   => count( $categories ),
			'brands'       => count( $brands ),
			'attributes'   => count( $attributes ),
			'price_range'  => $this->get_price_range( $variations ),
			'total_stock'  => $total_stock,
			'stock_status' => $this->determine_stock_status( $total_stock ),
		);
	}

	/**
	 * Generate realistic product title based on product type
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_type Product type (physical/digital).
	 *
	 * @return string Product title.
	 */
	private function generate_product_title( string $product_type ): string {
		$adjectives = array(
			'Premium',
			'Deluxe',
			'Professional',
			'Classic',
			'Modern',
			'Vintage',
			'Ultra',
			'Advanced',
			'Eco-Friendly',
			'Smart',
			'Portable',
			'Ergonomic',
			'High-Performance',
		);

		$physical_products = array(
			'Wireless Headphones',
			'Smart Watch',
			'Bluetooth Speaker',
			'Gaming Mouse',
			'Laptop Stand',
			'Coffee Maker',
			'Insulated Water Bottle',
			'Travel Backpack',
			'Phone Case',
			'LED Desk Lamp',
			'Mechanical Keyboard',
			'4K Monitor',
			'Tablet Pro',
			'DSLR Camera',
			'Fitness Tracker',
			'Power Bank',
			'Wireless Charger',
			'USB-C Cable',
			'Tempered Glass Screen Protector',
			'Car Phone Mount',
		);

		$digital_products = array(
			'Productivity Software',
			'Graphic Design Suite',
			'Video Editing Software',
			'E-Learning Course',
			'Digital Planner',
			'Music Production Plugin',
			'Website Template',
			'Stock Photo Bundle',
			'Mobile App',
			'Game Asset Pack',
			'E-Book',
			'Audio Book',
			'Virtual Workshop',
			'Coding Tutorial Series',
		);

		$product_names = 'physical' === $product_type ? $physical_products : $digital_products;
		$brand_prefix  = $this->faker->randomElement( array( '', $this->faker->company . ' ' ) );

		return $brand_prefix . $this->faker->randomElement( $adjectives ) . ' ' . $this->faker->randomElement( $product_names );
	}

	/**
	 * Generate detailed product description
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_type Product type (physical/digital).
	 *
	 * @return string Product description.
	 */
	private function generate_product_description( string $product_type ): string {
		$paragraphs = array();

		// Introduction paragraph.
		$paragraphs[] = 'Discover the ultimate ' . ( 'physical' === $product_type ? 'product' : 'digital solution' ) . ' designed to ' .
						$this->faker->randomElement(
							array(
								'elevate your experience',
								'enhance your productivity',
								'simplify your daily tasks',
								'redefine convenience',
							)
						) . '. ' .
						$this->faker->sentence( 50, true );

		// Features paragraph.
		$features     = 'physical' === $product_type ?
			array(
				'durable construction',
				'sleek design',
				'advanced technology',
				'ergonomic comfort',
				'long-lasting battery',
				'water-resistant coating',
			) :
			array(
				'user-friendly interface',
				'cross-platform compatibility',
				'regular updates',
				'cloud integration',
				'secure encryption',
				'customizable features',
			);
		$paragraphs[] = 'Key features include: ' . implode( ', ', $this->faker->randomElements( $features, 3 ) ) . '. ' .
						$this->faker->sentence( 50, true );

		// Use case paragraph.
		$use_cases    = 'physical' === $product_type ?
			array(
				'perfect for home, office, or travel',
				'ideal for professionals and hobbyists',
				'designed for everyday use',
				'great for outdoor adventures',
			) :
			array(
				'perfect for remote work',
				'ideal for creative professionals',
				'designed for seamless integration',
				'great for educational purposes',
			);
		$paragraphs[] = $this->faker->randomElement( $use_cases ) . '. ' . $this->faker->sentence( 15, true );

		// Technical specifications.
		$paragraphs[] = 'Built with ' . ( 'physical' === $product_type ? 'premium materials and cutting-edge technology' : 'robust code and scalable architecture' ) . '. ' .
						$this->faker->sentence( 50, true ) . ' ' . $this->faker->sentence( 8, true );

		return implode( "\n\n", $paragraphs );
	}

	/**
	 * Generate short product description
	 *
	 * @since 1.0.0
	 *
	 * @param string $product_type Product type (physical/digital).
	 *
	 * @return string Short description.
	 */
	private function generate_short_description( string $product_type ): string {
		$use_case = 'physical' === $product_type ?
			$this->faker->randomElement(
				array(
					'professionals',
					'students',
					'gamers',
					'home use',
					'outdoor enthusiasts',
				)
			) :
			$this->faker->randomElement(
				array(
					'creatives',
					'developers',
					'educators',
					'remote workers',
					'businesses',
				)
			);

		return $this->faker->sentence( 10, true ) . ' Perfect for ' . $use_case . '.';
	}

	/**
	 * Generate thumbnail image ID
	 *
	 * @since 1.0.0
	 *
	 * @return int Thumbnail ID (placeholder for now).
	 */
	private function generate_thumbnail(): int {
		// Placeholder for WordPress media library integration.
		return 0; // In a real implementation, this would upload an image and return the attachment ID.
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
	 * @return array Array of image metadata for product gallery.
	 */
	private function generate_gallery_images(): array {
		$gallery_images = array();
		$image_count    = $this->faker->numberBetween( 3, 8 );

		for ( $i = 0; $i < $image_count; $i++ ) {
			$gallery_images[] = array(
				'id'          => 0, // Placeholder for WordPress attachment ID.
				'url'         => $this->faker->imageUrl( 1200, 800, 'products' ),
				'alt'         => $this->faker->words( 4, true ),
				'caption'     => $this->faker->sentence( 8, true ),
				'description' => $this->faker->sentence( 12, true ),
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

		$possible_attributes = 'physical' === $product_type ? array(
			'color'    => array(
				'name'   => 'Color',
				'type'   => 'text',
				'values' => array(
					'Red',
					'Blue',
					'Green',
					'Black',
					'White',
					'Gray',
					'Silver',
					'Gold',
					'Navy',
					'Purple',
				),
			),
			'size'     => array(
				'name'   => 'Size',
				'type'   => 'text',
				'values' => array( 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'One Size' ),
			),
			'material' => array(
				'name'   => 'Material',
				'type'   => 'text',
				'values' => array(
					'Cotton',
					'Polyester',
					'Aluminum',
					'Stainless Steel',
					'Leather',
					'Wood',
					'Glass',
					'Ceramic',
					'Silicone',
				),
			),
			'finish'   => array(
				'name'   => 'Finish',
				'type'   => 'text',
				'values' => array( 'Matte', 'Glossy', 'Brushed', 'Polished', 'Textured' ),
			),
			'capacity' => array(
				'name'   => 'Capacity',
				'type'   => 'text',
				'values' => array( '16GB', '32GB', '64GB', '128GB', '256GB', '500ml', '1L', '2L' ),
			),
		) : array(
			'format'       => array(
				'name'   => 'Format',
				'type'   => 'text',
				'values' => array( 'PDF', 'MP4', 'MP3', 'ZIP', 'EXE', 'DMG' ),
			),
			'license'      => array(
				'name'   => 'License',
				'type'   => 'text',
				'values' => array( 'Personal', 'Commercial', 'Extended', 'Enterprise' ),
			),
			'version'      => array(
				'name'   => 'Version',
				'type'   => 'text',
				'values' => array( '1.0', '2.0', '3.0', 'Basic', 'Pro', 'Premium' ),
			),
			'platform'     => array(
				'name'   => 'Platform',
				'type'   => 'text',
				'values' => array( 'Windows', 'macOS', 'Linux', 'iOS', 'Android', 'Web' ),
			),
			'subscription' => array(
				'name'   => 'Subscription',
				'type'   => 'text',
				'values' => array( 'Monthly', 'Annual', 'Lifetime' ),
			),
		);

		// Select 2-4 random attributes for more variety.
		$selected_attributes = $this->faker->randomElements(
			array_keys( $possible_attributes ),
			$this->faker->numberBetween( 2, 4 )
		);

		foreach ( $selected_attributes as $attribute_slug ) {
			$attribute_info = $possible_attributes[ $attribute_slug ];

			// Create or get the attribute using EasyCommerce models.
			$attribute_id = $this->get_or_create_attribute(
				$attribute_info['name'],
				$attribute_info['type'],
				$attribute_slug
			);

			if ( $attribute_id ) {
				// Create or get attribute values.
				$attribute_values = array();
				$selected_values  = $this->faker->randomElements(
					$attribute_info['values'],
					$this->faker->numberBetween( 2, count( $attribute_info['values'] ) )
				);
				foreach ( $selected_values as $value ) {
					$value_id = $this->get_or_create_attribute_value(
						$attribute_id,
						$value,
						sanitize_title( $value )
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
	 * @return int Attribute ID or false on failure.
	 */
	private function get_or_create_attribute( string $name, string $type, string $slug ): int {
		$attribute_model = new Attribute();

		// Check if attribute already exists.
		$existing_attribute = $attribute_model->get_by_slug( $slug );
		if ( $existing_attribute ) {
			return $existing_attribute->id;
		}

		// Create new attribute.
		return $attribute_model->add( $name, $type, $slug );
	}

	/**
	 * Get or create an attribute value using EasyCommerce Attribute_Value model
	 *
	 * @since 1.0.0
	 *
	 * @param int    $attribute_id Attribute ID.
	 * @param string $name Value name.
	 * @param string $value Value slug/value.
	 *
	 * @return int Attribute value ID or false on failure.
	 */
	private function get_or_create_attribute_value( int $attribute_id, string $name, string $value ): int {
		$value_model = new Attribute_Value();
		$value_slug  = sanitize_title( $value );

		// Check if value already exists.
		$existing_value = $value_model->get_by_slug( $value_slug );
		if ( $existing_value && (int) $existing_value->attribute_id === $attribute_id ) {
			return $existing_value->id;
		}

		// Create new attribute value.
		return $value_model->add( $attribute_id, $name, $value, $value_slug );
	}

	/**
	 * Generate product variations based on attributes
	 *
	 * @since 1.0.0
	 *
	 * @param array  $attributes Product attributes.
	 * @param string $product_type Product type (physical/digital).
	 *
	 * @return array Array of product variations with pricing and stock information.
	 */
	private function generate_product_variations( array $attributes, string $product_type = 'physical' ): array {
		$variations  = array();
		$base_price  = 'physical' === $product_type ? $this->faker->randomFloat( 2, 20, 800 ) : $this->faker->randomFloat( 2, 5, 200 );
		$price_range = $base_price * 0.3; // 30% price variation for more realistic pricing

		// Generate combinations of attributes.
		$attribute_keys   = array_keys( $attributes );
		$attribute_values = array_values( $attributes );

		// Limit to a reasonable number of variations (max 10).
		$counts         = array_map( 'count', $attribute_values );
		$max_variations = empty( $counts ) ? 0 : min( 10, array_product( $counts ) );
		$generated      = 0;

		// Generate cartesian product of attributes.
		foreach ( $this->cartesian_product( $attribute_values ) as $combination ) {
			if ( $generated >= $max_variations ) {
				break;
			}

			$variation_attributes = array();
			$variation_name_parts = array();

			foreach ( $combination as $index => $value ) {
				$attribute_name                          = $attribute_keys[ $index ];
				$variation_attributes[ $attribute_name ] = $value;
				$variation_name_parts[]                  = $value;
			}

			$variation_name = implode( ' - ', $variation_name_parts );
			$price_modifier = $this->faker->randomFloat( 2, - $price_range, $price_range );
			$regular_price  = max( 1, $base_price + $price_modifier );
			$sale_price     = $this->faker->boolean( 40 ) ? $regular_price * $this->faker->randomFloat( 2, 0.6, 0.9 ) : null;
			$stock_quantity = 'physical' === $product_type ? $this->faker->optional( 0.85 )->numberBetween( 0, 150 ) : null;

			$variation = array(
				'name'           => $variation_name,
				'sku'            => $this->generate_unique_sku(),
				'type'           => $product_type,
				'regular_price'  => $regular_price,
				'sale_price'     => $sale_price,
				'stock_quantity' => $stock_quantity,
				'stock_limit'    => $this->faker->numberBetween( 10, 50 ),
				'status'         => $this->determine_stock_status( $stock_quantity ),
				'attributes'     => $variation_attributes,
				'meta'           => $this->generate_variation_meta( $product_type ),
				'downloads'      => array(),
			);

			// Add downloads for digital products.
			if ( 'digital' === $product_type ) {
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
			return 'in_stock'; // Digital products or unlimited stock.
		}

		if ( $stock_quantity > 20 ) {
			return 'in_stock';
		}

		if ( $stock_quantity > 0 ) {
			return $this->faker->randomElement( array( 'in_stock', 'low_stock', 'backorder' ) );
		}

		return 'out_of_stock';
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
			'tax_class'        => $this->faker->randomElement( array( 'standard', 'reduced-rate', 'zero-rate' ) ),
			'is_managed_stock' => 'physical' === $product_type,
		);

		if ( 'physical' === $product_type ) {
			$dimension_unit = $this->faker->randomElement( array( 'cm', 'in' ) );
			$meta           = array_merge(
				$meta,
				array(
					'weight'            => array(
						'value' => $this->faker->randomFloat( 2, 0.05, 10.0 ),
						'unit'  => $this->faker->randomElement( array( 'kg', 'g', 'lb' ) ),
					),
					'dimensions'        => array(
						'height' => array(
							'value' => $this->faker->randomFloat( 2, 1, 50 ),
							'unit'  => $dimension_unit,
						),
						'width'  => array(
							'value' => $this->faker->randomFloat( 2, 5, 100 ),
							'unit'  => $dimension_unit,
						),
						'length' => array(
							'value' => $this->faker->randomFloat( 2, 5, 100 ),
							'unit'  => $dimension_unit,
						),
					),
					'requires_shipping' => true,
					'packaging'         => $this->faker->randomElement( array( 'standard', 'gift', 'eco-friendly' ) ),
				)
			);
		} else {
			$meta = array_merge(
				$meta,
				array(
					'requires_shipping' => false,
					'download_limit'    => $this->faker->optional( 0.8 )->numberBetween( 1, 20 ),
					'download_expiry'   => $this->faker->optional( 0.6 )->numberBetween( 7, 365 ),
					'file_format'       => $this->faker->randomElement(
						array(
							'PDF',
							'MP4',
							'MP3',
							'ZIP',
							'EXE',
							'DMG',
						)
					),
				)
			);
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
			'PDF User Guide',
			'Video Tutorial Series',
			'High-Quality Audio Track',
			'Design Template Pack',
			'Digital Asset Bundle',
			'E-Book Chapter',
			'Source Code Package',
		);

		$downloads = array();
		$count     = $this->faker->numberBetween( 1, 4 );

		for ( $i = 0; $i < $count; $i++ ) {
			$file_type   = $this->faker->randomElement( array( 'pdf', 'mp4', 'mp3', 'zip', 'exe', 'dmg' ) );
			$downloads[] = array(
				'media_id'  => 0, // Placeholder for WordPress media library.
				'name'      => $this->faker->randomElement( $download_types ),
				'file_url'  => $this->faker->imageUrl( 800, 600, 'digital', true, $file_type ),
				'file_size' => $this->faker->numberBetween( 1024, 52428800 ), // 1KB to 50MB
				'file_type' => $file_type,
				'version'   => $this->faker->randomElement( array( '1.0', '1.1', '2.0', 'Latest' ) ),
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
		// Handle empty input arrays.
		if ( empty( $arrays ) ) {
			return array();
		}

		// Handle arrays containing empty sub-arrays.
		foreach ( $arrays as $array ) {
			if ( empty( $array ) ) {
				return array();
			}
		}

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
		$prefix = strtoupper( $this->faker->lexify( '????' ) );
		$number = $this->faker->unique()->numberBetween( 10000, 99999 );

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
		$category_names = array(
			'Electronics & Gadgets',
			'Fashion & Apparel',
			'Books & Stationery',
			'Home & Kitchen',
			'Sports & Fitness',
			'Health & Wellness',
			'Toys & Games',
			'Automotive Accessories',
			'Food & Beverages',
			'Jewelry & Accessories',
			'Beauty & Personal Care',
			'Office Supplies',
		);

		$category_ids = array();
		foreach ( $category_names as $category_name ) {
			$term = get_term_by( 'name', $category_name, 'product_cat' );
			if ( ! $term ) {
				$term_result = wp_insert_term(
					$category_name,
					'product_cat',
					array(
						'description' => $this->faker->sentence( 10, true ),
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

		return $this->faker->randomElements( $category_ids, $this->faker->numberBetween( 1, 4 ) );
	}

	/**
	 * Get or create product brands
	 *
	 * @since 1.0.0
	 *
	 * @return array Brand IDs.
	 */
	private function get_or_create_product_brands(): array {
		$brand_names = array(
			'TechTrend Innovations',
			'EcoVibe Solutions',
			'QualityCraft',
			'NextGen Tech',
			'PureEssence',
			'StylePeak',
			'FutureWave',
			'ProElite Series',
			'UrbanPulse',
			'SmartLife Co.',
		);

		$brand_ids = array();
		foreach ( $brand_names as $brand_name ) {
			$term = get_term_by( 'name', $brand_name, 'product_brand' );
			if ( ! $term ) {
				$term_result = wp_insert_term(
					$brand_name,
					'product_brand',
					array(
						'description' => $this->faker->company . ' - ' . $this->faker->sentence( 8, true ),
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

		return $this->faker->randomElements( $brand_ids, $this->faker->numberBetween( 1, 2 ) );
	}

	/**
	 * Format variations for EasyCommerce model compatibility
	 *
	 * @since 1.0.0
	 *
	 * @param array $variations Generated variations.
	 * @param array $attributes Product attributes.
	 *
	 * @return array Formatted variations.
	 */
	private function format_variations_for_model( array $variations, array $attributes ): array {
		$formatted_variations = array();

		foreach ( $variations as $variation ) {
			$formatted_attributes = array();

			// Format attributes to match EasyCommerce model expectations.
			foreach ( $variation['attributes'] as $attr_slug => $attr_value ) {
				$attribute_model       = new Attribute();
				$attribute_value_model = new Attribute_Value();

				// Get or create attribute.
				$attribute = $attribute_model->get_by_slug( $attr_slug );
				if ( ! $attribute ) {
					continue; // Skip if attribute doesn't exist.
				}

				// Get or create attribute value.
				$value_slug      = sanitize_title( $attr_value );
				$attribute_value = $attribute_value_model->get_by_slug( $value_slug );
				if ( ! $attribute_value ) {
					continue; // Skip if value doesn't exist.
				}

				$formatted_attributes[ $attr_slug ] = array(
					'id'     => $attribute->id,
					'slug'   => $attribute->slug,
					'values' => array(
						array(
							'id'   => $attribute_value->id,
							'slug' => $attribute_value->slug,
						),
					),
				);
			}

			$formatted_variations[] = array(
				'name'           => $variation['name'],
				'sku'            => $variation['sku'],
				'type'           => $variation['type'],
				'regular_price'  => $variation['regular_price'],
				'sale_price'     => $variation['sale_price'],
				'stock_quantity' => $variation['stock_quantity'],
				'stock_limit'    => $variation['stock_limit'],
				'status'         => $variation['status'],
				'attributes'     => $formatted_attributes,
				'meta'           => $variation['meta'],
				'downloads'      => $variation['downloads'],
			);
		}

		return $formatted_variations;
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
			'new-arrival',
			'best-seller',
			'featured',
			'trending',
			'premium',
			'limited-edition',
			'exclusive',
			'on-sale',
			'clearance',
			'eco-friendly',
			'high-demand',
		);

		$selected_tags = $this->faker->randomElements( $tags, $this->faker->numberBetween( 2, 5 ) );
		wp_set_post_terms( $product_id, $selected_tags, 'product_tag' );
	}

	/**
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'products' => __( 'Products with Attributes, Variations, and Categories', 'easycommerce-fakerpress' ),
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return __( 'Generates realistic product data with attributes, variations, pricing, inventory management, categories, brands, tags, and comprehensive meta data for testing ecommerce functionality.', 'easycommerce-fakerpress' );
	}
}
