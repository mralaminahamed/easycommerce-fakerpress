<?php
/**
 * Order Generator REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\REST\Controllers
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Order_Generator;

/**
 * Order Generator REST Controller
 *
 * Handles REST API endpoints for order generation
 *
 * @since 1.0.0
 */
class Order_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'orders';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Order_Generator Generator instance.
	 */
	protected function get_generator_instance(): Order_Generator {
		return new Order_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'order';
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
			'orders' => array(
				'description' => __( 'Generated orders data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'type' => 'integer',
						),
						'customer' => array(
							'type' => 'string',
						),
						'total'    => array(
							'type' => 'string',
						),
						'status'   => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
