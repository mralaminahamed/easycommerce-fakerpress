<?php
/**
 * Coupon Generator REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\REST\Controllers
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Coupon_Generator;

/**
 * Coupon Generator REST Controller
 *
 * Handles REST API endpoints for coupon generation
 *
 * @since 1.0.0
 */
class Coupon_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'coupons';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Coupon_Generator Generator instance.
	 */
	protected function get_generator_instance(): Coupon_Generator {
		return new Coupon_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'coupon';
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
			'discount_types' => array(
				'description'       => __( 'Types of discount coupons to generate', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'percentage', 'fixed_amount', 'free_shipping', 'buy_x_get_y' ),
				),
				'default'           => array( 'percentage', 'fixed_amount' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'discount_range' => array(
				'description' => __( 'Discount value range', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min_percentage' => array(
						'type'    => 'integer',
						'minimum' => 5,
						'maximum' => 95,
						'default' => 10,
					),
					'max_percentage' => array(
						'type'    => 'integer',
						'minimum' => 5,
						'maximum' => 95,
						'default' => 50,
					),
					'min_fixed' => array(
						'type'    => 'number',
						'minimum' => 1,
						'default' => 5,
					),
					'max_fixed' => array(
						'type'    => 'number',
						'minimum' => 1,
						'default' => 100,
					),
				),
			),
			'usage_limits' => array(
				'description' => __( 'Usage limitation settings', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'set_usage_limits' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'max_uses' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 1000,
						'default' => 100,
					),
					'max_uses_per_user' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 10,
						'default' => 1,
					),
				),
			),
			'validity_period' => array(
				'description' => __( 'Coupon validity period configuration', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min_days' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 365,
						'default' => 7,
					),
					'max_days' => array(
						'type'    => 'integer',
						'minimum' => 1,
						'maximum' => 365,
						'default' => 90,
					),
				),
			),
			'restrictions' => array(
				'description' => __( 'Coupon usage restrictions', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'minimum_spend' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'maximum_spend' => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'exclude_sale_items' => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'product_restrictions' => array(
						'type'    => 'boolean',
						'default' => true,
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
			'coupons' => array(
				'description' => __( 'Generated coupons data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'          => array(
							'type' => 'integer',
						),
						'code'        => array(
							'type' => 'string',
						),
						'discount'    => array(
							'type' => 'string',
						),
						'type'        => array(
							'type' => 'string',
						),
						'expiry_date' => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
