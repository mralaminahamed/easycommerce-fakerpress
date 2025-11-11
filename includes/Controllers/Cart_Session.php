<?php
/**
 * Cart Session REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Cart_Session as CartSessionGenerator;

/**
 * Cart Session REST Controller Class
 *
 * Handles REST API endpoints for cart session generation
 *
 * @since 1.0.0
 */
class Cart_Session extends Controller {

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
	 * Get resource type label for cart sessions
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for cart session resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Cart Session', 'easycommerce-fakerpress' );
	}

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
	 * @return CartSessionGenerator Generator instance.
	 */
	protected function get_generator_instance(): CartSessionGenerator {
		return new CartSessionGenerator();
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
			'customer_type'        => array(
				'description'       => __( 'Type of customers for cart sessions.', 'easycommerce-fakerpress' ),
				'type'              => 'string',
				'enum'              => array( 'existing', 'new', 'mixed', 'specific', 'guest_only' ),
				'default'           => 'mixed',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'specific_customer_id' => array(
				'description'       => __( 'Specific customer ID for cart sessions (when customer_type is "specific").', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'guest_cart_ratio'     => array(
				'description'       => __( 'Percentage of guest carts (0-100) when customer_type is "mixed".', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 0,
				'maximum'           => 100,
				'default'           => 40,
				'sanitize_callback' => 'absint',
			),
			'abandonment_rate'     => array(
				'description'       => __( 'Cart abandonment rate percentage (0-100).', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 0,
				'maximum'           => 100,
				'default'           => 30,
				'sanitize_callback' => 'absint',
			),
			'status_distribution'  => array(
				'description' => __( 'Custom cart status distribution.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'pending'   => array(
						'description' => __( 'Percentage of pending carts.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
					),
					'abandoned' => array(
						'description' => __( 'Percentage of abandoned carts.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
					),
					'completed' => array(
						'description' => __( 'Percentage of completed carts.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
					),
					'cancelled' => array(
						'description' => __( 'Percentage of cancelled carts.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
					),
				),
			),
			'cart_value_range'     => array(
				'description' => __( 'Cart value range for generated sessions.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min' => array(
						'description' => __( 'Minimum cart value.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 0,
						'default'     => 5,
					),
					'max' => array(
						'description' => __( 'Maximum cart value.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 1,
						'default'     => 500,
					),
				),
			),
			'items_per_cart'       => array(
				'description' => __( 'Number of items per cart session.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min' => array(
						'description' => __( 'Minimum items per cart.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'default'     => 1,
					),
					'max' => array(
						'description' => __( 'Maximum items per cart.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'maximum'     => 15,
						'default'     => 5,
					),
				),
			),
			'abandonment_tracking' => array(
				'description' => __( 'Abandonment tracking settings.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'generate_reminders' => array(
						'description' => __( 'Generate abandoned cart reminders.', 'easycommerce-fakerpress' ),
						'type'        => 'boolean',
						'default'     => true,
					),
					'reminder_count'     => array(
						'description' => __( 'Maximum number of reminders to generate.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 10,
						'default'     => 3,
					),
					'recovery_rate'      => array(
						'description' => __( 'Cart recovery rate percentage (0-100).', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
						'default'     => 15,
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
