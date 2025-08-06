<?php
/**
 * Transaction REST Controller
 *
 * @since   1.0.0
 * @package EasyCommerceFakerPress\Controllers
 */

namespace EasyCommerceFakerPress\Controllers;

use EasyCommerceFakerPress\Abstracts\REST_Controller;
use EasyCommerceFakerPress\Generators\Transaction_Generator;

/**
 * Transaction REST Controller Class
 *
 * Handles REST API endpoints for transaction generation
 *
 * @since 1.0.0
 */
class Transaction_REST_Controller extends REST_Controller {

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
	 * @return Generator Generator instance.
	 */
	protected function get_generator_instance(): Transaction_Generator {
		return new Transaction_Generator();
	}

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
