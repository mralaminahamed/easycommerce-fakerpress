<?php
/**
 * Product Generator REST Controller
 *
 * @package EasyCommerceFakerPress\REST\Controllers
 * @since   1.0.0
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Product_Generator;

/**
 * Product Generator REST Controller
 *
 * Handles REST API endpoints for product generation
 *
 * @since 1.0.0
 */
class Product_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base() {
		return 'products';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Product_Generator Generator instance.
	 */
	protected function get_generator_instance() {
		return new Product_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type() {
		return 'product';
	}

	/**
	 * Get resource-specific schema properties
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific properties.
	 */
	protected function get_resource_specific_properties() {
		return array(
			'data' => array(
				'properties' => array(
					'products' => array(
						'description' => __( 'Generated products data.', 'easycommerce-fakerpress' ),
						'type'        => 'array',
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
				),
			),
		);
	}

}