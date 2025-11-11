<?php
/**
 * Product Generator REST Controller
 *
 * Handles REST API endpoints for product data generation in EasyCommerce FakerPress.
 * Provides endpoints for generating products with attributes, variations, and metadata.
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Product as ProductGenerator;

/**
 * Product Generator REST Controller
 *
 * Handles REST API endpoints for generating product data in EasyCommerce stores.
 * Provides comprehensive product generation with support for attributes, variations,
 * categories, pricing, and inventory management through the REST API.
 *
 * Endpoints:
 * - POST /wp-json/easycommerce-fakerpress/v1/products/generate
 *
 * Features:
 * - Full product creation with EasyCommerce model integration
 * - Attribute and variation support
 * - Category and taxonomy assignment
 * - Pricing and inventory management
 * - Metadata and SEO optimization
 *
 * @since 1.0.0
 */
class Product extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'product';
	}

	/**
	 * Get resource type label for products
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for product resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Product', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * Returns the REST API base path for product generation endpoints.
	 * Forms the endpoint URL: /wp-json/easycommerce-fakerpress/v1/products/generate
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base path segment ('products').
	 */
	protected function get_rest_base(): string {
		return 'products';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return ProductGenerator Generator instance.
	 */
	protected function get_generator_instance(): ProductGenerator {
		return new ProductGenerator();
	}

	/**
	 * Get resource-specific generation parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific parameters.
	 */
	protected function get_resource_specific_params(): array {
		return array(
			'product_type'    => array(
				'description'       => __( 'Type of products to generate.', 'easycommerce-fakerpress' ),
				'type'              => 'string',
				'enum'              => array( 'simple', 'variable', 'grouped', 'external', 'digital', 'mixed' ),
				'default'           => 'mixed',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'price_range'     => array(
				'description' => __( 'Price range for generated products.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min' => array(
						'description' => __( 'Minimum price.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 0,
						'default'     => 10,
					),
					'max' => array(
						'description' => __( 'Maximum price.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 1,
						'default'     => 500,
					),
				),
			),
			'categories'      => array(
				'description' => __( 'Product categories configuration.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'create_new'      => array(
						'description' => __( 'Create new categories if needed.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'max_per_product' => array(
						'description' => __( 'Maximum categories per product.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'maximum'     => 10,
						'default'     => 3,
					),
				),
			),
			'attributes'      => array(
				'description' => __( 'Product attributes configuration.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'include_attributes' => array(
						'description' => __( 'Include product attributes.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'variation_count'    => array(
						'description' => __( 'Number of variations for variable products.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'maximum'     => 20,
						'default'     => 5,
					),
				),
			),
			'inventory'       => array(
				'description' => __( 'Inventory settings for generated products.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'manage_stock' => array(
						'description' => __( 'Enable stock management.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'stock_range'  => array(
						'description' => __( 'Stock quantity range.', 'easycommerce-fakerpress' ),
						'type'        => 'object',
						'properties'  => array(
							'min' => array(
								'type'    => 'integer',
								'minimum' => 0,
								'default' => 0,
							),
							'max' => array(
								'type'    => 'integer',
								'minimum' => 1,
								'default' => 100,
							),
						),
					),
				),
			),
			'content_options' => array(
				'description' => __( 'Product content generation options.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'include_images'     => array(
						'description' => __( 'Generate product images.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => false,
					),
					'description_length' => array(
						'description' => __( 'Product description length.', 'easycommerce-fakerpress' ),
						'type'        => 'string',
						'enum'        => array( 'short', 'medium', 'long' ),
						'default'     => 'medium',
					),
				),
			),
		);
	}

	/**
	 * Get resource-specific schema properties
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific properties.
	 */
	protected function get_resource_specific_properties(): array {
		return array(
			'products' => array(
				'description' => __( 'Generated products data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'         => array(
							'type' => 'integer',
						),
						'title'      => array(
							'type' => 'string',
						),
						'variations' => array(
							'type' => 'integer',
						),
					),
				),
			),
		);
	}
}
