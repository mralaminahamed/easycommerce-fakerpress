<?php
/**
 * Shipping Plan REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Shipping_Plan as ShippingPlanGenerator;

/**
 * Shipping Plan REST Controller Class
 *
 * Handles REST API endpoints for shipping plan generation
 *
 * @since 1.0.0
 */
class Shipping_Plan extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'shipping_plan';
	}

	/**
	 * Get resource type label for shipping plans
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for shipping plan resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Shipping Plan', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'shipping-plans';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return ShippingPlanGenerator Generator instance.
	 */
	protected function get_generator_instance(): ShippingPlanGenerator {
		return new ShippingPlanGenerator();
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
			'shipping_types'      => array(
				'description'       => __( 'Types of shipping methods to generate.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array(
						'standard',
						'express',
						'overnight',
						'pickup',
						'free',
						'weight_based',
						'flat_rate',
					),
				),
				'default'           => array( 'standard', 'express', 'free' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'cost_range'          => array(
				'description' => __( 'Shipping cost range.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min' => array(
						'description' => __( 'Minimum shipping cost.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 0,
						'default'     => 0,
					),
					'max' => array(
						'description' => __( 'Maximum shipping cost.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 0,
						'default'     => 50,
					),
				),
			),
			'coverage_areas'      => array(
				'description'       => __( 'Geographic coverage areas.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'domestic', 'international', 'regional', 'worldwide' ),
				),
				'default'           => array( 'domestic', 'international' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'calculation_methods' => array(
				'description'       => __( 'Shipping calculation methods.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'flat_rate', 'weight_based', 'price_based', 'quantity_based' ),
				),
				'default'           => array( 'flat_rate', 'weight_based' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'delivery_timeframes' => array(
				'description' => __( 'Delivery time ranges.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min_days' => array(
						'description' => __( 'Minimum delivery days.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'default'     => 1,
					),
					'max_days' => array(
						'description' => __( 'Maximum delivery days.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'default'     => 14,
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
			'shipping_plans' => array(
				'description' => __( 'Generated shipping plans with methods and regions.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'               => array(
							'type' => 'integer',
						),
						'name'             => array(
							'type' => 'string',
						),
						'description'      => array(
							'type' => 'string',
						),
						'active'           => array(
							'type' => 'boolean',
						),
						'taxable'          => array(
							'type' => 'boolean',
						),
						'calculation_base' => array(
							'type' => 'string',
						),
						'methods'          => array(
							'type' => 'array',
						),
						'regions'          => array(
							'type' => 'array',
						),
					),
				),
			),
		);
	}
}
