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
