<?php
/**
 * Refund Generator REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Refund as RefundGenerator;

/**
 * Refund Generator REST Controller
 *
 * Handles REST API endpoints for refund generation
 *
 * @since 1.0.0
 */
class Refund extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'refund';
	}

	/**
	 * Get resource type label for refunds
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for refund resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Refund', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'refunds';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return RefundGenerator Generator instance.
	 */
	protected function get_generator_instance(): RefundGenerator {
		return new RefundGenerator();
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
			'order_statuses'   => array(
				'description'       => __( 'Order statuses eligible for refund generation', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'completed', 'processing', 'pending', 'cancelled' ),
				),
				'default'           => array( 'completed', 'processing' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'payment_gateways' => array(
				'description'       => __( 'Payment gateways to use for generated refunds', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'stripe', 'paypal', 'square', 'bank_transfer', 'authorize_net' ),
				),
				'default'           => array( 'stripe', 'paypal', 'square' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
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
			'refunds' => array(
				'description' => __( 'Generated refunds data.', 'easycommerce-fakerpress' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'              => array(
							'type' => 'integer',
						),
						'order_id'        => array(
							'type' => 'integer',
						),
						'amount'          => array(
							'type' => 'number',
						),
						'status'          => array(
							'type' => 'string',
						),
						'payment_gateway' => array(
							'type' => 'string',
						),
						'currency'        => array( 'type' => 'string' ),
						'reason'          => array( 'type' => 'string' ),
						'type'            => array( 'type' => 'string' ),
						'transaction_id'  => array( 'type' => 'string' ),
					),
				),
			),
		);
	}
}
