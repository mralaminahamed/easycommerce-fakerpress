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
	 * @return Tax_Generator Generator instance.
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
	 * Get resource-specific generation parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific parameters.
	 */
	protected function get_resource_specific_params(): array {
		return array(
			'tax_types'       => array(
				'description'       => __( 'Types of tax classes to generate.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'standard', 'reduced', 'zero', 'exempt', 'digital' ),
				),
				'default'           => array( 'standard', 'reduced', 'zero' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'jurisdictions'   => array(
				'description'       => __( 'Tax jurisdictions to generate rates for.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'country', 'state', 'city', 'county', 'postcode' ),
				),
				'default'           => array( 'country', 'state' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'rate_ranges'     => array(
				'description' => __( 'Tax rate ranges by type.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'standard' => array(
						'description' => __( 'Standard tax rate range.', 'easycommerce-fakerpress' ),
						'type'        => 'object',
						'properties'  => array(
							'min' => array(
								'type'    => 'number',
								'minimum' => 0,
								'maximum' => 50,
								'default' => 5,
							),
							'max' => array(
								'type'    => 'number',
								'minimum' => 0,
								'maximum' => 50,
								'default' => 25,
							),
						),
					),
					'reduced'  => array(
						'description' => __( 'Reduced tax rate range.', 'easycommerce-fakerpress' ),
						'type'        => 'object',
						'properties'  => array(
							'min' => array(
								'type'    => 'number',
								'minimum' => 0,
								'maximum' => 20,
								'default' => 1,
							),
							'max' => array(
								'type'    => 'number',
								'minimum' => 0,
								'maximum' => 20,
								'default' => 10,
							),
						),
					),
				),
			),
			'location_coverage' => array(
				'description' => __( 'Geographic coverage for tax rates.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'countries' => array(
						'description'       => __( 'Countries to generate tax rates for.', 'easycommerce-fakerpress' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'string',
						),
						'default'           => array( 'US', 'CA', 'GB', 'AU', 'DE' ),
						'sanitize_callback' => array( $this, 'sanitize_array' ),
					),
					'include_compound' => array(
						'description' => __( 'Include compound tax rates.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
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
