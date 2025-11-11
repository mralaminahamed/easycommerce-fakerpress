<?php
/**
 * Order Generator REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Order as OrderGenerator;

/**
 * Order Generator REST Controller
 *
 * Handles REST API endpoints for order generation
 *
 * @since 1.0.0
 */
class Order extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'order';
	}

	/**
	 * Get resource type label for orders
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for order resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Order', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'orders';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return OrderGenerator Generator instance.
	 */
	protected function get_generator_instance(): OrderGenerator {
		return new OrderGenerator();
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
			'order_status'              => array(
				'description'       => __( 'Order status distribution.', 'easycommerce-fakerpress' ),
				'type'              => 'string',
				'enum'              => array(
					'pending',
					'processing',
					'completed',
					'cancelled',
					'on_hold',
					'refunded',
					'mixed',
				),
				'default'           => 'mixed',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'customer_type'             => array(
				'description'       => __( 'Type of customers for orders.', 'easycommerce-fakerpress' ),
				'type'              => 'string',
				'enum'              => array( 'existing', 'new', 'mixed', 'specific' ),
				'default'           => 'mixed',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'specific_customer_id'      => array(
				'description'       => __( 'Specific customer ID to use for all orders (when customer_type is "specific").', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'customer_distribution'     => array(
				'description' => __( 'Customer type distribution for mixed mode.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'existing_ratio' => array(
						'description' => __( 'Percentage of existing customers (0-100).', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
						'default'     => 70,
					),
					'new_ratio'      => array(
						'description' => __( 'Percentage of new customers (0-100).', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 0,
						'maximum'     => 100,
						'default'     => 30,
					),
				),
			),
			'order_value'               => array(
				'description' => __( 'Order value configuration.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min_total' => array(
						'description' => __( 'Minimum order total.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 0,
						'default'     => 10,
					),
					'max_total' => array(
						'description' => __( 'Maximum order total.', 'easycommerce-fakerpress' ),
						'type'        => 'number',
						'minimum'     => 1,
						'default'     => 1000,
					),
				),
			),
			'items_per_order'           => array(
				'description' => __( 'Number of items per order.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'min' => array(
						'description' => __( 'Minimum items per order.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'default'     => 1,
					),
					'max' => array(
						'description' => __( 'Maximum items per order.', 'easycommerce-fakerpress' ),
						'type'        => 'integer',
						'minimum'     => 1,
						'maximum'     => 20,
						'default'     => 5,
					),
				),
			),
			'payment_methods'           => array(
				'description'       => __( 'Payment methods to use.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'stripe', 'paypal', 'bank_transfer', 'cash_on_delivery', 'credit_card' ),
				),
				'default'           => array( 'stripe', 'paypal', 'bank_transfer' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'geographical_distribution' => array(
				'description' => __( 'Geographic distribution of orders.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'countries' => array(
						'description'       => __( 'Countries to generate orders from.', 'easycommerce-fakerpress' ),
						'type'              => 'array',
						'items'             => array(
							'type' => 'string',
							'enum' => array( 'US', 'CA', 'GB', 'AU', 'DE', 'FR' ),
						),
						'default'           => array( 'US', 'CA', 'GB' ),
						'sanitize_callback' => array( $this, 'sanitize_array' ),
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
			'orders' => array(
				'description' => __( 'Generated orders data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'type' => 'integer',
						),
						'customer' => array(
							'type' => 'string',
						),
						'total'    => array(
							'type' => 'string',
						),
						'status'   => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
