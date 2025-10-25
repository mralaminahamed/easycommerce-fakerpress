<?php
/**
 * Product Variation Generator for EasyCommerce FakerPress.
 *
 * Generates fake product variation data for testing purposes.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Generators
 */

namespace EasyCommerceFakerPress\Generators;

defined( 'ABSPATH' ) || exit;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerce\Models\Attribute as AttributeModel;
use EasyCommerce\Models\Attribute_Value as AttributeValueModel;
use EasyCommerce\Models\Product as ProductModel;
use EasyCommerce\Models\Product_Variation as ProductVariationModel;
use WP_Error;

/**
 * Product Variation Generator Class
 *
 * Generates realistic product variations with attributes, pricing, and inventory data.
 */
class Product_Variation extends Generator {

	/**
	 * Get the resource type name
	 *
	 * @return string Resource type name.
	 */
	protected function get_resource_type(): string {
		return 'product_variation';
	}

	/**
	 * Get supported data types for this generator.
	 *
	 * @return array Supported types
	 */
	public function get_supported_types(): array {
		return array(
			'product_variations' => __( 'Product Variations with Attributes', 'easycommerce-fakerpress' ),
		);
	}

	/**
	 * Get generator description.
	 *
	 * @return string Description
	 */
	public function get_description(): string {
		return __( 'Generates realistic product variations with attributes, pricing variations, inventory management, and comprehensive meta data for testing ecommerce functionality.', 'easycommerce-fakerpress' );
	}

	/**
	 * Generate a single product variation
	 *
	 * @return WP_Error|array Single variation data, error, or false on failure.
	 */
	protected function generate_single_item() {
		// Check if EasyCommerce Product_Variation class exists.
		if ( ! class_exists( ProductVariationModel::class ) ) {
			return new WP_Error( 'missing_model', __( 'EasyCommerce Product_Variation model not found. Please ensure EasyCommerce plugin is active.', 'easycommerce-fakerpress' ) );
		}

		// Get product for variation generation.
		$product = $this->get_product_for_variation();

		if ( ! $product ) {
			return new WP_Error( 'no_suitable_products', __( 'No suitable products found. Please generate products first.', 'easycommerce-fakerpress' ) );
		}
		if ( ! $product->exists() ) {
			return new WP_Error( 'product_not_exists', __( 'Product does not exist.', 'easycommerce-fakerpress' ) );
		}

		$variation_data = $this->generate_variation_data( $product );
		$variation      = $this->create_variation( $variation_data );

		if ( ! $variation ) {
			return new WP_Error( 'variation_creation_failed', __( 'Failed to create product variation.', 'easycommerce-fakerpress' ) );
		}

		// Add attributes to the variation.
		$this->add_variation_attributes( $variation );

		// Add meta data (dimensions, weight, etc.).
		$this->add_variation_meta( $variation );

		return array(
			'id'             => $variation->get_id(),
			'product_id'     => $variation->get_product_id(),
			'name'           => $variation->get_name(),
			'sku'            => $variation->get_sku(),
			'price'          => $variation->get_price(),
			'sale_price'     => $variation->get_sale_price(),
			'stock_quantity' => $variation->get_stock(),
			'type'           => $variation->get_type(),
			'status'         => $variation->get_status(),
			'attributes'     => $variation->get_attributes(),
			'meta'           => array(
				'weight'     => $variation->get_weight(),
				'dimensions' => array(
					'length' => $variation->get_length(),
					'width'  => $variation->get_width(),
					'height' => $variation->get_height(),
				),
				'tax_class'  => $variation->get_tax_class(),
			),
		);
	}

	/**
	 * Get product for variation generation based on parameters
	 *
	 * @since 1.0.0
	 *
	 * @return ProductModel|null Product instance or null if none found.
	 */
	private function get_product_for_variation() {
		$specific_product_id = $this->generation_params['specific_product_id'] ?? null;
		$product_types       = $this->generation_params['product_types'] ?? array();
		$exclude_products    = $this->generation_params['exclude_products'] ?? array();

		// If specific product is requested.
		if ( $specific_product_id ) {
			$product = new ProductModel( $specific_product_id );

			return $product->exists() ? $product : null;
		}

		// Get products with filters.
		$query_params = array( 'per_page' => 50 );

		// Add product type filter.
		if ( ! empty( $product_types ) ) {
			$query_params['type'] = $product_types;
		}

		// Get products.
		$data     = ProductModel::list( $query_params );
		$products = $data['products'] ?? array();

		if ( empty( $products ) ) {
			return null;
		}

		// Filter out excluded products.
		if ( ! empty( $exclude_products ) ) {
			$products = array_filter(
				$products,
				static function ( $product ) use ( $exclude_products ) {
					return ! in_array( $product->get_id(), $exclude_products, true );
				}
			);
		}

		// Filter to products that can have variations.
		$variable_products = array_filter(
			$products,
			function ( $product ) {
				// Check if product can have variations (has attributes or is variable type).
				return $this->can_product_have_variations( $product );
			}
		);

		if ( empty( $variable_products ) ) {
			// If no variable products, use any available product.
			return ! empty( $products ) ? $this->get_faker()->randomElement( $products ) : null;
		}

		return $this->get_faker()->randomElement( $variable_products );
	}

	/**
	 * Check if product can have variations
	 *
	 * @since 1.0.0
	 *
	 * @param ProductModel $product Product to check.
	 *
	 * @return bool True if product can have variations.
	 */
	private function can_product_have_variations( ProductModel $product ): bool {
		// Check if product has attributes or is already a variable product.
		$is_variable    = $product->is_variable();
		$has_attributes = ! empty( $product->get_attributes() );

		// Products with attributes can have variations, or products that are already variable.
		return $is_variable || $has_attributes;
	}

	/**
	 * Generate multiple product variations.
	 *
	 * @param int   $count Number of variations to generate.
	 * @param array $args Additional arguments.
	 *
	 * @return array Generated variation data.
	 */
	public function generate_multiple( int $count = 10, array $args = array() ): array {
		$results = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$item_result = $this->generate_single_item();

			if ( $item_result && ! is_wp_error( $item_result ) ) {
				$results[] = $item_result;
			}
		}

		return $results;
	}

	/**
	 * Generate variation data.
	 *
	 * @param ProductModel $product Parent product.
	 *
	 * @return array Variation data.
	 */
	private function generate_variation_data( ProductModel $product ): array {
		$prices = $product->get_prices( false );

		$base_price      = (float) ( $prices[0]['regular_price'] ?? $this->get_faker()->randomFloat( 2, 10, 500 ) );
		$price_variation = $this->get_faker()->randomFloat( 2, - 0.2 * $base_price, 0.3 * $base_price );
		$regular_price   = max( 1, $base_price + $price_variation );

		// Generate sale price with 30% probability.
		$sale_price = null;
		if ( $this->get_faker()->boolean( 30 ) ) {
			$sale_price = $this->get_faker()->randomFloat( 2, $regular_price * 0.1, $regular_price * 0.9 );
		}

		$variation_attributes = $this->get_variation_attributes();
		$variation_name       = $this->generate_variation_name( $variation_attributes );

		return array(
			'product_id'     => $product->get_id(),
			'name'           => $variation_name,
			'type'           => $this->get_faker()->randomElement( array( 'physical', 'digital' ) ),
			'price'          => $regular_price,
			'sale_price'     => $sale_price,
			'sku'            => $this->generate_variation_sku( $product->get_slug(), $variation_attributes ),
			'stock_quantity' => $this->get_faker()->numberBetween( 0, 100 ),
			'stock_limit'    => $this->get_faker()->numberBetween( 5, 20 ),
			'status'         => $this->get_faker()->randomElement( array( 'active', 'inactive', 'draft' ) ),
			'attributes'     => $variation_attributes,
		);
	}

	/**
	 * Create variation in database.
	 *
	 * @param array $data Variation data.
	 *
	 * @return ProductVariationModel|null Created variation instance.
	 */
	private function create_variation( array $data ): ?ProductVariationModel {
		$variation = new ProductVariationModel();

		$variation->product_id = $data['product_id'];
		$variation->set_name( $data['name'] );
		$variation->set_sku( $data['sku'] );
		$variation->set_type( $data['type'] );
		$variation->set_price( $data['price'] );
		$variation->set_sale_price( $data['sale_price'] );
		$variation->set_stock_quantity( $data['stock_quantity'] );
		$variation->set_low_stock_limit( $data['stock_limit'] );
		$variation->set_status( $data['status'] );

		if ( $variation->save() ) {
			return $variation;
		}

		return null;
	}

	/**
	 * Get realistic variation attributes.
	 *
	 * @return array Attribute combinations.
	 */
	private function get_variation_attributes(): array {
		$attribute_sets = array(
			// Clothing variations.
			array(
				'size'  => $this->get_faker()->randomElement( array( 'XS', 'S', 'M', 'L', 'XL', 'XXL' ) ),
				'color' => $this->get_faker()->randomElement(
					array( 'Red', 'Blue', 'Green', 'Black', 'White', 'Gray', 'Navy', 'Beige' )
				),
			),
			// Electronics variations.
			array(
				'storage' => $this->get_faker()->randomElement( array( '64GB', '128GB', '256GB', '512GB', '1TB' ) ),
				'color'   => $this->get_faker()->randomElement(
					array( 'Space Gray', 'Silver', 'Gold', 'Rose Gold', 'Midnight', 'Blue' )
				),
			),
			// Shoe variations.
			array(
				'size'  => $this->get_faker()->randomElement(
					array( '6', '6.5', '7', '7.5', '8', '8.5', '9', '9.5', '10', '10.5', '11', '12' )
				),
				'color' => $this->get_faker()->randomElement( array( 'Black', 'White', 'Brown', 'Navy', 'Gray', 'Red' ) ),
			),
			// Book variations.
			array(
				'format'   => $this->get_faker()->randomElement( array( 'Hardcover', 'Paperback', 'eBook', 'Audiobook' ) ),
				'language' => $this->get_faker()->randomElement(
					array( 'English', 'Spanish', 'French', 'German', 'Italian' )
				),
			),
			// Watch variations.
			array(
				'size'  => $this->get_faker()->randomElement( array( '38mm', '40mm', '42mm', '44mm', '45mm' ) ),
				'band'  => $this->get_faker()->randomElement( array( 'Sport Band', 'Leather', 'Milanese', 'Link Bracelet' ) ),
				'color' => $this->get_faker()->randomElement( array( 'Space Gray', 'Silver', 'Gold', 'Rose Gold' ) ),
			),
		);

		return $this->get_faker()->randomElement( $attribute_sets );
	}

	/**
	 * Generate variation name from attributes.
	 *
	 * @param array $attributes Variation attributes.
	 *
	 * @return string Generated name.
	 */
	private function generate_variation_name( array $attributes ): string {
		$name_parts = array();

		foreach ( $attributes as $value ) {
			$name_parts[] = $value;
		}

		return implode( ' - ', $name_parts );
	}

	/**
	 * Generate variation SKU.
	 *
	 * @param string $base_sku Parent product SKU.
	 * @param array  $attributes Variation attributes.
	 *
	 * @return string Generated SKU.
	 */
	private function generate_variation_sku( string $base_sku, array $attributes ): string {
		$sku_parts = array( $base_sku );

		foreach ( $attributes as $value ) {
			// Create short codes from attribute values.
			$short_code  = strtoupper( substr( preg_replace( '/[^A-Za-z0-9]/', '', $value ), 0, 3 ) );
			$sku_parts[] = $short_code;
		}

		return implode( '-', $sku_parts );
	}

	/**
	 * Add attributes to variation.
	 *
	 * @param ProductVariationModel $variation Variation instance.
	 */
	private function add_variation_attributes( ProductVariationModel $variation ): void {
		$attributes     = $variation->attributes;
		$variation_data = $this->get_variation_attributes();

		foreach ( $variation_data as $attribute_slug => $value_slug ) {
			// Get or create attribute.
			$attribute = $this->get_or_create_attribute( $attribute_slug );
			if ( ! $attribute ) {
				continue;
			}

			// Get or create attribute value.
			$attribute_value = $this->get_or_create_attribute_value( $attribute->id, $value_slug );
			if ( ! $attribute_value ) {
				continue;
			}

			// Add to variation using Product_Variation_Attribute model.
			$attributes->add(
				$variation->get_id(),
				$attribute_slug,
				$value_slug,
				$attribute->id,
				$attribute_value->id
			);
		}
	}

	/**
	 * Get or create attribute.
	 *
	 * @param string $slug Attribute slug.
	 *
	 * @return WP_Error|object Attribute data object.
	 */
	private function get_or_create_attribute( string $slug ) {
		$attribute_model = new AttributeModel();
		$existing        = $attribute_model->get_by_slug( $slug );

		if ( $existing ) {
			return $existing;
		}

		// Create new attribute.
		$name         = ucfirst( str_replace( '_', ' ', $slug ) );
		$attribute_id = $attribute_model->add( $name, 'select', $slug );

		if ( $attribute_id ) {
			return $attribute_model->get( $attribute_id );
		}

		return new WP_Error( 'attribute_not_found', sprintf( 'Attribute %s not found.', $slug ) );
	}

	/**
	 * Get or create attribute value.
	 *
	 * @param int    $attribute_id Attribute ID.
	 * @param string $value_slug Value slug.
	 *
	 * @return WP_Error|object Attribute value data object.
	 */
	private function get_or_create_attribute_value( int $attribute_id, string $value_slug ) {
		$attribute_value_model = new AttributeValueModel();
		$existing              = $attribute_value_model->get_by_slug( $value_slug );

		// Check if existing value belongs to the same attribute.
		if ( $existing && (int) $existing->attribute_id === $attribute_id ) {
			return $existing;
		}

		// Create new attribute value.
		$name     = $value_slug;
		$slug     = strtolower( str_replace( ' ', '_', $value_slug ) );
		$value_id = $attribute_value_model->add( $attribute_id, $name, $value_slug, $slug );

		if ( $value_id ) {
			return $attribute_value_model->get( $value_id );
		}

		return new WP_Error( 'attribute_not_found', sprintf( 'Attribute %s not found.', $slug ) );
	}

	/**
	 * Add meta data to variation.
	 *
	 * @param ProductVariationModel $variation Variation instance.
	 */
	private function add_variation_meta( ProductVariationModel $variation ): void {
		// Physical dimensions (for physical products).
		if ( $variation->get_type() === 'physical' ) {
			$variation->update_meta(
				'weight',
				array(
					'value' => $this->get_faker()->randomFloat( 2, 0.1, 10 ),
					'unit'  => 'kg',
				)
			);

			$variation->update_meta(
				'length',
				array(
					'value' => $this->get_faker()->randomFloat( 2, 1, 50 ),
					'unit'  => 'cm',
				)
			);

			$variation->update_meta(
				'width',
				array(
					'value' => $this->get_faker()->randomFloat( 2, 1, 50 ),
					'unit'  => 'cm',
				)
			);

			$variation->update_meta(
				'height',
				array(
					'value' => $this->get_faker()->randomFloat( 2, 1, 50 ),
					'unit'  => 'cm',
				)
			);
		}

		// Tax class.
		$variation->update_meta( 'tax_class', $this->get_faker()->randomElement( array( 1, 2, 3 ) ) );

		// Stock management.
		$variation->update_meta( 'is_managed_stock', $this->get_faker()->boolean( 80 ) ? 1 : 0 );

		// Additional meta for digital products.
		if ( $variation->get_type() === 'digital' ) {
			$variation->update_meta( 'download_limit', $this->get_faker()->numberBetween( 1, 10 ) );
			$variation->update_meta( 'download_expiry_days', $this->get_faker()->numberBetween( 7, 365 ) );
		}

		// SEO and marketing meta.
		$variation->update_meta( 'meta_description', $this->get_faker()->sentence( 20 ) );
		$variation->update_meta( 'meta_keywords', implode( ', ', (array) $this->get_faker()->words( 5 ) ) );

		// Thumbnail URL.
		$variation->update_meta( 'thumbnail', $this->get_faker()->imageUrl( 300, 300, 'products' ) );
	}
}
