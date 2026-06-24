<?php
/**
 * Transaction REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
use EasyCommerceFakerPress\Abstracts\Controller;
use EasyCommerceFakerPress\Generators\Transaction as TransactionGenerator;

/**
 * Transaction REST Controller Class
 *
 * Handles REST API endpoints for transaction generation
 *
 * @since 1.0.0
 */
class Transaction extends Controller {

	/**
	 * Get resource type name
	 *
	 * @since 1.0.0
	 *
	 * @return string Resource type.
	 */
	protected function get_resource_type(): string {
		return 'transaction';
	}

	/**
	 * Get resource type label for transactions
	 *
	 * @since 1.0.0
	 *
	 * @return string The translated label for transaction resource type.
	 */
	protected function get_resource_type_label(): string {
		return __( 'Transaction', 'easycommerce-fakerpress' );
	}

	/**
	 * Get REST base for the endpoint
	 *
	 * @since 1.0.0
	 *
	 * @return string REST base.
	 */
	protected function get_rest_base(): string {
		return 'transactions';
	}

	/**
	 * Get generator instance
	 *
	 * @since 1.0.0
	 *
	 * @return TransactionGenerator Generator instance.
	 */
	protected function get_generator_instance(): TransactionGenerator {
		return new TransactionGenerator();
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
				'description'       => __( 'Type of customers for transactions.', 'easycommerce-fakerpress' ),
				'type'              => 'string',
				'enum'              => array( 'all', 'specific', 'existing_customers_only', 'new_customers_only' ),
				'default'           => 'all',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'specific_customer_id' => array(
				'description'       => __( 'Specific customer ID for transactions (when customer_type is "specific").', 'easycommerce-fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'order_status_filter'  => array(
				'description'       => __( 'Filter orders by status for transaction generation.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'pending', 'processing', 'completed', 'cancelled', 'on_hold', 'refunded' ),
				),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'transaction_types'    => array(
				'description'       => __( 'Types of transactions to generate.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'payment', 'refund', 'adjustment', 'fee', 'commission' ),
				),
				'default'           => array( 'payment', 'refund' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'payment_gateways'     => array(
				'description'       => __( 'Payment gateways to use for transactions.', 'easycommerce-fakerpress' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'string',
					'enum' => array( 'stripe', 'paypal', 'square', 'authorize_net', 'braintree', 'razorpay', 'mollie' ),
				),
				'default'           => array( 'stripe', 'paypal', 'square' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'date_range'           => array(
				'description' => __( 'Date range for transaction generation.', 'easycommerce-fakerpress' ),
				'type'        => 'object',
				'properties'  => array(
					'start' => array(
						'description' => __( 'Start date (YYYY-MM-DD format).', 'easycommerce-fakerpress' ),
						'type'        => 'string',
						'format'      => 'date',
					),
					'end'   => array(
						'description' => __( 'End date (YYYY-MM-DD format).', 'easycommerce-fakerpress' ),
						'type'        => 'string',
						'format'      => 'date',
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
			'transactions' => array(
				'description' => __( 'Generated payment transactions with realistic data.', 'easycommerce-fakerpress' ),
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
						'customer_id'     => array(
							'type' => 'integer',
						),
						'transaction_id'  => array(
							'type' => 'string',
						),
						'payment_gateway' => array(
							'type' => 'string',
						),
						'amount'          => array(
							'type' => 'number',
						),
						'currency'        => array(
							'type' => 'string',
						),
						'status'          => array(
							'type' => 'string',
						),
						'type'            => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	}
}
