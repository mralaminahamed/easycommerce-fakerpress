<?php
/**
 * Location REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Location_Generator;

/**
 * Location REST Controller Class
 *
 * Handles REST API endpoints for location data generation
 *
 * @since 1.0.0
 */
class Location_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'locations';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Location_Generator Generator instance.
	 */
	protected function get_generator_instance(): Location_Generator {
		return new Location_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'location';
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
			'locations' => array(
				'description' => __( 'Generated location hierarchy with countries, states, and cities.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'countries_created' => array(
							'type' => 'integer',
						),
						'total_states'      => array(
							'type' => 'integer',
						),
						'total_cities'      => array(
							'type' => 'integer',
						),
						'data_file_path'    => array(
							'type' => 'string',
						),
						'created_date'      => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
