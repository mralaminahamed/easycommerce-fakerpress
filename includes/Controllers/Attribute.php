<?php
/**
 * Attribute Generator REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Attribute as AttributeGenerator;

/**
 * Attribute Generator REST Controller
 *
 * Handles REST API endpoints for attribute generation
 *
 * @since 1.0.0
 */
class Attribute extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'attribute';
	}

	/**
	 * Get resource type label for attributes
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for attribute resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Attribute', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'attributes';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return AttributeGenerator Generator instance.
	 */
	protected function get_generator_instance(): AttributeGenerator {
		return new AttributeGenerator();
	}

	/**
	 * Get resource-specific parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array Resource-specific parameters.
	 */
	protected function get_resource_specific_params(): array {
		return array(
			'attribute_types'      => array(
				'description'       => __( 'Types of attributes to generate', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'Text', 'Color', 'Image' ),
				),
				'default'           => array( 'Text', 'Color' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'values_per_attribute' => array(
				'description' => __( 'Number of values to generate per attribute', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min' => array(
						'type'    => 'integer',
						'minimum' => 2,
						'maximum' => 20,
						'default' => 3,
					),
					'max' => array(
						'type'    => 'integer',
						'minimum' => 2,
						'maximum' => 20,
						'default' => 8,
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
			'attributes' => array(
				'description' => __( 'Generated attributes data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'     => array(
							'type' => 'integer',
						),
						'name'   => array(
							'type' => 'string',
						),
						'type'   => array(
							'type' => 'string',
						),
						'slug'   => array(
							'type' => 'string',
						),
						'values' => array(
							'type' => 'array',
						),
					),
				),
			),
		);
	}
}
