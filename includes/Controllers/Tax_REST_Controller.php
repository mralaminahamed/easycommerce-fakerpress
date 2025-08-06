<?php
/**
 * Tax REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Tax_Generator;

/**
 * Tax REST Controller Class
 *
 * Handles REST API endpoints for tax class generation
 *
 * @since 1.0.0
 */
class Tax_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'taxes';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Generator Generator instance.
	 */
	protected function get_generator_instance(): Tax_Generator {
		return new Tax_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'tax_class';
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
			'tax_classes' => array(
				'description' => __( 'Generated tax classes with location-based rates.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'          => array(
							'type' => 'integer',
						),
						'name'        => array(
							'type' => 'string',
						),
						'description' => array(
							'type' => 'string',
						),
						'status'      => array(
							'type' => 'boolean',
						),
						'rates'       => array(
							'type' => 'array',
						),
						'regions'     => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
