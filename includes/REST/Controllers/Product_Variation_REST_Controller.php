<?php
/**
 * Product Variation REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\REST\Controllers
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Product_Variation_Generator;

/**
 * Product Variation REST Controller Class
 *
 * Handles REST API endpoints for product variation generation
 *
 * @since 1.0.0
 */
class Product_Variation_REST_Controller extends REST_Controller {

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
	 * @return Generator Generator instance.
	 */
	protected function get_generator_instance(): Product_Variation_Generator {
		return new Product_Variation_Generator();
	}

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
