<?php
/**
 * Product Variation REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Product_Variation;

/**
 * Product Variation REST Controller Class
 *
 * Handles REST API endpoints for product variation generation
 *
 * @since 1.0.0
 */
class Product_Variations extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'product_variation';
	}

	/**
	 * Get resource type label for product variations
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for product variation resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Product Variation', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'product-variations';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Product_Variation Generator instance.
	 */
	protected function get_generator_instance(): Product_Variation {
		return new Product_Variation();
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
			'specific_product_id'  => array(
				'description'       => __( 'Specific product ID to generate variations for.', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'product_types'        => array(
				'description'       => __( 'Product types to consider for variation generation.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'simple', 'variable', 'grouped', 'external', 'digital' ),
				),
				'default'           => array( 'simple', 'variable' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'exclude_products'     => array(
				'description'       => __( 'Product IDs to exclude from variation generation.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'integer',
				),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'price_variance'       => array(
				'description' => __( 'Price variance settings for variations.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min_percentage' => array(
						'description' => __( 'Minimum price variance percentage from base product.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => - 50,
						'maximum'     => 50,
						'default'     => - 20,
					),
					'max_percentage' => array(
						'description' => __( 'Maximum price variance percentage from base product.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => - 50,
						'maximum'     => 100,
						'default'     => 30,
					),
				),
			),
			'stock_settings'       => array(
				'description' => __( 'Stock management settings for variations.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'manage_stock' => array(
						'description' => __( 'Enable stock management for variations.', 'easycommerce-fakerpress' ),
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
			'variation_attributes' => array(
				'description' => __( 'Attribute generation settings.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'create_missing_attributes'    => array(
						'description' => __( 'Create missing attributes if needed.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'max_attributes_per_variation' => array(
						'description' => __( 'Maximum attributes per variation.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'maximum'     => 10,
						'default'     => 3,
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
			'product_variations' => array(
				'description' => __( 'Generated product variations with attributes.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'             => array(
							'type' => 'integer',
						),
						'product_id'     => array(
							'type' => 'integer',
						),
						'name'           => array(
							'type' => 'string',
						),
						'sku'            => array(
							'type' => 'string',
						),
						'price'          => array(
							'type' => 'number',
						),
						'sale_price'     => array(
							'type' => 'number',
						),
						'stock_quantity' => array(
							'type' => 'integer',
						),
						'type'           => array(
							'type' => 'string',
						),
						'status'         => array(
							'type' => 'string',
						),
						'attributes'     => array(
							'type' => 'object',
						),
						'meta'           => array(
							'type' => 'object',
						),
					),
				),
			),
		);
	}
}
