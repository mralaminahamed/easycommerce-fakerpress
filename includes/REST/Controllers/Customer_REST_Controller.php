<?php
/**
 * Customer Generator REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\REST\Controllers
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Customer_Generator;

/**
 * Customer Generator REST Controller
 *
 * Handles REST API endpoints for customer generation
 *
 * @since 1.0.0
 */
class Customer_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'customers';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Customer_Generator Generator instance.
	 */
	protected function get_generator_instance(): Customer_Generator {
		return new Customer_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'customer';
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
			'customers' => array(
				'description' => __( 'Generated customers data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'type' => 'integer',
						),
						'username' => array(
							'type' => 'string',
						),
						'email'    => array(
							'type' => 'string',
						),
						'name'     => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
