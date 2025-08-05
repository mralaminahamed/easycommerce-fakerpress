<?php
/**
 * Cart Session REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\REST\Controllers
 */

namespace EasyCommerceFakerPress\REST\Controllers;

use EasyCommerceFakerPress\Abstracts\Generator;
use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Cart_Session_Generator;

/**
 * Cart Session REST Controller Class
 *
 * Handles REST API endpoints for cart session generation
 *
 * @since 1.0.0
 */
class Cart_Session_REST_Controller extends REST_Controller {

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'cart-sessions';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return Generator Generator instance.
	 */
	protected function get_generator_instance(): Cart_Session_Generator {
		return new Cart_Session_Generator();
	}

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'cart_session';
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
			'cart_sessions' => array(
				'description' => __( 'Generated cart sessions with items and customer data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'hash'           => array(
							'type' => 'string',
						),
						'user_id'        => array(
							'type' => 'integer',
						),
						'status'         => array(
							'type' => 'string',
						),
						'items_count'    => array(
							'type' => 'integer',
						),
						'total_amount'   => array(
							'type' => 'number',
						),
						'customer_email' => array(
							'type' => 'string',
						),
						'customer_name'  => array(
							'type' => 'string',
						),
						'reminders'      => array(
							'type' => 'integer',
						),
						'created_at'     => array(
							'type' => 'string',
						),
						'updated_at'     => array(
							'type' => 'string',
						),
						'items'          => array(
							'type' => 'object',
						),
						'addresses'      => array(
							'type' => 'object',
						),
					),
				),
			),
		);
	}
}
